<?php

namespace App\Models;
use PDO;
use App\Config\Database;
use Ramsey\Uuid\Uuid;


class Post {
    private static $table = 'posts';

    private static function validate($postData, $categoryData) {
        $data = [...$postData, ...$categoryData];
        if (!isset($data['user_id']) || !isset($data['title']) || !isset($data['content']) || !isset($data['category_id'])) {
            throw new \InvalidArgumentException("Missing required fields: user_id, title, content, category_id");
        }
        return;
    }
    
    public static function find($id) {
        $db = Database::getConnection();
        $query = 'SELECT p.*, c.name as category 
                  FROM posts p 
                  LEFT JOIN post_categories pc ON p.id = pc.post_id 
                  LEFT JOIN categories c ON pc.category_id = c.id
                  WHERE p.id = :post_id';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':post_id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAll() {
        $db = Database::getConnection();
        $query = 'SELECT p.*, c.name as category 
                  FROM posts p 
                  LEFT JOIN post_categories pc ON p.id = pc.post_id 
                  LEFT JOIN categories c ON pc.category_id = c.id';
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPostsByCategory($category_id) {
        $db = Database::getConnection();
        $query = 'SELECT p.*, c.name as category 
                  FROM posts p 
                  LEFT JOIN post_categories pc ON p.id = pc.post_id 
                  LEFT JOIN categories c ON pc.category_id = c.id
                  WHERE c.id = :category_id';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPostsByUser($user_id) {
        $db = Database::getConnection();
        $query = 'SELECT p.*
                  FROM posts p 
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE u.id = :user_id';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($postData, $categoryData) {
        self::validate($postData, $categoryData);
        $db = Database::getConnection();
        
        // Generate a UUID
        $uuid = Uuid::uuid4();
        $postId = $uuid->toString();
    
        try {
            $db->beginTransaction();
    
            $query = 'INSERT INTO ' . self::$table . ' (id, user_id, title, content) VALUES (:id, :user_id, :title, :content)';
            $stmt = $db->prepare($query);
            // Include the UUID in the data to insert
            $postData['id'] = $postId;
            $stmt->execute($postData);
    
            // Insert post.id and category.id in the junction table
            $query = 'INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)';
            $stmt = $db->prepare($query);
            $result = $stmt->execute([
                'post_id' => $postId,
                'category_id' => $categoryData['category_id']
            ]);
            // Commit the changes previously made in the database
            $db->commit();
    
            return $result;
        } catch(\PDOException $e) {
            // if anything goes wrong, we catch the exception, roll back the transaction, and then throw the exception.
            $db->rollback();
            throw $e;
        }
    }

    public static function update($id, $postData, $categoryId) {
        $db = Database::getConnection();
        
        try {
            // Start the transaction
            $db->beginTransaction();
    
            // Update the post in the posts table
            $updateQuery = 'UPDATE ' . self::$table . ' SET updated_at = NOW(), ';
            $updateData = [];

            foreach($postData as $key => $value){
                $updateQuery .= $key . ' = :' . $key . ', ';
                // Named parameters help prevent SQL injection attacks
                $updateData[':'.$key] = $value;
            }

            // rtrim function removes the trailing comma from the SQL query 
            $updateQuery = rtrim($updateQuery, ', ');
            $updateQuery .= ' WHERE id = :id';
            $updateData[':id'] = $id;

            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute($updateData);
            
            if ($categoryId) {
                // Update the category in the post_categories table
                $deleteQuery = 'DELETE FROM post_categories WHERE post_id = :post_id';
                $deleteStmt = $db->prepare($deleteQuery);
                $deleteStmt->execute(['post_id' => $id]);
        
                $insertQuery = 'INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)';
                $insertStmt = $db->prepare($insertQuery);
                $insertStmt->execute(['post_id' => $id, 'category_id' => $categoryId]);
            }    

            // Commit the transaction
            $db->commit();
        } catch (\Exception $e) {
            // An error occurred; rollback the transaction
            $db->rollback();
    
            // Rethrow the exception
            throw $e;
        }
    }
    

    public static function delete($id) {
        // INTEGRITY PROBLEM !!!!!!
        $db = Database::getConnection();
        $query = 'DELETE FROM ' . self::$table . ' WHERE id = :id';
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $id]);
    }

    public static function attachCategory($postId, $categoryId) {
        $db = Database::getConnection();
        $query = 'INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)';
        $stmt = $db->prepare($query);
        $stmt->execute(['post_id' => $postId, 'category_id' => $categoryId]);
    }    
}

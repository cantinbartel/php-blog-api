<?php

namespace App\Models;
use PDO;
use App\Config\Database;
use Ramsey\Uuid\Uuid;


class Post {
    private static $table = 'posts';

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

    public static function create($postData, $categoryData) {
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
            $stmt->execute([
                'post_id' => $postId,
                'category_id' => $categoryData['category_id']
            ]);
            // Commit the changes previously made in the database
            $db->commit();
    
            return $postId;
        } catch(\PDOException $e) {
            // if anything goes wrong, we catch the exception, roll back the transaction, and then rethrow the exception.
            $db->rollback();
            throw $e;
        }
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $query = 'UPDATE ' . self::$table . ' SET user_id = :user_id, title = :title, body = :body WHERE id = :id';
        $data['id'] = $id;
        $stmt = $db->prepare($query);
        $stmt->execute($data);
    }

    public static function delete($id) {
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

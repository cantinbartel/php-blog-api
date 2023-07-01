<?php

namespace App\Models;
use PDO;
use Ramsey\Uuid\Uuid;
use App\Config\Database;

class User {
    private static $table = 'users';

    private static function validate($data) {
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['name']) || !isset($data['firstname'])) {
            throw new \InvalidArgumentException("Missing required fields: email, password, name, firstname");
        }
        return;
    }

    public static function find($id) {
        $db = Database::getConnection();
        $query = 'SELECT * FROM ' . self::$table . ' WHERE id = ? LIMIT 0,1';
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        // PDO::FETCH_ASSOC returns the data in an associative array.
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAll() {
        $db = Database::getConnection();
        $query = 'SELECT * FROM ' . self::$table;
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        self::validate($data);
        $db = Database::getConnection();
        $uuid = Uuid::uuid4();
        $query = 'INSERT INTO ' . self::$table . ' (id, email, password, name, firstname, role) VALUES (:id, :email, :password, :name, :firstname, :role)';
        if (!isset($data['role'])) {
            $data['role'] = 'USER';
        } 
        $data['id'] = $uuid->toString();   
        $stmt = $db->prepare($query);
        $result = $stmt->execute($data);
        return $result;
    }

    public static function update($id, $data) {
        // initialize our database connection
        $db = Database::getConnection();
        $query = 'UPDATE ' . self::$table . ' SET updated_at = NOW(), ';
        $updateData = [];
    
        foreach($data as $key => $value){
            $query .= $key . ' = :' . $key . ', ';
            // named parameters help prevent SQL injection attacks
            $updateData[':'.$key] = $value;
        }
        // rtrim function removes the trailing comma from our SQL query 
        $query = rtrim($query, ', ');
        $query .= ' WHERE id = :id';
        $updateData[':id'] = $id;
    
        $stmt = $db->prepare($query);
        $stmt->execute($updateData);
    }
    

    public static function delete($id) {
        $db = Database::getConnection();
        $query = 'DELETE FROM ' . self::$table . ' WHERE id = :id';
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $id]);
    }

    public static function findByEmail($email) {
        $db = Database::getConnection();
        $query = 'SELECT * FROM ' . self::$table . " WHERE email = ? LIMIT 0,1";
        $stmt = $db->prepare($query);
        $stmt->execute([$email]);
        // PDO::FETCH_ASSOC returns the data in an associative array.
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }    
}

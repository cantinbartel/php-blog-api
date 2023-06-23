<?php

namespace App\Models;
use PDO;
use App\Config\Database;

class User {
    private static $table = 'users';

    public static function find($id) {
        $db = Database::getConnection();
        $query = 'SELECT * FROM ' . self::$table . ' WHERE id = ? LIMIT 0,1';
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
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
        $db = Database::getConnection();
        $query = 'INSERT INTO ' . self::$table . ' (email, password, name, firstname, role) VALUES (:email, :password, :name, :firstname, :role)';
        if (!isset($data['role'])) {
            $data['role'] = 'USER';
        }    
        $stmt = $db->prepare($query);
        $stmt->execute($data);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        // initialize our database connection
        $db = Database::getConnection();
        $query = 'UPDATE ' . self::$table . ' SET ';
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
}

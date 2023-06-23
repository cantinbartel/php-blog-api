<?php

namespace App\Models;
use PDO;
use App\Config\Database;

class Post {
    private static $table = 'posts';

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
        $query = 'INSERT INTO ' . self::$table . ' (user_id, title, body) VALUES (:user_id, :title, :body)';
        $stmt = $db->prepare($query);
        $stmt->execute($data);
        return $db->lastInsertId();
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
}

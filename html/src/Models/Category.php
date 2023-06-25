<?php

namespace App\Models;
use PDO;
use Ramsey\Uuid\Uuid;
use App\Config\Database;

class Category {
    private static $table = 'categories';

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
        $uuid = Uuid::uuid4();
        $query = 'INSERT INTO ' . self::$table . ' (id, name) VALUES (:id, :name)';
        $data['id'] = $uuid->toString();
        $stmt = $db->prepare($query);
        $stmt->execute($data);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $query = 'UPDATE ' . self::$table . ' SET name = :name WHERE id = :id';
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

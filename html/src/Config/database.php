<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $host = "db";
    private static $dbname;
    private static $username;
    private static $password;
    private static $conn = null;

    // Private constructor to prevent direct object creation in order to follow the singleton pattern
    private function __construct() {}

    // get the database connection
    public static function getConnection(): PDO {
        self::$dbname = $_ENV['MYSQL_DATABASE'];
        self::$username = $_ENV['MYSQL_USER'];
        self::$password = $_ENV['MYSQL_PASSWORD'];

        if (self::$conn == null) {
            try {
                self::$conn = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$dbname, self::$username, self::$password);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $exception) {
                echo "Connection error: " . $exception->getMessage();
                die();  // stop the execution
            }
        }
    
        return self::$conn;
    }    
}

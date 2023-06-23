<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $host = "db";
    private static $dbname;
    private static $username;
    private static $password;
    private static $conn;

    // get the database connection
    public static function getConnection() {
        self::$dbname = 'blog_api';
        self::$username = 'root';
        self::$password = 'rootpassword';
        self::$conn = null;

        print(getenv('MYSQL_DATABASE'));
        print(getenv('MYSQL_USER'));
        print(getenv('MYSQL_PASSWORD'));

        try {
            self::$conn = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$dbname, self::$username, self::$password);
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die();  // stop the execution
        }
    
        return self::$conn;
    }    
}

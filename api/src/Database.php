<?php

namespace App;

use PDO;
use PDOException;

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $host = $_ENV['DB_HOST'] ?? '';
            $db_name = $_ENV['DB_NAME'] ?? '';
            $username = $_ENV['DB_USERNAME'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $port = $_ENV['DB_PORT'] ?? '3306';

            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$db_name}";
                self::$connection = new PDO($dsn, $username, $password);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->exec("set names utf8");
            } catch (PDOException $e) {
                // Log exception silently in production
                error_log("Database connection error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(["error" => "An internal exception occurred during connection."]);
                exit();
            }
        }
        return self::$connection;
    }
}

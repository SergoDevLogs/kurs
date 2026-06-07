<?php
// src/config/database.php

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "pgsql:host=localhost;dbname=store_db",
                "postgres",
                null, // null вместо пустой строки
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            $this->pdo->exec("SET NAMES 'UTF8'");
        } catch (PDOException $e) {
            // Если не получилось - пробуем следующий вариант
            $this->fallbackConnection();
        }
    }
    
    private function fallbackConnection() {
        try {
            $this->pdo = new PDO(
                "pgsql:host=localhost;port=5432;dbname=store_db",
                "postgres",
                "postgres",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            try {
                $this->pdo = new PDO(
                    "pgsql:host=localhost;dbname=store_db",
                    null,
                    null,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e2) {
                die("Connection failed: " . $e->getMessage() . "\n" . $e2->getMessage());
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

function getDB() {
    return Database::getInstance()->getConnection();
}

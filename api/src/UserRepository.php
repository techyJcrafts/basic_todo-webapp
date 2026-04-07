<?php

namespace App;

use PDO;
use PDOException;

class UserRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(string $username, string $password): bool {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $this->db->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password_hash", $passwordHash);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Usually indicates duplicate entry
            return false;
        }
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }

    public function verify(string $username, string $password): ?array {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }
}

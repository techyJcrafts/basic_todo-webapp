<?php

namespace App;

use PDO;

class TaskRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findAllByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findByIdAndUser(int $id, int $userId): ?array {
        $fetchStmt = $this->db->prepare("SELECT * FROM tasks WHERE id = :id AND user_id = :user_id");
        $fetchStmt->bindParam(":id", $id, PDO::PARAM_INT);
        $fetchStmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $fetchStmt->execute();
        $task = $fetchStmt->fetch(PDO::FETCH_ASSOC);
        return $task ?: null;
    }

    public function create(array $data, int $userId): ?array {
        $stmt = $this->db->prepare("INSERT INTO tasks (title, description, reminder_time, user_id) VALUES (:title, :description, :reminder_time, :user_id)");
        
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindValue(":description", $data['description'] ?? null);
        $stmt->bindValue(":reminder_time", $data['reminder_time'] ?? null);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->findByIdAndUser((int)$this->db->lastInsertId(), $userId);
        }
        return null;
    }

    public function updateDetails(int $id, array $data, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE tasks SET title = :title, description = :description WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindValue(":description", $data['description'] ?? null);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateCompletion(int $id, int $completed, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE tasks SET completed = :completed WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(":completed", $completed, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

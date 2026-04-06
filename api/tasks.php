<?php
include_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $conn->prepare("SELECT * FROM tasks ORDER BY created_at DESC");
        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tasks);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->title)) {
            $stmt = $conn->prepare("INSERT INTO tasks (title, description, reminder_time) VALUES (:title, :description, :reminder_time)");

            $stmt->bindParam(":title", $data->title);
            $stmt->bindValue(":description", isset($data->description) ? $data->description : null);
            $stmt->bindValue(":reminder_time", isset($data->reminder_time) ? $data->reminder_time : null);

            if ($stmt->execute()) {
                $id = $conn->lastInsertId();
                $fetchStmt = $conn->prepare("SELECT * FROM tasks WHERE id = :id");
                $fetchStmt->bindParam(":id", $id);
                $fetchStmt->execute();
                echo json_encode($fetchStmt->fetch(PDO::FETCH_ASSOC));
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create task."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if (isset($data->title)) {
                $stmt = $conn->prepare("UPDATE tasks SET title = :title, description = :description WHERE id = :id");
                $stmt->bindParam(":title", $data->title);
                $stmt->bindValue(":description", isset($data->description) ? $data->description : null);
                $stmt->bindParam(":id", $id);
            } else if (isset($data->completed)) {
                $stmt = $conn->prepare("UPDATE tasks SET completed = :completed WHERE id = :id");
                $stmt->bindParam(":completed", $data->completed, PDO::PARAM_INT);
                $stmt->bindParam(":id", $id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data for update."]);
                break;
            }

            if ($stmt->execute()) {
                echo json_encode(["message" => "Task updated."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update task."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->bindParam(":id", $id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Task deleted."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete task."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        break;
}
?>
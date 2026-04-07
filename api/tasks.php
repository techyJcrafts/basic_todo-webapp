<?php
require_once 'db.php';
use App\TaskRepository;

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized. Please log in."]);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$taskRepo = new TaskRepository($conn);

switch ($method) {
    case 'GET':
        echo json_encode($taskRepo->findAllByUser($user_id));
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['title'])) {
            $task = $taskRepo->create($data, $user_id);
            if ($task) {
                http_response_code(201);
                echo json_encode($task);
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
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $success = false;

            if (isset($data['title'])) {
                $success = $taskRepo->updateDetails($id, $data, $user_id);
            } else if (isset($data['completed'])) {
                $success = $taskRepo->updateCompletion($id, (int)$data['completed'], $user_id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data for update."]);
                exit;
            }

            if ($success) {
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
            $id = (int)$_GET['id'];
            if ($taskRepo->delete($id, $user_id)) {
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
}
?>
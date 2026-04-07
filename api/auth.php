<?php
require_once 'db.php';
use App\UserRepository;

$userRepo = new UserRepository($conn);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'signup':
        if ($method !== 'POST') exit;
        $data = json_decode(file_get_contents("php://input"));
        $username = trim($data->username ?? '');
        $password = $data->password ?? '';

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(["message" => "Username and password required."]);
            exit;
        }

        if ($userRepo->create($username, $password)) {
            echo json_encode(["message" => "Account created successfully! Please log in."]);
        } else {
            http_response_code(409);
            echo json_encode(["message" => "Username already exists."]);
        }
        break;

    case 'login':
        if ($method !== 'POST') exit;
        $data = json_decode(file_get_contents("php://input"));
        $username = trim($data->username ?? '');
        $password = $data->password ?? '';

        $user = $userRepo->verify($username, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(["message" => "Logged in successfully!", "username" => $user['username']]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Invalid username or password."]);
        }
        break;

    case 'logout':
        session_destroy();
        echo json_encode(["message" => "Logged out."]);
        break;

    case 'session':
        if (isset($_SESSION['user_id'])) {
            echo json_encode(["authenticated" => true, "username" => $_SESSION['username']]);
        } else {
            echo json_encode(["authenticated" => false]);
        }
        break;
}
?>

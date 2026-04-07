<?php
// Initialize system and auto-connect to DB
require_once 'db.php';

try {
    // Perform a lightweight query to ensure MySQL connection doesn't go cold
    $stmt = $conn->query("SELECT 1");
    
    if ($stmt) {
        http_response_code(200);
        echo json_encode([
            "service" => "online", 
            "database" => "connected", 
            "timestamp" => date('c')
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "service" => "degraded", 
            "database" => "query_failed",
            "timestamp" => date('c')
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Health check failed"]);
}
?>

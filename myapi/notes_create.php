<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->user_id) && !empty($data->title) && !empty($data->content)) {
    
    // 1. SECURITY CHECK: Is user Banned?
    $checkQuery = "SELECT status FROM users WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute([$data->user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['status'] === 'banned') {
        http_response_code(403); // Forbidden
        echo json_encode(["status" => "error", "message" => "You are BANNED. You cannot publish notes."]);
        exit;
    }

    // 2. If Active, Save the Note
    $query = "INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    if($stmt->execute([$data->user_id, $data->title, $data->content])) {
        echo json_encode(["status" => "success", "message" => "Note published successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}
?>
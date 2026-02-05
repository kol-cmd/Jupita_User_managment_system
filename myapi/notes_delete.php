<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->note_id) && !empty($data->user_id)) {
    // 1. Get the note's owner and the requester's role
    $check = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
    $check->execute([$data->note_id]);
    $note = $check->fetch(PDO::FETCH_ASSOC);

    $userCheck = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $userCheck->execute([$data->user_id]);
    $user = $userCheck->fetch(PDO::FETCH_ASSOC);

    if($note && $user) {
        // 2. LOGIC: You can delete if you own the note OR if you are an Admin
        if($note['user_id'] == $data->user_id || $user['role'] === 'admin') {
            $query = "DELETE FROM notes WHERE id = ?";
            $stmt = $conn->prepare($query);
            if($stmt->execute([$data->note_id])) {
                echo json_encode(["status" => "success", "message" => "Note deleted."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database error."]);
            }
        } else {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Permission denied."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Note not found."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}
?>
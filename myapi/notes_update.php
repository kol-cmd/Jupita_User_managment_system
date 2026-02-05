<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && !empty($data->user_id) && !empty($data->title) && !empty($data->content)) {
    
    // 1. Check Ownership
    $check = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
    $check->execute([$data->id]);
    $note = $check->fetch(PDO::FETCH_ASSOC);

    if($note && $note['user_id'] == $data->user_id) {
        // 2. Update
        $query = "UPDATE notes SET title = ?, content = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if($stmt->execute([$data->title, $data->content, $data->id])) {
            echo json_encode(["status" => "success", "message" => "Note updated."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Update failed."]);
        }
    } else {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "You can only edit your own notes."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}
?>
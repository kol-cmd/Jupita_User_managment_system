<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && !empty($data->status)) {
    // Swap status: If 'active' -> make 'banned', else make 'active'
    $newStatus = ($data->status === 'active') ? 'banned' : 'active';
    
    $query = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if($stmt->execute([$newStatus, $data->id])) {
        echo json_encode(["status" => "success", "new_status" => $newStatus]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}
?>
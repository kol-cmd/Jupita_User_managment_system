<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';


$query = "SELECT id, name, email, avatar, status, role, created_at FROM users ORDER BY id DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // FETCH_ASSOC ensures we get a clean JSON object
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
} catch(PDOException $e) {
    echo json_encode(["message" => "Error reading users."]);
}
?>
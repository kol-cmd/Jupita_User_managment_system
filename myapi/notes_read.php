<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once 'db.php';

// JOIN users table so we get the author's name and avatar, not just their ID
$query = "SELECT notes.*, users.name as author_name, users.avatar as author_avatar 
          FROM notes 
          JOIN users ON notes.user_id = users.id 
          ORDER BY notes.created_at DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(PDOException $e) {
    echo json_encode([]);
}
?>
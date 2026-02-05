<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

// We need the User ID to update, the New Data, AND the ID of the person making the request (admin_id)
if(!empty($data->id) && !empty($data->name) && !empty($data->email) && !empty($data->admin_id)) {
    
    // --- SECURITY CHECK ---
    // verify the requester is actually an admin
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->execute([$data->admin_id]);
    $requester = $check->fetch(PDO::FETCH_ASSOC);

    if(!$requester || $requester['role'] !== 'admin') {
        http_response_code(403); // Forbidden
        echo json_encode(["status" => "error", "message" => "Access Denied. Only Admins can edit users."]);
        exit();
    }
    // ----------------------

    try {
        if (!empty($data->password)) {
            // Update Name, Email, AND Password
            $query = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
            $result = $stmt->execute([$data->name, $data->email, $password_hash, $data->id]);
        } else {
            // Update Name and Email ONLY
            $query = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([$data->name, $data->email, $data->id]);
        }

        if($result) {
            echo json_encode(["status" => "success", "message" => "User updated."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Update failed."]);
        }
    } catch(PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            echo json_encode(["status" => "error", "message" => "Email already exists."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error."]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data or missing permissions."]);
}
?>
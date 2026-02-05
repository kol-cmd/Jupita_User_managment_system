<?php
// 1. HEADERS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle Preflight Options Request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)){
    try {
        // 2. QUERY (Select everything needed for the UI)
        $query = "SELECT id, name, email, password, avatar, role, status FROM users WHERE email = ? LIMIT 0,1";
        $stmt = $conn->prepare($query);
        
        // Fix: Use execute array for '?' placeholder
        $stmt->execute([$data->email]);

        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 3. VERIFY PASSWORD
            if(password_verify($data->password, $row['password'])){
                
                // Security: Remove the password hash before sending to frontend
                unset($row['password']);
                
                http_response_code(200);
                
                // Return the FULL user row so Vue knows the Role and Avatar
                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful",
                    "user" => $row 
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => "Invalid password."]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "User not found."]);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data."]);
}
?>
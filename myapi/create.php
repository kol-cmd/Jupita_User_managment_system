<?php
/**
 * API Endpoint: Create User (Registration)
 * ----------------------------------------
 * Accepts JSON data to register a new user.
 * Validates input, checks for duplicates, and hashes the password securely.
 */

// 1. HEADERS & CONFIGURATION
// --------------------------
// Turn off error display to client (prevents leaking stack traces), 
// but in a real app, ensure errors are logged to a file.
error_reporting(0); 

// CORS Configuration: Allow frontend to communicate with this backend
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle "Preflight" OPTIONS request (Browsers send this before POST)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database Connection
include_once 'db.php';

// 2. INPUT PROCESSING
// -------------------
// Decode the incoming JSON payload from the request body
$data = json_decode(file_get_contents("php://input"));

// 3. VALIDATION (GUARD CLAUSE PATTERN)
// ------------------------------------
// Check for missing required fields first. If missing, fail early.
if(empty($data->name) || empty($data->email) || empty($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Incomplete data. Name, Email, and Password are required."]);
    exit();
}

// Clean inputs (Basic sanitization to prevent XSS if displayed later)
$name = htmlspecialchars(strip_tags($data->name));
$email = htmlspecialchars(strip_tags($data->email));
$password = $data->password;
$avatar_path = isset($data->avatar) ? $data->avatar : null; // Avatar is optional

try {
    // 4. DUPLICATE CHECK
    // ------------------
    // Check if email already exists to enforce unique constraints
    $checkQuery = "SELECT id FROM users WHERE email = :email LIMIT 1";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([':email' => $email]);
    
    if($checkStmt->rowCount() > 0){
        http_response_code(409); // 409 Conflict (standard for duplicates)
        echo json_encode(["message" => "This email address is already registered."]);
        exit();
    }

    // 5. DATA INSERTION
    // -----------------
    // SECURITY NOTE: We use PASSWORD_DEFAULT (Bcrypt) to hash passwords.
    // We never store plain-text passwords.
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, avatar) VALUES (:name, :email, :pass, :avatar)";
    $stmt = $conn->prepare($sql);

    // Bind parameters using an array (cleaner syntax)
    $params = [
        ':name'   => $name,
        ':email'  => $email,
        ':pass'   => $password_hash,
        ':avatar' => $avatar_path
    ];

    if($stmt->execute($params)){
        // Success: 201 Created is the correct REST status for new resources
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "User account created successfully."]);
    } else {
        // Server Error: Database accepted query but failed to execute
        http_response_code(503); // Service Unavailable
        echo json_encode(["status" => "error", "message" => "Unable to create user. Please try again."]);
    }

} catch(PDOException $e) {
    // 6. ERROR HANDLING
    // -----------------
    // Catch database errors (like connection loss)
    http_response_code(500); // Internal Server Error
    // In production, log $e->getMessage() to a file, don't show it to the user.
    echo json_encode(["status" => "error", "message" => "Database error occurred."]);
}
?>
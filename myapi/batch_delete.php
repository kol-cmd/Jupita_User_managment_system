<?php
/**
 * API Endpoint: Batch User Delete
 * --------------------------------
 * This script handles the deletion of multiple users in a single database transaction.
 * It expects a JSON payload containing an array of user IDs.
 * * Usage: POST /myapi/batch_delete.php
 * Payload: { "ids": [1, 2, 3] }
 */

// Headers: Allow cross-origin requests (CORS) and define content type
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection configuration
include_once 'db.php';

// 1. Capture and decode the incoming JSON payload
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input);

// 2. Validate input: Ensure 'ids' exists and is actually an array
if (!empty($data->ids) && is_array($data->ids)) {
    
    try {
        /**
         * SECURITY NOTE: Preventing SQL Injection in an 'IN' clause.
         * * We cannot simply concatenate the IDs into the query string (e.g., "1,2,3")
         * because that creates an injection vulnerability.
         * * Instead, we dynamically generate a string of placeholders (?,?,?) 
         * matching the exact count of IDs in the array.
         */
        
        // Count how many IDs we need to delete
        $count = count($data->ids);
        
        // Create a placeholder string. If we have 3 IDs, this creates "?,?,?"
        $placeholders = implode(',', array_fill(0, $count, '?'));

        // Prepare the SQL statement using the dynamic placeholders
        $query = "DELETE FROM users WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($query);

        // 3. Execute the statement
        // We pass the actual array of IDs ($data->ids) directly to execute().
        // PDO automatically maps each array item to a '?' placeholder safely.
        if ($stmt->execute($data->ids)) {
            // Success response
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Selected users deleted successfully."]);
        } else {
            // Server error response (Database failed to execute)
            http_response_code(503);
            echo json_encode(["status" => "error", "message" => "Unable to process deletion request."]);
        }

    } catch (PDOException $e) {
        // Catch any connection or query errors
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Internal Database Error: " . $e->getMessage()]);
    }

} else {
    // Client error: Bad Request (Missing or invalid data)
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid request. No IDs provided."]);
}
?>
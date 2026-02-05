<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

// 1. Check if a file was actually sent
if(!isset($_FILES['avatar'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file received.']);
    exit;
}

$file = $_FILES['avatar'];

// 2. Check for PHP Upload Errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File is too large (Check php.ini).',
        UPLOAD_ERR_FORM_SIZE => 'File is too large.',
        UPLOAD_ERR_PARTIAL => 'File only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server temp folder missing.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload.'
    ];
    $msg = $errors[$file['error']] ?? 'Unknown upload error.';
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

// 3. Validate File Type
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if(!in_array($ext, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Use JPG, PNG, or WEBP.']);
    exit;
}

// 4. CHECK if "uploads" folder exists (Do NOT create it)
$uploadDir = __DIR__ . '/uploads/'; 

if (!is_dir($uploadDir)) {
    // If the folder is missing, we STOP here and return an error.
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error: The "uploads" folder does not exist. Please create it manually.']);
    exit;
}

// 5. Save the file
$newFilename = uniqid('user_', true) . '.' . $ext;
$destination = $uploadDir . $newFilename;

if(move_uploaded_file($file['tmp_name'], $destination)) {
    // Success! Return the public path
    echo json_encode([
        'status' => 'success', 
        'filepath' => 'myapi/uploads/' . $newFilename
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not move file to destination. Check folder permissions.']);
}
?>
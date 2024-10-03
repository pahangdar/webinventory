<?php
// Allow CORS in PHP files
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json');

// Define the directory where you want to save uploaded images
$targetDir = "uploads/images/";

// Ensure the directory exists, if not create it
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Check if a file was uploaded via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File upload error']);
        exit;
    }

    // Generate a unique name for the uploaded file to avoid conflicts
    $fileName = uniqid() . '-' . basename($file['name']);
    $targetFilePath = $targetDir . $fileName;

    // Attempt to move the uploaded file to the target directory
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // Return the file path for the client to store
        echo json_encode(['filePath' => $targetFilePath]);
    } else {
        echo json_encode(['error' => 'Failed to save the file.']);
    }
} else {
    echo json_encode(['error' => 'No file uploaded.']);
}
?>

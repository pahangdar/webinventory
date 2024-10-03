<?php
// Allow CORS in PHP files
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json');

// Check if a file path is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the file path from the request
    $data = json_decode(file_get_contents("php://input"), true);
    $filePath = isset($data['filePath']) ? $data['filePath'] : '';

    if ($filePath && file_exists($filePath)) {
        // Attempt to delete the file
        if (unlink($filePath)) {
            echo json_encode(['success' => true, 'message' => 'File deleted successfully.']);
        } else {
            echo json_encode(['error' => 'File could not be deleted.']);
        }
    } else {
        echo json_encode(['error' => 'File not found.', 'file' => $filePath]);
    }
} else {
    echo json_encode(['error' => 'Invalid request.']);
}
?>

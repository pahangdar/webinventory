<?php
// Allow CORS in PHP files
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json');
include 'config.php';

// Get the 'id' parameter from the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "ID is required"]);
    exit;
}

$response = array();

if ($id > 0) {
    // Prepare and execute the delete statement
    $sql = "DELETE FROM properties WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response['ok'] = true;
        $response['message'] = "Object deleted successfully";
    } else {
        if ($stmt->errno == 1451) { // Error code for foreign key constraint failure
            $response['ok'] = false;
            $response['message'] = "Cannot delete the object because it has child objects. Please remove the child objects first.";
        } else {
            $response['ok'] = false;
            $response['message'] = $stmt->error;
        }
    }

    $stmt->close();
} else {
    $response['ok'] = false;
    $response['message'] = "Invalid ID";
}

echo json_encode($response);

$conn->close();
?>

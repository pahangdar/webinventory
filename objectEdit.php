<?php
// Allow CORS in PHP files
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json');
include 'config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];  // ID of the object to be edited
$name = $data['name'];
$objecttype_id = $data['objecttype_id'];
$parentid = isset($data['parentid']) ? $data['parentid'] : null;

// Update object
$sql = "UPDATE objects SET objecttype_id = ?, name = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $objecttype_id, $name, $id);

$response = array();
if ($stmt->execute()) {
    $response['ok'] = true;
    $response['id'] = $id;  // Return the ID of the edited object
} else {
    $response['ok'] = false;
    $response['message'] = $stmt->error;
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>

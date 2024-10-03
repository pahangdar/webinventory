<?php
// Allow CORS in PHP files
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

header('Content-Type: application/json');
include 'config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'];
$propertytype_id = $data['propertytype_id'];
$values_list = isset($data['values_list']) ? $data['values_list'] : null;

// Insert new object
$sql = "INSERT INTO properties (propertytype_id, name, values_list) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $propertytype_id, $name, $values_list);

$response = array();
if ($stmt->execute()) {
    $response['ok'] = true;
    $response['id'] = $stmt->insert_id;
} else {
    $response['ok'] = false;
    $response['message'] = $stmt->error;
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>

<?php
// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

// Include your database configuration file
include 'config.php';

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

$objectId = isset($input['objectId']) ? $input['objectId'] : null;
$properties = isset($input['properties']) ? $input['properties'] : null;

if ($objectId === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Object ID is required"]);
    exit;
}

if ($properties === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Properties are required"]);
    exit;
}

foreach ($properties as $propertyId => $value) {
    // Check if the property exists for the object
    $checkQuery = "SELECT COUNT(*) FROM object_properties WHERE object_id = ? AND property_id = ?";
    $checkStatement = $conn->prepare($checkQuery);
    if (!$checkStatement) {
        echo json_encode(["error" => "Error preparing check query: " . $conn->error]);
        exit;
    }
    $checkStatement->bind_param('ii', $objectId, $propertyId);
    $checkStatement->execute();
    $checkStatement->bind_result($exists);
    $checkStatement->fetch();
    $checkStatement->close();

    if ($exists) {
        // Update if exists
        $query = "UPDATE object_properties SET value = ? WHERE object_id = ? AND property_id = ?";
    } else {
        // Insert if it does not exist
        $query = "INSERT INTO object_properties (value, object_id, property_id) VALUES (?, ?, ?)";
    }

    $statement = $conn->prepare($query);
    if (!$statement) {
        echo json_encode(["error" => "Error preparing main query: " . $conn->error]);
        exit;
    }

    if ($exists) {
        $statement->bind_param('sii', $value, $objectId, $propertyId);
    } else {
        $statement->bind_param('sii', $value, $objectId, $propertyId);
    }

    if (!$statement->execute()) {
        echo json_encode(["error" => "Error executing query: " . $statement->error]);
        exit;
    }
    $statement->close();
}

echo json_encode(['status' => 'success']);
$conn->close();
?>

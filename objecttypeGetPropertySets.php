<?php
// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'config.php';

$typeid = isset($_GET['typeid']) ? $_GET['typeid'] : null;

if ($typeid === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Type ID is required"]);
    exit;
}

try {
    // Query to get object types and their count
    $sql = "
        SELECT ps.id AS setId, ps.name as setName
        FROM objecttype_propertysets otp
        JOIN propertysets ps ON otp.propertyset_id = ps.id
        WHERE otp.objecttype_id = ?
        ORDER BY ps.id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $typeid);

    $stmt->execute();
    $result = $stmt->get_result();

    $propertySets = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $propertySets[] = $row;
        }
    }

    echo json_encode($propertySets);

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

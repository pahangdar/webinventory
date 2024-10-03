<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'config.php';

$objectid = isset($_GET['objectid']) ? $_GET['objectid'] : null;
$setid = isset($_GET['setid']) ? $_GET['setid'] : null;

if ($objectid === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Object ID is required"]);
    exit;
}

try {
    // Query to get object by id
    $sql = "
        SELECT ps.id AS setId, ps.name as setName, p.id, p.name, p.values_list, p.propertytype_id AS type, op.value FROM objecttype_propertysets otp
        JOIN propertysets ps ON otp.propertyset_id = ps.id
        JOIN propertyset_properties psp ON ps.id = psp.propertyset_id
        JOIN properties p on psp.property_id = p.id
        LEFT JOIN (SELECT * FROM object_properties WHERE object_id = ?) op ON p.id = op.property_id
        WHERE otp.objecttype_id = (SELECT o.objecttype_id FROM objects o WHERE o.id = ?)
    ";

    if ($setid !== null) {
        $sql .= " AND ps.id = ?";
    }
    $sql .= " ORDER BY ps.id, p.id";

    $stmt = $conn->prepare($sql);
    if ($setid === null) {
        $stmt->bind_param("ii", $objectid, $objectid);
    } else {
        $stmt->bind_param("iii", $objectid, $objectid, $setid);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $objects = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $objects[] = $row;
        }
    }

    echo json_encode($objects);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

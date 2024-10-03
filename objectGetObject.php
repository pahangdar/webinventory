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

if ($objectid === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Object ID is required"]);
    exit;
}

try {
    // Base SQL query
    $sql = "
        SELECT o.id, o.name, o.objecttype_id, o.parent_id, ot.name AS typeName, p.name as parentName, pot.name as parentTypeName
        FROM objects o 
        JOIN objecttypes ot ON o.objecttype_id = ot.id 
        LEFT JOIN objects p on o.parent_id = p.id
        LEFT JOIN objecttypes pot on p.objecttype_id = pot.id
        WHERE o.id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $objectid);

    $stmt->execute();
    $result = $stmt->get_result();

    // $objects = array();

    // if ($result->num_rows > 0) {
    //     while ($row = $result->fetch_assoc()) {
    //         $objects[] = $row;
    //     }
    // }
    $objects = $result->fetch_assoc();

    echo json_encode($objects);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

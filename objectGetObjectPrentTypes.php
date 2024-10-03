<?php
// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include 'config.php';

$typeId = isset($_GET['typeId']) ? $_GET['typeId'] : null;
if ($typeId === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Type ID is required"]);
    exit;
}

try {
    // Query to get object types and their count
    $sql = "
        SELECT 
        ot.id as typeId,ot.name AS typeName
        FROM objecttypes ot
        JOIN (SELECT otc.objecttype_id FROM objecttype_childtypes otc WHERE otc.childtype_id = ?) pt
        on ot.id = pt.objecttype_id
        ORDER BY ot.id 
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $typeId);

    $stmt->execute();
    $result = $stmt->get_result();

    $objectTypes = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $objectTypes[] = $row;
        }
    }

    echo json_encode($objectTypes);

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

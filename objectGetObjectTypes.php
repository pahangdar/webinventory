<?php
// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include 'config.php';

$parentid = isset($_GET['parentid']) ? $_GET['parentid'] : null;

try {
    // Query to get object types and their count
    $sql = "
    SELECT 
        ot.id as typeId,
        ot.name AS typeName, 
        COUNT(o.id) AS count 
    FROM 
        objects o 
    JOIN 
        objecttypes ot 
    ON 
        o.objecttype_id = ot.id 
    ";

    if ($parentid !== null) {
        $sql .= " WHERE o.parent_id = ?";
    }

    $sql .= " GROUP BY o.objecttype_id;";
    $stmt = $conn->prepare($sql);

    if ($parentid !== null) {
        $stmt->bind_param("i", $parentid);
    }

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

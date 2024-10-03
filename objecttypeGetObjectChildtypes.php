<?php
// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include 'config.php';

$typeid = isset($_GET['typeid']) ? $_GET['typeid'] : null;
$parentid = isset($_GET['parentid']) ? $_GET['parentid'] : null;

if ($typeid === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Type ID is required"]);
    exit;
}

try {
    // Query to get object types and their count
    if ($parentid === NULL) {
        $sql = "
            SELECT
                ot.id as typeId,
                ot.name AS typeName,
                0 AS count
            FROM
                objecttype_childtypes otc
            JOIN
                objecttypes ot
            ON
                otc.childtype_id = ot.id
            WHERE otc.objecttype_id = ?
        ";
    } else {
        $sql = "
            SELECT
                ot.id as typeId,
                ot.name AS typeName,
                COUNT(o.id) AS count
            FROM
                objecttype_childtypes otc
            JOIN
                objecttypes ot
            ON
                otc.childtype_id = ot.id
            LEFT JOIN
                (SELECT * FROM objects WHERE objects.parent_id = ?) o
            ON ot.id = o.objecttype_id
            WHERE otc.objecttype_id = ?
            GROUP BY ot.id
        ";
    }

    $stmt = $conn->prepare($sql);
    if ($parentid === NULL) {
        $stmt->bind_param("i", $typeid);
    } else {
        $stmt->bind_param("ii", $parentid, $typeid);
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

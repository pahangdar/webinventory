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


$typeid = isset($_GET['typeid']) ? $_GET['typeid'] : null;
$parentid = isset($_GET['parentid']) ? $_GET['parentid'] : null;
$objectid = isset($_GET['objectid']) ? $_GET['objectid'] : null;

try {
    // Base SQL query
    $sql = "
        SELECT o.id, o.name, o.objecttype_id, o.parent_id, ot.name AS typeName, p.name as parentName, pot.name as parentTypeName, c.childCount
        FROM objects o 
        JOIN objecttypes ot ON o.objecttype_id = ot.id 
        LEFT JOIN objects p on o.parent_id = p.id
        LEFT JOIN objecttypes pot on p.objecttype_id = pot.id
        LEFT JOIN (SELECT co.parent_id, COUNT(*) AS childCount from objects co GROUP BY co.parent_id ) c on o.id = c.parent_id
        WHERE 1 = 1
    ";

    if ($objectid !== null) {
        $sql .= " AND o.id = ?";
    } else {
        if ($typeid !== null) {
            $sql .= " AND ot.id = ?";
        }
        if ($parentid !== null) {
            $sql .= " AND o.parent_id = ?";
        }    
    }

    $stmt = $conn->prepare($sql);

    // Bind parameters depending on whether parentid is present
    if ($objectid !== null) {
        $stmt->bind_param("i", $objectid);
    } else {
        if ($typeid !== null && $parentid !== null) {
            $stmt->bind_param("ii", $typeid, $parentid);
        } else if ($typeid !== null && $parentid === null) {
            $stmt->bind_param("i", $typeid);
        } else if($typeid === null && $parentid !== null) {
            $stmt->bind_param("i", $parentid);
        }    
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

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

try {
    $sql = "
        SELECT pt.* FROM propertytypes pt
    ";

    $stmt = $conn->prepare($sql);

    // $stmt->bind_param("i", $objectid);

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

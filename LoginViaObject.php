<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

require 'vendor/autoload.php'; // Load JWT library
use \Firebase\JWT\JWT;

header('Content-Type: application/json');

// Database connection
require 'config.php';


// JWT Secret Key
$secret_key = "your_secret_key"; // Use a secure and long secret key
$issued_at = time();
$expiration_time = $issued_at + (60 * 60);  // jwt valid for 1 hour

// Function to generate JWT token
function generate_jwt($email, $role, $secret_key) {
    global $issued_at, $expiration_time;
    
    $payload = [
        "iat" => $issued_at,
        "exp" => $expiration_time,
        "data" => [
            "email" => $email,
            "role" => $role
        ]
    ];
    
    return JWT::encode($payload, $secret_key, 'HS256');
}

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from POST body
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email'], $input['password'])) {
        echo json_encode(['message' => 'Invalid input']);
        exit();
    }

    $email = $input['email'];
    $password = $input['password'];


    // Prepare and execute the query
    $stmt = $conn->prepare("
        select o.id, o.name, op1.value 'email', op2.value 'password', op3.value 'role'
        From objects o
        LEFT JOIN (select op.object_id, op.value from object_properties op WHERE op.property_id = -103) op1 ON o.id = op1.object_id 
        LEFT JOIN (select op.object_id, op.value from object_properties op WHERE op.property_id = -102) op2 ON o.id = op2.object_id 
        LEFT JOIN (select op.object_id, op.value from object_properties op WHERE op.property_id = -101) op3 ON o.id = op3.object_id 
        WHERE o.objecttype_id = -100 AND op1.value = ?
        ");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows > 0) {
        //$stmt->bind_result($id, $hashed_password, $role, $name);
        $stmt->bind_result($uid, $uname, $uemail, $upassword, $urole);
        $stmt->fetch();

        // Verify the password
        // if (password_verify($password, $hashed_password)) {
        if ($password === $upassword) {

            // Password is correct, start a session
            // Generate JWT token
            $token = generate_jwt($uemail, $urole, $secret_key);

            // Return token and user role
            echo json_encode([
                'ok' => true,
                'message' => 'Login successful',
                'token' => $token,
                'id' => $uid,
                'role' => $urole,
                'userName' => $uname
            ]);
            exit();
        } else {
            // Incorrect password
            echo json_encode(['message' => 'Invalid password']);
            exit();
        }
    } else {
        // No user found
        echo json_encode(['message' => 'User not found']);
        exit();
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['message' => 'Method not allowed']);
    exit();
}

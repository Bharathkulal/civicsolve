<?php
$servername = "localhost";
$username = "root"; // default in XAMPP/WAMP
$password = "";     // default in XAMPP/WAMP
$dbname = "civicsolve";

// Create connection
$conn = @new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isApiRequest = str_contains($requestUri, '/backend/') || str_contains($contentType, 'application/json');

    if ($isApiRequest) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }
        echo json_encode([
            "success" => false,
            "message" => "Database connection failed"
        ]);
        exit();
    }

    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully";
?>
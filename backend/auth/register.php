<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config/db.php";

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

function respond_json($payload) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($payload);
    exit();
}

// Fallback to $_POST if not JSON (e.g., standard form submission)
$name = $input['name'] ?? $_POST['name'] ?? '';
$email = $input['email'] ?? $_POST['email'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';
// Note: auth.js doesn't send confirm_password, but the standard form might.
$confirm = $input['confirm_password'] ?? $_POST['confirm_password'] ?? $password;

if (empty($name) || empty($email) || empty($password)) {
    respond_json(["success" => false, "message" => "All fields are required"]);
}

if ($password !== $confirm) {
    respond_json(["success" => false, "message" => "Passwords do not match"]);
}

$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");

if (!$stmt) {
    respond_json(["success" => false, "message" => "Server error: unable to prepare query"]);
}

$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    respond_json(["success" => true, "message" => "Registered Successfully"]);
} else {
    respond_json(["success" => false, "message" => "Error: " . $stmt->error]);
}

$stmt->close();
?>
<?php
session_start();
ob_start();
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

function respond_json_login($payload) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($payload);
    exit;
}

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    respond_json_login(["success" => false, "message" => "Email and password required"]);
}

// Special testing credentials for all admins
if ($email === 'admin' && $password === 'admin') {
    $_SESSION['user_id'] = 999; // Dummy ID
    $_SESSION['name'] = 'Test Admin';
    $_SESSION['role'] = $input['role'] ?? 'admin';
    $_SESSION['department'] = $input['department'] ?? 'road';

    $redirect = "user/home.php";
    if ($_SESSION['role'] == 'super_admin') {
        $redirect = "admin/super_admin/home.php";
    } elseif ($_SESSION['role'] == 'admin') {
        $dept = $_SESSION['department'];
        $redirect = "admin/{$dept}/home.php";
    }

    respond_json_login(["success" => true, "redirect" => $redirect]);
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
if (!$stmt) {
    respond_json_login(["success" => false, "message" => "Server error"]);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if ($password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['department'] = $user['department'];

        $redirect = "user/home.php";
        if ($user['role'] == 'super_admin') {
            $redirect = "admin/super_admin/home.php";
        } elseif ($user['role'] == 'admin') {
            $dept = $user['department'];
            if ($dept == 'electricity') {
                $redirect = "admin/electricity/home.php";
            } elseif ($dept == 'water') {
                $redirect = "admin/water/home.php";
            } elseif ($dept == 'garbage') {
                $redirect = "admin/garbage/home.php";
            } elseif ($dept == 'road') {
                $redirect = "admin/road/home.php";
            }
        }

        respond_json_login(["success" => true, "redirect" => $redirect]);
    } else {
        respond_json_login(["success" => false, "message" => "Invalid password"]);
    }
} else {
    respond_json_login(["success" => false, "message" => "User not found"]);
}

$stmt->close();
?>
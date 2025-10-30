<?php
session_start();
require_once  '../config/conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$teacherId = intval($_POST['teacher_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($teacherId <= 0 || !in_array($action, ['activate','deactivate'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Toggle via web_users.usertype: 't' active, 'td' deactivated
$row = $conn->query("SELECT t_user_name FROM teachers WHERE t_id = $teacherId")->fetch_assoc();
if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Teacher not found']);
    exit;
}

$email = $conn->real_escape_string($row['t_user_name']);
$newType = $action === 'deactivate' ? 'td' : 't';

// Ensure web_users exists for this email; if not, create it when activating
$exists = $conn->query("SELECT 1 FROM web_users WHERE email = '$email'");
if ($exists && $exists->num_rows) {
    $ok = $conn->query("UPDATE web_users SET usertype = '$newType' WHERE email = '$email'");
} else {
    if ($action === 'activate') {
        $ok = $conn->query("INSERT INTO web_users(email, usertype) VALUES('$email','t')");
    } else {
        $ok = true; // Consider already deactivated
    }
}

if ($ok) {
    echo json_encode(['success' => true, 'message' => ($action === 'deactivate' ? 'Instructor deactivated' : 'Instructor activated')]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}



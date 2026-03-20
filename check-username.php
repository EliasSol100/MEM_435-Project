<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/plain; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    echo 'invalid';
    exit;
}

$username = trim($_POST['username'] ?? '');
if ($username === '' || !preg_match('/^[a-zA-Z0-9._]{3,30}$/', $username)) {
    echo 'invalid';
    exit;
}

$currentUserId = auth_flow_user_id();

if ($currentUserId > 0) {
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) AND id != ? LIMIT 1");
    $stmt->bind_param('si', $username, $currentUserId);
} else {
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
    $stmt->bind_param('s', $username);
}

$stmt->execute();
$stmt->store_result();

echo $stmt->num_rows > 0 ? 'taken' : 'available';
$stmt->close();

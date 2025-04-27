<?php
header('Content-Type: application/json');
include_once 'connect.php';

if (!isset($_GET['email'])) {
    echo json_encode(['error' => 'Email parameter is missing']);
    exit();
}

$email = $myconnection->real_escape_string($_GET['email']);
$query = "SELECT id FROM users WHERE email = ?";
$stmt = $myconnection->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => 'Database preparation failed']);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

echo json_encode([
    'exists' => $stmt->num_rows > 0,
    'email' => $email
]);

$stmt->close();
?>
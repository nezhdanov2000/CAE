<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$response = [
    'name' => $_SESSION['name'],
    'surname' => $_SESSION['surname'],
    'role' => $_SESSION['role']
];

echo json_encode($response);
?>

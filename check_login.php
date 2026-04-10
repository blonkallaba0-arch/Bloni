<?php
session_start();

$response = [
    'logged_in' => false,
    'username' => '',
    'user_id' => 0
];

if (isset($_SESSION['user_id'])) {
    $response['logged_in'] = true;
    $response['username'] = $_SESSION['username'] ?? '';
    $response['user_id'] = $_SESSION['user_id'];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
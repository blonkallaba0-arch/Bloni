<?php
$host = '127.0.0.1:3307';
$user = 'root';
$pass = '';
$db = 'pinguinshop';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    $conn = null;
} else {
    $conn->set_charset('utf8mb4');
}
?>
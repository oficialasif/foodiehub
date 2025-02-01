<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id");
$count = $result->fetch_assoc()['count'] ?? 0;

echo json_encode(['count' => (int)$count]);

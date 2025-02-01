<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($product_id <= 0 || !in_array($action, ['increase', 'decrease'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit();
    }

    // Get current quantity
    $cart_item = $conn->query("SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id")->fetch_assoc();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
        exit();
    }

    $new_quantity = $action === 'increase' ? $cart_item['quantity'] + 1 : $cart_item['quantity'] - 1;

    if ($new_quantity <= 0) {
        // Remove item if quantity becomes 0
        $delete = $conn->query("DELETE FROM cart WHERE id = {$cart_item['id']}");
        echo json_encode(['success' => $delete, 'message' => $delete ? 'Item removed' : 'Failed to remove item']);
    } else {
        // Update quantity
        $update = $conn->query("UPDATE cart SET quantity = $new_quantity WHERE id = {$cart_item['id']}");
        echo json_encode(['success' => $update, 'message' => $update ? 'Quantity updated' : 'Failed to update quantity']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

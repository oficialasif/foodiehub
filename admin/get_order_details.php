<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['order_id'])) {
    echo json_encode(['error' => 'Order ID is required']);
    exit();
}

$order_id = (int)$_GET['order_id'];

// Get order details with user information
$order_query = "
    SELECT 
        o.*,
        u.username,
        u.email,
        u.phone,
        u.full_name
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = $order_id
";

$order_result = $conn->query($order_query);

if (!$order_result) {
    echo json_encode(['error' => 'Error fetching order details: ' . $conn->error]);
    exit();
}

$order = $order_result->fetch_assoc();

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit();
}

// Get order items with product details
$items_query = "
    SELECT 
        oi.*,
        p.name as product_name,
        p.image as product_image,
        c.name as category_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE oi.order_id = $order_id
";

$items_result = $conn->query($items_query);

if (!$items_result) {
    echo json_encode(['error' => 'Error fetching order items: ' . $conn->error]);
    exit();
}

$items = [];
while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

// Format dates for display
$created_at = new DateTime($order['created_at']);
$updated_at = new DateTime($order['updated_at']);
$estimated_delivery = $order['estimated_delivery'] ? new DateTime($order['estimated_delivery']) : null;

$response = [
    'order' => [
        'id' => $order['id'],
        'status' => $order['status'],
        'payment_method' => $order['payment_method'],
        'payment_status' => $order['payment_status'],
        'total_amount' => $order['total_amount'],
        'tracking_number' => $order['tracking_number'],
        'estimated_delivery' => $estimated_delivery ? $estimated_delivery->format('Y-m-d H:i:s') : null,
        'created_at' => $created_at->format('Y-m-d H:i:s'),
        'updated_at' => $updated_at->format('Y-m-d H:i:s'),
        'delivery_address' => $order['delivery_address'],
        'delivery_phone' => $order['delivery_phone'],
        'delivery_notes' => $order['delivery_notes']
    ],
    'customer' => [
        'name' => $order['full_name'],
        'username' => $order['username'],
        'email' => $order['email'],
        'phone' => $order['phone']
    ],
    'items' => array_map(function($item) {
        return [
            'product_name' => $item['product_name'],
            'category' => $item['category_name'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'subtotal' => $item['subtotal'],
            'image' => $item['product_image']
        ];
    }, $items)
];

echo json_encode($response);

<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Fetch cart items
$cart_query = "SELECT c.*, p.name, p.price, p.image 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id";
$cart_items = $conn->query($cart_query);

// Calculate total
$total = 0;
$cart_products = [];
while($item = $cart_items->fetch_assoc()) {
    $total += $item['price'] * $item['quantity'];
    $cart_products[] = $item;
}

if (empty($cart_products)) {
    header("Location: cart.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = mysqli_real_escape_string($conn, $_POST['delivery_address']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Create order
    $sql = "INSERT INTO orders (user_id, total_amount, delivery_address, payment_method, payment_status) 
            VALUES ($user_id, $total, '$delivery_address', '$payment_method', 'pending')";
    
    if ($conn->query($sql)) {
        $order_id = $conn->insert_id;
        
        // Add order items
        foreach ($cart_products as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) 
                         VALUES ($order_id, $product_id, $quantity, $price)");
        }
        
        // Clear cart
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");
        
        // Redirect to payment page
        header("Location: payment.php?order_id=$order_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - FoodieHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f8f9fa;
            padding-bottom: 80px;
        }

        .header {
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
        }

        .back-button {
            color: #2f3542;
            text-decoration: none;
            font-size: 20px;
            margin-right: 20px;
        }

        .page-title {
            color: #2f3542;
            font-size: 20px;
        }

        .main-content {
            max-width: 800px;
            margin: 80px auto 0;
            padding: 20px;
        }

        .section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #2f3542;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .order-summary {
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
            margin-right: 15px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            color: #2f3542;
            margin-bottom: 5px;
        }

        .item-price {
            color: #ff4757;
            font-weight: bold;
        }

        .item-quantity {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #2f3542;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .payment-methods {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .payment-method {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #ff4757;
        }

        .payment-method.selected {
            border-color: #ff4757;
            background: #fff5f6;
        }

        .payment-method i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2f3542;
        }

        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .total-label {
            color: #2f3542;
            font-size: 18px;
        }

        .total-amount {
            color: #ff4757;
            font-size: 24px;
            font-weight: bold;
        }

        .checkout-btn {
            background: #ff4757;
            color: white;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .checkout-btn:hover {
            background: #ff2e44;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="cart.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Checkout</h1>
        </div>
    </header>

    <main class="main-content">
        <form action="" method="POST">
            <section class="section">
                <h2 class="section-title">Order Summary</h2>
                <div class="order-summary">
                    <?php foreach($cart_products as $item): ?>
                    <div class="order-item">
                        <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                        <div class="item-details">
                            <div class="item-name"><?php echo $item['name']; ?></div>
                            <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="total-section">
                    <div class="total-label">Total Amount</div>
                    <div class="total-amount">$<?php echo number_format($total, 2); ?></div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Delivery Address</h2>
                <div class="form-group">
                    <textarea name="delivery_address" required><?php echo $user['address']; ?></textarea>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Payment Method</h2>
                <div class="payment-methods">
                    <div class="payment-method" onclick="selectPayment('cash')">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>Cash on Delivery</div>
                        <input type="radio" name="payment_method" value="cash" required style="display: none;">
                    </div>
                    <div class="payment-method" onclick="selectPayment('card')">
                        <i class="fas fa-credit-card"></i>
                        <div>Credit Card</div>
                        <input type="radio" name="payment_method" value="card" required style="display: none;">
                    </div>
                </div>
            </section>

            <button type="submit" class="checkout-btn">Place Order</button>
        </form>
    </main>

    <script>
    function selectPayment(method) {
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        
        const selectedMethod = document.querySelector(`.payment-method:has(input[value="${method}"])`);
        selectedMethod.classList.add('selected');
        selectedMethod.querySelector('input').checked = true;
    }
    </script>
</body>
</html>

<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_GET['order_id'];

// Fetch order details
$order_query = "SELECT o.*, u.email, u.full_name, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id AND o.user_id = $user_id";
$order = $conn->query($order_query)->fetch_assoc();

if (!$order) {
    header("Location: homepage.php");
    exit();
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($order['payment_method'] === 'card') {
        // In a real application, you would integrate with a payment gateway here
        // For demo purposes, we'll just mark the payment as completed
        $conn->query("UPDATE orders SET payment_status = 'completed', status = 'processing' WHERE id = $order_id");
        $_SESSION['success'] = "Payment successful! Your order is being processed.";
    } else {
        // For cash on delivery
        $conn->query("UPDATE orders SET status = 'processing' WHERE id = $order_id");
        $_SESSION['success'] = "Order placed successfully! You will pay on delivery.";
    }
    header("Location: orders.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - FoodieHub</title>
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
            min-height: 100vh;
            padding: 80px 20px 20px;
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

        .page-title {
            color: #2f3542;
            font-size: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .payment-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .order-summary {
            margin-bottom: 30px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #2f3542;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-weight: bold;
        }

        .amount {
            color: #ff4757;
            font-weight: bold;
        }

        .payment-form {
            margin-top: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #2f3542;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus {
            border-color: #ff4757;
            outline: none;
        }

        .card-row {
            display: flex;
            gap: 15px;
        }

        .card-row .form-group {
            flex: 1;
        }

        .submit-btn {
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

        .submit-btn:hover {
            background: #ff2e44;
        }

        .cod-message {
            text-align: center;
            color: #2f3542;
            line-height: 1.6;
        }

        .cod-message i {
            font-size: 48px;
            color: #ff4757;
            margin-bottom: 20px;
        }

        .cod-amount {
            font-size: 24px;
            color: #ff4757;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="page-title">Payment</h1>
        </div>
    </header>

    <div class="container">
        <div class="payment-section">
            <div class="order-summary">
                <div class="summary-row">
                    <div>Order ID</div>
                    <div>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div class="summary-row">
                    <div>Payment Method</div>
                    <div><?php echo ucfirst($order['payment_method']); ?></div>
                </div>
                <div class="summary-row">
                    <div>Total Amount</div>
                    <div class="amount">$<?php echo number_format($order['total_amount'], 2); ?></div>
                </div>
            </div>

            <?php if($order['payment_method'] === 'card'): ?>
            <form action="" method="POST" class="payment-form">
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                </div>

                <div class="form-group">
                    <label for="card_name">Cardholder Name</label>
                    <input type="text" id="card_name" name="card_name" placeholder="John Doe" required>
                </div>

                <div class="card-row">
                    <div class="form-group">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required>
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" required>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Pay Now</button>
            </form>
            <?php else: ?>
            <div class="cod-message">
                <i class="fas fa-truck"></i>
                <p>You have selected Cash on Delivery</p>
                <div class="cod-amount">$<?php echo number_format($order['total_amount'], 2); ?></div>
                <p>Please keep the exact amount ready at the time of delivery</p>
                <form action="" method="POST">
                    <button type="submit" class="submit-btn">Confirm Order</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($order['payment_method'] === 'card'): ?>
    <script>
    // Add basic card input formatting
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = value;
    });

    document.getElementById('expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0,2) + '/' + value.slice(2);
        }
        e.target.value = value;
    });

    document.getElementById('cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0,3);
    });
    </script>
    <?php endif; ?>
</body>
</html>

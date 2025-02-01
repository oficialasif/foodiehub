<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for the user
$orders_query = "SELECT o.*, 
                 GROUP_CONCAT(p.name SEPARATOR ', ') as products,
                 GROUP_CONCAT(oi.quantity SEPARATOR ', ') as quantities,
                 GROUP_CONCAT(p.price * oi.quantity SEPARATOR ', ') as subtotals
                 FROM orders o
                 JOIN order_items oi ON o.id = oi.order_id
                 JOIN products p ON oi.product_id = p.id
                 WHERE o.user_id = $user_id
                 GROUP BY o.id
                 ORDER BY o.created_at DESC";

$orders = $conn->query($orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - FoodieHub</title>
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
            padding-bottom: 70px;
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
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }

        .logo span {
            color: #ff4757;
            font-size: 20px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .cart-icon {
            position: relative;
            text-decoration: none;
            color: #2f3542;
            font-size: 20px;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            font-size: 12px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-name {
            color: #2f3542;
            font-weight: 500;
        }

        .profile-photo {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f2f6;
            color: #2f3542;
            text-decoration: none;
        }

        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-photo i {
            font-size: 18px;
        }

        .main-content {
            max-width: 1200px;
            margin: 100px auto 0;
            padding: 20px;
        }

        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-id {
            font-weight: bold;
            color: #2f3542;
        }

        .order-date {
            color: #666;
            font-size: 14px;
        }

        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .status-pending {
            background: #ffeaa7;
            color: #fdcb6e;
        }

        .status-processing {
            background: #81ecec;
            color: #00cec9;
        }

        .status-completed {
            background: #55efc4;
            color: #00b894;
        }

        .status-cancelled {
            background: #ff7675;
            color: #d63031;
        }

        .order-items {
            margin-bottom: 15px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
        }

        .order-total {
            font-weight: bold;
            color: #2f3542;
            text-align: right;
            font-size: 18px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 15px;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .nav-item {
            text-decoration: none;
            color: #666;
            font-size: 12px;
            text-align: center;
        }

        .nav-item i {
            font-size: 20px;
            margin-bottom: 5px;
            display: block;
        }

        .nav-item.active {
            color: #ff4757;
        }

        .no-orders {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .no-orders i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="homepage.php" class="logo">
                <img src="assets/images/logo/foodie-logo.svg" alt="FoodieHub Logo">
                <span>FoodieHub</span>
            </a>
            <div class="user-info">
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cart-count">0</span>
                </a>
                <?php
                $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
                ?>
                <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
                <a href="profile.php" class="profile-photo">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="assets/images/profiles/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="orders-list">
            <?php if ($orders->num_rows > 0): ?>
                <?php while($order = $orders->fetch_assoc()): 
                    $products = explode(', ', $order['products']);
                    $quantities = explode(', ', $order['quantities']);
                    $subtotals = explode(', ', $order['subtotals']);
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">Order #<?php echo $order['id']; ?></div>
                        <div class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </div>
                    </div>
                    <div class="order-items">
                        <?php for($i = 0; $i < count($products); $i++): ?>
                            <div class="order-item">
                                <span><?php echo $products[$i]; ?> × <?php echo $quantities[$i]; ?></span>
                                <span>৳<?php echo number_format($subtotals[$i], 2); ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="order-total">
                        Total: ৳<?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <h2>No Orders Yet</h2>
                    <p>Your ordered items will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <nav class="bottom-nav">
        <a href="homepage.php" class="nav-item">
            <i class="fas fa-home"></i>
            Home
        </a>
        <a href="cart.php" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            Cart
        </a>
        <a href="my_orders.php" class="nav-item active">
            <i class="fas fa-list-alt"></i>
            Orders
        </a>
        <a href="profile.php" class="nav-item">
            <i class="fas fa-user"></i>
            Profile
        </a>
    </nav>
</body>
</html>

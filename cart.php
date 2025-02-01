<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - FoodieHub</title>
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

        .cart-empty {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .cart-empty i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .cart-empty p {
            color: #666;
            margin-bottom: 20px;
        }

        .cart-empty a {
            display: inline-block;
            background: #ff4757;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .cart-empty a:hover {
            background: #ff2e44;
        }

        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: 18px;
            color: #2f3542;
            margin-bottom: 5px;
        }

        .item-price {
            color: #ff4757;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            background: #f1f2f6;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            color: #2f3542;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .quantity-btn:hover {
            background: #dfe4ea;
        }

        .quantity {
            font-size: 16px;
            color: #2f3542;
            min-width: 20px;
            text-align: center;
        }

        .remove-item {
            color: #ff4757;
            text-decoration: none;
            font-size: 20px;
            transition: color 0.3s ease;
        }

        .remove-item:hover {
            color: #ff2e44;
        }

        .cart-summary {
            position: fixed;
            bottom: 70px;
            left: 0;
            right: 0;
            background: white;
            padding: 20px;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        }

        .summary-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-amount {
            font-size: 20px;
            color: #2f3542;
        }

        .total-amount span {
            color: #ff4757;
            font-weight: bold;
        }

        .checkout-btn {
            background: #ff4757;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .checkout-btn:hover {
            background: #ff2e44;
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

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .cart-item {
                padding: 15px;
            }

            .item-image {
                width: 60px;
                height: 60px;
            }

            .cart-summary {
                padding: 15px;
            }
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
        <?php if(empty($cart_products)): ?>
        <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
            <a href="homepage.php">Start Shopping</a>
        </div>
        <?php else: ?>
        <div class="cart-items">
            <?php foreach($cart_products as $item): ?>
            <div class="cart-item">
                <img src="assets/images/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                <div class="item-details">
                    <h3 class="item-name"><?php echo $item['name']; ?></h3>
                    <div class="item-price">৳<?php echo number_format($item['price'], 2); ?></div>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 'decrease')">-</button>
                        <span class="quantity"><?php echo $item['quantity']; ?></span>
                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 'increase')">+</button>
                    </div>
                </div>
                <a href="javascript:void(0)" class="remove-item" onclick="removeItem(<?php echo $item['product_id']; ?>)">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <?php if(!empty($cart_products)): ?>
    <div class="cart-summary">
        <div class="summary-content">
            <div class="total-amount">Total: <span>৳<?php echo number_format($total, 2); ?></span></div>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </div>
    </div>
    <?php endif; ?>

    <nav class="bottom-nav">
        <a href="homepage.php" class="nav-item">
            <i class="fas fa-home"></i>
            Home
        </a>
        <a href="cart.php" class="nav-item active">
            <i class="fas fa-shopping-cart"></i>
            Cart
        </a>
        <a href="my_orders.php" class="nav-item">
            <i class="fas fa-list-alt"></i>
            Orders
        </a>
        <a href="profile.php" class="nav-item">
            <i class="fas fa-user"></i>
            Profile
        </a>
    </nav>

    <script>
    function updateQuantity(productId, action) {
        fetch('ajax/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update quantity. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    function removeItem(productId) {
        if (confirm('Are you sure you want to remove this item?')) {
            fetch('ajax/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to remove item. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    }
    </script>
</body>
</html>

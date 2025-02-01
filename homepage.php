<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Fetch user data
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Fetch products with their categories
$products_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_id = (int)$_GET['category'];
    $products_query .= " AND p.category_id = $category_id";
}

$products_query .= " ORDER BY p.name";
$products = $conn->query($products_query);

// Get selected category if any
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Fetch trending products
$trending_products = $conn->query("SELECT * FROM products WHERE is_trending = 1 LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodieHub - Home</title>
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

        .search-bar {
            flex: 1;
            max-width: 500px;
            margin: 0 20px;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
        }

        .search-bar i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-name {
            color: #2f3542;
            font-size: 14px;
            font-weight: 500;
        }

        .profile-photo {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #ff4757;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 16px;
            overflow: hidden;
        }

        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-photo i {
            font-size: 18px;
        }

        .cart-icon {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .main-content {
            margin-top: 80px;
            padding: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding-bottom: 80px;
        }

        /* Categories Section with new functionality */
        .categories {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            padding: 20px 0;
            margin-bottom: 30px;
            scrollbar-width: none;
        }

        .categories::-webkit-scrollbar {
            display: none;
        }

        .category-list {
            display: inline-flex;
            gap: 20px;
            padding: 10px;
        }

        .category-item {
            min-width: 100px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .category-item:hover {
            transform: translateY(-5px);
        }

        .category-item.active {
            color: #ff4757;
        }

        .category-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .category-icon i {
            font-size: 24px;
            color: #ff4757;
        }

        .category-name {
            font-size: 14px;
            color: #2f3542;
        }

        .section-title {
            margin: 30px 0 20px;
            color: #2f3542;
            font-size: 24px;
        }

        .trending-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-category {
            color: #ff4757;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .product-name {
            font-size: 18px;
            color: #2f3542;
            margin-bottom: 5px;
        }

        .product-price {
            font-weight: bold;
            color: #ff4757;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .add-to-cart {
            display: block;
            background: #ff4757;
            color: white;
            text-align: center;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
            border: none;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
            position: relative;
        }

        .add-to-cart.adding {
            background: #ff2e44;
        }

        .add-success {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            animation: slideIn 0.3s ease, slideOut 0.3s ease 2s forwards;
            z-index: 1000;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
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
            .search-bar {
                display: none;
            }
            
            .trending-products {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
                <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
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
        <!-- Categories Section -->
        <div class="categories">
            <div class="category-list">
                <a href="homepage.php" class="category-item <?php echo !$selected_category ? 'active' : ''; ?>">
                    <div class="category-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="category-name">All</div>
                </a>
                <?php while($category = $categories->fetch_assoc()): ?>
                <a href="homepage.php?category=<?php echo $category['id']; ?>" 
                   class="category-item <?php echo $selected_category == $category['id'] ? 'active' : ''; ?>">
                    <div class="category-icon">
                        <i class="<?php echo $category['icon'] ?? 'fas fa-hamburger'; ?>"></i>
                    </div>
                    <div class="category-name"><?php echo $category['name']; ?></div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Products Section -->
        <h2 class="section-title">
            <?php echo $selected_category ? 'Category Products' : 'Featured Products'; ?>
        </h2>
        <div class="trending-products">
            <?php while($product = $products->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                <div class="product-info">
                    <div class="product-category"><?php echo $product['category_name']; ?></div>
                    <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    <div class="product-price">৳<?php echo number_format($product['price'], 2); ?></div>
                    <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                        Add to Cart
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Trending Products Section -->
        <h2 class="section-title">Trending Now 🔥</h2>
        <div class="trending-products">
            <?php while($product = $trending_products->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                <div class="product-info">
                    <div class="product-category">Trending</div>
                    <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    <div class="product-price">৳<?php echo number_format($product['price'], 2); ?></div>
                    <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                        Add to Cart
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <nav class="bottom-nav">
        <a href="homepage.php" class="nav-item active">
            <i class="fas fa-home"></i>
            Home
        </a>
        <a href="cart.php" class="nav-item">
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
    // Update cart count on page load
    document.addEventListener('DOMContentLoaded', updateCartCount);

    function updateCartCount() {
        fetch('ajax/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.cart-count').textContent = data.count;
            });
    }

    function addToCart(productId) {
        const button = event.target;
        button.classList.add('adding');
        
        fetch('ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                updateCartCount();
                
                // Show success message
                const message = document.createElement('div');
                message.className = 'add-success';
                message.textContent = 'Added to cart!';
                document.body.appendChild(message);
                
                // Remove message after animation
                setTimeout(() => {
                    message.remove();
                }, 2500);
            }
            button.classList.remove('adding');
        })
        .catch(error => {
            console.error('Error:', error);
            button.classList.remove('adding');
        });
    }
    </script>
</body>
</html>

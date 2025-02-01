<?php
require_once '../config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    // First, delete related order items
    $conn->query("DELETE FROM order_items WHERE product_id = $product_id");
    // Now delete the product
    $conn->query("DELETE FROM products WHERE id = $product_id");
    header("Location: products.php");
    exit();
}

// Fetch all products with their categories
$products_query = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
";
$products = $conn->query($products_query);

// Fetch categories for the add product form
$categories = $conn->query("SELECT * FROM categories");
$category_options = [];
while ($category = $categories->fetch_assoc()) {
    $category_options[] = $category;
}

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/products/$image");
        }
    } elseif (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
        $image = $_POST['image_url'];
    }

    $sql = "INSERT INTO products (name, description, price, category_id, image, is_trending) 
            VALUES ('$name', '$description', $price, $category_id, '$image', $is_trending)";

    if ($conn->query($sql)) {
        header("Location: products.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - FoodieHub Admin</title>
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
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2f3542;
            min-height: 100vh;
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            margin-bottom: 30px;
        }

        .logo img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            color: #a4b0be;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #ff4757;
            color: white;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .page-title {
            color: #2f3542;
            font-size: 24px;
        }

        .add-product-btn {
            background: #ff4757;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .add-product-btn:hover {
            background: #ff2e44;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }

        .product-details {
            padding: 20px;
        }

        .product-category {
            color: #ff4757;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .product-name {
            color: #2f3542;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .product-price {
            color: #2f3542;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .edit-btn {
            background: #f1f2f6;
            color: #2f3542;
        }

        .edit-btn:hover {
            background: #dfe4ea;
        }

        .delete-btn {
            background: #ff4757;
            color: white;
        }

        .delete-btn:hover {
            background: #ff2e44;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            color: #2f3542;
            font-size: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #2f3542;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #2f3542;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn {
            background: #ff4757;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #ff2e44;
        }

        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2f3542;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 90% !important;
                margin: 20px auto;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                width: 100% !important;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <aside class="sidebar">
        <a href="dashboard.php" class="logo">
            <img src="../assets/images/logo.png" alt="FoodieHub Logo">
            <span>FoodieHub Admin</span>
        </a>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link">
                    <i class="fas fa-shopping-bag"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link active">
                    <i class="fas fa-utensils"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link">
                    <i class="fas fa-list"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1 class="page-title">Manage Products</h1>
            <button class="add-product-btn" onclick="openModal()">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </header>

        <div class="product-grid">
            <?php while($product = $products->fetch_assoc()): ?>
            <div class="product-card">
                <img src="../assets/images/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                <div class="product-details">
                    <div class="product-category"><?php echo $product['category_name']; ?></div>
                    <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                    <div class="product-actions">
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="action-btn edit-btn">Edit</a>
                        <button class="action-btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <div class="modal" id="addProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New Product</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <?php foreach($category_options as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image_url">Image URL (from web)</label>
                    <input type="url" id="image_url" name="image_url" 
                           placeholder="https://example.com/image.jpg">
                    <small class="form-text text-muted">Enter a direct URL to an image from the web</small>
                </div>

                <div class="form-group">
                    <label for="image">Upload Image (optional)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">You can either provide an Image URL above or upload an image here</small>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="is_trending" name="is_trending">
                    <label for="is_trending">Mark as Trending</label>
                </div>

                <button type="submit" class="submit-btn">Add Product</button>
            </form>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById('addProductModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('addProductModal').classList.remove('active');
    }

    function deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            window.location.href = `products.php?delete=${productId}`;
        }
    }

    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.querySelector('.menu-toggle');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(event.target) && 
            !menuToggle.contains(event.target) &&
            sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });
    </script>
</body>
</html>

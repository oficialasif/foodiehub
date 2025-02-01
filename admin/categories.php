<?php
require_once '../config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    
    // Get category info to delete image if exists
    $category = $conn->query("SELECT image FROM categories WHERE id = $category_id")->fetch_assoc();
    if ($category && $category['image']) {
        $image_path = "../assets/images/categories/" . $category['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $conn->query("DELETE FROM categories WHERE id = $category_id");
    header("Location: categories.php");
    exit();
}

// Handle category addition/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $photo_link = mysqli_real_escape_string($conn, $_POST['photo_link']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon'] ?: 'fas fa-hamburger');
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

    // Handle image upload (optional)
    $image_sql = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('category_') . '.' . $ext;
            $upload_dir = "../assets/images/categories/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                // If updating, delete old image
                if ($category_id > 0) {
                    $old_image = $conn->query("SELECT image FROM categories WHERE id = $category_id")->fetch_assoc();
                    if ($old_image && $old_image['image']) {
                        $old_image_path = $upload_dir . $old_image['image'];
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                }
                $image_sql = ", image = '$new_filename'";
            }
        }
    }

    if ($category_id > 0) {
        // Update existing category
        $sql = "UPDATE categories SET 
                name = '$name', 
                photo_link = '$photo_link',
                icon = '$icon'
                $image_sql
                WHERE id = $category_id";
    } else {
        // Add new category
        $sql = "INSERT INTO categories (name, photo_link, icon" . ($image_sql ? ", image" : "") . ") 
                VALUES ('$name', '$photo_link', '$icon'" . ($image_sql ? ", '$new_filename'" : "") . ")";
    }

    if ($conn->query($sql)) {
        header("Location: categories.php?success=1");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Check for success message
$success = isset($_GET['success']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - FoodieHub Admin</title>
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

        .add-category-btn {
            background: #ff4757;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .add-category-btn:hover {
            background: #ff2e44;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .category-image {
            width: 100%;
            height: 150px;
            background: #f1f2f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-image i {
            font-size: 50px;
            color: #2f3542;
        }

        .category-details {
            padding: 20px;
        }

        .category-name {
            color: #2f3542;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .category-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .action-btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background: #2ed573;
            color: white;
        }

        .edit-btn:hover {
            background: #28c363;
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
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            color: #2f3542;
            font-size: 24px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #ff4757;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2f3542;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #ff4757;
            outline: none;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
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

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .modal-content {
                width: 90% !important;
                margin: 20px auto;
            }

            .form-group input,
            .form-group select {
                width: 100% !important;
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
                <a href="products.php" class="nav-link">
                    <i class="fas fa-utensils"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link active">
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
            <h1 class="page-title">Manage Categories</h1>
            <button class="add-category-btn" onclick="openModal()">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </header>

        <div class="categories-grid">
            <?php while($category = $categories->fetch_assoc()): ?>
            <div class="category-card">
                <div class="category-image">
                    <?php if($category['image']): ?>
                        <img src="../assets/images/categories/<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <?php elseif($category['photo_link']): ?>
                        <img src="<?php echo htmlspecialchars($category['photo_link']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <?php else: ?>
                        <i class="<?php echo htmlspecialchars($category['icon'] ?: 'fas fa-hamburger'); ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="category-details">
                    <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <div class="category-actions">
                        <button class="action-btn edit-btn" onclick='editCategory(<?php echo json_encode($category, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteCategory(<?php echo (int)$category['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Category</h2>
                <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" id="categoryForm">
                <input type="hidden" id="category_id" name="category_id">
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="photo_link">Photo Link (Optional)</label>
                    <input type="url" id="photo_link" name="photo_link" placeholder="https://example.com/image.jpg">
                    <small>Enter a direct link to an image (e.g., https://example.com/image.jpg)</small>
                </div>

                <div class="form-group">
                    <label for="icon">Icon Class (Optional)</label>
                    <input type="text" id="icon" name="icon" placeholder="fas fa-hamburger">
                    <small>Enter a Font Awesome icon class (default: fas fa-hamburger)</small>
                </div>

                <div class="form-group">
                    <label for="image">Upload Image (Optional)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Upload a local image file (JPG, JPEG, PNG, or GIF)</small>
                </div>

                <button type="submit" id="submitBtn" class="submit-btn">Add Category</button>
            </form>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById('categoryModal').classList.add('active');
        document.getElementById('modalTitle').textContent = 'Add New Category';
        document.getElementById('submitBtn').textContent = 'Add Category';
        document.getElementById('categoryForm').reset();
        document.getElementById('category_id').value = '';
    }

    function closeModal() {
        document.getElementById('categoryModal').classList.remove('active');
        document.getElementById('categoryForm').reset();
    }

    function editCategory(category) {
        console.log('Editing category:', category); // Debug log
        document.getElementById('categoryModal').classList.add('active');
        document.getElementById('modalTitle').textContent = 'Edit Category';
        document.getElementById('submitBtn').textContent = 'Update Category';
        
        // Fill form with category data
        document.getElementById('category_id').value = category.id;
        document.getElementById('name').value = category.name;
        document.getElementById('photo_link').value = category.photo_link || '';
        document.getElementById('icon').value = category.icon || 'fas fa-hamburger';
    }

    function deleteCategory(categoryId) {
        if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
            window.location.href = `categories.php?delete=${categoryId}`;
        }
    }

    // Close modal when clicking outside
    document.getElementById('categoryModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

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

    // Debug any JavaScript errors
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
        return false;
    };
    </script>
</body>
</html>

<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Handle profile photo upload
    $profile_photo_sql = "";
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png');

        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid('profile_') . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
                // Delete old profile photo if exists
                if (!empty($user['profile_photo'])) {
                    $old_photo = $upload_dir . $user['profile_photo'];
                    if (file_exists($old_photo)) {
                        unlink($old_photo);
                    }
                }
                $profile_photo_sql = ", profile_photo = '$new_filename'";
            }
        }
    }

    // Update profile
    $conn->query("UPDATE users SET 
                  full_name = '$full_name',
                  email = '$email',
                  phone = '$phone',
                  address = '$address'
                  $profile_photo_sql
                  WHERE id = $user_id");

    // Refresh user data
    $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - FoodieHub</title>
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
            padding: 20px;
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
            color: #2f3542;
            font-weight: bold;
            font-size: 24px;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-actions a {
            color: #2f3542;
            text-decoration: none;
            font-size: 20px;
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
            max-width: 800px;
            margin: 100px auto 0;
            padding: 20px;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #ff4757;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 15px;
            position: relative;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            text-align: center;
            padding: 5px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-avatar:hover label {
            opacity: 1;
        }

        .profile-avatar input[type="file"] {
            display: none;
        }

        .profile-title {
            font-size: 24px;
            color: #2f3542;
            margin-bottom: 5px;
        }

        .profile-subtitle {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2f3542;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #ff4757;
            outline: none;
        }

        .save-button {
            background: #ff4757;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background 0.3s ease;
        }

        .save-button:hover {
            background: #ff2e44;
        }

        .success-message {
            background: #55efc4;
            color: #00b894;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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

            .profile-card {
                padding: 20px;
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
            <div class="user-actions">
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <?php if (isset($success)): ?>
        <div class="success-message">
            Profile updated successfully!
        </div>
        <?php endif; ?>

        <div class="profile-card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="assets/images/profiles/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                        <label>
                            Change Photo
                            <input type="file" name="profile_photo" accept="image/jpeg,image/png">
                        </label>
                    </div>
                    <h1 class="profile-title"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <div class="profile-subtitle">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></div>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Delivery Address</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                </div>

                <button type="submit" class="save-button">Save Changes</button>
            </form>
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
        <a href="my_orders.php" class="nav-item">
            <i class="fas fa-list-alt"></i>
            Orders
        </a>
        <a href="profile.php" class="nav-item active">
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
    </script>
</body>
</html>

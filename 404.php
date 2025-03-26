<?php
// Include database connection
require_once 'config/database.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - FoodieHub</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            text-align: center;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            animation: fadeIn 0.5s ease;
        }

        .error-icon {
            font-size: 80px;
            color: #ff4757;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 36px;
            color: #2f3542;
            margin-bottom: 15px;
        }

        p {
            color: #747d8c;
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .primary-button {
            background: #ff4757;
            color: white;
        }

        .primary-button:hover {
            background: #ff2e44;
            transform: translateY(-2px);
        }

        .secondary-button {
            background: #f1f2f6;
            color: #2f3542;
        }

        .secondary-button:hover {
            background: #dfe4ea;
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .error-icon {
                font-size: 60px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            p {
                font-size: 16px;
            }
            
            .button {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-utensils"></i>
        </div>
        <h1>Oops! Page Not Found</h1>
        <p>The page you're looking for seems to have wandered off the menu. Let's get you back to something delicious!</p>
        <div class="button-group">
            <a href="homepage.php" class="button primary-button">Back to Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="menu.php" class="button secondary-button">View Menu</a>
            <?php else: ?>
                <a href="login.php" class="button secondary-button">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 
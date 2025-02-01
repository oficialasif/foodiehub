<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to FoodieHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
        }

        .splash-container {
            max-width: 500px;
            width: 100%;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo img {
            width: 120px;
            height: 120px;
        }

        .illustration {
            margin: 30px 0;
            max-width: 100%;
            height: auto;
        }

        .illustration img {
            width: 100%;
            max-width: 400px;
            height: auto;
        }

        h1 {
            color: #2f3542;
            font-size: 28px;
            margin-bottom: 15px;
        }

        p {
            color: #747d8c;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .get-started-btn {
            display: inline-block;
            background: #ff4757;
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-size: 18px;
            font-weight: bold;
            transition: transform 0.3s ease, background 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 71, 87, 0.2);
        }

        .get-started-btn:hover {
            background: #ff2e44;
            transform: translateY(-2px);
        }

        .get-started-btn:active {
            transform: translateY(0);
        }

        .loading-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: #ff4757;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="splash-container">
        <div class="logo">
            <img src="assets/images/logo/foodie-logo.svg" alt="FoodieHub Logo">
        </div>

        <div class="illustration">
            <img src="assets/images/illustrations/food-delivery.svg" alt="Food Delivery Illustration">
        </div>

        <h1>Welcome to FoodieHub</h1>
        <p>Your favorite restaurants and delicious food, delivered to your doorstep.</p>

        <div class="loading-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = 'getstartedscreen.php';
        }, 3000);
    </script>
</body>
</html>

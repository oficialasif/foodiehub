<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Started - FoodieHub</title>
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
        }

        .container {
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo img {
            width: 100px;
            height: 100px;
        }

        .slides {
            position: relative;
            height: 400px;
            margin-bottom: 40px;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.5s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .slide.active {
            opacity: 1;
            transform: translateX(0);
        }

        .slide-image {
            width: 100%;
            max-width: 300px;
            height: auto;
            margin-bottom: 30px;
        }

        .slide-content h2 {
            color: #2f3542;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .slide-content p {
            color: #747d8c;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .navigation {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 30px;
        }

        .nav-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #dfe4ea;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .nav-dot.active {
            background: #ff4757;
        }

        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 15px 40px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #ff4757;
            color: white;
            box-shadow: 0 4px 15px rgba(255, 71, 87, 0.2);
        }

        .btn-primary:hover {
            background: #ff2e44;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f1f2f6;
            color: #2f3542;
        }

        .btn-secondary:hover {
            background: #dfe4ea;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="assets/images/logo/foodie-logo.svg" alt="FoodieHub Logo">
        </div>

        <div class="slides">
            <div class="slide active">
                <img src="assets/images/illustrations/order-food.svg" alt="Order Food" class="slide-image">
                <div class="slide-content">
                    <h2>Order Your Favorite Food</h2>
                    <p>Choose from a wide variety of delicious dishes from the best restaurants near you.</p>
                </div>
            </div>

            <div class="slide">
                <img src="assets/images/illustrations/fast-delivery.svg" alt="Fast Delivery" class="slide-image">
                <div class="slide-content">
                    <h2>Fast Delivery</h2>
                    <p>Get your food delivered to your doorstep quickly and safely.</p>
                </div>
            </div>

            <div class="slide">
                <img src="assets/images/illustrations/track-order.svg" alt="Track Order" class="slide-image">
                <div class="slide-content">
                    <h2>Live Order Tracking</h2>
                    <p>Track your order in real-time and know exactly when your food will arrive.</p>
                </div>
            </div>
        </div>

        <div class="navigation">
            <div class="nav-dot active"></div>
            <div class="nav-dot"></div>
            <div class="nav-dot"></div>
        </div>

        <div class="buttons">
            <a href="login.php" class="btn btn-primary">Get Started</a>
            <a href="register.php" class="btn btn-secondary">Sign Up</a>
        </div>
    </div>

    <script>
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.nav-dot');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Auto-advance slides
        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 4000);
    </script>
</body>
</html>

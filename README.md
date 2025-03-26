# 🍔 FoodieHub - Food Delivery Application

![FoodieHub Logo](assets/images/logo/foodie-logo.svg)

## 📋 Overview

FoodieHub is a comprehensive food delivery web application built with PHP and MySQL. It provides an intuitive platform for customers to browse food items, place orders, and track deliveries, while allowing restaurant administrators to manage products, categories, and process orders.

## ✨ Features

### 👤 Customer Features
- **User Authentication**: Secure registration and login system
- **Food Browsing**: Browse foods by categories with an intuitive interface
- **Search Functionality**: Find favorite foods quickly
- **Shopping Cart**: Add, remove, and update items in cart
- **Order Placement**: Easy checkout and payment process
- **Order History**: View past orders and their statuses
- **Profile Management**: Update personal details and delivery addresses

### 👑 Admin Features
- **Dashboard**: Overview with key statistics and recent orders
- **Order Management**: Process, update, and track customer orders
- **Product Management**: Add, edit, and remove food items
- **Category Management**: Organize food items into categories
- **User Management**: View and manage customer accounts
- **Settings**: Configure application settings

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome
- **Server Environment**: Apache

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- XAMPP/WAMP/MAMP or any PHP development environment

### Installation Steps

1. **Clone the repository or extract the zip file**
   ```
   git clone https://github.com/yourusername/foodiehub.git
   ```

2. **Copy the project to your web server's document root**
   ```
   # For XAMPP (Windows)
   xcopy /E /I foodiehub C:\xampp\htdocs\FoodieHub

   # For XAMPP (macOS/Linux)
   cp -R foodiehub /Applications/XAMPP/xamppfiles/htdocs/FoodieHub
   ```

3. **Create the database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `fooddelivery`
   - Import the SQL file from `database/fooddelivery.sql`

4. **Configure database connection**
   - If needed, update database credentials in `config/database.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'fooddelivery');
     ```

5. **Start your web server and access the application**
   - Visit http://localhost/FoodieHub/login.php in your browser

## 👥 User Access

### Customer Access
- **URL**: http://localhost/FoodieHub/login.php
- **Register**: Create a new account
- **Login**: Use your email and password

### Admin Access
- **URL**: http://localhost/FoodieHub/login.php (same as customer)
- **Username**: admin
- **Password**: admin123

## 📱 Application Screenshots

### Customer Interface
![Homepage](assets/images/screenshots/homepage.jpg)
![Food Menu](assets/images/screenshots/menu.jpg)
![Shopping Cart](assets/images/screenshots/cart.jpg)

### Admin Interface
![Admin Dashboard](assets/images/screenshots/admin-dashboard.jpg)
![Order Management](assets/images/screenshots/admin-orders.jpg)
![Product Management](assets/images/screenshots/admin-products.jpg)

## 📂 Project Structure

```
FoodieHub/
├── admin/                  # Admin panel files
│   ├── dashboard.php       # Admin dashboard
│   ├── orders.php          # Order management
│   ├── products.php        # Product management
│   ├── categories.php      # Category management
│   └── ...
├── ajax/                   # AJAX request handlers
├── assets/                 # Static resources
│   ├── images/             # Images folder
│   └── ...
├── config/                 # Configuration files
│   └── database.php        # Database connection
├── database/               # Database scripts
│   └── fooddelivery.sql    # Database schema and sample data
├── cart.php                # Shopping cart page
├── checkout.php            # Checkout process
├── homepage.php            # Main landing page
├── login.php               # User login
├── register.php            # User registration
├── profile.php             # User profile management
└── ...
```

## 🔑 Key Files and Their Functions

- **config/database.php**: Database connection configuration
- **login.php & register.php**: User authentication system
- **homepage.php**: Main landing page with food listings
- **cart.php**: Shopping cart functionality
- **checkout.php & payment.php**: Order placement process
- **admin/dashboard.php**: Admin control panel
- **admin/orders.php**: Order processing system
- **admin/products.php**: Product management
- **admin/categories.php**: Category management

## 🔄 Database Schema

- **users**: User account details
- **admin**: Administrator credentials
- **categories**: Food categories
- **products**: Food items with details
- **orders**: Customer orders information
- **order_items**: Individual items in orders
- **cart**: Shopping cart items

## 🔨 Development & Customization

### Adding New Food Items
1. Login as admin
2. Navigate to Products management
3. Click "Add New Product"
4. Fill in the details and upload an image
5. Save the product

### Customizing Categories
1. Login as admin
2. Navigate to Categories management
3. Add, edit, or delete categories as needed

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Contact

For questions or support, please email [your-email@example.com](mailto:your-email@example.com)

---

⭐ Developed with ❤️ by [Your Name] 
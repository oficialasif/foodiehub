# 🚀 FoodieHub Quick Start Guide

This guide provides the minimum steps needed to get FoodieHub up and running on your local machine.

## 🔧 Prerequisites

- XAMPP, WAMP, MAMP, or any PHP development environment
- PHP 7.4+ and MySQL 5.7+

## 📋 Setup Instructions

### 1. Start Your Server Environment

**For XAMPP:**
- Start the XAMPP Control Panel
- Start Apache and MySQL services

![XAMPP Control Panel](assets/images/screenshots/xampp.jpg)

### 2. Set Up the Database

1. Open your browser and navigate to phpMyAdmin:
   ```
   http://localhost/phpmyadmin
   ```

2. Create a new database:
   - Click "New" in the left sidebar
   - Enter "fooddelivery" as the database name
   - Click "Create"

3. Import the database schema:
   - Select the newly created "fooddelivery" database
   - Click "Import" in the top menu
   - Click "Choose File" and select the SQL file at `database/fooddelivery.sql`
   - Click "Go" to import

### 3. Access the Application

1. **Customer Interface:**
   - Open your browser and navigate to:
   ```
   http://localhost/FoodieHub/login.php
   ```
   - Register a new account or use existing credentials

2. **Admin Interface:**
   - Navigate to the same login page:
   ```
   http://localhost/FoodieHub/login.php
   ```
   - Use the admin credentials:
     - Username: `admin`
     - Password: `admin123`

## 🔍 Troubleshooting

### Common Issues:

1. **Database Connection Error**
   - Verify MySQL is running
   - Check database credentials in `config/database.php`
   - Ensure the database "fooddelivery" exists

2. **Page Not Found Error**
   - Verify Apache is running
   - Confirm the project is in the correct directory (htdocs for XAMPP)
   - Check for any URL rewriting issues

3. **Permission Issues**
   - Ensure the web server has read/write permissions to the project directory

For detailed setup information, refer to the full README.md file.

---

## 📱 Quick Feature Overview

- **Browse Foods**: View food items organized by categories
- **Shopping Cart**: Add items to cart and proceed to checkout
- **Order Tracking**: View status of placed orders
- **Admin Panel**: Manage products, categories, and orders

Enjoy using FoodieHub! 🍔🍕🍣 
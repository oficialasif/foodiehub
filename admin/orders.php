<?php
require_once '../config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize error and success messages
$error = '';
$success = '';

// Handle status updates
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $tracking_number = isset($_POST['tracking_number']) ? mysqli_real_escape_string($conn, $_POST['tracking_number']) : '';
    $estimated_delivery = isset($_POST['estimated_delivery']) ? mysqli_real_escape_string($conn, $_POST['estimated_delivery']) : NULL;
    
    $update_query = "
        UPDATE orders 
        SET 
            status = '$status',
            tracking_number = " . ($tracking_number ? "'$tracking_number'" : "NULL") . ",
            estimated_delivery = " . ($estimated_delivery ? "'$estimated_delivery'" : "NULL") . "
        WHERE id = $order_id
    ";
    
    if ($conn->query($update_query)) {
        $success = "Order #$order_id has been updated successfully!";
    } else {
        $error = "Error updating order: " . $conn->error;
    }
}

// Get all orders with user details and item count
$orders_query = "
    SELECT 
        o.*, 
        u.username, 
        u.email,
        u.phone,
        COUNT(oi.id) as item_count,
        SUM(oi.quantity * oi.price) as order_total,
        MAX(o.updated_at) as last_updated
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$orders = $conn->query($orders_query);

if (!$orders) {
    $error = "Error fetching orders: " . $conn->error;
}

// Get order statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
        COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
        COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
        SUM(total_amount) as total_revenue
    FROM orders
";

$stats = $conn->query($stats_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - FoodieHub Admin</title>
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

        .logo i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
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
            padding: 25px;
            transition: margin-left 0.3s ease;
        }

        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            right: 15px;
            background: #2f3542;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
            font-size: 1.2em;
        }

        .orders-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }

        .view-btn { background: #007bff; color: white; }
        .view-btn:hover { background: #0056b3; }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 25px;
            border-radius: 10px;
            position: relative;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .update-btn {
            background: #28a745;
            color: white;
        }

        .update-btn:hover {
            background: #218838;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 15px;
            margin-top: 20px;
            border-top: 1px solid #eee;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #333;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading:after {
            content: '';
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                padding: 15px;
            }
            
            .menu-toggle {
                display: block;
            }

            .orders-container {
                padding: 15px;
            }

            th, td {
                padding: 12px;
            }

            .modal-content {
                width: 95%;
                margin: 20px auto;
                padding: 15px;
            }

            .action-btn {
                padding: 6px 12px;
            }

            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .action-btn {
                width: 100%;
            }

            .alert {
                padding: 10px;
                font-size: 14px;
            }
        }

        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 1.5em;
            font-weight: bold;
            color: #2f3542;
            margin: 0;
        }

        .stat-card.pending { border-top: 3px solid #ffc107; }
        .stat-card.processing { border-top: 3px solid #17a2b8; }
        .stat-card.delivered { border-top: 3px solid #28a745; }
        .stat-card.cancelled { border-top: 3px solid #dc3545; }
        .stat-card.total { border-top: 3px solid #6c757d; }
        .stat-card.revenue { border-top: 3px solid #28a745; }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <aside class="sidebar" id="sidebar">
        <a href="dashboard.php" class="logo">
            <i class="fas fa-utensils"></i>
            <span>FoodieHub Admin</span>
        </a>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link">
                    <i class="fas fa-hamburger"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link">
                    <i class="fas fa-list"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>Orders Management</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Order Statistics -->
        <div class="stats-grid">
            <div class="stat-card total">
                <h3>Total Orders</h3>
                <p><?php echo number_format($stats['total_orders']); ?></p>
            </div>
            <div class="stat-card pending">
                <h3>Pending</h3>
                <p><?php echo number_format($stats['pending_orders']); ?></p>
            </div>
            <div class="stat-card processing">
                <h3>Processing</h3>
                <p><?php echo number_format($stats['processing_orders']); ?></p>
            </div>
            <div class="stat-card delivered">
                <h3>Delivered</h3>
                <p><?php echo number_format($stats['delivered_orders']); ?></p>
            </div>
            <div class="stat-card cancelled">
                <h3>Cancelled</h3>
                <p><?php echo number_format($stats['cancelled_orders']); ?></p>
            </div>
            <div class="stat-card revenue">
                <h3>Total Revenue</h3>
                <p><i class="fas fa-bangladeshi-taka-sign"></i> <?php echo number_format($stats['total_revenue'], 2); ?></p>
            </div>
        </div>

        <div class="orders-container">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders && $orders->num_rows > 0): ?>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['username']); ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td><?php echo $order['item_count']; ?> items</td>
                                    <td>
                                        <i class="fas fa-bangladeshi-taka-sign"></i> 
                                        <?php echo number_format($order['order_total'], 2); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <button class="action-btn view-btn" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="action-btn update-btn" onclick="openUpdateModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                            <i class="fas fa-edit"></i> Update
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Update Status Modal -->
    <div class="modal" id="updateModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Order Status</h2>
                <button type="button" class="close-modal" onclick="closeModal('updateModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="updateOrderId">
                <div class="form-group">
                    <label for="updateStatus">Status</label>
                    <select name="status" id="updateStatus" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group" id="trackingNumberGroup" style="display: none;">
                    <label for="tracking_number">Tracking Number</label>
                    <input type="text" name="tracking_number" id="tracking_number" class="form-control">
                </div>
                <div class="form-group" id="estimatedDeliveryGroup" style="display: none;">
                    <label for="estimated_delivery">Estimated Delivery</label>
                    <input type="datetime-local" name="estimated_delivery" id="estimated_delivery" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="action-btn" onclick="closeModal('updateModal')">Cancel</button>
                    <button type="submit" name="update_status" class="action-btn update-btn">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Order Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
                <button type="button" class="close-modal" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="orderDetails">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
                <div class="order-content"></div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            sidebar.classList.toggle('active');
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

        function openUpdateModal(orderId, currentStatus) {
            document.getElementById('updateOrderId').value = orderId;
            document.getElementById('updateStatus').value = currentStatus;
            
            // Show/hide tracking number and estimated delivery based on status
            const status = document.getElementById('updateStatus').value;
            toggleAdditionalFields(status);
            
            document.getElementById('updateModal').style.display = 'block';
        }

        // Add event listener to status select
        document.getElementById('updateStatus').addEventListener('change', function() {
            toggleAdditionalFields(this.value);
        });

        function toggleAdditionalFields(status) {
            const trackingNumberGroup = document.getElementById('trackingNumberGroup');
            const estimatedDeliveryGroup = document.getElementById('estimatedDeliveryGroup');
            
            if (status === 'processing') {
                trackingNumberGroup.style.display = 'block';
                estimatedDeliveryGroup.style.display = 'block';
            } else {
                trackingNumberGroup.style.display = 'none';
                estimatedDeliveryGroup.style.display = 'none';
            }
        }

        function viewOrder(orderId) {
            const modal = document.getElementById('viewModal');
            const loading = modal.querySelector('.loading');
            const content = modal.querySelector('.order-content');
            
            modal.style.display = 'block';
            loading.style.display = 'block';
            content.innerHTML = '';
            
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    loading.style.display = 'none';
                    if (data.error) {
                        content.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                        return;
                    }
                    
                    // Format the order details HTML
                    let html = `
                        <div class="order-section">
                            <h3>Customer Information</h3>
                            <p><strong>Name:</strong> ${data.customer.name || data.customer.username}</p>
                            <p><strong>Email:</strong> ${data.customer.email}</p>
                            <p><strong>Phone:</strong> ${data.customer.phone || 'N/A'}</p>
                        </div>

                        <div class="order-section">
                            <h3>Order Information</h3>
                            <p><strong>Order ID:</strong> #${data.order.id}</p>
                            <p><strong>Status:</strong> <span class="status-badge status-${data.order.status.toLowerCase()}">${data.order.status}</span></p>
                            <p><strong>Payment Method:</strong> ${data.order.payment_method || 'N/A'}</p>
                            <p><strong>Payment Status:</strong> ${data.order.payment_status}</p>
                            <p><strong>Order Date:</strong> ${data.order.created_at}</p>
                            <p><strong>Last Updated:</strong> ${data.order.updated_at}</p>
                            ${data.order.tracking_number ? `<p><strong>Tracking Number:</strong> ${data.order.tracking_number}</p>` : ''}
                            ${data.order.estimated_delivery ? `<p><strong>Estimated Delivery:</strong> ${data.order.estimated_delivery}</p>` : ''}
                        </div>

                        <div class="order-section">
                            <h3>Delivery Information</h3>
                            <p><strong>Address:</strong> ${data.order.delivery_address || 'N/A'}</p>
                            <p><strong>Phone:</strong> ${data.order.delivery_phone || 'N/A'}</p>
                            ${data.order.delivery_notes ? `<p><strong>Notes:</strong> ${data.order.delivery_notes}</p>` : ''}
                        </div>

                        <div class="order-section">
                            <h3>Order Items</h3>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.items.map(item => `
                                            <tr>
                                                <td>
                                                    <div class="product-info">
                                                        ${item.image ? `<img src="../assets/images/products/${item.image}" alt="${item.product_name}" class="product-thumbnail">` : ''}
                                                        <span>${item.product_name}</span>
                                                    </div>
                                                </td>
                                                <td>${item.category || 'N/A'}</td>
                                                <td><i class="fas fa-bangladeshi-taka-sign"></i> ${parseFloat(item.price).toFixed(2)}</td>
                                                <td>${item.quantity}</td>
                                                <td><i class="fas fa-bangladeshi-taka-sign"></i> ${parseFloat(item.subtotal).toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-right"><strong>Total Amount:</strong></td>
                                            <td><i class="fas fa-bangladeshi-taka-sign"></i> ${parseFloat(data.order.total_amount).toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `;
                    content.innerHTML = html;
                })
                .catch(error => {
                    loading.style.display = 'none';
                    content.innerHTML = `<div class="alert alert-danger">Error loading order details: ${error.message}</div>`;
                });
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modalId === 'viewModal') {
                const content = modal.querySelector('.order-content');
                content.innerHTML = '';
            }
            modal.style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        };

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

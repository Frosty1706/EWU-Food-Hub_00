<?php
// File: C:\xampp\htdocs\EWU Food Hub\admin\dashboard.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Total Users
$users_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
$total_users = mysqli_fetch_assoc($users_query)['total'];

// Total Customers
$customers_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$total_customers = mysqli_fetch_assoc($customers_query)['total'];

// Total Restaurant Owners
$restaurants_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'restaurant'");
$total_restaurants = mysqli_fetch_assoc($restaurants_query)['total'];

// Total Riders
$riders_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'rider'");
$total_riders = mysqli_fetch_assoc($riders_query)['total'];

// Total Orders
$orders_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
$total_orders = mysqli_fetch_assoc($orders_query)['total'];

// Total Revenue (delivered orders)
$revenue_query = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'");
$total_revenue = mysqli_fetch_assoc($revenue_query)['total'] ?? 0;

// Admin Commission (5%)
$commission_query = mysqli_query($conn, "SELECT SUM(admin_commission) as total FROM orders WHERE status = 'delivered'");
$total_commission = mysqli_fetch_assoc($commission_query)['total'] ?? 0;

// Pending Orders
$pending_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$total_pending = mysqli_fetch_assoc($pending_query)['total'];

// Recent Orders
$recent_orders = mysqli_query($conn, "SELECT o.*, u.full_name as customer_name, r.restaurant_name 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    JOIN restaurants r ON o.restaurant_id = r.id 
    ORDER BY o.created_at DESC LIMIT 10");

include '../includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">👥</div>
        <div class="stat-details">
            <h3><?php echo $total_users; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">🛒</div>
        <div class="stat-details">
            <h3><?php echo $total_customers; ?></h3>
            <p>Customers</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">🍽</div>
        <div class="stat-details">
            <h3><?php echo $total_restaurants; ?></h3>
            <p>Restaurants</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">🏍</div>
        <div class="stat-details">
            <h3><?php echo $total_riders; ?></h3>
            <p>Riders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal">📦</div>
        <div class="stat-details">
            <h3><?php echo $total_orders; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">⏳</div>
        <div class="stat-details">
            <h3><?php echo $total_pending; ?></h3>
            <p>Pending Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div class="stat-details">
            <h3>৳<?php echo number_format($total_revenue, 2); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">💎</div>
        <div class="stat-details">
            <h3>৳<?php echo number_format($total_commission, 2); ?></h3>
            <p>Admin Commission (5%)</p>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>📋 Recent Orders</h3>
    </div>
    <?php if (mysqli_num_rows($recent_orders) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Restaurant</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                <td><strong>৳<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                <td><?php echo strtoupper($order['payment_method']); ?></td>
                <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>No Orders Yet</h3>
        <p>Orders will appear here once customers start ordering.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

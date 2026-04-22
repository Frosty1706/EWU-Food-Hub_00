<?php
// File: C:\xampp\htdocs\EWU Food Hub\includes\sidebar.php

$role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">🍔</div>
        <h2>EWU Food Hub</h2>
        <span class="sidebar-subtitle">Online Food Delivery</span>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
            <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="manage_users.php" class="nav-link <?php echo $current_page === 'manage_users' ? 'active' : ''; ?>">
                <span class="nav-icon">👥</span> Manage Users
            </a>
            <a href="manage_orders.php" class="nav-link <?php echo $current_page === 'manage_orders' ? 'active' : ''; ?>">
                <span class="nav-icon">📦</span> All Orders
            </a>
        <?php elseif ($role === 'restaurant'): ?>
            <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="manage_foods.php" class="nav-link <?php echo $current_page === 'manage_foods' ? 'active' : ''; ?>">
                <span class="nav-icon">🍕</span> Manage Foods
            </a>
            <a href="orders.php" class="nav-link <?php echo $current_page === 'orders' ? 'active' : ''; ?>">
                <span class="nav-icon">📦</span> Orders
            </a>
            <a href="assign_rider.php" class="nav-link <?php echo $current_page === 'assign_rider' ? 'active' : ''; ?>">
                <span class="nav-icon">🏍</span> Assign Rider
            </a>
        <?php elseif ($role === 'rider'): ?>
            <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="deliveries.php" class="nav-link <?php echo $current_page === 'deliveries' ? 'active' : ''; ?>">
                <span class="nav-icon">🚚</span> Deliveries
            </a>
            <a href="chat.php" class="nav-link <?php echo $current_page === 'chat' ? 'active' : ''; ?>">
                <span class="nav-icon">💬</span> Chat
            </a>
        <?php elseif ($role === 'customer'): ?>
            <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="browse_foods.php" class="nav-link <?php echo $current_page === 'browse_foods' ? 'active' : ''; ?>">
                <span class="nav-icon">🍔</span> Browse Foods
            </a>
            <a href="cart.php" class="nav-link <?php echo $current_page === 'cart' ? 'active' : ''; ?>">
                <span class="nav-icon">🛒</span> My Cart
            </a>
            <a href="orders.php" class="nav-link <?php echo $current_page === 'orders' ? 'active' : ''; ?>">
                <span class="nav-icon">📦</span> My Orders
            </a>
            <a href="chat.php" class="nav-link <?php echo $current_page === 'chat' ? 'active' : ''; ?>">
                <span class="nav-icon">💬</span> Chat
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="../logout.php" class="nav-link nav-logout">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </div>
</div>

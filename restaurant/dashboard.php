<?php
// File: C:\xampp\htdocs\EWU Food Hub\restaurant\dashboard.php

session_start();
require_once '../config/db.php';

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header('Location: ../auth/login.php');
    exit();
}

$owner_id = $_SESSION['user_id'];

// Get restaurant info
$restaurant_query = mysqli_query($conn, "SELECT * FROM restaurants WHERE owner_id = $owner_id LIMIT 1");
$restaurant = mysqli_fetch_assoc($restaurant_query);
$restaurant_id = $restaurant['id'] ?? 0;

// Total foods
$foods_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM foods WHERE restaurant_id = $restaurant_id");
$total_foods = mysqli_fetch_assoc($foods_query)['total'] ?? 0;

// Total orders
$orders_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE restaurant_id = $restaurant_id");
$total_orders = mysqli_fetch_assoc($orders_query)['total'] ?? 0;

// Total income (delivered orders)
// Replace 'restaurant_earning' with 'total_amount' or another valid column
$income_query = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE restaurant_id = $restaurant_id AND status = 'delivered'");
$total_income = mysqli_fetch_assoc($income_query)['total'] ?? 0;

// Available riders assigned to this restaurant
$riders_query = mysqli_query($conn, "SELECT u.id, u.full_name, ra.is_available 
    FROM users u 
    JOIN rider_availability ra ON u.id = ra.rider_id 
    WHERE u.role = 'rider'");

include '../includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon orange">🍕</div>
        <div class="stat-details">
            <h3><?php echo $total_foods; ?></h3>
            <p>Total Foods</p>
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
        <div class="stat-icon green">💰</div>
        <div class="stat-details">
            <h3>৳<?php echo number_format($total_income, 2); ?></h3>
            <p>Total Income</p>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>🏍 Available Riders</h3>
    </div>
    <?php if (mysqli_num_rows($riders_query) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Rider ID</th>
                <th>Name</th>
                <th>Availability</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($rider = mysqli_fetch_assoc($riders_query)): ?>
            <tr>
                <td>#<?php echo $rider['id']; ?></td>
                <td><?php echo htmlspecialchars($rider['full_name']); ?></td>
                <td>
                    <?php if ($rider['is_available']): ?>
                        <span class="badge badge-available">Available</span>
                    <?php else: ?>
                        <span class="badge badge-unavailable">Unavailable</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">🏍</div>
        <h3>No Riders Available</h3>
        <p>Please check back later or assign riders.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

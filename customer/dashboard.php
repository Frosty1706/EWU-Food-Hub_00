<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];
$current_name = $_SESSION['full_name'] ?? 'Guest';

// Fetch recent orders
$orders_query = mysqli_query($conn, "SELECT o.*, r.restaurant_name 
    FROM orders o 
    JOIN restaurants r ON o.restaurant_id = r.id 
    WHERE o.customer_id = $customer_id 
    ORDER BY o.created_at DESC LIMIT 5");

// Fetch available foods (limit 8)
$foods_query = mysqli_query($conn, "SELECT f.*, r.restaurant_name 
    FROM foods f 
    JOIN restaurants r ON f.restaurant_id = r.id 
    WHERE f.availability = 'available' 
    ORDER BY f.created_at DESC LIMIT 8");

include '../includes/header.php';
?>

<div class="section-title">Welcome, <?php echo htmlspecialchars($current_name); ?>!</div>

<div class="cards-grid">
    <div class="stat-card">
        <div class="stat-icon teal">🍔</div>
        <div class="stat-details">
            <h3><?php echo mysqli_num_rows($foods_query); ?></h3>
            <p>Available Foods</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">📦</div>
        <div class="stat-details">
            <h3><?php echo mysqli_num_rows($orders_query); ?></h3>
            <p>Recent Orders</p>
        </div>
    </div>
</div>

<div class="section-title">Browse Foods</div>
<div class="cards-grid">
    <?php if (mysqli_num_rows($foods_query) > 0): ?>
        <?php while ($food = mysqli_fetch_assoc($foods_query)): ?>
            <div class="food-card">
                <div class="food-card-image">
                    <?php if (!empty($food['image']) && file_exists('../uploads/foods/' . $food['image'])): ?>
                        <img src="../uploads/foods/<?php echo htmlspecialchars($food['image']); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
                    <?php else: ?>
                        <span>🍽️</span>
                    <?php endif; ?>
                </div>
                <div class="food-card-body">
                    <h4><?php echo htmlspecialchars($food['name']); ?></h4>
                    <p><?php echo htmlspecialchars($food['restaurant_name']); ?></p>
                    <p>৳<?php echo number_format($food['price'], 2); ?></p>
                    <form action="cart_process.php" method="POST">
                        <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                        <input type="hidden" name="action" value="add_to_cart">
                        <button type="submit" class="btn btn-primary btn-block">Add to Cart</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🍽️</div>
            <h3>No Foods Available</h3>
            <p>Please check back later.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

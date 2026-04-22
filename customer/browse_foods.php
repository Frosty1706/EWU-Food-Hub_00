<?php
// File: C:\xampp\htdocs\EWU Food Hub\customer\browse_foods.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, trim($_GET['category'])) : '';

$where = "WHERE f.availability = 'available'";

if ($search !== '') {
    $where .= " AND (f.name LIKE '%$search%' OR r.restaurant_name LIKE '%$search%')";
}

if ($category !== '') {
    $where .= " AND f.category = '$category'";
}

$foods_query = mysqli_query($conn, "SELECT f.*, r.restaurant_name 
    FROM foods f 
    JOIN restaurants r ON f.restaurant_id = r.id 
    $where 
    ORDER BY f.created_at DESC");

include '../includes/header.php';
?>

<div class="section-title">Browse Foods</div>

<form method="GET" action="" class="filter-bar">
    <input type="text" name="search" placeholder="Search foods or restaurants..." value="<?php echo htmlspecialchars($search); ?>">
    <select name="category">
        <option value="">All Categories</option>
        <?php
        $categories_query = mysqli_query($conn, "SELECT DISTINCT category FROM foods WHERE category IS NOT NULL AND category != ''");
        while ($cat = mysqli_fetch_assoc($categories_query)) {
            $selected = ($category === $cat['category']) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($cat['category']) . '" ' . $selected . '>' . htmlspecialchars($cat['category']) . '</option>';
        }
        ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
</form>

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
            <h3>No Foods Found</h3>
            <p>Try adjusting your search or filter criteria.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

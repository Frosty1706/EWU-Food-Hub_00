<?php
// File: C:\xampp\htdocs\EWU Food Hub\restaurant\assign_rider.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header('Location: ../auth/login.php');
    exit();
}

$owner_id = $_SESSION['user_id'];

// Get restaurant info
$restaurant_query = mysqli_query($conn, "SELECT * FROM restaurants WHERE owner_id = $owner_id LIMIT 1");
$restaurant = mysqli_fetch_assoc($restaurant_query);
$restaurant_id = $restaurant['id'] ?? 0;

// Get order ID from GET or POST
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : (isset($_POST['order_id']) ? intval($_POST['order_id']) : 0);

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

// Verify order belongs to this restaurant
$order_check = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND restaurant_id = $restaurant_id LIMIT 1");
if (mysqli_num_rows($order_check) === 0) {
    header('Location: orders.php');
    exit();
}

$order = mysqli_fetch_assoc($order_check);

// Fetch available riders
$riders_query = mysqli_query($conn, "SELECT u.id, u.full_name, ra.is_available 
    FROM users u 
    JOIN rider_availability ra ON u.id = ra.rider_id 
    WHERE u.role = 'rider' AND ra.is_available = 1");

$assign_success = '';
$assign_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_rider'])) {
    $rider_id = intval($_POST['rider_id']);

    // Check if rider is available
    $rider_check = mysqli_query($conn, "SELECT * FROM rider_availability WHERE rider_id = $rider_id AND is_available = 1");
    if (mysqli_num_rows($rider_check) === 1) {
        // Assign rider to order
        $update_order = mysqli_query($conn, "UPDATE orders SET rider_id = $rider_id, status = 'assigned' WHERE id = $order_id");
        if ($update_order) {
            // Optionally mark rider as unavailable
            mysqli_query($conn, "UPDATE rider_availability SET is_available = 0 WHERE rider_id = $rider_id");

            $assign_success = 'Rider assigned successfully.';
            // Refresh order data
            $order['rider_id'] = $rider_id;
            $order['status'] = 'assigned';
        } else {
            $assign_error = 'Failed to assign rider. Please try again.';
        }
    } else {
        $assign_error = 'Selected rider is not available.';
    }
}

include '../includes/header.php';
?>

<div class="section-title">Assign Rider for Order #<?php echo $order_id; ?></div>

<?php if ($assign_success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($assign_success); ?></div>
<?php endif; ?>

<?php if ($assign_error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($assign_error); ?></div>
<?php endif; ?>

<form method="POST" action="">
    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
    <div class="form-group">
        <label for="rider_id">Select Rider</label>
        <select id="rider_id" name="rider_id" required>
            <option value="" disabled selected>-- Choose a Rider --</option>
            <?php while ($rider = mysqli_fetch_assoc($riders_query)): ?>
                <option value="<?php echo $rider['id']; ?>"><?php echo htmlspecialchars($rider['full_name']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit" name="assign_rider" class="btn btn-primary">Assign Rider</button>
    <a href="orders.php" class="btn btn-warning">Back to Orders</a>
</form>

<?php include '../includes/footer.php'; ?>

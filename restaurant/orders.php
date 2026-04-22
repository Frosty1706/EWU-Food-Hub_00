<?php
// File: C:\xampp\htdocs\EWU Food Hub\restaurant\orders.php

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

// Fetch orders for this restaurant
$orders_query = mysqli_query($conn, "SELECT o.*, u.full_name as customer_name, u.phone as customer_phone, 
    IFNULL(rider.full_name, 'Not Assigned') as rider_name
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    LEFT JOIN users rider ON o.rider_id = rider.id
    WHERE o.restaurant_id = $restaurant_id
    ORDER BY o.created_at DESC");

include '../includes/header.php';
?>

<div class="section-title">Orders</div>

<?php if (mysqli_num_rows($orders_query) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Rider</th>
                <th>Total Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($orders_query)): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                <td><?php echo htmlspecialchars($order['rider_name']); ?></td>
                <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo strtoupper($order['payment_method']); ?></td>
                <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                <td>
                    <a href="assign_rider.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">Assign Rider</a>
                    <!-- Additional actions can be added here -->
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>No Orders Found</h3>
        <p>No orders have been placed for your restaurant yet.</p>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

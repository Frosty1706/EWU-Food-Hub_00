<?php
// File: C:\xampp\htdocs\EWU Food Hub\customer\orders.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

// Fetch customer orders
$orders_query = mysqli_query($conn, "SELECT o.*, r.restaurant_name 
    FROM orders o 
    JOIN restaurants r ON o.restaurant_id = r.id 
    WHERE o.customer_id = $customer_id 
    ORDER BY o.created_at DESC");

include '../includes/header.php';
?>

<div class="section-title">My Orders</div>

<?php if (mysqli_num_rows($orders_query) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Restaurant</th>
                <th>Total Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($orders_query)): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo strtoupper($order['payment_method']); ?></td>
                <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                <td>
                    <?php if (!in_array($order['status'], ['delivered', 'cancelled'])): ?>
                        <form method="POST" action="order_process.php" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <input type="hidden" name="action" value="cancel_order">
                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>No Orders Found</h3>
        <p>You have not placed any orders yet.</p>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

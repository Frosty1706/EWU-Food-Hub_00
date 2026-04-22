<?php
// File: C:\xampp\htdocs\EWU Food Hub\admin\manage_orders.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$success = '';
if (isset($_SESSION['admin_success'])) {
    $success = $_SESSION['admin_success'];
    unset($_SESSION['admin_success']);
}

// Filter by status
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$where = "";
if (!empty($filter_status)) {
    $where = "WHERE o.status = '$filter_status'";
}

$orders_query = mysqli_query($conn, "SELECT o.*, u.full_name as customer_name, u.phone as customer_phone,
    r.restaurant_name, 
    IFNULL(rd.full_name, 'Not Assigned') as rider_name
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    JOIN restaurants r ON o.restaurant_id = r.id 
    LEFT JOIN users rd ON o.rider_id = rd.id 
    $where
    ORDER BY o.created_at DESC");

include '../includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="table-container">
    <div class="table-header">
        <h3>📦 All Orders</h3>
        <div class="filter-bar">
            <form method="GET" action="">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="assigned" <?php echo $filter_status === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                    <option value="accepted" <?php echo $filter_status === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                    <option value="picked" <?php echo $filter_status === 'picked' ? 'selected' : ''; ?>>Picked</option>
                    <option value="on_the_way" <?php echo $filter_status === 'on_the_way' ? 'selected' : ''; ?>>On The Way</option>
                    <option value="delivered" <?php echo $filter_status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <noscript><button type="submit" class="btn btn-sm btn-primary">Filter</button></noscript>
            </form>
        </div>
    </div>

    <?php if (mysqli_num_rows($orders_query) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Restaurant</th>
                <th>Rider</th>
                <th>Amount</th>
                <th>Commission</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($orders_query)): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                    <br><small><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                <td><?php echo htmlspecialchars($order['rider_name']); ?></td>
                <td><strong>৳<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                <td>৳<?php echo number_format($order['admin_commission'], 2); ?></td>
                <td><?php echo strtoupper($order['payment_method']); ?></td>
                <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                <td>
                    <div class="action-btns">
                        <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                        <form method="POST" action="admin_process.php">
                            <input type="hidden" name="action" value="cancel_order">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>No Orders Found</h3>
        <p>No orders match the selected filter.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

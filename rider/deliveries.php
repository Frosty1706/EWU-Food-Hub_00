<?php
// File: C:\xampp\htdocs\EWU Food Hub\rider\deliveries.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: ../auth/login.php');
    exit();
}

$rider_id = $_SESSION['user_id'];

// Fetch deliveries assigned to this rider that are in progress (accepted, picked, on_the_way)
$in_progress_statuses = "'accepted','picked','on_the_way'";
$deliveries_query = mysqli_query($conn, "SELECT o.*, c.full_name as customer_name, c.phone as customer_phone, r.restaurant_name
    FROM orders o
    JOIN users c ON o.customer_id = c.id
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.rider_id = $rider_id AND o.status IN ($in_progress_statuses)
    ORDER BY o.updated_at DESC");

// Fetch completed deliveries (delivered)
$completed_deliveries_query = mysqli_query($conn, "SELECT o.*, c.full_name as customer_name, r.restaurant_name
    FROM orders o
    JOIN users c ON o.customer_id = c.id
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.rider_id = $rider_id AND o.status = 'delivered'
    ORDER BY o.updated_at DESC LIMIT 10");

// Calculate total income (3% commission on delivered orders)
$income_query = mysqli_query($conn, "SELECT SUM(rider_commission) as total_income FROM orders WHERE rider_id = $rider_id AND status = 'delivered'");
$income_row = mysqli_fetch_assoc($income_query);
$total_income = $income_row['total_income'] ?? 0;

include '../includes/header.php';
?>

<div class="section-title">My Deliveries</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purple">🚚</div>
        <div class="stat-details">
            <h3><?php echo mysqli_num_rows($deliveries_query); ?></h3>
            <p>Deliveries In Progress</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div class="stat-details">
            <h3>৳<?php echo number_format($total_income, 2); ?></h3>
            <p>Total Income (3% commission)</p>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>🚚 Deliveries In Progress</h3>
    </div>
    <?php if (mysqli_num_rows($deliveries_query) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Restaurant</th>
                <th>Total Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Last Update</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($deliveries_query)): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo strtoupper($order['payment_method']); ?></td>
                <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                <td><?php echo date('d M Y, h:i A', strtotime($order['updated_at'])); ?></td>
                <td>
                    <form method="POST" action="rider_process.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="new_status" required>
                            <?php
                            $statuses = ['accepted', 'picked', 'on_the_way', 'delivered'];
                            foreach ($statuses as $status) {
                                $selected = ($order['status'] === $status) ? 'selected' : '';
                                echo "<option value=\"$status\" $selected>" . ucfirst(str_replace('_', ' ', $status)) . "</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="action" value="update_status" class="btn btn-sm btn-primary">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">🚚</div>
        <h3>No Deliveries In Progress</h3>
        <p>You have no active deliveries currently.</p>
    </div>
    <?php endif; ?>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>✅ Completed Deliveries</h3>
    </div>
    <?php if (mysqli_num_rows($completed_deliveries_query) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Restaurant</th>
                <th>Total Amount</th>
                <th>Delivered On</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = mysqli_fetch_assoc($completed_deliveries_query)): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['restaurant_name']); ?></td>
                <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo date('d M Y, h:i A', strtotime($order['updated_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">✅</div>
        <h3>No Completed Deliveries</h3>
        <p>You have not completed any deliveries yet.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

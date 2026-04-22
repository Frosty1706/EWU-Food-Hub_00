<?php
// File: C:\xampp\htdocs\EWU Food Hub\admin\manage_users.php

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

$error = '';
if (isset($_SESSION['admin_error'])) {
    $error = $_SESSION['admin_error'];
    unset($_SESSION['admin_error']);
}

// Filter by role
$filter_role = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';

$where = "WHERE role != 'admin'";
if (!empty($filter_role) && in_array($filter_role, ['customer', 'restaurant', 'rider'])) {
    $where .= " AND role = '$filter_role'";
}

$users_query = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC");

include '../includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="table-container">
    <div class="table-header">
        <h3>👥 All Users</h3>
        <div class="filter-bar">
            <form method="GET" action="">
                <select name="role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="customer" <?php echo $filter_role === 'customer' ? 'selected' : ''; ?>>Customers</option>
                    <option value="restaurant" <?php echo $filter_role === 'restaurant' ? 'selected' : ''; ?>>Restaurant Owners</option>
                    <option value="rider" <?php echo $filter_role === 'rider' ? 'selected' : ''; ?>>Riders</option>
                </select>
                <noscript><button type="submit" class="btn btn-sm btn-primary">Filter</button></noscript>
            </form>
        </div>
    </div>

    <?php if (mysqli_num_rows($users_query) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($users_query)): ?>
            <tr>
                <td>#<?php echo $user['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                <td><span class="badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                <td><span class="badge badge-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                <td>
                    <div class="action-btns">
                        <?php if ($user['status'] === 'active'): ?>
                            <form method="POST" action="admin_process.php">
                                <input type="hidden" name="action" value="deactivate_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-warning">Deactivate</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="admin_process.php">
                                <input type="hidden" name="action" value="activate_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-success">Activate</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="admin_process.php">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">👤</div>
        <h3>No Users Found</h3>
        <p>No users match the selected filter.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

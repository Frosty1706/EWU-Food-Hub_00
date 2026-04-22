<?php
// File: C:\xampp\htdocs\EWU Food Hub\admin\admin_process.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ACTIVATE USER
    if ($action === 'activate_user') {
        $user_id = intval($_POST['user_id']);
        $query = "UPDATE users SET status = 'active' WHERE id = $user_id AND role != 'admin'";
        if (mysqli_query($conn, $query)) {
            $_SESSION['admin_success'] = 'User activated successfully.';
        } else {
            $_SESSION['admin_error'] = 'Failed to activate user.';
        }
        header('Location: manage_users.php');
        exit();
    }

    // DEACTIVATE USER
    if ($action === 'deactivate_user') {
        $user_id = intval($_POST['user_id']);
        $query = "UPDATE users SET status = 'inactive' WHERE id = $user_id AND role != 'admin'";
        if (mysqli_query($conn, $query)) {
            $_SESSION['admin_success'] = 'User deactivated successfully.';
        } else {
            $_SESSION['admin_error'] = 'Failed to deactivate user.';
        }
        header('Location: manage_users.php');
        exit();
    }

    // DELETE USER
    if ($action === 'delete_user') {
        $user_id = intval($_POST['user_id']);

        // Check if user is restaurant owner and delete related foods images
        $check_role = mysqli_query($conn, "SELECT role FROM users WHERE id = $user_id");
        $user_data = mysqli_fetch_assoc($check_role);

        if ($user_data['role'] === 'restaurant') {
            $rest_query = mysqli_query($conn, "SELECT id FROM restaurants WHERE owner_id = $user_id");
            while ($rest = mysqli_fetch_assoc($rest_query)) {
                $foods_query = mysqli_query($conn, "SELECT image FROM foods WHERE restaurant_id = " . $rest['id']);
                while ($food = mysqli_fetch_assoc($foods_query)) {
                    if (!empty($food['image']) && file_exists('../uploads/foods/' . $food['image'])) {
                        unlink('../uploads/foods/' . $food['image']);
                    }
                }
            }
        }

        $query = "DELETE FROM users WHERE id = $user_id AND role != 'admin'";
        if (mysqli_query($conn, $query)) {
            $_SESSION['admin_success'] = 'User removed successfully.';
        } else {
            $_SESSION['admin_error'] = 'Failed to remove user.';
        }
        header('Location: manage_users.php');
        exit();
    }

    // CANCEL ORDER
    if ($action === 'cancel_order') {
        $order_id = intval($_POST['order_id']);
        $query = "UPDATE orders SET status = 'cancelled' WHERE id = $order_id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['admin_success'] = 'Order cancelled successfully.';
        } else {
            $_SESSION['admin_error'] = 'Failed to cancel order.';
        }
        header('Location: manage_orders.php');
        exit();
    }
}

header('Location: dashboard.php');
exit();
?>

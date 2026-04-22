<?php
// File: C:\xampp\htdocs\EWU Food Hub\rider\rider_process.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: ../auth/login.php');
    exit();
}

$rider_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $order_id = intval($_POST['order_id'] ?? 0);

    if ($order_id <= 0) {
        $_SESSION['rider_error'] = 'Invalid order ID.';
        header('Location: dashboard.php');
        exit();
    }

    // Verify order belongs to this rider
    $order_check = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND rider_id = $rider_id LIMIT 1");
    if (mysqli_num_rows($order_check) === 0) {
        $_SESSION['rider_error'] = 'Order not found or not assigned to you.';
        header('Location: dashboard.php');
        exit();
    }

    if ($action === 'accept_request') {
        // Accept delivery request
        $update = mysqli_query($conn, "UPDATE orders SET status = 'accepted' WHERE id = $order_id");
        if ($update) {
            $_SESSION['rider_success'] = 'Delivery request accepted.';
        } else {
            $_SESSION['rider_error'] = 'Failed to accept delivery request.';
        }
        header('Location: dashboard.php');
        exit();
    }

    if ($action === 'reject_request') {
        // Reject delivery request and unassign rider
        $update = mysqli_query($conn, "UPDATE orders SET status = 'pending', rider_id = NULL WHERE id = $order_id");
        if ($update) {
            // Optionally mark rider as available again
            mysqli_query($conn, "UPDATE rider_availability SET is_available = 1 WHERE rider_id = $rider_id");
            $_SESSION['rider_success'] = 'Delivery request rejected.';
        } else {
            $_SESSION['rider_error'] = 'Failed to reject delivery request.';
        }
        header('Location: dashboard.php');
        exit();
    }

    if ($action === 'update_status') {
        $new_status = $_POST['new_status'] ?? '';
        $allowed_statuses = ['accepted', 'picked', 'on_the_way', 'delivered'];

        if (!in_array($new_status, $allowed_statuses)) {
            $_SESSION['rider_error'] = 'Invalid status update.';
            header('Location: deliveries.php');
            exit();
        }

        $update = mysqli_query($conn, "UPDATE orders SET status = '$new_status', updated_at = NOW() WHERE id = $order_id");
        if ($update) {
            // If delivered, mark rider as available again
            if ($new_status === 'delivered') {
                mysqli_query($conn, "UPDATE rider_availability SET is_available = 1 WHERE rider_id = $rider_id");
            }
            $_SESSION['rider_success'] = 'Order status updated successfully.';
        } else {
            $_SESSION['rider_error'] = 'Failed to update order status.';
        }
        header('Location: deliveries.php');
        exit();
    }
}

header('Location: dashboard.php');
exit();
?>

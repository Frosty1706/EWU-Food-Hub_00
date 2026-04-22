<?php
// File: C:\xampp\htdocs\EWU Food Hub\customer\order_process.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'] ?? '';

    // Validate order ownership
    $order_check = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND customer_id = $customer_id");
    if (mysqli_num_rows($order_check) === 0) {
        $_SESSION['order_error'] = 'Invalid order.';
        header('Location: orders.php');
        exit();
    }

    // Cancel order (if allowed)
    if ($action === 'cancel_order') {
        $order = mysqli_fetch_assoc($order_check);
        if (!in_array($order['status'], ['delivered', 'cancelled'])) {
            $update = mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = $order_id");
            if ($update) {
                $_SESSION['order_success'] = 'Order cancelled successfully.';
            } else {
                $_SESSION['order_error'] = 'Failed to cancel order.';
            }
        } else {
            $_SESSION['order_error'] = 'Order cannot be cancelled at this stage.';
        }
        header('Location: orders.php');
        exit();
    }
}

header('Location: orders.php');
exit();
?>

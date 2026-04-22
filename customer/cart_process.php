<?php
// File: C:\xampp\htdocs\EWU Food Hub\customer\cart_process.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add to cart
    if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
        $food_id = intval($_POST['food_id']);
        if ($food_id > 0) {
            // Check if already in cart
            $check_query = "SELECT id, quantity FROM cart WHERE customer_id = $customer_id AND food_id = $food_id";
            $check_result = mysqli_query($conn, $check_query);
            if (mysqli_num_rows($check_result) > 0) {
                // Update quantity
                $row = mysqli_fetch_assoc($check_result);
                $new_qty = $row['quantity'] + 1;
                $update_query = "UPDATE cart SET quantity = $new_qty WHERE id = " . $row['id'];
                mysqli_query($conn, $update_query);
            } else {
                // Insert new
                $insert_query = "INSERT INTO cart (customer_id, food_id, quantity) VALUES ($customer_id, $food_id, 1)";
                mysqli_query($conn, $insert_query);
            }
        }
        header('Location: cart.php');
        exit();
    }

    // Update quantities
    if (isset($_POST['update']) && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $cart_id => $qty) {
            $cart_id = intval($cart_id);
            $qty = intval($qty);
            if ($cart_id > 0 && $qty > 0) {
                $update_query = "UPDATE cart SET quantity = $qty WHERE id = $cart_id AND customer_id = $customer_id";
                mysqli_query($conn, $update_query);
            }
        }
        header('Location: cart.php');
        exit();
    }

    // Remove item
    if (isset($_POST['remove'])) {
        $cart_id = intval($_POST['remove']);
        if ($cart_id > 0) {
            $delete_query = "DELETE FROM cart WHERE id = $cart_id AND customer_id = $customer_id";
            mysqli_query($conn, $delete_query);
        }
        header('Location: cart.php');
        exit();
    }
}

header('Location: cart.php');
exit();
?>

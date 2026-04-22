<?php
// File: C:\xampp\htdocs\EWU Food Hub\customer\checkout.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

// Fetch cart items
$cart_query = mysqli_query($conn, "SELECT c.*, f.price, f.restaurant_id 
    FROM cart c 
    JOIN foods f ON c.food_id = f.id 
    WHERE c.customer_id = $customer_id");

if (mysqli_num_rows($cart_query) === 0) {
    $_SESSION['checkout_error'] = 'Your cart is empty.';
    header('Location: cart.php');
    exit();
}

$total_amount = 0;
$restaurant_id = null;
while ($item = mysqli_fetch_assoc($cart_query)) {
    $total_amount += $item['price'] * $item['quantity'];
    $restaurant_id = $item['restaurant_id']; // Assuming all items are from the same restaurant
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $delivery_address = mysqli_real_escape_string($conn, trim($_POST['delivery_address']));
    $delivery_phone = mysqli_real_escape_string($conn, trim($_POST['delivery_phone']));

    if (empty($payment_method) || empty($delivery_address) || empty($delivery_phone)) {
        $_SESSION['checkout_error'] = 'All fields are required.';
        header('Location: checkout.php');
        exit();
    }

    // Calculate commissions
    $admin_commission = $total_amount * 0.05; // 5% admin commission
    $rider_commission = $total_amount * 0.03; // 3% rider commission
    $restaurant_earning = $total_amount - $admin_commission - $rider_commission;

    // Insert order
    $stmt = mysqli_prepare($conn, "INSERT INTO orders (customer_id, restaurant_id, total_amount, admin_commission, rider_commission, payment_method, delivery_address, delivery_phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, 'iidddsss', $customer_id, $restaurant_id, $total_amount, $admin_commission, $rider_commission, $payment_method, $delivery_address, $delivery_phone);

    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($conn);

        // Insert order items
        mysqli_data_seek($cart_query, 0); // Reset cart query pointer
        while ($item = mysqli_fetch_assoc($cart_query)) {
            $food_id = $item['food_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];

            $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, food_id, quantity, price) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_item, 'iiid', $order_id, $food_id, $quantity, $price);
            mysqli_stmt_execute($stmt_item);
        }

        // Clear cart
        mysqli_query($conn, "DELETE FROM cart WHERE customer_id = $customer_id");

        $_SESSION['checkout_success'] = 'Order placed successfully!';
        header('Location: orders.php');
        exit();
    } else {
        $_SESSION['checkout_error'] = 'Failed to place order. Please try again.';
        header('Location: checkout.php');
        exit();
    }
}

include '../includes/header.php';
?>

<div class="section-title">Checkout</div>

<form method="POST" action="">
    <div class="form-group">
        <label for="delivery_address">Delivery Address</label>
        <input type="text" id="delivery_address" name="delivery_address" required>
    </div>
    <div class="form-group">
        <label for="delivery_phone">Phone Number</label>
        <input type="text" id="delivery_phone" name="delivery_phone" required>
    </div>
    <div class="form-group">
        <label for="payment_method">Payment Method</label>
        <select id="payment_method" name="payment_method" required>
            <option value="COD">Cash on Delivery</option>
            <option value="bKash">bKash</option>
            <option value="Nagad">Nagad</option>
            <option value="Visa">Visa</option>
        </select>
    </div>
    <button type="submit" class="btn btn-success">Place Order</button>
</form>

<?php include '../includes/footer.php'; ?>

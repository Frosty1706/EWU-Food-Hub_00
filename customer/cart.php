<?php
// File: C:\xampp\htdocs\EWU Food Hub\customer\cart.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$customer_id = $_SESSION['user_id'];

// Fetch cart items with food and restaurant details
$cart_query = mysqli_query($conn, "SELECT c.id as cart_id, c.quantity, f.id as food_id, f.name, f.price, f.image, r.restaurant_name
    FROM cart c
    JOIN foods f ON c.food_id = f.id
    JOIN restaurants r ON f.restaurant_id = r.id
    WHERE c.customer_id = $customer_id");

$total_amount = 0;
while ($item = mysqli_fetch_assoc($cart_query)) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Re-run query for display
$cart_query = mysqli_query($conn, "SELECT c.id as cart_id, c.quantity, f.id as food_id, f.name, f.price, f.image, r.restaurant_name
    FROM cart c
    JOIN foods f ON c.food_id = f.id
    JOIN restaurants r ON f.restaurant_id = r.id
    WHERE c.customer_id = $customer_id");

include '../includes/header.php';
?>

<div class="section-title">My Cart</div>

<?php if (mysqli_num_rows($cart_query) > 0): ?>
<form action="cart_process.php" method="POST">
    <table>
        <thead>
            <tr>
                <th>Food</th>
                <th>Restaurant</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = mysqli_fetch_assoc($cart_query)): ?>
            <tr>
                <td>
                    <?php if (!empty($item['image']) && file_exists('../uploads/foods/' . $item['image'])): ?>
                        <img src="../uploads/foods/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:50px; height:50px; border-radius:8px; object-fit:cover; margin-right:8px;">
                    <?php else: ?>
                        <span style="font-size:24px; margin-right:8px;">🍽️</span>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($item['name']); ?>
                </td>
                <td><?php echo htmlspecialchars($item['restaurant_name']); ?></td>
                <td>৳<?php echo number_format($item['price'], 2); ?></td>
                <td>
                    <input type="number" name="quantities[<?php echo $item['cart_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="99" style="width:60px; padding:6px; border-radius:8px; border:1px solid #ccc;">
                </td>
                <td>৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                <td>
                    <button type="submit" name="remove" value="<?php echo $item['cart_id']; ?>" class="btn btn-danger btn-sm">Remove</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div style="margin-top:20px; text-align:right;">
        <strong>Total: ৳<?php echo number_format($total_amount, 2); ?></strong>
    </div>

    <div style="margin-top:20px; display:flex; justify-content: space-between;">
        <button type="submit" name="update" class="btn btn-warning">Update Quantities</button>
        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    </div>
</form>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Browse foods and add items to your cart.</p>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

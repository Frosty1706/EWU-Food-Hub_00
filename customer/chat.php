<?php
// File: customer/chat.php

session_start();
require_once '../config/db.php';

// ==========================
// AUTHENTICATION CHECK
// ==========================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$customer_id = intval($_SESSION['user_id']);

// ==========================
// AUTO-RESOLVE ORDER (UX FIX)
// ==========================
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {

    $latest_order_query = mysqli_query($conn, "
        SELECT id 
        FROM orders 
        WHERE customer_id = $customer_id AND rider_id IS NOT NULL
        ORDER BY created_at DESC 
        LIMIT 1
    ");

    if ($latest_order_query && mysqli_num_rows($latest_order_query) > 0) {
        $latest = mysqli_fetch_assoc($latest_order_query);
        header("Location: chat.php?order_id=" . $latest['id']);
        exit();
    }

    include '../includes/header.php';
    echo "<div style='padding:50px;text-align:center;'>";
    echo "<h2>No active chat available</h2>";
    echo "<p>Please place an order or wait for rider assignment.</p>";
    echo "<a href='orders.php' class='btn btn-primary'>Go to Orders</a>";
    echo "</div>";
    include '../includes/footer.php';
    exit();
}

// ==========================
// VALIDATE ORDER
// ==========================
$order_id = intval($_GET['order_id']);

$order_query = mysqli_query($conn, "
    SELECT o.*, u.full_name AS rider_name
    FROM orders o
    LEFT JOIN users u ON o.rider_id = u.id
    WHERE o.id = $order_id AND o.customer_id = $customer_id
");

if (!$order_query || mysqli_num_rows($order_query) === 0) {
    die("Unauthorized or invalid order.");
}

$order = mysqli_fetch_assoc($order_query);

// ==========================
// CHECK RIDER ASSIGNED
// ==========================
if (!$order['rider_id']) {
    include '../includes/header.php';
    echo "<div style='padding:50px;text-align:center;'>";
    echo "<h3>Rider not assigned yet</h3>";
    echo "<p>Chat will be available once a rider is assigned.</p>";
    echo "<a href='orders.php'>Back to Orders</a>";
    echo "</div>";
    include '../includes/footer.php';
    exit();
}

$rider_id = intval($order['rider_id']);

// ==========================
// HANDLE MESSAGE SEND
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $message = trim($_POST['message'] ?? '');

    if (!empty($message)) {

        $safe_msg = mysqli_real_escape_string($conn, $message);

        mysqli_query($conn, "
            INSERT INTO messages (order_id, sender_id, receiver_id, message)
            VALUES ($order_id, $customer_id, $rider_id, '$safe_msg')
        ");
    }

    header("Location: chat.php?order_id=$order_id");
    exit();
}

// ==========================
// FETCH CHAT MESSAGES
// ==========================
$messages_query = mysqli_query($conn, "
    SELECT * FROM messages
    WHERE order_id = $order_id
    ORDER BY created_at ASC
");

// ==========================
// MARK MESSAGES AS READ
// ==========================
mysqli_query($conn, "
    UPDATE messages 
    SET is_read = 1 
    WHERE order_id = $order_id AND receiver_id = $customer_id
");

include '../includes/header.php';
?>

<div class="section-title">Chat with Rider</div>

<div style="max-width:900px;margin:auto;">

    <div style="margin-bottom:15px;">
        <strong>Order #<?php echo $order_id; ?></strong><br>
        Rider: <?php echo htmlspecialchars($order['rider_name']); ?>
    </div>

    <div id="chatBox" style="
        height:400px;
        overflow-y:auto;
        border:1px solid #ddd;
        padding:15px;
        background:#f9f9f9;
        border-radius:8px;
    ">

        <?php if ($messages_query && mysqli_num_rows($messages_query) > 0): ?>
            <?php while ($msg = mysqli_fetch_assoc($messages_query)): ?>

                <div style="
                    margin-bottom:10px;
                    text-align:<?php echo ($msg['sender_id'] == $customer_id) ? 'right' : 'left'; ?>;
                ">

                    <span style="
                        display:inline-block;
                        padding:10px 14px;
                        border-radius:12px;
                        max-width:70%;
                        background:<?php echo ($msg['sender_id'] == $customer_id) ? '#4f46e5' : '#e5e7eb'; ?>;
                        color:<?php echo ($msg['sender_id'] == $customer_id) ? '#fff' : '#000'; ?>;
                    ">
                        <?php echo htmlspecialchars($msg['message']); ?>
                    </span>

                    <div style="font-size:11px;color:#777;">
                        <?php echo date('d M, h:i A', strtotime($msg['created_at'])); ?>
                    </div>

                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;color:#777;">No messages yet</p>
        <?php endif; ?>

    </div>

    <form method="POST" style="margin-top:15px;display:flex;gap:10px;">
        <input type="text" name="message" required placeholder="Type your message..."
               style="flex:1;padding:12px;border-radius:6px;border:1px solid #ccc;">
        <button type="submit" class="btn btn-primary">Send</button>
    </form>

</div>

<script>
    var box = document.getElementById("chatBox");
    if (box) {
        box.scrollTop = box.scrollHeight;
    }
</script>

<?php include '../includes/footer.php'; ?>
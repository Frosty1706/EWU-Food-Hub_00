<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$current_role = $_SESSION['role'] ?? 'guest';
$current_name = $_SESSION['full_name'] ?? 'Guest';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EWU Food Hub - <?php echo ucfirst($current_page); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-left">
                    <h2 class="page-title"><?php echo ucfirst($current_page); ?></h2>
                </div>
                <div class="topbar-right">
                    <div class="user-info">
                        <span class="user-avatar">👤</span>
                        <span class="user-name"><?php echo htmlspecialchars($current_name); ?></span>
                        <span class="user-role-badge role-<?php echo $current_role; ?>"><?php echo ucfirst($current_role); ?></span>
                    </div>
                    <a href="../logout.php" class="btn btn-logout">🚪 Logout</a>
                </div>
            </div>
            <div class="content-area">

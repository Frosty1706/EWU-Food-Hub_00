<?php
// File: C:\xampp\htdocs\EWU Food Hub\index.php

session_start();

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    switch ($role) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'restaurant':
            header('Location: restaurant/dashboard.php');
            break;
        case 'rider':
            header('Location: rider/dashboard.php');
            break;
        case 'customer':
            header('Location: customer/dashboard.php');
            break;
        default:
            header('Location: auth/login.php');
            break;
    }
    exit();
}

header('Location: auth/login.php');
exit();
?>

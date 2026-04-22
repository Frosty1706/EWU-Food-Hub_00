<?php
// File: C:\xampp\htdocs\EWU Food Hub\config\db.php

$host = 'localhost';
$dbname = 'ewu_food_hub';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>

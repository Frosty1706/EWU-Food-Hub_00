<?php
// File: C:\xampp\htdocs\EWU Food Hub\auth\auth_process.php

session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // LOGIN
    if ($action === 'login') {
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Please fill in all fields.';
            header('Location: login.php');
            exit();
        }

        $query = "SELECT * FROM users WHERE email = '$email' AND status = 'active' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['address'] = $user['address'];

                header('Location: ../index.php');
                exit();
            } else {
                $_SESSION['login_error'] = 'Invalid email or password.';
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['login_error'] = 'Invalid email or password.';
            header('Location: login.php');
            exit();
        }
    }

    // REGISTER
    if ($action === 'register') {
        $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
        $address = mysqli_real_escape_string($conn, trim($_POST['address']));
        $role = mysqli_real_escape_string($conn, trim($_POST['role']));
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $restaurant_name = mysqli_real_escape_string($conn, trim($_POST['restaurant_name'] ?? ''));

        // Validate input
        if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($role) || empty($password)) {
            $_SESSION['register_error'] = 'Please fill in all required fields.';
            header('Location: register.php');
            exit();
        }

        if ($password !== $confirm_password) {
            $_SESSION['register_error'] = 'Passwords do not match.';
            header('Location: register.php');
            exit();
        }

        if (strlen($password) < 6) {
            $_SESSION['register_error'] = 'Password must be at least 6 characters.';
            header('Location: register.php');
            exit();
        }

        if (!in_array($role, ['customer', 'restaurant', 'rider'])) {
            $_SESSION['register_error'] = 'Invalid role selected.';
            header('Location: register.php');
            exit();
        }

        // Check if email is already registered
        $check_query = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['register_error'] = 'Email already registered.';
            header('Location: register.php');
            exit();
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $insert_query = "INSERT INTO users (full_name, email, password, phone, address, role, status) 
                         VALUES ('$full_name', '$email', '$hashed_password', '$phone', '$address', '$role', 'active')";

        if (mysqli_query($conn, $insert_query)) {
            $user_id = mysqli_insert_id($conn);

            // Additional setup for restaurant owners
            if ($role === 'restaurant') {
                if (empty($restaurant_name)) {
                    $restaurant_name = $full_name . "'s Restaurant";
                }

                // Escape the restaurant name and description
                $restaurant_name_escaped = mysqli_real_escape_string($conn, $restaurant_name);
                $description = "Welcome to $restaurant_name";
                $description_escaped = mysqli_real_escape_string($conn, $description);

                $rest_query = "INSERT INTO restaurants (owner_id, restaurant_name, description) 
                               VALUES ('$user_id', '$restaurant_name_escaped', '$description_escaped')";
                mysqli_query($conn, $rest_query);
            }

            // Additional setup for riders
            if ($role === 'rider') {
                $rider_query = "INSERT INTO rider_availability (rider_id, is_available) VALUES ('$user_id', 1)";
                mysqli_query($conn, $rider_query);
            }

            $_SESSION['register_success'] = 'Registration successful! Please login.';
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['register_error'] = 'Registration failed. Please try again.';
            header('Location: register.php');
            exit();
        }
    }
}

// Redirect to login if no valid action is provided
header('Location: login.php');
exit();
?>

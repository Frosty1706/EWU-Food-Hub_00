<?php
// File: C:\xampp\htdocs\EWU Food Hub\restaurant\food_process.php

session_start();
require_once '../config/db.php';

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header('Location: ../auth/login.php');
    exit();
}

$owner_id = $_SESSION['user_id'];

// Get restaurant info for the logged-in owner
$restaurant_query = mysqli_query($conn, "SELECT id FROM restaurants WHERE owner_id = $owner_id LIMIT 1");
if (mysqli_num_rows($restaurant_query) === 0) {
    $_SESSION['restaurant_error'] = 'No restaurant found for your account. Please create a restaurant first.';
    header('Location: manage_foods.php');
    exit();
}
$restaurant = mysqli_fetch_assoc($restaurant_query);
$restaurant_id = $restaurant['id'];

// Ensure the upload directory exists
$upload_dir = '../uploads/foods/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD FOOD
    if ($action === 'add_food') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
        $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
        $price = floatval($_POST['price'] ?? 0);
        $category = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));
        $availability = 'available';

        // Validate inputs
        if (empty($name) || $price <= 0) {
            $_SESSION['restaurant_error'] = 'Food name and a valid price are required.';
            header('Location: manage_foods.php?action=add');
            exit();
        }

        // Handle image upload
        $image_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $file_size = $_FILES['image']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            // Validate file type
            if (!in_array($ext, $allowed)) {
                $_SESSION['restaurant_error'] = 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp.';
                header('Location: manage_foods.php?action=add');
                exit();
            }

            // Validate file size (max 5MB)
            if ($file_size > 5 * 1024 * 1024) {
                $_SESSION['restaurant_error'] = 'Image size must be less than 5MB.';
                header('Location: manage_foods.php?action=add');
                exit();
            }

            $image_name = uniqid('food_', true) . '.' . $ext;
            $upload_path = $upload_dir . $image_name;

            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $_SESSION['restaurant_error'] = 'Failed to upload image. Please check folder permissions.';
                header('Location: manage_foods.php?action=add');
                exit();
            }
        } else {
            $_SESSION['restaurant_error'] = 'Image is required.';
            header('Location: manage_foods.php?action=add');
            exit();
        }

        // Insert food into the database
        $stmt = mysqli_prepare($conn, "INSERT INTO foods (restaurant_id, name, description, price, category, image, availability) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'issdsss', $restaurant_id, $name, $description, $price, $category, $image_name, $availability);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['restaurant_success'] = 'Food added successfully!';
        } else {
            // Remove uploaded image if DB insert fails
            if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                unlink($upload_dir . $image_name);
            }
            $_SESSION['restaurant_error'] = 'Failed to add food. Database error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
        header('Location: manage_foods.php');
        exit();
    }

    // EDIT FOOD
    if ($action === 'edit_food') {
        $food_id = intval($_POST['food_id'] ?? 0);
        $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
        $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
        $price = floatval($_POST['price'] ?? 0);
        $category = mysqli_real_escape_string($conn, trim($_POST['category'] ?? ''));

        // Validate inputs
        if (empty($name) || $price <= 0) {
            $_SESSION['restaurant_error'] = 'Food name and a valid price are required.';
            header('Location: manage_foods.php?action=edit&id=' . $food_id);
            exit();
        }

        // Check food ownership
        $food_check = mysqli_query($conn, "SELECT * FROM foods WHERE id = $food_id AND restaurant_id = $restaurant_id LIMIT 1");
        if (mysqli_num_rows($food_check) === 0) {
            $_SESSION['restaurant_error'] = 'Food not found or unauthorized.';
            header('Location: manage_foods.php');
            exit();
        }
        $food = mysqli_fetch_assoc($food_check);
        $image_name = $food['image'];

        // Handle image upload if new image provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $file_size = $_FILES['image']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $_SESSION['restaurant_error'] = 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp.';
                header('Location: manage_foods.php?action=edit&id=' . $food_id);
                exit();
            }

            if ($file_size > 5 * 1024 * 1024) {
                $_SESSION['restaurant_error'] = 'Image size must be less than 5MB.';
                header('Location: manage_foods.php?action=edit&id=' . $food_id);
                exit();
            }

            $new_image_name = uniqid('food_', true) . '.' . $ext;
            $upload_path = $upload_dir . $new_image_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Delete old image
                if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                    unlink($upload_dir . $image_name);
                }
                $image_name = $new_image_name;
            } else {
                $_SESSION['restaurant_error'] = 'Failed to upload new image.';
                header('Location: manage_foods.php?action=edit&id=' . $food_id);
                exit();
            }
        }

        // Update food in the database
        $stmt = mysqli_prepare($conn, "UPDATE foods SET name = ?, description = ?, price = ?, category = ?, image = ? WHERE id = ? AND restaurant_id = ?");
        mysqli_stmt_bind_param($stmt, 'ssdssii', $name, $description, $price, $category, $image_name, $food_id, $restaurant_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['restaurant_success'] = 'Food updated successfully!';
        } else {
            $_SESSION['restaurant_error'] = 'Failed to update food. Database error: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
        header('Location: manage_foods.php');
        exit();
    }

    // DELETE FOOD
    if ($action === 'delete_food') {
        $food_id = intval($_POST['food_id'] ?? 0);

        // Check food ownership
        $food_check = mysqli_query($conn, "SELECT * FROM foods WHERE id = $food_id AND restaurant_id = $restaurant_id LIMIT 1");
        if (mysqli_num_rows($food_check) === 0) {
            $_SESSION['restaurant_error'] = 'Food not found or unauthorized.';
            header('Location: manage_foods.php');
            exit();
        }
        $food = mysqli_fetch_assoc($food_check);

        // Delete image file
        if (!empty($food['image']) && file_exists($upload_dir . $food['image'])) {
            unlink($upload_dir . $food['image']);
        }

        $delete_query = "DELETE FROM foods WHERE id = $food_id AND restaurant_id = $restaurant_id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['restaurant_success'] = 'Food deleted successfully!';
        } else {
            $_SESSION['restaurant_error'] = 'Failed to delete food. Database error: ' . mysqli_error($conn);
        }
        header('Location: manage_foods.php');
        exit();
    }
}

header('Location: manage_foods.php');
exit();
?>

<?php
// File: C:\xampp\htdocs\EWU Food Hub\restaurant\restaurant_process.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header('Location: ../auth/login.php');
    exit();
}

$owner_id = $_SESSION['user_id'];

// Get restaurant info
$restaurant_query = mysqli_query($conn, "SELECT * FROM restaurants WHERE owner_id = $owner_id LIMIT 1");
$restaurant = mysqli_fetch_assoc($restaurant_query);
$restaurant_id = $restaurant['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD FOOD
    if ($action === 'add_food') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $description = mysqli_real_escape_string($conn, trim($_POST['description']));
        $price = floatval($_POST['price']);
        $category = mysqli_real_escape_string($conn, trim($_POST['category']));
        $availability = 'available';

        // Handle image upload
        $image_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = basename($_FILES['image']['name']);
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($ext, $allowed)) {
                $image_name = uniqid('food_', true) . '.' . $ext;
                $upload_path = '../uploads/foods/' . $image_name;
                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $_SESSION['restaurant_error'] = 'Failed to upload image.';
                    header('Location: manage_foods.php');
                    exit();
                }
            } else {
                $_SESSION['restaurant_error'] = 'Invalid image format. Allowed: jpg, jpeg, png, gif.';
                header('Location: manage_foods.php');
                exit();
            }
        } else {
            $_SESSION['restaurant_error'] = 'Image is required.';
            header('Location: manage_foods.php');
            exit();
        }

        $insert_query = "INSERT INTO foods (restaurant_id, name, description, price, category, image, availability) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'issdsss', $restaurant_id, $name, $description, $price, $category, $image_name, $availability);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['restaurant_success'] = 'Food added successfully.';
        } else {
            $_SESSION['restaurant_error'] = 'Failed to add food.';
        }
        header('Location: manage_foods.php');
        exit();
    }

    // EDIT FOOD
    if ($action === 'edit_food') {
        $food_id = intval($_POST['food_id']);
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $description = mysqli_real_escape_string($conn, trim($_POST['description']));
        $price = floatval($_POST['price']);
        $category = mysqli_real_escape_string($conn, trim($_POST['category']));

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
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($ext, $allowed)) {
                $new_image_name = uniqid('food_', true) . '.' . $ext;
                $upload_path = '../uploads/foods/' . $new_image_name;
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Delete old image
                    if (!empty($image_name) && file_exists('../uploads/foods/' . $image_name)) {
                        unlink('../uploads/foods/' . $image_name);
                    }
                    $image_name = $new_image_name;
                } else {
                    $_SESSION['restaurant_error'] = 'Failed to upload new image.';
                    header('Location: manage_foods.php');
                    exit();
                }
            } else {
                $_SESSION['restaurant_error'] = 'Invalid image format. Allowed: jpg, jpeg, png, gif.';
                header('Location: manage_foods.php');
                exit();
            }
        }

        $update_query = "UPDATE foods SET name = ?, description = ?, price = ?, category = ?, image = ? WHERE id = ? AND restaurant_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'ssdssii', $name, $description, $price, $category, $image_name, $food_id, $restaurant_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['restaurant_success'] = 'Food updated successfully.';
        } else {
            $_SESSION['restaurant_error'] = 'Failed to update food.';
        }
        header('Location: manage_foods.php');
        exit();
    }

    // DELETE FOOD
    if ($action === 'delete_food') {
        $food_id = intval($_POST['food_id']);

        // Check food ownership
        $food_check = mysqli_query($conn, "SELECT * FROM foods WHERE id = $food_id AND restaurant_id = $restaurant_id LIMIT 1");
        if (mysqli_num_rows($food_check) === 0) {
            $_SESSION['restaurant_error'] = 'Food not found or unauthorized.';
            header('Location: manage_foods.php');
            exit();
        }
        $food = mysqli_fetch_assoc($food_check);

        // Delete image file
        if (!empty($food['image']) && file_exists('../uploads/foods/' . $food['image'])) {
            unlink('../uploads/foods/' . $food['image']);
        }

        $delete_query = "DELETE FROM foods WHERE id = $food_id AND restaurant_id = $restaurant_id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['restaurant_success'] = 'Food deleted successfully.';
        } else {
            $_SESSION['restaurant_error'] = 'Failed to delete food.';
        }
        header('Location: manage_foods.php');
        exit();
    }
}

header('Location: manage_foods.php');
exit();
?>

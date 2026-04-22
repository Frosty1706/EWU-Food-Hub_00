<?php
// File: C:\xampp\htdocs\EWU Food Hub\restaurant\manage_foods.php

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

// Handle success/error messages
$success = $_SESSION['restaurant_success'] ?? '';
unset($_SESSION['restaurant_success']);

$error = $_SESSION['restaurant_error'] ?? '';
unset($_SESSION['restaurant_error']);

// Fetch foods for this restaurant
$foods_query = mysqli_query($conn, "SELECT * FROM foods WHERE restaurant_id = $restaurant_id ORDER BY created_at DESC");

include '../includes/header.php';
?>

<div class="section-title">Manage Foods</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<a href="manage_foods.php?action=add" class="btn btn-primary" style="margin-bottom: 20px;">+ Add New Food</a>

<?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
    <div class="form-card">
        <h3>Add New Food</h3>
        <form action="food_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_food">
            <div class="form-group">
                <label for="name">Food Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price (৳)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category">
            </div>
            <div class="form-group">
                <label for="image">Food Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-success">Add Food</button>
            <a href="manage_foods.php" class="btn btn-warning">Cancel</a>
        </form>
    </div>

<?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): 
    $food_id = intval($_GET['id']);
    $food_query = mysqli_query($conn, "SELECT * FROM foods WHERE id = $food_id AND restaurant_id = $restaurant_id LIMIT 1");
    if (mysqli_num_rows($food_query) === 1):
        $food = mysqli_fetch_assoc($food_query);
?>
    <div class="form-card">
        <h3>Edit Food</h3>
        <form action="food_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_food">
            <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
            <div class="form-group">
                <label for="name">Food Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($food['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($food['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price (৳)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $food['price']; ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($food['category']); ?>">
            </div>
            <div class="form-group">
                <label for="image">Food Image</label>
                <?php if (!empty($food['image']) && file_exists('../uploads/foods/' . $food['image'])): ?>
                    <img src="../uploads/foods/<?php echo htmlspecialchars($food['image']); ?>" alt="Food Image" style="max-width:150px; display:block; margin-bottom:10px;">
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Leave blank to keep existing image.</small>
            </div>
            <button type="submit" class="btn btn-success">Update Food</button>
            <a href="manage_foods.php" class="btn btn-warning">Cancel</a>
        </form>
    </div>
<?php else: ?>
    <div class="alert alert-danger">Food item not found.</div>
    <a href="manage_foods.php" class="btn btn-primary">Back to Foods</a>
<?php endif; else: ?>

<?php if (mysqli_num_rows($foods_query) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price (৳)</th>
                <th>Availability</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($food = mysqli_fetch_assoc($foods_query)): ?>
            <tr>
                <td>
                    <?php if (!empty($food['image']) && file_exists('../uploads/foods/' . $food['image'])): ?>
                        <img src="../uploads/foods/<?php echo htmlspecialchars($food['image']); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>" style="width:60px; height:60px; border-radius:8px; object-fit:cover;">
                    <?php else: ?>
                        <span>🍽️</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($food['name']); ?></td>
                <td><?php echo htmlspecialchars($food['category']); ?></td>
                <td>৳<?php echo number_format($food['price'], 2); ?></td>
                <td>
                    <?php if ($food['availability'] === 'available'): ?>
                        <span class="badge badge-available">Available</span>
                    <?php else: ?>
                        <span class="badge badge-unavailable">Unavailable</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="manage_foods.php?action=edit&id=<?php echo $food['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <form action="food_process.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this food?');">
                        <input type="hidden" name="action" value="delete_food">
                        <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">🍽️</div>
        <h3>No Foods Found</h3>
        <p>You have not added any foods yet.</p>
    </div>
<?php endif; ?>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>

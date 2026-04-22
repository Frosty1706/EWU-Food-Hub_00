<?php
// File: C:\xampp\htdocs\EWU Food Hub\auth\register.php

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - EWU Food Hub</title>
    <style>
        /* Simple clean design without external CSS */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #2d3436;
        }
        .auth-container {
            background: #fff;
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 520px;
            box-sizing: border-box;
            text-align: center;
        }
        .auth-logo {
            font-size: 50px;
            margin-bottom: 10px;
        }
        h1 {
            margin-bottom: 5px;
            font-weight: 700;
            font-size: 28px;
            color: #2d3436;
        }
        p.subtitle {
            margin-bottom: 30px;
            color: #636e72;
            font-size: 14px;
        }
        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            text-align: left;
        }
        .alert-danger {
            background: #ffe0e0;
            color: #c0392b;
            border-left: 4px solid #e74c3c;
        }
        form {
            text-align: left;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #2d3436;
        }
        .input-icon-wrap {
            position: relative;
            margin-bottom: 20px;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #999;
            pointer-events: none;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            color: #333;
            background: #fafafa;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
        }
        input:focus,
        select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            background: #fff;
        }
        button.btn-primary {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        button.btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
            transform: translateY(-2px);
        }
        .auth-footer {
            margin-top: 24px;
            font-size: 14px;
            color: #636e72;
            text-align: center;
        }
        .auth-footer a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
        .auth-credits {
            margin-top: 16px;
            font-size: 12px;
            color: #b2bec3;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="auth-container" role="main" aria-label="Registration form">
        <div class="auth-logo" aria-hidden="true">🍔</div>
        <h1>EWU Food Hub</h1>
        <p class="subtitle">Create a new account</p>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="auth_process.php" method="POST" class="auth-form" novalidate>
            <input type="hidden" name="action" value="register" />

            <div class="input-icon-wrap">
                <label for="full_name">Full Name</label>
                <span class="input-icon" aria-hidden="true">👤</span>
                <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required />
            </div>

            <div class="input-icon-wrap">
                <label for="email">Email Address</label>
                <span class="input-icon" aria-hidden="true">✉</span>
                <input type="email" id="email" name="email" placeholder="Enter your email" required />
            </div>

            <div class="input-icon-wrap">
                <label for="phone">Phone Number</label>
                <span class="input-icon" aria-hidden="true">📞</span>
                <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required />
            </div>

            <div class="input-icon-wrap">
                <label for="address">Address</label>
                <span class="input-icon" aria-hidden="true">📍</span>
                <input type="text" id="address" name="address" placeholder="Enter your address" required />
            </div>

            <div class="input-icon-wrap">
                <label for="role">Register As</label>
                <span class="input-icon" aria-hidden="true">🏷</span>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="customer">Customer</option>
                    <option value="restaurant">Restaurant Owner</option>
                    <option value="rider">Rider</option>
                </select>
            </div>

            <div class="input-icon-wrap" id="restaurant-name-group" style="display:none;">
                <label for="restaurant_name">Restaurant Name</label>
                <span class="input-icon" aria-hidden="true">🍽</span>
                <input type="text" id="restaurant_name" name="restaurant_name" placeholder="Enter restaurant name" />
            </div>

            <div class="input-icon-wrap">
                <label for="password">Password</label>
                <span class="input-icon" aria-hidden="true">🔒</span>
                <input type="password" id="password" name="password" placeholder="Create a password" required />
            </div>

            <div class="input-icon-wrap">
                <label for="confirm_password">Confirm Password</label>
                <span class="input-icon" aria-hidden="true">🔒</span>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required />
            </div>

            <button type="submit" class="btn-primary">Create Account</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Sign in here</a></p>
        </div>

        <div class="auth-credits">
            <p>Developed by <strong>Nahian Ma Jabin</strong></p>
        </div>
    </div>
</body>
</html>

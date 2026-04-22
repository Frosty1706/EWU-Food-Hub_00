-- File: C:\xampp\htdocs\EWU Food Hub\ewu_food.sql

CREATE DATABASE IF NOT EXISTS ewu_food_hub;
USE ewu_food_hub;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('admin','restaurant','rider','customer') NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Restaurants Table
CREATE TABLE restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    restaurant_name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Foods Table
CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    availability ENUM('available','unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    rider_id INT DEFAULT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    admin_commission DECIMAL(10,2) DEFAULT 0.00,
    rider_commission DECIMAL(10,2) DEFAULT 0.00,
    payment_method ENUM('cod','bkash','nagad','visa','nexuspay') NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_phone VARCHAR(20) NOT NULL,
    status ENUM('pending','confirmed','assigned','accepted','picked','on_the_way','delivered','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

-- Cart Table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

-- Chat Messages Table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Admin
INSERT INTO users (full_name, email, password, phone, role, status)
VALUES ('Admin', 'admin@ewu.edu', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkF.0oP0Ht4a9LFkfmcjF3kzL3FO', '01700000000', 'admin', 'active');
-- Default admin password: admin123

-- Insert Sample Restaurant Owner
INSERT INTO users (full_name, email, password, phone, role, status)
VALUES ('Nahian Ma Jabin', 'nahian@ewu.edu', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkF.0oP0Ht4a9LFkfmcjF3kzL3FO', '01711111111', 'restaurant', 'active');

INSERT INTO restaurants (owner_id, restaurant_name, description)
VALUES (2, 'EWU Campus Kitchen', 'Delicious homemade food for EWU students');

-- Insert Sample Rider
INSERT INTO users (full_name, email, password, phone, role, status)
VALUES ('Rider One', 'rider@ewu.edu', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkF.0oP0Ht4a9LFkfmcjF3kzL3FO', '01722222222', 'rider', 'active');

-- Insert Sample Customer
INSERT INTO users (full_name, email, password, phone, role, status)
VALUES ('Customer One', 'customer@ewu.edu', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkF.0oP0Ht4a9LFkfmcjF3kzL3FO', '01733333333', 'customer', 'active');

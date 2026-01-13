<?php
/**
 * Database Connection using PDO
 * This file provides a secure database connection for the car selling platform
 */

// Database configuration
// Detect environment
$isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');

if ($isLocal) {
    // Local (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'car_sell');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Production (InfinityFree)
    define('DB_HOST', 'sql106.infinityfree.com');
    define('DB_NAME', 'if0_40862261_carsell');
    define('DB_USER', 'if0_40862261');
    define('DB_PASS', 'C987MaGROi'); // vPanel password configured
}

try {
    // Create PDO instance
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Helper function to format price in Indian Rupees
 */
function formatPrice($amount) {
    return '₹' . number_format($amount, 0, '.', ',');
}

/**
 * Helper function to check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Helper function to check user role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Helper function to check if seller is approved
 */
function isSellerApproved() {
    return isset($_SESSION['is_approved']) && $_SESSION['is_approved'] == 1;
}

/**
 * Helper function to check if user is banned
 */
function isUserBanned() {
    return isset($_SESSION['status']) && $_SESSION['status'] === 'banned';
}

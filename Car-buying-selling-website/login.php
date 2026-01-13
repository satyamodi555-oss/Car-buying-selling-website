<?php
session_start();
require_once 'includes/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (hasRole('admin')) {
        header('Location: admin/dashboard.php');
    } elseif (hasRole('seller')) {
        header('Location: seller/seller_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Fetch user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Check if user is banned
            if ($user['status'] === 'banned') {
                $error = "Your account has been banned. Please contact support.";
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['status'] = $user['status'];
                $_SESSION['is_approved'] = $user['is_approved'];
                
                // Role-based redirect
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } elseif ($user['role'] === 'seller') {
                    header('Location: seller/seller_dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Selling Platform</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0043ff',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 transform hover:scale-[1.01] transition-transform duration-300">
            <!-- Back Link -->
            <div class="text-center mb-6">
                <a href="index.html" class="text-gray-500 hover:text-primary transition text-sm">
                    ← Back to Home
                </a>
            </div>

            <!-- Title -->
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Login</h2>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="post" action="login.php" class="space-y-6">
                <!-- Email -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                           name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                           name="password" required>
                </div>

                <!-- Login Button -->
                <button type="submit" name="login" 
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                    Login
                </button>
            </form>

            <!-- Register Link -->
            <div class="text-center mt-6">
                <p class="text-gray-600">Don't have an account? 
                    <a href="registration.php" class="text-primary hover:text-blue-700 font-semibold">Register Here</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Validation Script -->
    <script>
        // Bootstrap validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('form')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                }, false)
            })
        })()
    </script>
</body>
</html>

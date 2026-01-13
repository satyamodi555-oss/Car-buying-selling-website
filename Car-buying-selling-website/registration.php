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

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $fullName = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordRepeat = $_POST['repeat_password'];
    $role = $_POST['role'] ?? 'buyer';
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($fullName) || empty($email) || empty($password) || empty($passwordRepeat)) {
        $errors[] = "All fields are required.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid.";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    
    if ($password !== $passwordRepeat) {
        $errors[] = "Passwords do not match.";
    }
    
    if (!preg_match("/^(?=.*[a-zA-Z])(?=.*[0-9])/", $password)) {
        $errors[] = "Password must contain at least one letter and one number.";
    }
    
    // Check if email exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists.";
        }
    }
    
    // Insert user
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $isApproved = ($role === 'seller') ? 0 : 1; // Sellers need approval
        
        $stmt = $pdo->prepare(
            "INSERT INTO users (full_name, email, password, role, is_approved, phone, status) 
             VALUES (?, ?, ?, ?, ?, ?, 'active')"
        );
        
        if ($stmt->execute([$fullName, $email, $passwordHash, $role, $isApproved, $phone])) {
            $success = "Registration successful! " . 
                      ($role === 'seller' ? "Please wait for admin approval before listing cars." : "You can now login.");
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Selling Platform</title>
    
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
    <div class="w-full max-w-lg my-8">
        <!-- Registration Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Create Account</h2>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="post" action="" class="space-y-5" oninput='repeat_password.setCustomValidity(repeat_password.value != password.value ? "Passwords do not match." : "")'>
                <!-- Full Name -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Full Name</label>
                    <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                           name="fullname" required pattern=".{3,}"
                           value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                           name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Phone (Optional)</label>
                    <input type="tel" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                           name="phone" pattern="[0-9]{10}"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>

                <!-- Register As -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Register As</label>
                    <select class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                            name="role" required>
                        <option value="buyer" <?= (($_POST['role'] ?? '') === 'buyer') ? 'selected' : '' ?>>Buyer</option>
                        <option value="seller" <?= (($_POST['role'] ?? '') === 'seller') ? 'selected' : '' ?>>Seller</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Note: Sellers require admin approval before listing cars.</p>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                           name="password" required minlength="8" id="password">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
                    <input type="password" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                           name="repeat_password" required id="repeat_password">
                </div>

                <!-- Register Button -->
                <button type="submit" name="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                    Register
                </button>
            </form>

            <!-- Login Link -->
            <div class="text-center mt-6">
                <p class="text-gray-600">Already have an account? 
                    <a href="login.php" class="text-primary hover:text-blue-700 font-semibold">Login Here</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Validation Script -->
    <script>
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

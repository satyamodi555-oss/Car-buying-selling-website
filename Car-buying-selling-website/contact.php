<?php
session_start();
require_once 'includes/db.php';

$message = '';
$msgType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $msgContent = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($msgContent)) {
        $message = "All fields are required.";
        $msgType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $msgType = "error";
    } elseif (strlen($msgContent) < 10) {
        $message = "Message should be at least 10 characters.";
        $msgType = "error";
    } else {
        try {
            // Using PDO from includes/db.php
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
            // Check if table exists or handle error if simpler table structure
            // Assuming table 'contacts' exists with columns: id, name, email, message, created_at
            // If created_at is auto, remove it from query
            
            // Note: If 'contacts' table is simple (id, name, email, message), just use that.
            // Generally safer to try basic insert first
             $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
             $stmt->execute([$name, $email, $msgContent]);

            $message = "Message sent successfully! We will get back to you soon.";
            $msgType = "success";
            
            // Reset form
            $name = $email = $msgContent = '';
        } catch (PDOException $e) {
            $message = "Error sending message: " . $e->getMessage();
            $msgType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - CarSell</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#0043ff' } } } }
    </script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="text-3xl font-bold text-primary">CAR ON SELL</a>
                <div class="hidden md:flex space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-primary transition font-medium">Home</a>
                    <a href="browse_cars.php" class="text-gray-700 hover:text-primary transition font-medium">Browse Cars</a>
                    <a href="about.php" class="text-gray-700 hover:text-primary transition font-medium">About Us</a>
                    <a href="contact.php" class="text-primary font-bold transition font-medium">Contact</a>
                </div>
                <div class="flex space-x-3">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= hasRole('admin') ? 'admin/dashboard.php' : (hasRole('seller') ? 'seller/seller_dashboard.php' : '#') ?>" class="text-gray-700 hover:text-primary transition font-medium py-2 px-4 flex items-center"><i class="fas fa-user mr-2"></i><?= htmlspecialchars($_SESSION['user_name']) ?></a>
                        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-6 rounded-lg transition shadow-md">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="border-2 border-primary text-primary hover:bg-primary hover:text-white font-semibold py-2 px-6 rounded-lg transition">Login</a>
                        <a href="registration.php" class="bg-primary hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition shadow-md">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="grid md:grid-cols-2">
                <!-- Contact Info Side -->
                <div class="bg-primary p-12 text-white flex flex-col justify-center">
                    <h2 class="text-3xl font-bold mb-6">Get in Touch</h2>
                    <p class="mb-8 opacity-90">Have questions about buying or selling? We're here to help!</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-phone"></i>
                            </div>
                            <span>+91 8605555555</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <span>support@carsell.com</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span>New Mondha, Nanded</span>
                        </div>
                    </div>
                </div>

                <!-- Form Side -->
                <div class="p-12">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Send us a Message</h2>
                    
                    <?php if ($message): ?>
                        <div class="mb-6 p-4 rounded-lg <?= $msgType === 'success' ? 'bg-green-100 text-green-700 border-l-4 border-green-500' : 'bg-red-100 text-red-700 border-l-4 border-red-500' ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?= htmlspecialchars($name ?? '') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?= htmlspecialchars($email ?? '') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea id="message" name="message" rows="4" required 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition"><?= htmlspecialchars($msgContent ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition shadow-md">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; <?= date('Y') ?> CarSell. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

<?php
session_start();
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - CarSell</title>
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
                    <a href="about.php" class="text-primary font-bold transition font-medium">About Us</a>
                    <a href="contact.php" class="text-gray-700 hover:text-primary transition font-medium">Contact</a>
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
    <main class="flex-grow">
        <!-- Hero Section -->
        <div class="bg-primary text-white py-16">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">About CarSell</h1>
                <p class="text-xl opacity-90">Your trusted partner for buying and selling cars.</p>
            </div>
        </div>

        <!-- Story Section -->
        <div class="max-w-7xl mx-auto px-4 py-16">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold mb-6 text-gray-800">Our Story</h2>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Founded with a vision to simplify the used car market, CarSell has grown into a premier destination for buyers and sellers alike. We believe that buying a car should be transparent, fair, and exciting—not a hassle.
                    </p>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Our platform connects thousands of verified sellers with eager buyers, ensuring a safe and efficient trading environment. With robust verification processes and intuitive tools, we empower you to make the right choice.
                    </p>
                    <div class="mt-8 flex gap-4">
                        <div class="text-center">
                            <span class="block text-3xl font-bold text-primary">5000+</span>
                            <span class="text-sm text-gray-500">Cars Sold</span>
                        </div>
                        <div class="text-center border-l pl-4">
                            <span class="block text-3xl font-bold text-primary">98%</span>
                            <span class="text-sm text-gray-500">Customer Satisfaction</span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1560179707-f14e90ef3623?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Team" class="rounded-xl shadow-2xl">
                    <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-xl shadow-xl hidden md:block">
                        <p class="font-bold text-gray-800">Trusted by over</p>
                        <p class="text-primary font-bold text-xl">10,000 Users</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Values Section -->
        <div class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Why Choose Us?</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-blue-100 text-primary rounded-full flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Secure Transactions</h3>
                        <p class="text-gray-600">We prioritize your safety with verified listings and secure processes.</p>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Transparent Integrity</h3>
                        <p class="text-gray-600">No hidden fees or surprises. What you see is what you get.</p>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition">
                        <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">Dedicated Support</h3>
                        <p class="text-gray-600">Our team is here to help you every step of the way.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; 2024 CarSell. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

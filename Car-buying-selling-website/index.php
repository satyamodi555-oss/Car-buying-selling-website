<?php
session_start();
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarSell - Buy & Sell Cars</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
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

<body class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="text-3xl font-bold text-primary">
                    CAR ON SELL
                </a>
                
                <div class="hidden md:flex space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-primary transition font-medium">Home</a>
                    <a href="browse_cars.php" class="text-gray-700 hover:text-primary transition font-medium">Browse Cars</a>
                    <a href="about.php" class="text-gray-700 hover:text-primary transition font-medium">About Us</a>
                    <a href="contact.php" class="text-gray-700 hover:text-primary transition font-medium">Contact</a>
                </div>
                
                <div class="flex space-x-3">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= hasRole('admin') ? 'admin/dashboard.php' : (hasRole('seller') ? 'seller/seller_dashboard.php' : '#') ?>" class="text-gray-700 hover:text-primary transition font-medium py-2 px-4 flex items-center">
                            <i class="fas fa-user mr-2"></i><?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-6 rounded-lg transition shadow-md">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="border-2 border-primary text-primary hover:bg-primary hover:text-white font-semibold py-2 px-6 rounded-lg transition">
                            Login
                        </a>
                        <a href="registration.php" class="bg-primary hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition shadow-md">
                            Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div>
                <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                    Easy And Fast Way To
                    <span class="text-primary">Buy & Sell</span> Your Car
                </h1>
                <p class="text-gray-600 text-lg mb-8">
                    Join thousands of buyers and sellers in the most trusted car marketplace
                </p>
                <div class="flex gap-4">
                    <a href="browse_cars.php" class="bg-primary hover:bg-blue-700 text-white font-bold px-8 py-4 rounded-lg transition shadow-lg">
                        <i class="fas fa-shopping-cart mr-2"></i>Buy Car
                    </a>
                    <a href="<?= isLoggedIn() && hasRole('seller') ? 'seller/seller_create_car.php' : 'registration.php' ?>" 
                       class="bg-white border-2 border-primary text-primary hover:bg-primary hover:text-white font-bold px-8 py-4 rounded-lg transition shadow-lg">
                        <i class="fas fa-tag mr-2"></i>Sell Car
                    </a>
                </div>
            </div>

            <!-- Right Image -->
            <div class="relative">
                <img src="bmw-22428.png" alt="Car" class="w-full animate-pulse">
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mt-16 bg-white rounded-2xl shadow-2xl p-8">
            <form action="browse_cars.php" method="GET" class="flex items-center gap-4">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Search by brand, model, or title..." 
                           class="w-full px-6 py-4 rounded-lg border-2 border-gray-200 focus:border-primary outline-none text-lg">
                </div>
                <button type="submit" class="bg-primary hover:bg-blue-700 text-white font-bold px-8 py-4 rounded-lg transition shadow-lg">
                    <i class="fas fa-search mr-2"></i>Search Car
                </button>
            </form>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="text-center p-6 rounded-xl hover:shadow-xl transition bg-gradient-to-br from-blue-50 to-white">
                    <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">24h Support</h3>
                    <p class="text-gray-600">Round-the-clock customer assistance</p>
                </div>

                <!-- Feature 2 -->
                <div class="text-center p-6 rounded-xl hover:shadow-xl transition bg-gradient-to-br from-green-50 to-white">
                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-dollar-sign text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Best Price</h3>
                    <p class="text-gray-600">Competitive pricing guaranteed</p>
                </div>

                <!-- Feature 3 -->
                <div class="text-center p-6 rounded-xl hover:shadow-xl transition bg-gradient-to-br from-purple-50 to-white">
                    <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-certificate text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Verified License</h3>
                    <p class="text-gray-600">All sellers are verified</p>
                </div>

                <!-- Feature 4 -->
                <div class="text-center p-6 rounded-xl hover:shadow-xl transition bg-gradient-to-br from-red-50 to-white">
                    <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times-circle text-white text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Free Cancelation</h3>
                    <p class="text-gray-600">Cancel anytime without fees</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 py-16 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="text-5xl font-bold mb-2">1000+</div>
                    <div class="text-xl opacity-90">Cars Listed</div>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">500+</div>
                    <div class="text-xl opacity-90">Happy Customers</div>
                </div>
                <div>
                    <div class="text-5xl font-bold mb-2">50+</div>
                    <div class="text-xl opacity-90">Verified Sellers</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2024 CarSell. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

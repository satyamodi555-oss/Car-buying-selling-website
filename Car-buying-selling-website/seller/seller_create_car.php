<?php
session_start();
require_once '../includes/db.php';

// Check if user is seller
if (!isLoggedIn() || !hasRole('seller')) {
    header('Location: login.php');
    exit();
}

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = intval($_POST['price']);
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = intval($_POST['year']);
    $mileage = intval($_POST['mileage']);
    $fuelType = $_POST['fuel_type'];
    $transmission = $_POST['transmission'];
    $color = trim($_POST['color']);
    $sellerId = $_SESSION['user_id'];
    
    // Validation
    if (empty($title) || empty($brand) || empty($model) || $price <= 0) {
        $errors[] = "Please fill in all required fields.";
    }
    
    if ($year < 1900 || $year > date('Y') + 1) {
        $errors[] = "Please enter a valid year.";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO cars (seller_id, title, description, price, brand, model, year, 
                            mileage, fuel_type, transmission, color, approval_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')
        ");
        
        if ($stmt->execute([$sellerId, $title, $description, $price, $brand, $model, 
                           $year, $mileage, $fuelType, $transmission, $color])) {
            $carId = $pdo->lastInsertId();
            header("Location: seller_add_photos.php?car_id=$carId");
            exit();
        } else {
            $errors[] = "Failed to create listing. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Car Listing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#0043ff' } } } }</script>
</head>
<body class="bg-gray-100">
    <nav class="bg-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="seller_dashboard.php" class="text-2xl font-bold text-white"><i class="fas fa-car mr-2"></i>CarSell</a>
                <a href="seller_dashboard.php" class="bg-white text-primary px-4 py-2 rounded-lg hover:bg-gray-100 transition font-semibold">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                <h2 class="text-2xl font-bold text-white"><i class="fas fa-plus-circle mr-2"></i>Create New Car Listing</h2>
            </div>

            <div class="p-8">
                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg"><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Title *</label>
                        <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                               name="title" required placeholder="e.g., 2020 Honda City - Well Maintained" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Brand *</label>
                            <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="brand" required placeholder="e.g., Honda" value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Model *</label>
                            <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="model" required placeholder="e.g., City" value="<?= htmlspecialchars($_POST['model'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Year *</label>
                            <input type="number" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="year" required min="1900" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($_POST['year'] ?? date('Y')) ?>">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Price (₹) *</label>
                            <input type="number" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="price" required min="1" placeholder="500000" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Mileage (km)</label>
                            <input type="number" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="mileage" min="0" placeholder="45000" value="<?= htmlspecialchars($_POST['mileage'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Fuel Type *</label>
                            <select class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" name="fuel_type" required>
                                <option value="Petrol">Petrol</option>
                                <option value="Diesel">Diesel</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="CNG">CNG</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Transmission *</label>
                            <select class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" name="transmission" required>
                                <option value="Manual">Manual</option>
                                <option value="Automatic">Automatic</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Color</label>
                            <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="color" placeholder="e.g., White" value="<?= htmlspecialchars($_POST['color'] ?? '') ?>">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Description</label>
                        <textarea class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                  name="description" rows="4" placeholder="Describe the condition, features, and any additional details..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="space-y-3 pt-4">
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-arrow-right mr-2"></i>Continue to Add Photos
                        </button>
                        <a href="seller_dashboard.php" class="block text-center w-full bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-3 rounded-lg transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

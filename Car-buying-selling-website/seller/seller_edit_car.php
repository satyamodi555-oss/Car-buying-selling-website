<?php
session_start();
require_once '../includes/db.php';

if (!isLoggedIn() || !hasRole('seller')) {
    header('Location: ../login.php');
    exit();
}

$carId = $_GET['car_id'] ?? 0;
$sellerId = $_SESSION['user_id'];

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND seller_id = ?");
$stmt->execute([$carId, $sellerId]);
$car = $stmt->fetch();

if (!$car) {
    die("Car not found or you don't have access.");
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
    
    // Validation
    if (empty($title) || empty($brand) || empty($model) || $price <= 0) {
        $errors[] = "Please fill in all required fields.";
    }
    
    if ($year < 1900 || $year > date('Y') + 1) {
        $errors[] = "Please enter a valid year.";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE cars SET title = ?, description = ?, price = ?, brand = ?, model = ?, year = ?, 
                          mileage = ?, fuel_type = ?, transmission = ?, color = ?
            WHERE id = ? AND seller_id = ?
        ");
        
        if ($stmt->execute([$title, $description, $price, $brand, $model, $year, 
                           $mileage, $fuelType, $transmission, $color, $carId, $sellerId])) {
            $success = "Car listing updated successfully!";
            // Refresh car data
            $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
            $stmt->execute([$carId]);
            $car = $stmt->fetch();
        } else {
            $errors[] = "Failed to update listing. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car Listing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script>tailwind.config = { theme: { extend: { colors: { primary: '#0043ff' } } } }</script>
</head>
<body class="bg-gray-100">
    <nav class="bg-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="seller_dashboard.php" class="text-2xl font-bold text-white"><i class="fas fa-car mr-2"></i>CarSell</a>
                <a href="seller_dashboard.php" class="bg-white text-primary px-4 py-2 rounded-lg hover:bg-gray-100 transition font-semibold">Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                <h2 class="text-2xl font-bold text-white"><i class="fas fa-edit mr-2"></i>Edit Car Listing</h2>
            </div>

            <div class="p-8">
                <?php if ($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i><?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg"><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Title *</label>
                        <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                               name="title" required placeholder="e.g., 2020 Honda City - Well Maintained" value="<?= htmlspecialchars($car['title']) ?>">
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Brand *</label>
                            <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="brand" required placeholder="e.g., Honda" value="<?= htmlspecialchars($car['brand']) ?>">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Model *</label>
                            <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="model" required placeholder="e.g., City" value="<?= htmlspecialchars($car['model']) ?>">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Year *</label>
                            <input type="number" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="year" required min="1900" max="<?= date('Y') + 1 ?>" value="<?= $car['year'] ?>">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Price (₹) *</label>
                            <input type="number" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="price" required min="1" placeholder="500000" value="<?= $car['price'] ?>">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Mileage (km)</label>
                            <input type="number" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="mileage" min="0" placeholder="45000" value="<?= $car['mileage'] ?>">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Fuel Type *</label>
                            <select class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" name="fuel_type" required>
                                <option value="Petrol" <?= $car['fuel_type'] === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                                <option value="Diesel" <?= $car['fuel_type'] === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="Electric" <?= $car['fuel_type'] === 'Electric' ? 'selected' : '' ?>>Electric</option>
                                <option value="Hybrid" <?= $car['fuel_type'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                                <option value="CNG" <?= $car['fuel_type'] === 'CNG' ? 'selected' : '' ?>>CNG</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Transmission *</label>
                            <select class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" name="transmission" required>
                                <option value="Manual" <?= $car['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                                <option value="Automatic" <?= $car['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Color</label>
                            <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="color" placeholder="e.g., White" value="<?= htmlspecialchars($car['color']) ?>">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Description</label>
                        <textarea class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                  name="description" rows="4" placeholder="Describe the condition, features, and any additional details..."><?= htmlspecialchars($car['description']) ?></textarea>
                    </div>

                    <div class="space-y-3 pt-4">
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                        <a href="seller_dashboard.php" class="block text-center w-full bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-3 rounded-lg transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

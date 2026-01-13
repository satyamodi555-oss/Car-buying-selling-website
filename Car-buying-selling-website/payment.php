<?php
session_start();
require_once 'includes/db.php';

if (!isLoggedIn() || !hasRole('buyer')) {
    header('Location: login.php');
    exit();
}

$carId = $_GET['car_id'] ?? 0;
$buyerId = $_SESSION['user_id'];

// Get car and seller details
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name as seller_name
    FROM cars c JOIN users u ON u.id = c.seller_id
    WHERE c.id = ? AND c.status = 'available'
");
$stmt->execute([$carId]);
$car = $stmt->fetch();

if (!$car) {
    die("Car not available.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardNumber = $_POST['card_number'];
    $cardName = $_POST['card_name'];
    
    // Mock validation
    if (strlen($cardNumber) === 16 && !empty($cardName)) {
        try {
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (car_id, buyer_id, seller_id, amount, status, payment_method)
                VALUES (?, ?, ?, ?, 'completed', 'card')
            ");
            $stmt->execute([$carId, $buyerId, $car['seller_id'], $car['price']]);
            
            // Mark car as sold
            $stmt = $pdo->prepare("UPDATE cars SET status = 'sold' WHERE id = ?");
            $stmt->execute([$carId]);
            
            $success = "Payment successful! Order ID: " . $pdo->lastInsertId();
        } catch (Exception $e) {
            $error = "Payment processing failed.";
        }
    } else {
        $error = "Invalid card details. Please enter a valid 16-digit card number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?= htmlspecialchars($car['title']) ?></title>
    
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

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16">
                <a href="index.html" class="text-2xl font-bold text-white">
                    <i class="fas fa-car mr-2"></i>CarSell
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check-circle text-5xl text-green-500"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4"><?= $success ?></h2>
                <p class="text-gray-600 mb-8">Thank you for your purchase!</p>
                <a href="index.html" class="inline-block bg-primary hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-lg transition">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Payment Form -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                    <h2 class="text-2xl font-bold text-white">
                        <i class="fas fa-credit-card mr-2"></i>Complete Payment
                    </h2>
                </div>

                <div class="p-8">
                    <!-- Error Message -->
                    <?php if ($error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                            <i class="fas fa-exclamation-circle mr-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>

                    <!-- Order Summary -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 mb-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Car:</span>
                                <span class="font-semibold"><?= htmlspecialchars($car['title']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Seller:</span>
                                <span class="font-semibold"><?= htmlspecialchars($car['seller_name']) ?></span>
                            </div>
                            <hr class="border-gray-300">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-gray-800">Total Price:</span>
                                <span class="text-3xl font-bold text-primary"><?= formatPrice($car['price']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form method="POST" class="space-y-6">
                        <!-- Info Alert -->
                        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg">
                            <i class="fas fa-info-circle mr-2"></i>This is a simulated payment. Use any 16-digit number.
                        </div>

                        <!-- Card Number -->
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Card Number</label>
                            <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                   name="card_number" placeholder="1234 5678 9012 3456" maxlength="16" pattern="[0-9]{16}" required>
                        </div>

                        <div class="grid md:grid-cols-3 gap-4">
                            <!-- Cardholder Name -->
                            <div class="md:col-span-1">
                                <label class="block text-gray-700 font-semibold mb-2">Cardholder Name</label>
                                <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                       name="card_name" required>
                            </div>

                            <!-- Expiry -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Expiry (MM/YY)</label>
                                <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                       placeholder="12/25" required>
                            </div>

                            <!-- CVV -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">CVV</label>
                                <input type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" 
                                       placeholder="123" maxlength="3" pattern="[0-9]{3}" required>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="space-y-3 pt-4">
                            <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-4 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                                <i class="fas fa-lock mr-2"></i>Pay <?= formatPrice($car['price']) ?>
                            </button>
                            <a href="car_details.php?id=<?= $carId ?>" 
                               class="block text-center w-full bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-3 rounded-lg transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

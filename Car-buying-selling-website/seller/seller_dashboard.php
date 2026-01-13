<?php
session_start();
require_once '../includes/db.php';

if (!isLoggedIn() || !hasRole('seller')) {
    header('Location: ../login.php');
    exit();
}

$sellerId = $_SESSION['user_id'];
// Refresh seller approval status
$stmt = $pdo->prepare("SELECT is_approved FROM users WHERE id = ?");
$stmt->execute([$sellerId]);
$userStatus = $stmt->fetch();
$_SESSION['is_approved'] = $userStatus['is_approved'];
$isApproved = $_SESSION['is_approved'];

// Get seller statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
       SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold
    FROM cars WHERE seller_id = ?
");
$stmt->execute([$sellerId]);
$stats = $stmt->fetch();

// Get seller's cars
$filter = $_GET['filter'] ?? 'all';
$query = "SELECT c.*, 
          (SELECT image_path FROM car_photos WHERE car_id = c.id AND is_main = 1 LIMIT 1) as main_photo
          FROM cars c WHERE c.seller_id = ?";

if ($filter === 'pending') $query .= " AND c.approval_status = 'pending'";
elseif ($filter === 'approved') $query .= " AND c.approval_status = 'approved'";
elseif ($filter === 'sold') $query .= " AND c.status = 'sold'";

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$sellerId]);
$cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#0043ff' } } } }
    </script>
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-primary shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="../index.html" class="text-2xl font-bold text-white">
                    <i class="fas fa-car mr-2"></i>CarSell
                </a>
                <div class="flex items-center space-x-4">
                    <span class="text-white"><i class="fas fa-user mr-1"></i><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="../logout.php" class="bg-white text-primary px-4 py-2 rounded-lg hover:bg-gray-100 transition font-semibold">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">


        <!-- Profile Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2"><i class="fas fa-store mr-2"></i>Seller Dashboard</h2>
            <p class="text-gray-600">Email: <?= htmlspecialchars($_SESSION['email']) ?></p>
            <span class="inline-block mt-2 px-4 py-1 rounded-full text-sm font-semibold <?= $isApproved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                <?= $isApproved ? 'Approved' : 'Pending Approval' ?>
            </span>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-blue-100 text-sm">Total Listings</p>
                <p class="text-4xl font-bold"><?= $stats['total'] ?></p>
                <i class="fas fa-car text-5xl opacity-20 absolute bottom-4 right-4"></i>
            </div>


        </div>

        <!-- Actions & Filters -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex space-x-2">
                <a href="?filter=all" class="px-4 py-2 <?= $filter === 'all' ? 'bg-primary text-white' : 'bg-white text-gray-700' ?> rounded-lg hover:bg-primary hover:text-white transition">All</a>

                <a href="?filter=sold" class="px-4 py-2 <?= $filter === 'sold' ? 'bg-primary text-white' : 'bg-white text-gray-700' ?> rounded-lg hover:bg-primary hover:text-white transition">Sold</a>
            </div>
            <a href="seller_create_car.php" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                <i class="fas fa-plus mr-2"></i>Add New Car
            </a>
        </div>

        <!-- Cars Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-800">My Listings</h3>
            </div>
            <?php if (empty($cars)): ?>
                <p class="text-center text-gray-500 py-12">No cars found. <a href="seller_create_car.php" class="text-primary hover:underline">Add your first listing!</a></p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Photo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($cars as $car): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <?php if ($car['main_photo']): ?>
                                        <img src="../<?= htmlspecialchars($car['main_photo']) ?>" class="w-20 h-16 object-cover rounded-lg">
                                    <?php else: ?>
                                        <div class="w-20 h-16 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 text-xs">No Photo</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($car['title']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></p>
                                </td>
                                <td class="px-6 py-4 font-semibold"><?= formatPrice($car['price']) ?></td>

                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $car['status'] === 'sold' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= ucfirst($car['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="seller_edit_car.php?car_id=<?= $car['id'] ?>" 
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm transition inline-block mr-2">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="seller_add_photos.php?car_id=<?= $car['id'] ?>" 
                                       class="bg-primary hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition inline-block mr-2">
                                        <i class="fas fa-images"></i>
                                    </a>
                                    <button onclick="deleteCar(<?= $car['id'] ?>)" 
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteCar(carId) {
            if (confirm('Are you sure you want to delete this car listing? This action cannot be undone.')) {
                fetch('../api/delete_car.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ car_id: carId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Car deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Delete failed'));
            }
        }
    </script>
</body>
</html>

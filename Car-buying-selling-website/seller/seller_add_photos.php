<?php
session_start();
require_once '../includes/db.php';

if (!isLoggedIn() || !hasRole('seller')) {
    header('Location: login.php');
    exit();
}

$carId = $_GET['car_id'] ?? 0;
$sellerId = $_SESSION['user_id'];

// Verify car ownership
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND seller_id = ?");
$stmt->execute([$carId, $sellerId]);
$car = $stmt->fetch();

if (!$car) {
    die("Car not found or you don't have access.");
}

// Get existing photos
$stmt = $pdo->prepare("SELECT * FROM car_photos WHERE car_id = ? ORDER BY is_main DESC, id ASC");
$stmt->execute([$carId]);
$photos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Photos - <?= htmlspecialchars($car['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style_v2.css" rel="stylesheet">
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($car['title']) ?></h2>
            <p class="text-xl text-primary font-semibold"><?= formatPrice($car['price']) ?></p>
        </div>

        <!-- Upload Area -->
        <div class="bg-white  rounded-xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                <h3 class="text-xl font-bold text-white"><i class="fas fa-cloud-upload-alt mr-2"></i>Upload Photos</h3>
            </div>
            <div class="p-8">
                <div class="upload-area border-4 border-dashed border-gray-300 rounded-xl p-12 text-center cursor-pointer hover:border-primary transition" id="uploadArea">
                    <i class="fas fa-images text-6xl text-gray-400 mb-4"></i>
                    <h5 class="text-xl font-semibold text-gray-700 mb-2">Drag & Drop photos here</h5>
                    <p class="text-gray-500">or click to browse</p>
                    <input type="file" id="fileInput" multiple accept="image/*" class="hidden">
                </div>
                <p class="text-sm text-gray-500 mt-3">Max 5MB per image. Supported formats: JPG, PNG, WEBP</p>
            </div>
        </div>

        <!-- Photo Grid -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-800"><i class="fas fa-image mr-2"></i>Current Photos (<span id="photoCount"><?= count($photos) ?></span>)</h3>
            </div>
            <div class="p-8" id="photosContainer">
                <?php if (empty($photos)): ?>
                    <p class="text-center text-gray-500 py-12">No photos uploaded yet. Add some photos to make your listing stand out!</p>
                <?php else: ?>
                    <div class="photo-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="photoGrid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-item relative group <?= $photo['is_main'] ? 'ring-4 ring-green-500' : '' ?> rounded-lg overflow-hidden shadow-lg" data-photo-id="<?= $photo['id'] ?>">
                                <?php if ($photo['is_main']): ?>
                                    <div class="main-badge absolute top-2 left-2 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold z-10">
                                        <i class="fas fa-star mr-1"></i>Main Photo
                                    </div>
                                <?php endif; ?>
                                <img src="../<?= htmlspecialchars($photo['image_path']) ?>" alt="Car Photo" class="w-full h-48 object-cover">
                                <div class="photo-actions absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 p-3 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                                    <?php if (!$photo['is_main']): ?>
                                        <button class="flex-1 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm transition" onclick="setMainPhoto(<?= $photo['id'] ?>)">
                                            <i class="fas fa-star mr-1"></i>Set Main
                                        </button>
                                    <?php endif; ?>
                                    <button class="flex-1 bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm transition" onclick="deletePhoto(<?= $photo['id'] ?>)">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/photo_manager.js?v=<?= time() ?>"></script>
    <script>const CAR_ID = <?= $carId ?>;</script>
</body>
</html>

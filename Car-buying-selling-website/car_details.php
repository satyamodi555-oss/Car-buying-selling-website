<?php
session_start();
require_once 'includes/db.php';

$carId = $_GET['id'] ?? 0;

// Get car details with seller info
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name as seller_name, u.email as seller_email, u.phone as seller_phone
    FROM cars c JOIN users u ON u.id = c.seller_id
    WHERE c.id = ? AND c.approval_status = 'approved' AND c.status = 'available'
");
$stmt->execute([$carId]);
$car = $stmt->fetch();

if (!$car) {
    die("Car not found or not available.");
}

// Get all photos
$stmt = $pdo->prepare("SELECT * FROM car_photos WHERE car_id = ? ORDER BY is_main DESC, id ASC");
$stmt->execute([$carId]);
$photos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-car"></i> CarSell</a>
            <div class="navbar-nav ms-auto">
                <a href="browse_cars.php" class="btn btn-outline-light btn-sm">← Back to Browse</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Photos Gallery -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <?php if (empty($photos)): ?>
                            <div style="height: 400px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-car fa-5x text-muted"></i>
                            </div>
                        <?php else: ?>
                            <div id="carouselPhotos" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php foreach ($photos as $index => $photo): ?>
                                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                            <img src="<?= htmlspecialchars($photo['image_path']) ?>" 
                                                 class="d-block w-100" alt="Car Photo"
                                                 style="height: 400px; object-fit: cover; border-radius: 10px;">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($photos) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotos" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotos" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Details -->
                <div class="card">
                    <div class="card-body">
                        <h3><?= htmlspecialchars($car['title']) ?></h3>
                        <p class="text-muted"><?= htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' • ' . $car['year']) ?></p>
                        
                        <h4 class="text-primary mt-3"><?= formatPrice($car['price']) ?></h4>
                        
                        <h5 class="mt-4">Specifications</h5>
                        <table class="table">
                            <tr><td><strong>Brand</strong></td><td><?= htmlspecialchars($car['brand']) ?></td></tr>
                            <tr><td><strong>Model</strong></td><td><?= htmlspecialchars($car['model']) ?></td></tr>
                            <tr><td><strong>Year</strong></td><td><?= $car['year'] ?></td></tr>
                            <tr><td><strong>Mileage</strong></td><td><?= number_format($car['mileage']) ?> km</td></tr>
                            <tr><td><strong>Fuel Type</strong></td><td><?= htmlspecialchars($car['fuel_type']) ?></td></tr>
                            <tr><td><strong>Transmission</strong></td><td><?= htmlspecialchars($car['transmission']) ?></td></tr>
                            <?php if ($car['color']): ?>
                                <tr><td><strong>Color</strong></td><td><?= htmlspecialchars($car['color']) ?></td></tr>
                            <?php endif; ?>
                        </table>
                        
                        <?php if ($car['description']): ?>
                            <h5 class="mt-4">Description</h5>
                            <p><?= nl2br(htmlspecialchars($car['description'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Seller Info & Actions -->
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Seller Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($car['seller_name']) ?></p>
     <?php if ($car['seller_email']): ?>
                            <p><strong>Email:</strong> <?= htmlspecialchars($car['seller_email']) ?></p>
                        <?php endif; ?>
                        <?php if ($car['seller_phone']): ?>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($car['seller_phone']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-primary mb-3"><?= formatPrice($car['price']) ?></h4>
                        <?php if (isLoggedIn() && hasRole('buyer')): ?>
                            <a href="payment.php?car_id=<?= $car['id'] ?>" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-shopping-cart"></i> Buy Now
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-lg w-100">
                                Login to Buy
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

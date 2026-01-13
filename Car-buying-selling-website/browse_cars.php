<?php
session_start();
require_once 'includes/db.php';

// Build search query
$where = ["c.approval_status = 'approved'", "c.status = 'available'"];
$params = [];

// Join with users to check seller approval
$where[] = "u.is_approved = 1";

// Search filters
if (!empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $where[] = "(c.title LIKE ? OR c.brand LIKE ? OR c.model LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['brand'])) {
    $where[] = "c.brand = ?";
    $params[] = $_GET['brand'];
}

if (!empty($_GET['fuel_type'])) {
    $where[] = "c.fuel_type = ?";
    $params[] = $_GET['fuel_type'];
}

if (!empty($_GET['transmission'])) {
    $where[] = "c.transmission = ?";
    $params[] = $_GET['transmission'];
}

if (!empty($_GET['min_price'])) {
    $where[] = "c.price >= ?";
    $params[] = intval($_GET['min_price']);
}

if (!empty($_GET['max_price'])) {
    $where[] = "c.price <= ?";
    $params[] = intval($_GET['max_price']);
}

$sql = "SELECT c.*, u.full_name as seller_name,
        (SELECT image_path FROM car_photos WHERE car_id = c.id AND is_main = 1 LIMIT 1) as main_photo
        FROM cars c
        JOIN users u ON u.id = c.seller_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Get unique brands for filter
$brandsStmt = $pdo->query("SELECT DISTINCT brand FROM cars WHERE brand IS NOT NULL ORDER BY brand");
$brands = $brandsStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars - Car Selling Platform</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style_v2.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-car"></i> CarSell</a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <?php if (hasRole('admin')): ?>
                        <a href="admin/dashboard.php" class="btn btn-outline-light btn-sm me-2">Admin Dashboard</a>
                    <?php elseif (hasRole('seller')): ?>
                        <a href="seller/seller_dashboard.php" class="btn btn-outline-light btn-sm me-2">Seller Dashboard</a>
                    <?php endif; ?>
                    <span class="navbar-text text-white me-3"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
                    <a href="registration.php" class="btn btn-light btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Search Section -->
        <div class="search-container">
            <h4 class="mb-3"><i class="fas fa-search"></i> Find Your Dream Car</h4>
            <form method="GET" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control search-input" name="search" 
                               placeholder="Search by title, brand, or model..."
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" name="brand">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= htmlspecialchars($brand) ?>" 
                                        <?= ($_GET['brand'] ?? '') === $brand ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($brand) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" name="fuel_type">
                            <option value="">All Fuel Types</option>
                            <option value="Petrol" <?= ($_GET['fuel_type'] ?? '') === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                            <option value="Diesel" <?= ($_GET['fuel_type'] ?? '') === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                            <option value="Electric" <?= ($_GET['fuel_type'] ?? '') === 'Electric' ? 'selected' : '' ?>>Electric</option>
                            <option value="Hybrid" <?= ($_GET['fuel_type'] ?? '') === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                            <option value="CNG" <?= ($_GET['fuel_type'] ?? '') === 'CNG' ? 'selected' : '' ?>>CNG</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" name="transmission">
                            <option value="">All Transmission</option>
                            <option value="Manual" <?= ($_GET['transmission'] ?? '') === 'Manual' ? 'selected' : '' ?>>Manual</option>
                            <option value="Automatic" <?= ($_GET['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <input type="number" class="form-control" name="min_price" placeholder="Min Price (₹)"
                               value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" name="max_price" placeholder="Max Price (₹)"
                               value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cars Grid -->
        <h3 class="mt-4 mb-3">Available Cars (<?= count($cars) ?>)</h3>
        <?php if (empty($cars)): ?>
            <div class="alert alert-info">No cars found matching your criteria.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($cars as $car): ?>
                    <div class="col-md-4 mb-4">
                        <div class="car-card">
                            <?php if ($car['main_photo']): ?>
                                <img src="<?= htmlspecialchars($car['main_photo']) ?>" 
                                     alt="<?= htmlspecialchars($car['title']) ?>"  class="car-card-img">
                            <?php else: ?>
                                <div class="car-card-img" style="background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-car fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="car-card-body">
                                <h5><?= htmlspecialchars($car['title']) ?></h5>
                                <p class="text-muted mb-2">
                                    <?= htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' • ' . $car['year']) ?>
                                </p>
                                <div class="mb-2">
                                    <span class="car-badge" style="background: #e7f3ff; color: #0056b3;">
                                        <?= htmlspecialchars($car['fuel_type']) ?>
                                    </span>
                                    <span class="car-badge" style="background: #fff3e0; color: #e65100;">
                                        <?= htmlspecialchars($car['transmission']) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="price-tag"><?= formatPrice($car['price']) ?></div>
                                    <a href="car_details.php?id=<?= $car['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

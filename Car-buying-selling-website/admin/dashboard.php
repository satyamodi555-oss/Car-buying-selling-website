<?php
session_start();
require_once '../includes/db.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'seller'");
$stats['total_sellers'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'buyer'");
$stats['total_buyers'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM cars");
$stats['total_cars'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'");
$stats['total_sales'] = $stmt->fetch()['count'];

// Pending approvals
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'seller' AND is_approved = 0 ORDER BY created_at DESC LIMIT 10");
$pendingSellers = $stmt->fetchAll();

$stmt = $pdo->query("SELECT c.*, u.full_name as seller_name FROM cars c JOIN users u ON u.id = c.seller_id WHERE c.approval_status = 'pending' ORDER BY c.created_at DESC LIMIT 10");
$pendingCars = $stmt->fetchAll();

// All users with search and filter
$userSearch = $_GET['search_user'] ?? '';
$userRole = $_GET['role_filter'] ?? '';

$userQuery = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($userSearch) {
    $userQuery .= " AND (full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$userSearch%";
    $params[] = "%$userSearch%";
}

if ($userRole) {
    $userQuery .= " AND role = ?";
    $params[] = $userRole;
}

$userQuery .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($userQuery);
$stmt->execute($params);
$users = $stmt->fetchAll();

// All cars with search and filter
$carSearch = $_GET['search_car'] ?? '';
$carStatus = $_GET['status_filter'] ?? '';

$carQuery = "SELECT c.*, u.full_name as seller_name FROM cars c JOIN users u ON u.id = c.seller_id WHERE 1=1";
$carParams = [];

if ($carSearch) {
    $carQuery .= " AND (c.title LIKE ? OR u.full_name LIKE ?)";
    $carParams[] = "%$carSearch%";
    $carParams[] = "%$carSearch%";
}

if ($carStatus) {
    $carQuery .= " AND c.status = ?";
    $carParams[] = $carStatus;
}

$carQuery .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($carQuery);
$stmt->execute($carParams);
$cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-red-600 to-pink-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="dashboard.php" class="text-2xl font-bold text-white">
                    <i class="fas fa-shield-alt mr-2"></i>Admin Panel
                </a>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-white font-semibold hover:text-gray-200 transition">
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                    </a>
                    <a href="../index.php" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-home mr-1"></i>View Site
                    </a>
                    <a href="messages.php" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-envelope mr-1"></i>Messages
                    </a>
                    <a href="../logout.php" class="bg-white text-red-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition font-semibold">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Sellers -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Total Sellers</p>
                        <p class="text-4xl font-bold"><?= $stats['total_sellers'] ?></p>
                    </div>
                    <div class="text-5xl opacity-30">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Buyers -->
            <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-cyan-100 text-sm">Total Buyers</p>
                        <p class="text-4xl font-bold"><?= $stats['total_buyers'] ?></p>
                    </div>
                    <div class="text-5xl opacity-30">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>

            <!-- Listings -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Total Listings</p>
                        <p class="text-4xl font-bold"><?= $stats['total_cars'] ?></p>
                    </div>
                    <div class="text-5xl opacity-30">
                        <i class="fas fa-car"></i>
                    </div>
                </div>
            </div>

            <!-- Sales -->
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm">Total Sales</p>
                        <p class="text-4xl font-bold"><?= $stats['total_sales'] ?></p>
                    </div>
                    <div class="text-5xl opacity-30">
                        <i class="fas fa-money-bill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid md:grid-cols-1 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">User Growth</h3>
                <canvas id="userGrowthChart" class="w-full" style="max-height: 300px;"></canvas>
            </div>
        </div>



        <!-- Pending Approvals -->
        <?php if (count($pendingSellers) > 0): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg mb-8">
            <h2 class="text-2xl font-bold text-yellow-800 mb-4">
                <i class="fas fa-clock mr-2"></i>Pending Seller Approvals
            </h2>

            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Sellers Awaiting Approval (<?= count($pendingSellers) ?>)</h3>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registered</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($pendingSellers as $seller): ?>
                            <tr>
                                <td class="px-6 py-4"><?= htmlspecialchars($seller['full_name']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($seller['email']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= $seller['created_at'] ?></td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="approveSeller(<?= $seller['id'] ?>)" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg mr-2 transition">
                                        Approve
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- All Users & Cars Tabs -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex">
                    <button onclick="showTab('users')" id="users-tab" 
                        class="px-6 py-4 text-sm font-medium border-b-2 border-primary text-primary">
                        All Users (<?= count($users) ?>)
                    </button>
                    <button onclick="showTab('cars')" id="cars-tab"
                        class="px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        All Listings (<?= count($cars) ?>)
                    </button>
                </nav>
            </div>

            <!-- Users Table -->
            <div id="users-content" class="overflow-x-auto">
                <div class="p-4 bg-gray-50 border-b">
                    <form action="" method="GET" class="flex flex-wrap gap-4 items-center">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                            <input type="text" name="search_user" placeholder="Search by name or email..." 
                                value="<?= htmlspecialchars($userSearch) ?>" 
                                class="pl-10 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary w-64 shadow-sm">
                        </div>
                        
                        <select name="role_filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                            <option value="">All Roles</option>
                            <option value="seller" <?= $userRole === 'seller' ? 'selected' : '' ?>>Seller</option>
                            <option value="buyer" <?= $userRole === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                            <option value="admin" <?= $userRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium shadow-sm">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        
                        <?php if ($userSearch || $userRole): ?>
                            <a href="dashboard.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition font-medium shadow-sm flex items-center">
                                <i class="fas fa-times mr-2"></i>Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4"><?= htmlspecialchars($user['full_name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : ($user['role'] === 'seller' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    <?= $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($user['status'] === 'active'): ?>
                                    <button onclick="banUser(<?= $user['id'] ?>)" 
                                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                                        Ban
                                    </button>
                                <?php else: ?>
                                    <button onclick="unbanUser(<?= $user['id'] ?>)" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition">
                                        Unban
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cars Table -->
            <div id="cars-content" class="hidden overflow-x-auto">
                <div class="p-4 bg-gray-50 border-b">
                    <form action="" method="GET" class="flex flex-wrap gap-4 items-center">
                        <input type="hidden" name="tab" value="cars">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                            <input type="text" name="search_car" placeholder="Search by title or seller..." 
                                value="<?= htmlspecialchars($carSearch) ?>" 
                                class="pl-10 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary w-64 shadow-sm">
                        </div>
                        
                        <select name="status_filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                            <option value="">All Status</option>
                            <option value="available" <?= $carStatus === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="sold" <?= $carStatus === 'sold' ? 'selected' : '' ?>>Sold</option>
                        </select>
                        
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium shadow-sm">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        
                        <?php if ($carSearch || $carStatus): ?>
                            <a href="dashboard.php?tab=cars" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition font-medium shadow-sm flex items-center">
                                <i class="fas fa-times mr-2"></i>Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>

                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($cars as $car): ?>
                        <tr>
                            <td class="px-6 py-4"><?= htmlspecialchars($car['title']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($car['seller_name']) ?></td>
                            <td class="px-6 py-4 font-semibold"><?= formatPrice($car['price']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    <?= $car['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= ucfirst($car['status']) ?>
                                </span>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../assets/js/charts.js"></script>
    <script>
        function showTab(tab) {
            if (tab === 'users') {
                document.getElementById('users-content').classList.remove('hidden');
                document.getElementById('cars-content').classList.add('hidden');
                document.getElementById('users-tab').classList.add('border-primary', 'text-primary');
                document.getElementById('users-tab').classList.remove('border-transparent', 'text-gray-500');
                document.getElementById('cars-tab').classList.remove('border-primary', 'text-primary');
                document.getElementById('cars-tab').classList.add('border-transparent', 'text-gray-500');
            } else {
                document.getElementById('users-content').classList.add('hidden');
                document.getElementById('cars-content').classList.remove('hidden');
                document.getElementById('cars-tab').classList.add('border-primary', 'text-primary');
                document.getElementById('cars-tab').classList.remove('border-transparent', 'text-gray-500');
                document.getElementById('users-tab').classList.remove('border-primary', 'text-primary');
                document.getElementById('users-tab').classList.add('border-transparent', 'text-gray-500');
            }
        }

        function approveSeller(id) {
            fetch('../api/approve_seller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ seller_id: id, action: 'approve' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Request failed'));
        }

        function approveCar(id) {
            fetch('../api/approve_car.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ car_id: id, action: 'approve' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Request failed'));
        }

        function rejectCar(id) {
            if (confirm('Reject this listing?')) {
                fetch('../api/approve_car.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ car_id: id, action: 'reject' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Request failed'));
            }
        }

        function banUser(id) {
            if (confirm('Ban this user?')) {
                fetch('../api/ban_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: id, action: 'ban' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }
        }

        function unbanUser(id) {
            if (confirm('Unban this user?')) {
                fetch('../api/ban_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: id, action: 'unban' })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }
        }

        // Check for tab parameter in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'cars') {
            showTab('cars');
        }
    </script>
</body>
</html>

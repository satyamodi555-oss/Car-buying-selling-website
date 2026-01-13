<?php
session_start();
require_once '../includes/db.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Fetch messages
$stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#0043ff' } } } }
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
                    <a href="messages.php" class="text-white font-bold transition border-b-2 border-white">
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
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">Contact Messages</h1>
            <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full font-semibold">
                Total: <?= count($messages) ?>
            </span>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <?php if (count($messages) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($messages as $msg): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    <?= htmlspecialchars($msg['name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:text-blue-800">
                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>">
                                        <?= htmlspecialchars($msg['email']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-700 text-sm whitespace-pre-wrap max-h-32 overflow-y-auto"><?= htmlspecialchars($msg['message']) ?></p>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-inbox text-6xl mb-4 opacity-30"></i>
                    <p class="text-xl">No messages found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

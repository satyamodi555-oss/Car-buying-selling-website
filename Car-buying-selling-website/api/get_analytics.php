<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // 1. User Growth (Last 6 months)
    $growth = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthName = date('M Y', strtotime("-$i months"));
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$month]);
        $growth['labels'][] = $monthName;
        $growth['data'][] = $stmt->fetch()['count'];
    }

    // 2. Cars by Brand (Top 5)
    $stmt = $pdo->query("
        SELECT brand, COUNT(*) as count 
        FROM cars 
        GROUP BY brand 
        ORDER BY count DESC 
        LIMIT 5
    ");
    $topBrands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Sales Status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM cars 
        GROUP BY status
    ");
    $salesStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'growth' => $growth,
        'brands' => [
            'labels' => array_column($topBrands, 'brand'),
            'data' => array_column($topBrands, 'count')
        ],
        'sales' => [
            'labels' => array_column($salesStatus, 'status'),
            'data' => array_column($salesStatus, 'count')
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

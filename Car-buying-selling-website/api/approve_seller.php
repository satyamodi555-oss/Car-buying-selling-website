<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$sellerId = $data['seller_id'] ?? 0;
$action = $data['action'] ?? ''; // 'approve' or 'reject'

if (!in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

$isApproved = ($action === 'approve') ? 1 : 0;

try {
    $stmt = $pdo->prepare("UPDATE users SET is_approved = ? WHERE id = ? AND role = 'seller'");
    $stmt->execute([$isApproved, $sellerId]);
    
    // Log
    $stmt = $pdo->prepare("
        INSERT INTO admin_logs (admin_id, action, target_type, target_id, details)
        VALUES (?, ?, 'user', ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], "seller_$action", $sellerId, "Seller $action".'d']);
    
    echo json_encode(['success' => true, 'message' => 'Seller ' . $action . 'd']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error']);
}

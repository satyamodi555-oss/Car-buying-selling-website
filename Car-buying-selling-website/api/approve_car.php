<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$carId = $data['car_id'] ?? 0;
$action = $data['action'] ?? ''; // 'approve' or 'reject'

if (!in_array($action, ['approve', 'approved', 'reject', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

$status = ($action === 'approve' || $action === 'approved') ? 'approved' : 'rejected';

try {
    $stmt = $pdo->prepare("UPDATE cars SET approval_status = ? WHERE id = ?");
    $stmt->execute([$status, $carId]);
    
    // Log
    $stmt = $pdo->prepare("
        INSERT INTO admin_logs (admin_id, action, target_type, target_id, details)
        VALUES (?, ?, 'car', ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], "car_$action", $carId, "Car listing $status"]);
    
    echo json_encode(['success' => true, 'message' => 'Car ' . $status]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error']);
}

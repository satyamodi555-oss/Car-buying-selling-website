<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? 0;
$action = $data['action'] ?? ''; // 'ban' or 'unban'

if (!in_array($action, ['ban', 'unban'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

$newStatus = ($action === 'ban') ? 'banned' : 'active';

try {
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
    $stmt->execute([$newStatus, $userId]);
    
    // Log admin action
    $stmt = $pdo->prepare("
        INSERT INTO admin_logs (admin_id, action, target_type, target_id, details)
        VALUES (?, ?, 'user', ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $action,
        $userId,
        "User $action".'ned'
    ]);
    
    echo json_encode(['success' => true, 'message' => 'User ' . $action . 'ned successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    if (!isLoggedIn() || !hasRole('seller')) {
        throw new Exception('Unauthorized');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $photoId = $data['photo_id'] ?? 0;
    $carId = $data['car_id'] ?? 0;
    $sellerId = $_SESSION['user_id'];

    if (!$photoId || !$carId) {
        throw new Exception('Missing parameters');
    }

    // Verify ownership
    $stmt = $pdo->prepare("
        SELECT cp.id FROM car_photos cp
        JOIN cars c ON c.id = cp.car_id
        WHERE cp.id = ? AND c.id = ? AND c.seller_id = ?
    ");
    $stmt->execute([$photoId, $carId, $sellerId]);

    if (!$stmt->fetch()) {
        throw new Exception('Photo not found or unauthorized');
    }

    $pdo->beginTransaction();

    try {
        // Set all photos to non-main
        $stmt = $pdo->prepare("UPDATE car_photos SET is_main = 0 WHERE car_id = ?");
        $stmt->execute([$carId]);
        
        // Set selected photo as main
        $stmt = $pdo->prepare("UPDATE car_photos SET is_main = 1 WHERE id = ?");
        $stmt->execute([$photoId]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Main photo updated', 'photo_id' => $photoId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

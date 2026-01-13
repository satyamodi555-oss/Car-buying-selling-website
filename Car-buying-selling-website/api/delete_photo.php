<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

try {
    if (!isLoggedIn() || !hasRole('seller')) {
        throw new Exception('Unauthorized');
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $photoId = $data['photo_id'] ?? 0;
    $sellerId = $_SESSION['user_id'];

    if (!$photoId) {
        throw new Exception('Invalid Photo ID');
    }

    // Get photo info and verify ownership
    $stmt = $pdo->prepare("
        SELECT cp.id, cp.image_path, cp.is_main, cp.car_id FROM car_photos cp
        JOIN cars c ON c.id = cp.car_id
        WHERE cp.id = ? AND c.seller_id = ?
    ");
    $stmt->execute([$photoId, $sellerId]);
    $photo = $stmt->fetch();

    if (!$photo) {
        throw new Exception('Photo not found or undefined permission');
    }

    // ALLOW deleting main photo. Logic to re-assign main follows deletion.

    // Delete file
    $filePath = __DIR__ . '/../' . $photo['image_path'];
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            // Log warning
        }
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM car_photos WHERE id = ?");
    $stmt->execute([$photoId]);

    $newMainId = null;

    // If we deleted the main photo, assign a new one
    if ($photo['is_main']) {
        // Find another photo for this car
        $stmt = $pdo->prepare("SELECT id FROM car_photos WHERE car_id = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$photo['car_id']]);
        $nextPhoto = $stmt->fetch();

        if ($nextPhoto) {
            $stmt = $pdo->prepare("UPDATE car_photos SET is_main = 1 WHERE id = ?");
            $stmt->execute([$nextPhoto['id']]);
            $newMainId = $nextPhoto['id'];
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Photo deleted', 
        'photo_id' => $photoId,
        'new_main_id' => $newMainId
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

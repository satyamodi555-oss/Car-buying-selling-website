<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('seller')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$carId = $data['car_id'] ?? 0;
$sellerId = $_SESSION['user_id'];

try {
    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM cars WHERE id = ? AND seller_id = ?");
    $stmt->execute([$carId, $sellerId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Car not found or access denied']);
        exit();
    }
    
    // Delete car photos first
    $stmt = $pdo->prepare("SELECT image_path FROM car_photos WHERE car_id = ?");
    $stmt->execute([$carId]);
    $photos = $stmt->fetchAll();
    
    foreach ($photos as $photo) {
        $filePath = '../' . $photo['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Delete photo records
    $stmt = $pdo->prepare("DELETE FROM car_photos WHERE car_id = ?");
    $stmt->execute([$carId]);
    
    // Delete the car
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ? AND seller_id = ?");
    $stmt->execute([$carId, $sellerId]);
    
    echo json_encode(['success' => true, 'message' => 'Car deleted successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error deleting car']);
}
?>

<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Enhanced error logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log

function debugLog($message) {
    // Write to uploads folder which should be writable
    $logFile = __DIR__ . '/../uploads/debug_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    // Try to open file in append mode
    if ($fp = @fopen($logFile, 'a')) {
        fwrite($fp, "[$timestamp] $message" . PHP_EOL);
        fclose($fp);
    }
}

try {
    if (!isLoggedIn() || !hasRole('seller')) {
        throw new Exception('Unauthorized - Please login as a seller');
    }

    $carId = $_POST['car_id'] ?? 0;
    $sellerId = $_SESSION['user_id'];

    if (!$carId) {
        throw new Exception('No car ID provided');
    }

    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM cars WHERE id = ? AND seller_id = ?");
    $stmt->execute([$carId, $sellerId]);
    if (!$stmt->fetch()) {
        throw new Exception('Car not found or you do not own this car');
    }

    if (empty($_FILES['photos'])) {
        throw new Exception('No files uploaded. Please select photos.');
    }

    // Check upload directory
    $baseDir = __DIR__ . '/../uploads/';
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0777, true);
    }
    
    $uploadDir = $baseDir . "cars/$carId/";
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            $error = error_get_last();
            throw new Exception('Failed to create upload directory');
        }
        chmod($uploadDir, 0777);
    }

    // Verify directory is writable
    if (!is_writable($uploadDir)) {
        throw new Exception('Upload directory is not writable');
    }

    $uploadedPhotos = [];
    $errors = [];

    // Helper to get array structure even for single file
    $files = $_FILES['photos'];
    $count = is_array($files['name']) ? count($files['name']) : 1;

    for ($i = 0; $i < $count; $i++) {
        $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
        $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];

        if ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = "$fileName: Upload error code $fileError";
            continue;
        }

        if ($fileSize > 5 * 1024 * 1024) {
            $errors[] = "$fileName: File too large (max 5MB)";
            continue;
        }
        
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $errors[] = "$fileName: Invalid format";
            continue;
        }
        
        // Generate unique filename
        $newFileName = uniqid() . '.' . $ext;
        $filePath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($tmpName, $filePath)) {
            $relativePath = "uploads/cars/$carId/$newFileName";
            
            // Check if this is the first photo
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM car_photos WHERE car_id = ?");
            $stmt->execute([$carId]);
            $existingCount = $stmt->fetch()['count'];
            $isMain = ($existingCount == 0 && count($uploadedPhotos) == 0) ? 1 : 0;
            
            // Insert to database
            $stmt = $pdo->prepare("INSERT INTO car_photos (car_id, image_path, is_main) VALUES (?, ?, ?)");
            $stmt->execute([$carId, $relativePath, $isMain]);
            $newId = $pdo->lastInsertId();

            $uploadedPhotos[] = [
                'id' => $newId,
                'image_path' => $relativePath,
                'is_main' => $isMain,
                'url' => '../' . $relativePath // Helper for frontend
            ];
        } else {
            $errors[] = "$fileName: Failed to move file";
        }
    }

    if (count($uploadedPhotos) > 0) {
        echo json_encode([
            'success' => true,
            'message' => count($uploadedPhotos) . ' photo(s) uploaded',
            'photos' => $uploadedPhotos, // Return the list of new photos
            'errors' => $errors
        ]);
    } else {
        throw new Exception('No photos were uploaded successfully. ' . implode(', ', $errors));
    }

} catch (Exception $e) {
    debugLog("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

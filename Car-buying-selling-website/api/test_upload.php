<?php
// Standalone test upload script - bypasses auth for debugging
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
    'files_received' => !empty($_FILES),
    'files_info' => isset($_FILES['photos']) ? [
        'count' => is_array($_FILES['photos']['name']) ? count($_FILES['photos']['name']) : 1,
        'first_name' => is_array($_FILES['photos']['name']) ? ($_FILES['photos']['name'][0] ?? 'none') : $_FILES['photos']['name'],
        'first_error' => is_array($_FILES['photos']['error']) ? ($_FILES['photos']['error'][0] ?? 'N/A') : $_FILES['photos']['error'],
        'first_size' => is_array($_FILES['photos']['size']) ? ($_FILES['photos']['size'][0] ?? 0) : $_FILES['photos']['size']
    ] : 'No photos key in FILES',
    'uploads_dir_exists' => is_dir(__DIR__ . '/../uploads'),
    'uploads_dir_writable' => is_writable(__DIR__ . '/../uploads'),
];

// Try to create a test file
$testFile = __DIR__ . '/../uploads/test_write_' . time() . '.txt';
$writeTest = @file_put_contents($testFile, 'test');
$response['write_test'] = $writeTest !== false ? 'SUCCESS - wrote to ' . basename($testFile) : 'FAILED';
if ($writeTest !== false) {
    @unlink($testFile); // Clean up
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>

<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/session.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No image uploaded.']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload failed.']);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'Image must be 2MB or smaller.']);
    exit;
}

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowed[$mime])) {
    echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed.']);
    exit;
}

$uploadDir = __DIR__ . '/../assets/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid('img_', true) . '.' . $allowed[$mime];
$dest     = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'error' => 'Could not save image.']);
    exit;
}

$url = '/assets/uploads/' . $filename;
echo json_encode(['success' => true, 'url' => $url]);

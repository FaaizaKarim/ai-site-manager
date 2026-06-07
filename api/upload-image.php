<?php
declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: application/json');

require_once __DIR__ . '/../auth/session.php';

function uploadJsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function detectMimeType(string $path, string $originalName): ?string
{
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }
    }

    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($path);
        if (is_string($mime) && $mime !== '') {
            return $mime;
        }
    }

    $imageInfo = @getimagesize($path);
    if ($imageInfo !== false && !empty($imageInfo['mime'])) {
        return $imageInfo['mime'];
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $map = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ];

    return $map[$ext] ?? null;
}

if (empty($_SESSION['user_id'])) {
    uploadJsonResponse(['success' => false, 'error' => 'Not authenticated'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    uploadJsonResponse(['success' => false, 'error' => 'No image uploaded.'], 400);
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    uploadJsonResponse(['success' => false, 'error' => 'Upload failed (code ' . $file['error'] . ').'], 400);
}

if ($file['size'] > 2 * 1024 * 1024) {
    uploadJsonResponse(['success' => false, 'error' => 'Image must be 2MB or smaller.'], 400);
}

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

$mime = detectMimeType($file['tmp_name'], $file['name'] ?? '');

if ($mime === null || !isset($allowed[$mime])) {
    uploadJsonResponse(['success' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed.'], 400);
}

$uploadDir = __DIR__ . '/../assets/uploads/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
    uploadJsonResponse(['success' => false, 'error' => 'Upload folder could not be created.'], 500);
}

if (!is_writable($uploadDir)) {
    uploadJsonResponse(['success' => false, 'error' => 'Upload folder is not writable.'], 500);
}

$filename = uniqid('img_', true) . '.' . $allowed[$mime];
$dest     = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    uploadJsonResponse(['success' => false, 'error' => 'Could not save image.'], 500);
}

$url = '/assets/uploads/' . $filename;
uploadJsonResponse(['success' => true, 'url' => $url]);

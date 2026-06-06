<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
$user   = currentUser();
$siteId = (int)($_GET['id'] ?? 0);

if ($siteId > 0) {
    $db   = getDB();
    $stmt = $db->prepare('DELETE FROM sites WHERE id = ? AND user_id = ?');
    $stmt->execute([$siteId, $user['id']]);
}

header('Location: /ai-site-manager/pages/dashboard.php');
exit;

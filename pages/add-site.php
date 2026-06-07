<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
$user = currentUser();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $url  = trim($_POST['url']  ?? '');
    $desc = trim($_POST['desc'] ?? '');

    if ($name) {
        $db   = getDB();
        $stmt = $db->prepare('INSERT INTO sites (user_id, name, url, description) VALUES (?,?,?,?)');
        $stmt->execute([$user['id'], $name, $url, $desc]);
        header('Location: /pages/dashboard.php');
        exit;
    } else {
        $error = 'Site name is required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Site — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/../auth/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Add New Site</h1>
                <p class="page-subtitle">Add a website to manage</p>
            </div>
            <a href="/pages/dashboard.php" class="btn btn-ghost">← Back</a>
        </div>
        <div class="card" style="max-width:560px">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Site Name *</label>
                    <input type="text" name="name" placeholder="My Portfolio" required>
                </div>
                <div class="form-group">
                    <label>URL</label>
                    <input type="text" name="url" placeholder="https://example.com">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="desc" placeholder="What is this site about?"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Create Site</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>

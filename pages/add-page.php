<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
$user   = currentUser();
$siteId = (int)($_GET['site_id'] ?? 0);
$error  = '';

$db    = getDB();
$sStmt = $db->prepare('SELECT * FROM sites WHERE id = ? AND user_id = ?');
$sStmt->execute([$siteId, $user['id']]);
$site  = $sStmt->fetch();
if (!$site) { header('Location: /pages/dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']   ?? '');
    $slug    = trim($_POST['slug']    ?? '');
    $content = trim($_POST['content'] ?? '');
    $status  = $_POST['status'] === 'published' ? 'published' : 'draft';

    $slug = $slug ?: strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));

    if ($title) {
        $stmt = $db->prepare('INSERT INTO pages (site_id, title, slug, content, status) VALUES (?,?,?,?,?)');
        $stmt->execute([$siteId, $title, $slug, $content, $status]);
        header('Location: /pages/site.php?id=' . $siteId);
        exit;
    } else {
        $error = 'Page title is required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Page — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/../auth/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Add Page</h1>
                <p class="page-subtitle">To: <?= htmlspecialchars($site['name']) ?></p>
            </div>
            <a href="/pages/site.php?id=<?= $siteId ?>" class="btn btn-ghost">← Back</a>
        </div>
        <div class="card" style="max-width:640px">
            <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Page Title *</label>
                    <input type="text" name="title" placeholder="About Us" required>
                </div>
                <div class="form-group">
                    <label>Slug (auto-generated if blank)</label>
                    <input type="text" name="slug" placeholder="about-us">
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" style="min-height:200px"
                              placeholder="Write your page content here..."></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Create Page</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>

<?php
// pages/dashboard.php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
$user = currentUser();

$db    = getDB();
$stmt  = $db->prepare('SELECT s.*, COUNT(p.id) as page_count
                        FROM sites s
                        LEFT JOIN pages p ON p.site_id = s.id
                        WHERE s.user_id = ?
                        GROUP BY s.id
                        ORDER BY s.created_at DESC');
$stmt->execute([$user['id']]);
$sites = $stmt->fetchAll();

$statsStmt = $db->prepare('
    SELECT
        (SELECT COUNT(*) FROM sites WHERE user_id = ?) AS total_sites,
        (SELECT COUNT(*) FROM pages p JOIN sites s ON s.id = p.site_id WHERE s.user_id = ?) AS total_pages,
        (SELECT COUNT(*) FROM ai_logs al
            JOIN pages p ON p.id = al.page_id
            JOIN sites s ON s.id = p.site_id
            WHERE s.user_id = ?) AS total_ai_requests,
        (SELECT COUNT(*) FROM pages p JOIN sites s ON s.id = p.site_id
            WHERE s.user_id = ? AND p.status = "published") AS published_pages,
        (SELECT COUNT(*) FROM pages p JOIN sites s ON s.id = p.site_id
            WHERE s.user_id = ? AND p.status = "draft") AS draft_pages
');
$statsStmt->execute([$user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/ai-site-manager/assets/images/logo.png">
    <link rel="stylesheet" href="/ai-site-manager/assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/../auth/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">My Sites</h1>
                <p class="page-subtitle">Welcome back, <?= htmlspecialchars($user['name']) ?></p>
            </div>
            <a href="/ai-site-manager/pages/add-site.php" class="btn btn-primary">+ Add Site</a>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-value"><?= (int)$stats['total_sites'] ?></span>
                <span class="stat-label">Sites</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= (int)$stats['total_pages'] ?></span>
                <span class="stat-label">Pages</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= (int)$stats['total_ai_requests'] ?></span>
                <span class="stat-label">AI Requests</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= (int)$stats['published_pages'] ?> / <?= (int)$stats['draft_pages'] ?></span>
                <span class="stat-label">Published / Draft</span>
            </div>
        </div>

        <?php if (!empty($sites)): ?>
        <div class="search-bar-wrap">
            <input type="search" id="site-search" class="search-input"
                   placeholder="Search sites by name..." autocomplete="off">
        </div>
        <?php endif; ?>

        <?php if (empty($sites)): ?>
            <div class="card" style="text-align:center;padding:3rem">
                <p style="color:var(--text-muted);margin-bottom:1rem">No sites yet.</p>
                <a href="/ai-site-manager/pages/add-site.php" class="btn btn-primary">Add your first site</a>
            </div>
        <?php else: ?>
            <div class="card-grid" id="sites-grid">
            <?php foreach ($sites as $site): ?>
                <div class="card site-card" data-site-name="<?= htmlspecialchars(strtolower($site['name'])) ?>">
                    <div class="card-title"><?= htmlspecialchars($site['name']) ?></div>
                    <a href="<?= htmlspecialchars($site['url']) ?>" target="_blank" class="card-url">
                        <?= htmlspecialchars($site['url']) ?></a>
                    <p style="font-size:13px;color:var(--text-muted);margin-bottom:1rem">
                        <?= htmlspecialchars($site['description'] ?? '') ?></p>
                    <div class="card-meta"><?= $site['page_count'] ?> pages</div>
                    <div class="card-actions">
                        <a href="/ai-site-manager/pages/site.php?id=<?= $site['id'] ?>"
                           class="btn btn-primary" style="flex:1;justify-content:center">Manage</a>
                        <a href="/ai-site-manager/pages/delete-site.php?id=<?= $site['id'] ?>"
                           class="btn btn-danger"
                           onclick="return confirm('Delete this site?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <p id="no-sites-match" class="search-empty" style="display:none">No sites match your search.</p>
        <?php endif; ?>
    </main>
</div>
<?php if (!empty($sites)): ?>
<script>
document.getElementById('site-search').addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.site-card').forEach(function (card) {
        const name = card.getAttribute('data-site-name') || '';
        const show = !q || name.indexOf(q) !== -1;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('no-sites-match').style.display = visible ? 'none' : 'block';
});
</script>
<?php endif; ?>
</body>
</html>

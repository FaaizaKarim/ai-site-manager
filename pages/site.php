<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
$user   = currentUser();
$siteId = (int)($_GET['id'] ?? 0);

$db     = getDB();
$sStmt  = $db->prepare('SELECT * FROM sites WHERE id = ? AND user_id = ?');
$sStmt->execute([$siteId, $user['id']]);
$site   = $sStmt->fetch();
if (!$site) { header('Location: /ai-site-manager/pages/dashboard.php'); exit; }

$pStmt = $db->prepare('SELECT * FROM pages WHERE site_id = ? ORDER BY created_at DESC');
$pStmt->execute([$siteId]);
$pages = $pStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($site['name']) ?> — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/ai-site-manager/assets/images/logo.png">
    <link rel="stylesheet" href="/ai-site-manager/assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/../auth/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title"><?= htmlspecialchars($site['name']) ?></h1>
                <p class="page-subtitle"><?= htmlspecialchars($site['url']) ?></p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="/ai-site-manager/pages/add-page.php?site_id=<?= $siteId ?>"
                   class="btn btn-primary">+ Add Page</a>
                <a href="/ai-site-manager/pages/dashboard.php" class="btn btn-ghost">← Dashboard</a>
            </div>
        </div>

        <?php if (empty($pages)): ?>
            <div class="card" style="text-align:center;padding:2.5rem">
                <p style="color:var(--text-muted);margin-bottom:1rem">No pages yet.</p>
                <a href="/ai-site-manager/pages/add-page.php?site_id=<?= $siteId ?>"
                   class="btn btn-primary">Add first page</a>
            </div>
        <?php else: ?>
            <div class="search-bar-wrap">
                <input type="search" id="page-search" class="search-input"
                       placeholder="Search pages by title..." autocomplete="off">
            </div>
            <div class="card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Title</th><th>Slug</th><th>Status</th><th>Updated</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="pages-tbody">
                        <?php foreach ($pages as $page): ?>
                            <tr class="page-row" data-page-title="<?= htmlspecialchars(strtolower($page['title'])) ?>">
                                <td><?= htmlspecialchars($page['title']) ?></td>
                                <td style="color:var(--text-muted)">/<?= htmlspecialchars($page['slug']) ?></td>
                                <td><span class="badge badge-<?= $page['status'] ?>"><?= $page['status'] ?></span></td>
                                <td style="color:var(--text-muted);font-size:13px">
                                    <?= date('M j, Y', strtotime($page['updated_at'])) ?></td>
                                <td>
                                    <a href="/ai-site-manager/pages/editor.php?id=<?= $page['id'] ?>"
                                       class="btn btn-ghost" style="padding:5px 12px;font-size:13px">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <p id="no-pages-match" class="search-empty" style="display:none">No pages match your search.</p>
        <?php endif; ?>
    </main>
</div>
<?php if (!empty($pages)): ?>
<script>
document.getElementById('page-search').addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.page-row').forEach(function (row) {
        const title = row.getAttribute('data-page-title') || '';
        const show = !q || title.indexOf(q) !== -1;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('no-pages-match').style.display = visible ? 'none' : 'block';
});
</script>
<?php endif; ?>
</body>
</html>

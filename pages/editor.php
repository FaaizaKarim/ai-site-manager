<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
$user   = currentUser();
$pageId = (int)($_GET['id'] ?? 0);

$db    = getDB();
$pStmt = $db->prepare('SELECT p.*, s.name as site_name, s.user_id
                        FROM pages p JOIN sites s ON s.id = p.site_id
                        WHERE p.id = ?');
$pStmt->execute([$pageId]);
$page = $pStmt->fetch();

if (!$page || $page['user_id'] != $user['id']) {
    header('Location: /pages/dashboard.php'); exit;
}

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $stmt = $db->prepare('UPDATE pages SET title=?, slug=?, content=?, status=? WHERE id=?');
    $stmt->execute([
        trim($_POST['title']),
        trim($_POST['slug']),
        $_POST['content'],
        $_POST['status'] === 'published' ? 'published' : 'draft',
        $pageId
    ]);
    $saved = true;
    $page['title']   = trim($_POST['title']);
    $page['slug']    = trim($_POST['slug']);
    $page['content'] = $_POST['content'];
    $page['status']  = $_POST['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit: <?= htmlspecialchars($page['title']) ?> — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/../auth/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Editing: <?= htmlspecialchars($page['title']) ?></h1>
                <p class="page-subtitle"><?= htmlspecialchars($page['site_name']) ?></p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="/pages/preview.php?id=<?= $pageId ?>"
                   target="_blank" class="btn btn-ghost">👁 Preview</a>
                <a href="/pages/site.php?id=<?= $page['site_id'] ?>"
                   class="btn btn-ghost">← Back</a>
            </div>
        </div>

        <?php if ($saved): ?>
            <div class="alert alert-success" style="margin-bottom:1rem">✅ Page saved successfully.</div>
        <?php endif; ?>

        <form method="POST" id="editor-form">
        <div class="editor-wrap">
            <div class="editor-main">
                <div class="card">
                    <div class="form-group">
                        <label>Page Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($page['title']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" value="<?= htmlspecialchars($page['slug']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" id="content"><?= htmlspecialchars($page['content']) ?></textarea>
                    </div>

                    <div class="upload-section">
                        <label class="upload-label">📷 Upload Image</label>
                        <div class="upload-row">
                            <input type="file" id="image-upload" accept="image/jpeg,image/png,image/gif,image/webp">
                            <button type="button" class="btn btn-ghost" id="upload-btn">Upload</button>
                        </div>
                        <p id="upload-status" class="upload-status"></p>
                        <div id="uploaded-images" class="uploaded-images"></div>
                    </div>

                    <div style="display:flex;align-items:center;gap:1rem">
                        <div class="form-group" style="margin:0;flex:1">
                            <select name="status">
                                <option value="draft"     <?= $page['status']=== 'draft'     ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $page['status']=== 'published' ? 'selected' : '' ?>>Published</option>
                            </select>
                        </div>
                        <button type="submit" name="save" class="btn btn-primary">💾 Save Page</button>
                    </div>
                </div>
            </div>

            <div class="editor-sidebar">
                <div class="ai-panel">
                    <div class="ai-panel-title">🤖 Claude AI Assist</div>
                    <div class="ai-actions">
                        <button type="button" class="btn btn-ghost"
                                onclick="aiAction('improve')">✨ Improve Writing</button>
                        <button type="button" class="btn btn-ghost"
                                onclick="aiAction('rewrite')">🔄 Rewrite Content</button>
                        <button type="button" class="btn btn-ghost"
                                onclick="aiAction('shorten')">✂️ Make it Shorter</button>
                        <button type="button" class="btn btn-ghost"
                                onclick="aiAction('seo')">📈 Improve SEO</button>
                        <button type="button" class="btn btn-ghost"
                                onclick="aiAction('generate')">💡 Generate from Title</button>
                    </div>
                    <div class="spinner" id="spinner" style="margin-top:12px">⏳ Claude is thinking...</div>
                    <div class="ai-result" id="ai-result"></div>
                    <div id="apply-wrap" style="display:none;margin-top:10px">
                        <button type="button" class="btn btn-primary btn-full"
                                onclick="applyResult()">⬆️ Apply to Editor</button>
                    </div>
                </div>

                <div class="card" style="padding:1rem">
                    <div style="font-size:12px;color:var(--text-muted)">
                        <strong style="color:var(--text)">Page ID:</strong> <?= $page['id'] ?><br>
                        <strong style="color:var(--text)">Site:</strong> <?= htmlspecialchars($page['site_name']) ?><br>
                        <strong style="color:var(--text)">Last updated:</strong>
                        <?= date('M j, Y H:i', strtotime($page['updated_at'])) ?>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js"></script>
<script>
const PAGE_ID = <?= $pageId ?>;

tinymce.init({
    selector: '#content',
    height: 420,
    menubar: false,
    branding: false,
    promotion: false,
    plugins: 'lists link autolink code table image',
    toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link image | removeformat | code',
    block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3',
    skin: 'oxide-dark',
    content_css: 'dark',
    base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7.6.1',
    suffix: '.min',
    setup: function (editor) {
        editor.on('change', function () {
            editor.save();
        });
    }
});

document.getElementById('editor-form').addEventListener('submit', function () {
    if (typeof tinymce !== 'undefined') {
        tinymce.triggerSave();
    }
});

document.getElementById('upload-btn').addEventListener('click', async function () {
    const input = document.getElementById('image-upload');
    const status = document.getElementById('upload-status');
    const gallery = document.getElementById('uploaded-images');

    if (!input.files.length) {
        status.textContent = 'Please choose an image first.';
        status.className = 'upload-status upload-status-error';
        return;
    }

    const formData = new FormData();
    formData.append('image', input.files[0]);

    status.textContent = 'Uploading...';
    status.className = 'upload-status';
    this.disabled = true;

    try {
        const response = await fetch('/api/upload-image.php', {
            method: 'POST',
            body: formData
        });
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseErr) {
            status.textContent = 'Upload failed: server returned an invalid response.';
            status.className = 'upload-status upload-status-error';
            return;
        }

        if (data.success) {
            status.textContent = 'Uploaded successfully.';
            status.className = 'upload-status upload-status-ok';
            addImageToGallery(data.url, gallery);
            input.value = '';
        } else {
            status.textContent = data.error || 'Upload failed.';
            status.className = 'upload-status upload-status-error';
        }
    } catch (err) {
        status.textContent = 'Network error: ' + err.message;
        status.className = 'upload-status upload-status-error';
    } finally {
        this.disabled = false;
    }
});

function addImageToGallery(url, gallery) {
    const item = document.createElement('div');
    item.className = 'uploaded-image-item';
    item.innerHTML =
        '<img src="' + url + '" alt="Uploaded">' +
        '<input type="text" class="upload-url-input" value="' + url + '" readonly>' +
        '<button type="button" class="btn btn-ghost btn-copy-url">Copy URL</button>';
    gallery.prepend(item);
    item.querySelector('.btn-copy-url').addEventListener('click', function () {
        const urlInput = item.querySelector('.upload-url-input');
        urlInput.select();
        navigator.clipboard.writeText(urlInput.value).then(function () {
            item.querySelector('.btn-copy-url').textContent = 'Copied!';
            setTimeout(function () {
                item.querySelector('.btn-copy-url').textContent = 'Copy URL';
            }, 1500);
        });
    });
}
</script>
<script src="/assets/js/ai.js"></script>
</body>
</html>

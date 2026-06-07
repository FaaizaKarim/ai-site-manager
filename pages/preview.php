<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
$user   = currentUser();
$pageId = (int)($_GET['id'] ?? 0);

$db    = getDB();
$pStmt = $db->prepare('SELECT p.*, s.user_id
                        FROM pages p JOIN sites s ON s.id = p.site_id
                        WHERE p.id = ?');
$pStmt->execute([$pageId]);
$page = $pStmt->fetch();

if (!$page || $page['user_id'] != $user['id']) {
    http_response_code(404);
    echo 'Page not found.';
    exit;
}

function fixPreviewImagePaths(string $html): string {
    return preg_replace_callback(
        '/<img([^>]*)\ssrc=(["\'])([^"\']+)\2/i',
        function (array $m): string {
            $src = $m[3];

            if (preg_match('#^https?://#i', $src)) {
                return $m[0];
            }

            if (strpos($src, '/assets/uploads/') === 0) {
                return $m[0];
            }

            if (strpos($src, '/assets/uploads/') === 0) {
                $src = '/ai-site-manager' . $src;
            } elseif (preg_match('#(?:^|/)assets/uploads/#', $src)) {
                $filename = basename($src);
                $src = '/assets/uploads/' . $filename;
            }

            return '<img' . $m[1] . ' src="' . htmlspecialchars($src, ENT_QUOTES) . '"';
        },
        $html
    );
}

$content = fixPreviewImagePaths($page['content'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['title']) ?> — Preview</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            line-height: 1.7;
            color: #1a1a2e;
            background: #fafafa;
            padding: 2rem 1rem;
        }
        .preview-wrap {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            padding: 2.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
        }
        h1 {
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #111;
        }
        .preview-meta {
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .preview-content h1, .preview-content h2, .preview-content h3 {
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 1.25em 0 0.5em;
        }
        .preview-content p { margin-bottom: 1em; }
        .preview-content ul, .preview-content ol {
            margin: 0 0 1em 1.5em;
        }
        .preview-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .preview-content a { color: #4f46e5; }
        .preview-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .preview-badge-published { background: #d1fae5; color: #065f46; }
        .preview-badge-draft { background: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body>
    <div class="preview-wrap">
        <h1><?= htmlspecialchars($page['title']) ?></h1>
        <div class="preview-meta">
            <span class="preview-badge preview-badge-<?= htmlspecialchars($page['status']) ?>">
                <?= htmlspecialchars($page['status']) ?>
            </span>
            &nbsp;·&nbsp; /<?= htmlspecialchars($page['slug']) ?>
        </div>
        <div class="preview-content">
            <?= $content ?>
        </div>
    </div>
</body>
</html>

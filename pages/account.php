<?php
require_once __DIR__ . '/../auth/session.php';
requireLogin();
$user = currentUser();
$db   = getDB();

$nameSuccess = $passSuccess = '';
$nameError   = $passError   = '';

// ── Update Name ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_name'])) {
    $newName = trim($_POST['name'] ?? '');
    if (strlen($newName) < 2) {
        $nameError = 'Name must be at least 2 characters.';
    } else {
        $stmt = $db->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->execute([$newName, $user['id']]);
        $_SESSION['user_name'] = $newName;
        $nameSuccess = 'Name updated successfully.';
        $user['name'] = $newName;
    }
}

// ── Change Password ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();

    if (!password_verify($current, $row['password_hash'])) {
        $passError = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $passError = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $passError = 'Passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $user['id']]);
        $passSuccess = 'Password changed successfully.';
    }
}

// ── Get member since date ──────────────────────────────────
$stmt = $db->prepare('SELECT created_at FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$row  = $stmt->fetch();
$memberSince = date('F j, Y', strtotime($row['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/../auth/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">My Account</h1>
                <p class="page-subtitle">Member since <?= $memberSince ?></p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;max-width:900px">

            <!-- Profile Info -->
            <div class="card">
                <div style="font-size:13px;font-weight:600;color:var(--accent);
                            margin-bottom:1rem">👤 Profile Information</div>

                <?php if ($nameSuccess): ?>
                    <div class="alert alert-success"><?= $nameSuccess ?></div>
                <?php endif; ?>
                <?php if ($nameError): ?>
                    <div class="alert alert-error"><?= $nameError ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Email</label>
                    <input type="text" value="<?= htmlspecialchars($user['email']) ?>"
                           disabled style="opacity:0.6;cursor:not-allowed">
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label>Display Name</label>
                        <input type="text" name="name"
                               value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <button type="submit" name="update_name" class="btn btn-primary">
                        💾 Save Name
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div style="font-size:13px;font-weight:600;color:var(--accent);
                            margin-bottom:1rem">🔒 Change Password</div>

                <?php if ($passSuccess): ?>
                    <div class="alert alert-success"><?= $passSuccess ?></div>
                <?php endif; ?>
                <?php if ($passError): ?>
                    <div class="alert alert-error"><?= $passError ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password"
                               placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password"
                               placeholder="Min 8 characters" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password"
                               placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        🔑 Change Password
                    </button>
                </form>
            </div>

            <!-- Account Stats -->
            <div class="card" style="grid-column:span 2">
                <div style="font-size:13px;font-weight:600;color:var(--accent);
                            margin-bottom:1rem">📊 Your Stats</div>
                <?php
                $sites = $db->prepare('SELECT COUNT(*) FROM sites WHERE user_id = ?');
                $sites->execute([$user['id']]);
                $siteCount = $sites->fetchColumn();

                $pages = $db->prepare('SELECT COUNT(*) FROM pages p
                                       JOIN sites s ON s.id = p.site_id
                                       WHERE s.user_id = ?');
                $pages->execute([$user['id']]);
                $pageCount = $pages->fetchColumn();

                $ai = $db->prepare('SELECT COUNT(*) FROM ai_logs al
                                    JOIN pages p ON p.id = al.page_id
                                    JOIN sites s ON s.id = p.site_id
                                    WHERE s.user_id = ?');
                $ai->execute([$user['id']]);
                $aiCount = $ai->fetchColumn();
                ?>
                <div style="display:flex;gap:2rem">
                    <div style="text-align:center">
                        <div style="font-size:2rem;font-weight:700;color:var(--accent)">
                            <?= $siteCount ?>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted)">SITES</div>
                    </div>
                    <div style="text-align:center">
                        <div style="font-size:2rem;font-weight:700;color:var(--accent)">
                            <?= $pageCount ?>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted)">PAGES</div>
                    </div>
                    <div style="text-align:center">
                        <div style="font-size:2rem;font-weight:700;color:var(--accent)">
                            <?= $aiCount ?>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted)">AI REQUESTS</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
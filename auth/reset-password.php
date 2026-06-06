<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: /ai-site-manager/pages/dashboard.php');
    exit;
}

function parseResetToken(string $raw): string
{
    $token = strtolower(trim($raw));

    if (preg_match('/^[a-f0-9]{64}$/', $token)) {
        return $token;
    }

    $hex = preg_replace('/[^a-f0-9]/', '', $token);
    return strlen($hex) === 64 ? $hex : '';
}

function getValidReset(PDO $db, string $token): ?array
{
    if ($token === '') {
        return null;
    }

    $stmt = $db->prepare(
        'SELECT * FROM password_resets
         WHERE token = ? AND used = 0 AND expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

$db    = getDB();
$token = parseResetToken($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$reset = getValidReset($db, $token);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    $reset = getValidReset($db, $token);

    if (!$reset) {
        $error = 'This reset link is invalid or has expired.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd  = $db->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
        $upd->execute([$hash, $reset['email']]);

        $mark = $db->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
        $mark->execute([$reset['id']]);

        header('Location: /ai-site-manager/auth/login.php?reset=1');
        exit;
    }
} elseif ($token && !$reset) {
    $error = 'This reset link is invalid or has expired.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/ai-site-manager/assets/images/logo.png">
    <link rel="stylesheet" href="/ai-site-manager/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">⚡ AI Site Manager</div>
        <h1 class="auth-title">Reset password</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($reset): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="••••••••" minlength="8" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm" placeholder="••••••••" minlength="8" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Update Password</button>
            </form>
        <?php elseif (!$token): ?>
            <p style="color:var(--text-muted);font-size:14px;margin-bottom:1rem">No reset token provided.</p>
        <?php endif; ?>

        <p class="auth-hint">
            <a href="/ai-site-manager/auth/login.php" style="color:var(--accent)">← Back to sign in</a>
        </p>
    </div>
</body>
</html>

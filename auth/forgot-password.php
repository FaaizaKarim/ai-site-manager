<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mail.php';
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));

            $del = $db->prepare('DELETE FROM password_resets WHERE email = ?');
            $del->execute([$email]);

            $ins = $db->prepare(
                'INSERT INTO password_resets (email, token, expires_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))'
            );
            $ins->execute([$email, $token]);

            $baseUrl = rtrim(env('APP_URL', ''), '/');
            if ($baseUrl === '' && !empty($_SERVER['HTTP_HOST'])) {
                $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
            }
            if ($baseUrl === '') {
                $baseUrl = 'http://localhost';
            }
            $resetLink = $baseUrl . '/auth/reset-password.php?token=' . urlencode($token);

            if (!sendResetEmail($email, $resetLink)) {
                error_log('Failed to send password reset email to: ' . $email);
            }
        }

        $message = 'If this email exists, a reset link has been sent.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/assets/images/logo.png"
                 alt="AI Site Manager"
                 style="width:32px;height:32px;object-fit:contain;vertical-align:middle;margin-right:8px;">
            AI Site Manager
        </div>
        <h1 class="auth-title">Forgot password</h1>

        <?php if ($message): ?>
            <div class="alert <?= str_contains($message, 'valid') ? 'alert-error' : 'alert-success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Send Reset Link</button>
        </form>
        <p class="auth-hint">
            <a href="/auth/login.php" style="color:var(--accent)">← Back to sign in</a>
        </p>
    </div>
</body>
</html>

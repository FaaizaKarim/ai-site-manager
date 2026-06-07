<?php
// auth/login.php
require_once __DIR__ . '/../config/db.php';
session_start();

// Already logged in? Send to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
    exit;
}

$error   = '';
$success = '';

if (!empty($_GET['registered'])) {
    $success = 'Account created successfully. Please sign in.';
}

if (!empty($_GET['reset'])) {
    $success = 'Your password has been reset. Please sign in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email && $password) {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, password_hash, name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: /pages/dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill in both fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
    <img src="/assets/images/logo.png"
         alt="logo"
         style="width:32px;height:32px;object-fit:contain;vertical-align:middle;margin-right:8px;">
    AI Site Manager
</div>
        <h1 class="auth-title">Welcome back</h1>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="admin@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Sign In</button>
        </form>
        <p class="auth-hint" style="margin-top:0.75rem">
            <a href="/auth/forgot-password.php" style="color:var(--accent)">Forgot password?</a>
        </p>
        <p class="auth-hint">
            Don't have an account?
            <a href="/auth/register.php" style="color:var(--accent)">Create account</a>
        </p>
    </div>
</body>
</html>

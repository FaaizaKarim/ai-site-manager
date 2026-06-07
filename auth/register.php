<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $db->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $ins->execute([$name, $email, $hash]);
            header('Location: /auth/login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — AI Site Manager</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">⚡ AI Site Manager</div>
        <h1 class="auth-title">Create account</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Your name"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" minlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
        </form>
        <p class="auth-hint">
            Already have an account?
            <a href="/auth/login.php" style="color:var(--accent)">Sign in</a>
        </p>
    </div>
</body>
</html>

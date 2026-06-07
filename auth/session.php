<?php
// auth/session.php
// Include this at the top of every protected page.

require_once __DIR__ . '/../config/db.php';

session_start();

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /auth/login.php');
        exit;
    }
}

function currentUser(): array {
    if (empty($_SESSION['user_id'])) return [];
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, email, name FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: [];
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

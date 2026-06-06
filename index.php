<?php
// index.php — redirects to login or dashboard
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /ai-site-manager/pages/dashboard.php');
} else {
    header('Location: /ai-site-manager/auth/login.php');
}
exit;

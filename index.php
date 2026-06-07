<?php
// index.php — redirects to login or dashboard
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /pages/dashboard.php');
} else {
    header('Location: /auth/login.php');
}
exit;

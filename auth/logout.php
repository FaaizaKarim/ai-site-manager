<?php
// auth/logout.php
session_start();
session_destroy();
header('Location: /ai-site-manager/auth/login.php');
exit;

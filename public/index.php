<?php
require __DIR__ . '/../app/bootstrap.php';
header('Location: ' . (empty($_SESSION['staff_id']) ? 'login.php' : 'dashboard.php'));
exit;

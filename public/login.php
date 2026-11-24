<?php
require __DIR__ . '/../app/bootstrap.php';
if (!empty($_SESSION['staff_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = $pdo->prepare('SELECT id, name, password_hash FROM staff_users WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([trim((string)($_POST['email'] ?? ''))]);
    $user = $stmt->fetch();
    if ($user && password_verify((string)($_POST['password'] ?? ''), $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['staff_id'] = (int)$user['id'];
        $_SESSION['staff_name'] = $user['name'];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Invalid email or password.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Staff Login | CareDesk</title><link rel="stylesheet" href="assets/style.css"></head><body class="login"><form class="login-card" method="post"><h1>CareDesk</h1><p>Hospital administration demo</p><?php if ($error): ?><p class="alert error"><?= e($error) ?></p><?php endif; ?><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Email<input type="email" name="email" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button>Sign in</button><small>Run setup.php once to create a local demo account.</small></form></body></html>

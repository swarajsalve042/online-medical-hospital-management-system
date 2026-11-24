<?php
require __DIR__ . '/../app/bootstrap.php';
$count = (int)$pdo->query('SELECT COUNT(*) FROM staff_users')->fetchColumn();
if ($count > 0) {
    exit('Setup is already complete. Delete public/setup.php and sign in.');
}
$demoEmail = 'admin@example.test';
$demoPassword = bin2hex(random_bytes(6));
$stmt = $pdo->prepare('INSERT INTO staff_users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$stmt->execute(['Demo Administrator', $demoEmail, password_hash($demoPassword, PASSWORD_DEFAULT), 'admin']);
echo '<h1>Demo account created</h1><p>Email: ' . e($demoEmail) . '</p><p>Password: <strong>' . e($demoPassword) . '</strong></p><p>Save these credentials, delete public/setup.php, then sign in.</p>';

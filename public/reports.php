<?php
require __DIR__.'/../app/bootstrap.php';require_login();page_start('Reports');
$paid=(float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM invoices WHERE payment_status='Paid'")->fetchColumn();
$pending=(float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM invoices WHERE payment_status='Pending'")->fetchColumn();
$counts=['Patients'=>(int)$pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn(),'Doctors'=>(int)$pdo->query('SELECT COUNT(*) FROM doctors')->fetchColumn(),'Appointments'=>(int)$pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn(),'Medical notes'=>(int)$pdo->query('SELECT COUNT(*) FROM medical_notes')->fetchColumn()];
echo '<section class="stats">';foreach($counts as $k=>$v)echo '<article class="stat"><span>'.e($k).'</span><strong>'.$v.'</strong></article>';echo '<article class="stat"><span>Paid invoices total</span><strong>₹'.number_format($paid,2).'</strong></article><article class="stat"><span>Pending invoices total</span><strong>₹'.number_format($pending,2).'</strong></article></section><p class="muted">Summary calculated from the connected demo database.</p>';page_end();

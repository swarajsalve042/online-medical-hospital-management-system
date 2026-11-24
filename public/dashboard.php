<?php
require __DIR__ . '/../app/bootstrap.php'; require_login();
page_start('Dashboard'); show_flash();
$stats = [
 'Patients' => (int)$pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn(),
 'Doctors' => (int)$pdo->query('SELECT COUNT(*) FROM doctors')->fetchColumn(),
 'Appointments' => (int)$pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn(),
 'Outstanding invoices' => (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE payment_status <> 'Paid'")->fetchColumn(),
];
echo '<section class="stats">';
foreach ($stats as $label=>$value) echo '<article class="stat"><span>' . e($label) . '</span><strong>' . $value . '</strong></article>';
echo '</section><section class="panel"><h2>Recent appointments</h2>';
$rows = $pdo->query("SELECT a.appointment_date, a.status, p.full_name patient, d.full_name doctor FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN doctors d ON d.id=a.doctor_id ORDER BY a.appointment_date DESC LIMIT 8")->fetchAll();
if (!$rows) echo '<p>No appointments recorded yet.</p>';
else { echo '<div class="table-wrap"><table><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Status</th></tr>'; foreach($rows as $r) echo '<tr><td>'.e($r['appointment_date']).'</td><td>'.e($r['patient']).'</td><td>'.e($r['doctor']).'</td><td>'.e($r['status']).'</td></tr>'; echo '</table></div>'; }
echo '</section>'; page_end();

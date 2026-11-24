<?php
require __DIR__ . '/../app/bootstrap.php'; require_login();
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 $name=trim((string)($_POST['full_name']??'')); $phone=trim((string)($_POST['phone']??'')); $dob=$_POST['date_of_birth']?:null; $gender=$_POST['gender']??'Prefer not to say';
 if ($name==='') flash('Patient name is required.','error');
 else { $s=$pdo->prepare('INSERT INTO patients(full_name,phone,date_of_birth,gender) VALUES(?,?,?,?)'); $s->execute([$name,$phone?:null,$dob,$gender]); flash('Patient record added.'); }
 header('Location: patients.php'); exit;
}
page_start('Patients'); show_flash();
echo '<section class="panel"><h2>Register patient</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><label>Full name<input name="full_name" required maxlength="120"></label><label>Phone<input name="phone" maxlength="30"></label><label>Date of birth<input type="date" name="date_of_birth"></label><label>Gender<select name="gender"><option>Prefer not to say</option><option>Female</option><option>Male</option><option>Other</option></select></label><button>Add patient</button></form></section>';
$rows=$pdo->query('SELECT id,full_name,phone,date_of_birth,gender,created_at FROM patients ORDER BY id DESC')->fetchAll();
echo '<section class="panel"><h2>Patient records</h2><div class="table-wrap"><table><tr><th>ID</th><th>Name</th><th>Phone</th><th>Date of birth</th><th>Gender</th></tr>';
foreach($rows as $r) echo '<tr><td>'.(int)$r['id'].'</td><td>'.e($r['full_name']).'</td><td>'.e($r['phone']).'</td><td>'.e($r['date_of_birth']).'</td><td>'.e($r['gender']).'</td></tr>';
echo '</table></div></section>'; page_end();

<?php
require __DIR__.'/../app/bootstrap.php'; require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 if(isset($_POST['mark_paid'])){$id=(int)$_POST['mark_paid'];$s=$pdo->prepare("UPDATE invoices SET payment_status='Paid' WHERE id=? AND payment_status='Pending'");$s->execute([$id]);flash($s->rowCount()?'Invoice marked paid.':'Invoice was not changed.');}
 else{$p=(int)($_POST['patient_id']??0);$desc=trim((string)($_POST['description']??''));$amount=filter_var($_POST['amount']??null,FILTER_VALIDATE_FLOAT);if(!$p||$desc===''||$amount===false||$amount<=0)flash('Enter a patient, description, and positive amount.','error');else{$s=$pdo->prepare("INSERT INTO invoices(patient_id,description,amount,payment_status) VALUES(?,?,?,'Pending')");$s->execute([$p,$desc,$amount]);flash('Invoice created.');}}
 header('Location: billing.php');exit;
}
page_start('Billing');show_flash();$patients=$pdo->query('SELECT id,full_name FROM patients ORDER BY full_name')->fetchAll();
echo '<section class="panel"><h2>Create invoice</h2>';
if(!$patients)echo '<p>Add a patient before creating an invoice.</p>';else{echo '<form method="post" class="form-grid"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><label>Patient<select name="patient_id">';foreach($patients as $p)echo '<option value="'.(int)$p['id'].'">'.e($p['full_name']).'</option>';echo '</select></label><label>Service / description<input name="description" required maxlength="255"></label><label>Amount (INR)<input type="number" name="amount" min="0.01" step="0.01" required></label><button>Create invoice</button></form>';}echo '</section>';
$rows=$pdo->query('SELECT i.*,p.full_name patient FROM invoices i JOIN patients p ON p.id=i.patient_id ORDER BY i.id DESC')->fetchAll();
echo '<section class="panel"><h2>Invoices</h2><div class="table-wrap"><table><tr><th>Invoice</th><th>Patient</th><th>Description</th><th>Amount</th><th>Status</th><th>Action</th></tr>';
foreach($rows as $r){echo '<tr><td>#'.(int)$r['id'].'</td><td>'.e($r['patient']).'</td><td>'.e($r['description']).'</td><td>₹'.number_format((float)$r['amount'],2).'</td><td>'.e($r['payment_status']).'</td><td>';if($r['payment_status']==='Pending')echo '<form method="post"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><button name="mark_paid" value="'.(int)$r['id'].'">Mark paid</button></form>';else echo '—';echo '</td></tr>';}
echo '</table></div></section>';page_end();

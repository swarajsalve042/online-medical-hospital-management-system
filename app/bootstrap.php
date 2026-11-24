<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly'=>true,'secure'=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off'),'samesite'=>'Lax']);
    session_start();
}
$configPath=__DIR__.'/../config/database.php';
if(!is_file($configPath)){http_response_code(500);exit('Database config missing. Copy config/database.example.php to config/database.php.');}
$db=require $configPath;
$dsn=sprintf('mysql:host=%s;dbname=%s;charset=%s',$db['host'],$db['database'],$db['charset']??'utf8mb4');
try{$pdo=new PDO($dsn,$db['username'],$db['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}
catch(PDOException $e){http_response_code(500);exit('Database connection failed. Check local database configuration.');}
function e(mixed $v):string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function csrf_token():string{if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function verify_csrf():void{$s=$_POST['csrf']??'';if(!is_string($s)||!hash_equals(csrf_token(),$s)){http_response_code(419);exit('Invalid form token. Refresh and try again.');}}
function require_login():void{if(empty($_SESSION['staff_id'])){header('Location: login.php');exit;}}
function page_start(string $title):void{$name=e($_SESSION['staff_name']??'Staff');echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($title).' | CareDesk</title><link rel="stylesheet" href="assets/style.css"></head><body><header class="top"><a class="brand" href="dashboard.php">CareDesk</a><span>Hospital administration demo</span><span class="staff">Signed in: '.$name.'</span><a href="logout.php">Log out</a></header><nav><a href="dashboard.php">Dashboard</a><a href="patients.php">Patients</a><a href="doctors.php">Doctors</a><a href="appointments.php">Appointments</a><a href="medical-notes.php">Medical Notes</a><a href="billing.php">Billing</a><a href="reports.php">Reports</a></nav><main><h1>'.e($title).'</h1>';}
function page_end():void{echo '</main><footer>CareDesk demo · Fictional data only · Not for clinical decision-making</footer></body></html>';}
function flash(string $m,string $t='success'):void{$_SESSION['flash']=['message'=>$m,'type'=>$t];}
function show_flash():void{if(!empty($_SESSION['flash'])){$f=$_SESSION['flash'];echo '<p class="alert '.e($f['type']).'">'.e($f['message']).'</p>';unset($_SESSION['flash']);}}

<?php
session_start();
require __DIR__.'/../src/db.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit; }
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'] ?? '')) die('Invalid CSRF');
$lid = (int)($_POST['location_id'] ?? 0);
$code = trim($_POST['code'] ?? '');
$name = trim($_POST['name'] ?? '');
$ip = trim($_POST['ip_address'] ?? '');
if ($lid<=0 || $code==='' || $name==='') { header('Location: /f1/public/location_view.php?id='.$lid); exit; }
$stmt = $pdo->prepare('INSERT INTO devices (location_id,code,name,ip_address) VALUES (?,?,?,?)');
$stmt->execute([$lid,$code,$name,$ip]);
header('Location: /f1/public/location_view.php?id='.$lid);

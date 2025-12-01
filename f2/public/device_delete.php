<?php
session_start();
require __DIR__.'/../src/db.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit; }
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'] ?? '')) die('Invalid CSRF');
$id = (int)($_POST['id'] ?? 0);
if ($id<=0) { header('Location: /f1/public/index.php'); exit; }
$st = $pdo->prepare('SELECT location_id FROM devices WHERE id=?'); $st->execute([$id]); $r = $st->fetch();
$lid = $r ? (int)$r['location_id'] : 0;
$pdo->prepare('DELETE FROM devices WHERE id=?')->execute([$id]);
header('Location: /f1/public/location_view.php?id='.$lid);

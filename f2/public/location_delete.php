<?php
session_start();
require __DIR__.'/../src/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'] ?? '')) die('Invalid CSRF');
$id = (int)($_POST['id'] ?? 0);
if ($id<=0) { header('Location: /f1/public/index.php'); exit; }
$pdo->prepare('DELETE FROM locations WHERE id=?')->execute([$id]);
header('Location: /f1/public/index.php');

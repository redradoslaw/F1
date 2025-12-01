<?php
session_start();
require __DIR__.'/../src/db.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit; }
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'] ?? '')) die('Invalid CSRF');
$lid = (int)($_POST['location_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if ($lid>0 && $content!=='') {
  $stmt = $pdo->prepare('INSERT INTO notes (location_id,content) VALUES (?,?)');
  $stmt->execute([$lid,$content]);
}
header('Location: /f1/public/location_view.php?id='.$lid);

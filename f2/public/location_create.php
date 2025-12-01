<?php
session_start();
require __DIR__.'/../src/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'] ?? '')) { die('Invalid CSRF'); }
  $name = trim($_POST['name'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $phones = [trim($_POST['phone1'] ?? ''), trim($_POST['phone2'] ?? ''), trim($_POST['phone3'] ?? '')];
  $ip = trim($_POST['ip_address'] ?? '');
  $gmap = trim($_POST['google_maps_link'] ?? '');

  $errors = [];
  if ($name === '') $errors[] = 'Nazwa jest wymagana';
  if ($address === '') $errors[] = 'Adres jest wymagany';
  if ($ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP)) $errors[] = 'Nieprawidlowy IP';

  if (empty($errors)) {
    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare('INSERT INTO locations (name,address,ip_address,google_maps_link) VALUES (?,?,?,?)');
      $stmt->execute([$name,$address,$ip,$gmap]);
      $lid = $pdo->lastInsertId();
      $pstmt = $pdo->prepare('INSERT INTO phones (location_id,phone_index,phone_number) VALUES (?,?,?)');
      foreach ($phones as $i => $p) {
        if ($p !== '') $pstmt->execute([$lid, $i+1, $p]);
      }
      $pdo->commit();
      header('Location: /f1/public/location_view.php?id='.$lid);
      exit;
    } catch (Exception $e) {
      $pdo->rollBack();
      $errors[] = 'Błąd zapisu: '. $e->getMessage();
    }
  }
}
require __DIR__.'/../templates/header.php';
?>
<div class="card">
  <div class="card-body">
    <h4>Dodaj lokalizację</h4>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><?php echo e(implode('<br>', $errors)); ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
      <div class="mb-3">
        <label class="form-label">Nazwa</label>
        <input class="form-control" name="name" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Adres</label>
        <input class="form-control" name="address" required>
      </div>
      <div class="mb-3 row">
        <div class="col"><label class="form-label">Telefon 1</label><input class="form-control" name="phone1" type="tel"></div>
        <div class="col"><label class="form-label">Telefon 2</label><input class="form-control" name="phone2" type="tel"></div>
        <div class="col"><label class="form-label">Telefon 3</label><input class="form-control" name="phone3" type="tel"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">IP</label>
        <input class="form-control" name="ip_address">
      </div>
      <div class="mb-3">
        <label class="form-label">Google Maps link</label>
        <input class="form-control" name="google_maps_link">
      </div>
      <button class="btn btn-primary" type="submit">Zapisz</button>
      <a class="btn btn-secondary" href="/f1/public/index.php">Anuluj</a>
    </form>
  </div>
</div>
<?php require __DIR__.'/../templates/footer.php'; ?>

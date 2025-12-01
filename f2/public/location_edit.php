<?php
session_start();
require __DIR__.'/../src/db.php';
$id = (int)($_GET['id'] ?? 0);
if ($id<=0) { header('Location: /f1/public/index.php'); exit; }
$st = $pdo->prepare('SELECT * FROM locations WHERE id=?'); $st->execute([$id]); $loc = $st->fetch();
if (!$loc) { header('Location: /f1/public/index.php'); exit; }
$ps = $pdo->prepare('SELECT phone_index, phone_number FROM phones WHERE location_id=?'); $ps->execute([$id]); $phones=[]; while($r=$ps->fetch()) $phones[$r['phone_index']]=$r['phone_number'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf'] ?? '')) die('Invalid CSRF');
  $name = trim($_POST['name']); $address = trim($_POST['address']);
  $ip = trim($_POST['ip_address']); $gmap = trim($_POST['google_maps_link']);
  $phs = [trim($_POST['phone1'] ?? ''), trim($_POST['phone2'] ?? ''), trim($_POST['phone3'] ?? '')];
  $errors=[]; if ($name==='') $errors[]='Nazwa wymagana'; if ($address==='') $errors[]='Adres wymagany';
  if (empty($errors)) {
    $pdo->beginTransaction();
    try {
      $upd = $pdo->prepare('UPDATE locations SET name=?,address=?,ip_address=?,google_maps_link=? WHERE id=?');
      $upd->execute([$name,$address,$ip,$gmap,$id]);
      $pdo->prepare('DELETE FROM phones WHERE location_id=?')->execute([$id]);
      $pin = $pdo->prepare('INSERT INTO phones (location_id,phone_index,phone_number) VALUES (?,?,?)');
      foreach ($phs as $i=>$p) if ($p!=='') $pin->execute([$id,$i+1,$p]);
      $pdo->commit();
      header('Location: /f1/public/location_view.php?id='.$id);
      exit;
    } catch (Exception $e) { $pdo->rollBack(); $errors[] = $e->getMessage(); }
  }
}
require __DIR__.'/../templates/header.php';
?>
<div class="card">
  <div class="card-body">
    <h4>Edytuj lokalizację</h4>
    <?php if (!empty($errors)) echo '<div class="alert alert-danger">'.e(implode('<br>',$errors)).'</div>'; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
      <div class="mb-3"><label class="form-label">Nazwa</label><input class="form-control" name="name" value="<?php echo e($loc['name']); ?>" required></div>
      <div class="mb-3"><label class="form-label">Adres</label><input class="form-control" name="address" value="<?php echo e($loc['address']); ?>" required></div>
      <div class="mb-3 row">
        <div class="col"><label class="form-label">Telefon 1</label><input class="form-control" name="phone1" value="<?php echo e($phones[1] ?? ''); ?>"></div>
        <div class="col"><label class="form-label">Telefon 2</label><input class="form-control" name="phone2" value="<?php echo e($phones[2] ?? ''); ?>"></div>
        <div class="col"><label class="form-label">Telefon 3</label><input class="form-control" name="phone3" value="<?php echo e($phones[3] ?? ''); ?>"></div>
      </div>
      <div class="mb-3"><label class="form-label">IP</label><input class="form-control" name="ip_address" value="<?php echo e($loc['ip_address']); ?>"></div>
      <div class="mb-3"><label class="form-label">Google Maps link</label><input class="form-control" name="google_maps_link" value="<?php echo e($loc['google_maps_link']); ?>"></div>
      <button class="btn btn-primary" type="submit">Zapisz</button>
      <a class="btn btn-secondary" href="/f1/public/location_view.php?id=<?php echo $id; ?>">Anuluj</a>
    </form>
  </div>
</div>
<?php require __DIR__.'/../templates/footer.php'; ?>

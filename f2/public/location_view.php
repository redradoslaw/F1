<?php
session_start();
require __DIR__.'/../src/db.php';
require __DIR__.'/../templates/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { 
    echo '<p>Nie znaleziono</p>'; 
    require __DIR__.'/../templates/footer.php'; 
    exit; 
}

// Pobranie danych lokalizacji
$st = $pdo->prepare('SELECT * FROM locations WHERE id=?'); 
$st->execute([$id]); 
$loc = $st->fetch();
if (!$loc) { 
    echo '<p>Nie znaleziono</p>'; 
    require __DIR__.'/../templates/footer.php'; 
    exit; 
}

// Telefony
$ps = $pdo->prepare('SELECT phone_index, phone_number FROM phones WHERE location_id=?'); 
$ps->execute([$id]); 
$phones = [];
while ($r = $ps->fetch()) $phones[$r['phone_index']] = $r['phone_number'];

// Notatki
$ns = $pdo->prepare('SELECT * FROM notes WHERE location_id=? ORDER BY created_at DESC'); 
$ns->execute([$id]); 
$notes = $ns->fetchAll();

// Sprzęty
$ds = $pdo->prepare('SELECT * FROM devices WHERE location_id=? ORDER BY name'); 
$ds->execute([$id]); 
$devices = $ds->fetchAll();

// Offsety IP dla sprzętów
$ipOffsets = [
    "Kasa" => 1,
    "SSBT1" => 5,
    "SSBT2" => 6,
    "SSBT3" => 7,
    "SSBT4" => 8,
    "Drukarka Oferty" => 10,
    "Oferta elektroniczna" => 11,
    "Oferta elektroniczna2" => 12,
    "Tablet1" => 13,
    "Tablet2" => 14,
    "Tablet3" => 15,
    "CMS" => 16,
    "Live1" => 17,
    "Live2" => 18,
    "IVG" => 21,
    "Tablet4" => 24
];

// Funkcja generowania IP na podstawie IP lokalizacji + offset
function getDeviceIP($deviceName, $baseIP, $ipOffsets) {
    if (!isset($ipOffsets[$deviceName])) return '';
    $parts = explode('.', $baseIP);
    $parts[3] = intval($parts[3]) + $ipOffsets[$deviceName];
    return implode('.', $parts);
}
?>

<div class="card mb-3">
  <div class="card-body">
    <h3 class="card-title"><?php echo e($loc['name']); ?></h3>
    <p><?php echo e($loc['address']); ?></p>
    <p>IP: <?php echo e($loc['ip_address'] ?: '—'); ?></p>
    <p>Google: <a href="<?php echo e($loc['google_maps_link']); ?>" target="_blank">Nawiguj</a></p>
    <p>Telefony: <?php for ($i=1;$i<=3;$i++) echo '<span class="badge bg-secondary me-1">'.e($phones[$i] ?? '—').'</span>'; ?></p>
    <a class="btn btn-sm btn-primary" href="/f1/public/location_edit.php?id=<?php echo $id; ?>">Edytuj lokalizację</a>
    <form class="d-inline" method="post" action="/f1/public/location_delete.php" onsubmit="return confirm('Usunąć lokalizację?');">
      <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <button class="btn btn-sm btn-danger" type="submit">Usuń lokalizację</button>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Notatki</h5>
        <form method="post" action="/f1/public/note_create.php">
          <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
          <input type="hidden" name="location_id" value="<?php echo $id; ?>">
          <div class="mb-3"><textarea class="form-control" name="content" required></textarea></div>
          <button class="btn btn-sm btn-success" type="submit">Dodaj notatkę</button>
        </form>
        <ul class="list-group list-group-flush mt-3">
          <?php foreach ($notes as $n): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div><?php echo e($n['content']); ?><br><small><?php echo e($n['created_at']); ?></small></div>
              <form method="post" action="/f1/public/note_delete.php" onsubmit="return confirm('Usunąć notatkę?');">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="id" value="<?php echo $n['id']; ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Usuń</button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-md-6">

  <!--  Lista sprzętów -->
  <div class="card mb-3">
    <div class="card-body">
      <h5>Lista</h5>
      <table class="table table-sm">
        <thead>
          <tr><th>Kod</th><th>Nazwa</th><th>IP</th><th>Akcje</th></tr>
        </thead>
        <tbody>
          <?php foreach ($devices as $d): ?>
          <tr>
            <td><?php echo e($d['code']); ?></td>
            <td><?php echo e($d['name']); ?></td>
            <td><?php echo e($d['ip_address'] ?: '—'); ?></td>
            <td class="d-flex gap-1">
              <a href="/f1/public/device_edit.php?id=<?php echo $d['id']; ?>" class="text-primary action-icon icon-edit" title="Edytuj">
                <i class="bi bi-pencil"></i>
              </a>
              <form method="post" action="/f1/public/device_delete.php"
                    onsubmit="return confirm('Usunąć sprzęt?');"
                    class="action-icon icon-del">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                <button type="submit" class="text-primary action-icon" title="Usuń">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!--  Formularz dodawania nowego sprzętu -->
  <div class="card mb-3">
  <div class="card-body">
    <h5>Dodaj sprzęt</h5>
    <form method="post" action="/f1/public/device_create.php">
      <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="location_id" value="<?php echo $id; ?>">

      <div class="mb-2">
        <input class="form-control" name="code" placeholder="Kod" required>
      </div>

      <div class="mb-2">
        <select class="form-control" name="name" id="name" required onchange="updateIP()">
          <option value="">-- Wybierz nazwę sprzętu --</option>
          <?php foreach(array_keys($ipOffsets) as $devName): ?>
            <option value="<?php echo $devName; ?>"><?php echo $devName; ?></option>
          <?php endforeach; ?>
          <!-- Dodatkowe sprzęty bez IP -->
          <option value="TV">TV</option>
          <option value="Monitor">Monitor</option>
          <option value="Skaner kuponów">Skaner kuponów</option>
          <option value="Skaner dokumentów">Skaner dokumentów</option>
          <option value="Kuponówka">Kuponówka</option>
          <option value="Alarm">Alarm</option>
          <option value="Klimatyzacja">Klimatyzacja</option>
          <option value="Router POE">Router POE</option>
        </select>
      </div>

      <div class="mb-2">
        <input class="form-control" name="ip_address" id="ip_address" placeholder="IP" readonly>
      </div>

      <button class="btn btn-sm btn-primary" type="submit">Dodaj sprzęt</button>
    </form>
  </div>
</div>

<script>
const baseIP = '<?php echo $loc['ip_address'] ?? ''; ?>';
const ipOffsets = <?php echo json_encode($ipOffsets); ?>;

function updateIP() {
  const name = document.getElementById('name').value;
  if(ipOffsets[name] !== undefined){
    const parts = baseIP.split('.').map(Number);
    parts[3] += ipOffsets[name];
    document.getElementById('ip_address').value = parts.join('.');
  } else {
    document.getElementById('ip_address').value = '';
  }
}
</script>

</div>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>

<?php
require __DIR__.'/../src/db.php';
require __DIR__.'/../templates/header.php';

$sql = "SELECT l.*, 
               GROUP_CONCAT(CONCAT(p.phone_index,':',p.phone_number) ORDER BY p.phone_index SEPARATOR ';') AS phones,
               GROUP_CONCAT(n.content ORDER BY n.id SEPARATOR ' | ') AS notes_summary
        FROM locations l
        LEFT JOIN phones p ON p.location_id = l.id
        LEFT JOIN notes n ON n.location_id = l.id
        GROUP BY l.id
        ORDER BY l.name";
$stmt = $pdo->query($sql);
$locations = $stmt->fetchAll();



?>
<div class="row row-cols-1 row-cols-md-3 g-4">
  <?php foreach ($locations as $loc):
    $phones = [];
    if (!empty($loc['phones'])) {
      foreach (explode(';', $loc['phones']) as $pp) {
        [$idx,$num] = explode(':', $pp, 2);
        $phones[(int)$idx] = $num;
      }
    }
	$notes = [];
    if (!empty($loc['notes_summary'])) {
        $notes = explode(' | ', $loc['notes_summary']);
	}
  ?>
  <div class="col">
    <div class="card h-100 card-sm">
      <div class="card-body">
        <h5 class="card-title"><a href="/f1/public/location_view.php?id=<?php echo $loc['id']; ?>"><?php echo e($loc['name']); ?></a></h5>
        <p class="card-text"><?php echo e($loc['address']); ?></p>
        <p class="card-text">Telefony:
          <?php for ($i=1;$i<=3;$i++): ?>
            <span class="badge bg-secondary"><?php echo e($phones[$i] ?? '—'); ?></span>
          <?php endfor; ?>
        </p>
        <p class="card-text">IP: <?php echo e($loc['ip_address'] ?: '—'); ?></p>
		<p class="card-text">Do zrobienia:</br> <?php echo e($loc['notes_summary'] ?: '—'); ?></p>
        <p><a href="<?php echo e($loc['google_maps_link']); ?>" target="_blank" rel="noopener noreferrer">Pokaż w Google</a></p>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a class="btn btn-sm btn-primary" href="/f1/public/location_edit.php?id=<?php echo $loc['id']; ?>">Edytuj</a>
        <form method="post" action="/f1/public/location_delete.php" onsubmit="return confirm('Na pewno usunąć lokalizację?');">
          <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
          <input type="hidden" name="id" value="<?php echo $loc['id']; ?>">
          <button class="btn btn-sm btn-danger" type="submit">Usuń</button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<div class="mt-4">
  <a class="btn btn-success" href="/f1/public/location_create.php">Dodaj lokalizację</a>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>

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
        ORDER BY l.id";
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
		<div class="card-body p-2">
			<div class="d-flex justify-content-between align-items-start">
			<h5 class="card-title mb-1">
				<a href="/f1/public/location_view.php?id=<?php echo $loc['id']; ?>"><?php echo e($loc['name']); ?></a>
			</h5>
			<div class="action-icons">
				<a href="<?php echo e($loc['google_maps_link']); ?>" class="text-primary action-icon icon-map">
				<!-- Jedź do -->
					<i class="bi bi-sign-turn-right"></i>
				</a>
			
				<!-- Edytuj -->
				<a href="/f1/public/location_edit.php?id=<?php echo $loc['id']; ?>" class="text-primary action-icon icon-edit">
					<i class="bi bi-pencil"></i>
				</a>
			
				<!-- Usuń -->
				<form method="post" action="/f1/public/location_delete.php" 
					onsubmit="return confirm('Na pewno usunąć lokalizację?');" 
					class="action-icon icon-del">
					<input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">
					<input type="hidden" name="id" value="<?php echo $loc['id']; ?>">
					<button type="submit" class="text-primary action-icon">
						<i class="bi bi-trash"></i>
					</button>
				</form>
			</div>
			</div>
		
			<p class="card-text mb-1"><?php echo e($loc['address']); ?></p>
			<p class="card-text mb-1">Tel: 
				<?php 
					for ($i = 1; $i <= 3; $i++): 
						$num = $phones[$i] ?? null;
						if ($num):
				?>
					<a href="tel:<?php echo e($num); ?>" class="badge bg-secondary text-decoration-none"><?php echo e($num); ?></a>
				<?php 
						endif;
					endfor; 
				?>
			</p>
			<p class="card-text mb-1">IP: <?php echo e($loc['ip_address'] ?: '—'); ?></p>
			<p class="card-text mb-1"><?php echo e($loc['notes_summary'] ?: '—'); ?></p>
		</div>
	</div>

  </div>
  <?php endforeach; ?>
</div>
<div class="mt-4">
  <a class="btn btn-success" href="/f1/public/location_create.php">Dodaj lokalizację</a>
</div>
<hr class="my-4">

<h5 class="mb-3">Wszystkie notatki</h5>

<?php
// Pobierz wszystkie notatki z nazwą lokalizacji
$sqlNotes = "SELECT n.id, n.content, n.created_at, l.name AS location_name
             FROM notes n
             JOIN locations l ON n.location_id = l.id
             ORDER BY l.name, n.created_at DESC";
$stmtNotes = $pdo->query($sqlNotes);
$allNotes = $stmtNotes->fetchAll();

$currentLocation = null;
if (count($allNotes) === 0): ?>
  <p class="text-muted">Brak notatek.</p>
<?php else: ?>
  <?php foreach ($allNotes as $note): ?>
    <?php if ($currentLocation !== $note['location_name']): ?>
      <?php 
        if ($currentLocation !== null) echo "</ul>"; 
        $currentLocation = $note['location_name'];
      ?>
      <h6 class="mt-3"><?php echo e($currentLocation); ?></h6>
      <ul class="list-group mb-2">
    <?php endif; ?>
      <li class="list-group-item py-1">
        <?php echo e($note['content']); ?>
        <small class="text-muted d-block"><?php echo e($note['created_at']); ?></small>
      </li>
  <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php require __DIR__.'/../templates/footer.php'; ?>

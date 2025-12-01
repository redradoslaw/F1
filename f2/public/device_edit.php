<?php
session_start();
require __DIR__.'/../src/db.php';
require __DIR__.'/../templates/header.php';

// Sprawdzenie ID sprzętu w URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Niepoprawne ID sprzętu.");
}
$id = (int)$_GET['id'];

// Pobranie danych sprzętu
$stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ?");
$stmt->execute([$id]);
$device = $stmt->fetch();

if (!$device) {
    die("Nie znaleziono sprzętu o podanym ID.");
}

// Obsługa formularza POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        die("Błąd CSRF.");
    }

    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $ip = trim($_POST['ip_address']);

    // Prosta walidacja
    if ($code === '' || $name === '') {
        $error = "Kod i nazwa są wymagane.";
    } else {
        $stmt = $pdo->prepare("UPDATE devices SET code = ?, name = ?, ip_address = ? WHERE id = ?");
        $stmt->execute([$code, $name, $ip ?: null, $id]);
        header("Location: location_view.php?id=" . $device['location_id']);
        exit;
    }
}

// Generowanie CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="container mt-4">
    <h2>Edycja sprzętu</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="mb-3">
            <label class="form-label">Kod</label>
            <input type="text" name="code" class="form-control" value="<?php echo e($device['code']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Nazwa</label>
            <input type="text" name="name" class="form-control" value="<?php echo e($device['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Adres IP</label>
            <input type="text" name="ip_address" class="form-control" value="<?php echo e($device['ip_address']); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
        <a href="location_view.php?id=<?php echo $device['location_id']; ?>" class="btn btn-secondary">Anuluj</a>
    </form>
</div>

<?php require __DIR__.'/../templates/footer.php'; ?>
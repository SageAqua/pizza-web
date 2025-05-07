<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/menu.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /public/index.php?page=login');
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<div class='container text-center my-5 text-danger'>Ungültige Benutzer-ID</div>";
    exit();
}

// Benutzer laden
$stmt = $pdo->prepare("SELECT id, vorname, email, rolle FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Adresse laden
$stmt = $pdo->prepare("SELECT full_name, street, zip, city, country FROM user_addresses WHERE user_id = ?");
$stmt->execute([$id]);
$address = $stmt->fetch();

if (!$user) {
    echo "<div class='container text-center my-5 text-danger'>Benutzer nicht gefunden</div>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Benutzer-Daten
    $vorname = $_POST['vorname'];
    $email = $_POST['email'];
    $rolle = $_POST['rolle'];

    // Adress-Daten
    $full_name = $_POST['full_name'];
    $street = $_POST['street'];
    $zip = $_POST['zip'];
    $city = $_POST['city'];
    $country = $_POST['country'];

    // Benutzer bearbeiten
    $updateUser = $pdo->prepare("UPDATE users SET vorname = ?, email = ?, rolle = ? WHERE id = ?");
    $updateUser->execute([$vorname, $email, $rolle, $id]);

    // Adresse bearbeitenn
    $updateAddress = $pdo->prepare("UPDATE user_addresses SET full_name = ?, street = ?, zip = ?, city = ?, country = ? WHERE user_id = ?");
    $updateAddress->execute([$full_name, $street, $zip, $city, $country, $id]);

    header("Location: /public/index.php?page=admin/users");
    exit();
}
?>

<div class="container my-5">
    <h2 class="text-center mb-4" style="color: #FF6600;">👤 Benutzer bearbeiten</h2>

    <form method="post" class="p-4 bg-dark text-white rounded shadow-sm mx-auto" style="max-width: 700px;">
        <div class="mb-3">
            <label for="vorname" class="form-label">Benutzername</label>
            <input type="text" name="vorname" id="vorname" class="form-control" value="<?= htmlspecialchars($user['vorname']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-Mail</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="mb-4">
            <label for="rolle" class="form-label">Rolle</label>
            <select name="rolle" id="rolle" class="form-select">
                <option value="user" <?= $user['rolle'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['rolle'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <hr class="my-4 text-secondary">
        <h4 class="text-orange mb-3">Adresse</h4>

        <div class="mb-3">
            <label for="full_name" class="form-label">Vollständiger Name</label>
            <input type="text" name="full_name" id="full_name" class="form-control" value="<?= htmlspecialchars($address['full_name'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="street" class="form-label">Straße</label>
            <input type="text" name="street" id="street" class="form-control" value="<?= htmlspecialchars($address['street'] ?? '') ?>" required>
        </div>

        <div class="row">
            <div class="mb-3 col-md-4">
                <label for="zip" class="form-label">PLZ</label>
                <input type="text" name="zip" id="zip" class="form-control" value="<?= htmlspecialchars($address['zip'] ?? '') ?>" required>
            </div>
            <div class="mb-3 col-md-4">
                <label for="city" class="form-label">Stadt</label>
                <input type="text" name="city" id="city" class="form-control" value="<?= htmlspecialchars($address['city'] ?? '') ?>" required>
            </div>
            <div class="mb-3 col-md-4">
                <label for="country" class="form-label">Land</label>
                <input type="text" name="country" id="country" class="form-control" value="<?= htmlspecialchars($address['country'] ?? '') ?>" required>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="/public/index.php?page=admin/users" class="btn btn-outline-light">
                <i class="fa-solid fa-arrow-left me-1"></i> Zurück
            </a>
            <button type="submit" class="btn btn-orange">
                <i class="fa-solid fa-floppy-disk me-1"></i> Änderungen speichern
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

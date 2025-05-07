<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

function validatePasswordRules(string $password): ?string {
    if (strlen($password) < 8) {
        return 'Passwort muss mindestens 8 Zeichen lang sein.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return 'Passwort muss mindestens einen Großbuchstaben enthalten.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        return 'Passwort muss mindestens einen Kleinbuchstaben enthalten.';
    }

    if (!preg_match('/[!._]/', $password)) {
        return 'Passwort muss mindestens eines der folgenden Sonderzeichen enthalten: ! . _';
    }

    if (preg_match('/[^a-zA-Z0-9!._]/', $password)) {
        return 'Passwort enthält ungültige Zeichen. Erlaubt sind nur Buchstaben, Zahlen und folgende Sonderzeichen: ! . _';
    }

    return null;
}


if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

$userId = $_SESSION['user_id'];

// Adresse laden
$addressStmt = $pdo->prepare("SELECT full_name, street, zip, city, country FROM user_addresses WHERE user_id = ?");
$addressStmt->execute([$userId]);
$userAddress = $addressStmt->fetch(PDO::FETCH_ASSOC);

// Adresse bearbeiten
$editMode = isset($_POST['edit_address']) || !$userAddress;
//Passwort bearbeiten
$passwordEditMode = isset($_POST['edit_password']);


// Adresse speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $password = $_POST['password'] ?? '';

    $userStmt = $pdo->prepare("SELECT passwort FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['passwort'])) {
        $error = "Falsches Passwort. Die Adresse wurde nicht gespeichert.";
        $editMode = true;
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $street = trim($_POST['street'] ?? '');
        $zip = trim($_POST['zip'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? '');

        if ($fullName && $street && $zip && $city && $country) {
            if ($userAddress) {
                $update = $pdo->prepare("UPDATE user_addresses SET full_name = ?, street = ?, zip = ?, city = ?, country = ? WHERE user_id = ?");
                $update->execute([$fullName, $street, $zip, $city, $country, $userId]);
            } else {
                $insert = $pdo->prepare("INSERT INTO user_addresses (user_id, full_name, street, zip, city, country) VALUES (?, ?, ?, ?, ?, ?)");
                $insert->execute([$userId, $fullName, $street, $zip, $city, $country]);
            }

            $userAddress = [
                'full_name' => $fullName,
                'street' => $street,
                'zip' => $zip,
                'city' => $city,
                'country' => $country
            ];

            $message = "Adresse erfolgreich gespeichert.";
            $editMode = false;
        } else {
            $error = "Bitte fülle alle Felder vollständig aus.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $userStmt = $pdo->prepare("SELECT passwort FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPassword, $user['passwort'])) {
        $error = "Das aktuelle Passwort ist falsch.";
        $passwordEditMode = true;
    } elseif ($error = validatePasswordRules($newPassword)) {
        $passwordEditMode = true;
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Die neuen Passwörter stimmen nicht überein.";
        $passwordEditMode = true;
    } elseif ($newPassword === $currentPassword) {
        $error = "Das neue Passwort darf nicht mit dem aktuellen Passwort identisch sein.";
        $passwordEditMode = true;
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET passwort = ? WHERE id = ?");
        $update->execute([$hashed, $userId]);
        $message = "Passwort erfolgreich geändert.";
        $passwordEditMode = false;
    }
}

// Bestellungen laden
$stmt = $pdo->prepare("SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<section class="section deals-dark text-white">
    <div class="container">
        <div class="text-end mt-4">
            <a href="?page=home" class="btn btn-outline-light">
                <i class="fa-solid fa-arrow-left me-1"></i> Zurück zur Startseite
            </a>
        </div>
        <h2 class="mb-5 text-center" style="color: #FF6600;"><i class="fa-solid fa-user me-2"></i> Mein Profil</h2>

        <h4 class="mb-3" style="color: #FF6600;"><i class="fa-solid fa-location-dot me-2"></i> Meine Adresse</h4>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($editMode): ?>
            <form method="POST" class="row g-3 mb-5">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Vollständiger Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name"
                           value="<?= htmlspecialchars($userAddress['full_name'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="street" class="form-label">Straße und Hausnummer</label>
                    <input type="text" class="form-control" id="street" name="street"
                           value="<?= htmlspecialchars($userAddress['street'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="zip" class="form-label">PLZ</label>
                    <input type="text" class="form-control" id="zip" name="zip"
                           value="<?= htmlspecialchars($userAddress['zip'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="city" class="form-label">Stadt</label>
                    <input type="text" class="form-control" id="city" name="city"
                           value="<?= htmlspecialchars($userAddress['city'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="country" class="form-label">Land</label>
                    <input type="text" class="form-control" id="country" name="country"
                           value="<?= htmlspecialchars($userAddress['country'] ?? 'Deutschland') ?>" required>
                </div>

                <div class="col-12 text-orange">
                    <label for="password" class="form-label">Passwort bestätigen</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="save_address" class="btn btn-orange me-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Adresse speichern
                    </button>
                    <a href="?page=profile" class="btn btn-secondary">
                        <i class="fa-solid fa-xmark me-1"></i> Abbrechen
                    </a>
                </div>
            </form>
        <?php else: ?>
            <div class="row mb-4">
                <!--Adresse-->
                <div class="col-md-6 mb-3">
                    <div class="card bg-dark text-white p-4 shadow-sm rounded-4 h-100">
                        <h5 class="text-orange mb-3"><i class="fa-solid fa-location-dot me-2"></i> Adresse</h5>
                        <p class="mb-1"><strong><?= htmlspecialchars($userAddress['full_name']) ?></strong></p>
                        <p class="mb-1"><?= htmlspecialchars($userAddress['street']) ?></p>
                        <p class="mb-1"><?= htmlspecialchars($userAddress['zip']) ?> <?= htmlspecialchars($userAddress['city']) ?></p>
                        <p class="mb-3"><?= htmlspecialchars($userAddress['country']) ?></p>
                        <form method="POST" class="text-end">
                            <button type="submit" name="edit_address" class="btn btn-orange btn-sm">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Bearbeiten
                            </button>
                        </form>
                    </div>
                </div>
                <!--Passwort-->
                <div class="col-md-6 mb-3">
                        <?php if ($passwordEditMode): ?>
                            <form method="POST" class="card bg-dark text-white p-4 shadow-sm rounded-4">
                                <h5 class="text-orange mb-3"><i class="fa-solid fa-key me-2"></i> Passwort ändern</h5>

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Aktuelles Passwort</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Neues Passwort</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Neues Passwort bestätigen</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>

                                <div class="text-end">
                                    <button type="submit" name="save_password" class="btn btn-orange me-2">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Speichern
                                    </button>
                                    <a href="?page=profile" class="btn btn-secondary">
                                        <i class="fa-solid fa-xmark me-1"></i> Abbrechen
                                    </a>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="card bg-dark text-white p-4 shadow-sm rounded-4 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="text-orange mb-3"><i class="fa-solid fa-key me-2"></i> Passwort ändern</h5>
                                    <p class="mb-0">Aus Sicherheitsgründen empfehlen wir, dein Passwort regelmäßig zu aktualisieren.</p>
                                </div>
                                <div class="text-end mt-3">
                                    <form method="POST">
                                        <button type="submit" name="edit_password" class="btn btn-outline-light btn-sm">
                                            <i class="fa-solid fa-lock me-1"></i> Passwort ändern
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p class="text-center">Du hast noch keine Bestellungen aufgegeben.</p>
        <?php else: ?>
            <h4 class="mb-4" style="color: #FF6600;"><i class="fa-solid fa-box me-2"></i> Meine Bestellungen</h4>
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                    <tr>
                        <th>Bestell-Nr</th>
                        <th>Datum</th>
                        <th>Gesamt</th>
                        <th>Status</th>
                        <th>Aktion</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                            <td><?= number_format($order['total'], 2, ',', '.') ?> €</td>
                            <td>
                                <?php
                                $statusColor = [
                                    'offen' => 'warning',
                                    'bezahlt' => 'success',
                                    'storniert' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColor[$order['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="?page=order_details&id=<?= $order['id'] ?>" class="btn btn-outline-light btn-sm">
                                    <i class="fa-solid fa-eye me-1"></i> Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</section>

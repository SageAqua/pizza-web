<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['session_id'] ?? null;

$primaryAddress = null;
if ($userId) {
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY id ASC LIMIT 1");
    $stmt->execute([$userId]);
    $primaryAddress = $stmt->fetch();
}


// Warenkorb abfragen
$stmt = $pdo->prepare("SELECT ci.product_id, ci.quantity, p.name, p.price 
                       FROM cart_items ci
                       JOIN products p ON ci.product_id = p.id
                       WHERE " . ($userId ? "ci.user_id = ?" : "ci.session_id = ?"));
$stmt->execute([$userId ?? $sessionId]);
$items = $stmt->fetchAll();

if (empty($items)) {
    echo "<p class='text-center mt-5'>Dein Warenkorb ist leer.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $useSaved = isset($_POST['use_saved_address']) && $_POST['use_saved_address'] === '1';

    if ($useSaved && $primaryAddress) {
        $fullname = $primaryAddress['full_name'];
        $street = $primaryAddress['street'];
        $zip = $primaryAddress['zip'];
        $city = $primaryAddress['city'];
        $addressId = $primaryAddress['id'];
    } else {
        $fullname = trim($_POST['fullname'] ?? '');
        $street = trim($_POST['street'] ?? '');
        $zip = trim($_POST['zip'] ?? '');
        $city = trim($_POST['city'] ?? '');

        if (!$fullname || !$street || !$zip || !$city) {
            $error = "Bitte gib eine vollständige Lieferadresse ein.";
        } else {
            if ($userId) {
                $insertAddress = $pdo->prepare("INSERT INTO user_addresses (user_id, full_name, street, zip, city, country) VALUES (?, ?, ?, ?, ?, ?)");
                $insertAddress->execute([$userId, $fullname, $street, $zip, $city, 'Deutschland']);
                $addressId = $pdo->lastInsertId();
            } else {
                $addressId = null;
            }
        }
    }


    if (!isset($error)) {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Bestellung eintragen
        $insertOrder = $pdo->prepare("INSERT INTO orders (user_id, session_id, address_id, total, status) VALUES (?, ?, ?, ?, 'offen')");
        $insertOrder->execute([$userId, $sessionId, $addressId ?? null, $total]);
        $orderId = $pdo->lastInsertId();

        // Bestellpositionen eintragen
        $orderItemInsert = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $orderItemInsert->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
        }

        // Warenkorb leeren
        $deleteStmt = $pdo->prepare("DELETE FROM cart_items WHERE " . ($userId ? "user_id = ?" : "session_id = ?"));
        $deleteStmt->execute([$userId ?? $sessionId]);

        // Erfolgsanzeige
        echo "<section class='section deals-dark text-white text-center'>
                <div class='container'>
                    <h2 class='text-success mb-4'><i class='fa-solid fa-circle-check me-2'></i> Bestellung erfolgreich!</h2>
                    <p class='mb-4'>Vielen Dank für deine Bestellung.<br>Deine Order-ID ist: <strong>#{$orderId}</strong></p>
                    <a href='?page=home' class='btn btn-orange px-4 py-2'><i class='fa-solid fa-arrow-left me-2'></i> Zurück zur Startseite</a>
                </div>
              </section>";
        exit;
    }
}
?>


<section class="section deals-dark text-white">
    <div class="container">
        <h2 class="mb-4 text-center"><i class="fa-solid fa-truck me-2"></i> Checkout</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-3 justify-content-center">
            <?php if ($primaryAddress): ?>
                <div class="col-12">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="useSaved" name="use_saved_address" value="1" checked>
                        <label class="form-check-label" for="useSaved">
                            Gespeicherte Adresse verwenden:
                        </label>
                    </div>
                    <div class="saved-address-box">
                        <?= htmlspecialchars($primaryAddress['full_name']) ?><br>
                        <?= htmlspecialchars($primaryAddress['street']) ?><br>
                        <?= htmlspecialchars($primaryAddress['zip']) ?> <?= htmlspecialchars($primaryAddress['city']) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Neue Adresse eingeben -->
            <div class="col-md-6">
                <label for="fullname" class="form-label">Vollständiger Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($primaryAddress['full_name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label for="street" class="form-label">Straße und Hausnummer</label>
                <input type="text" class="form-control" id="street" name="street" value="<?= htmlspecialchars($primaryAddress['street'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="zip" class="form-label">PLZ</label>
                <input type="text" class="form-control" id="zip" name="zip" value="<?= htmlspecialchars($primaryAddress['zip'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="city" class="form-label">Stadt</label>
                <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($primaryAddress['city'] ?? '') ?>">
            </div>

            <div class="col-12 text-end mt-3">
                <button type="submit" class="btn btn-orange px-4 py-2">
                    <i class="fa-solid fa-paper-plane me-2"></i> Bestellung abschicken
                </button>
                <a href="?page=cart" class="btn btn-outline-light ms-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> Zurück zum Warenkorb
                </a>
            </div>
        </form>


        <hr class="my-5">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h4 class="text-center mb-4" style="color: #FF6600;"><i class="fa-solid fa-pizza-slice me-2"></i> Deine Produkte</h4>
                <ul class="list-group mb-4">
                    <?php $total = 0; ?>
                    <?php foreach ($items as $item):
                        $itemTotal = $item['price'] * $item['quantity'];
                        $total += $itemTotal;
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                            <?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)
                            <span><?= number_format($itemTotal, 2, ',', '.') ?> €</span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary text-white fw-bold">
                        Gesamt:
                        <span><?= number_format($total, 2, ',', '.') ?> €</span>
                    </li>
                </ul>

                <h4 class="text-center mb-3" style="color: #FF6600;"><i class="fa-solid fa-credit-card me-2"></i> Zahlungsmethode</h4>
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-outline-light" disabled><i class="fa-brands fa-cc-paypal me-1"></i> PayPal</button>
                    <button class="btn btn-outline-light" disabled><i class="fa-solid fa-money-bill me-1"></i> Barzahlung</button>
                    <button class="btn btn-outline-light" disabled><i class="fa-solid fa-credit-card me-1"></i> Kreditkarte</button>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('useSaved');
        const fields = ['fullname', 'street', 'zip', 'city'].map(id => document.getElementById(id));

        function toggleFields() {
            const useSaved = checkbox.checked;
            fields.forEach(field => {
                field.disabled = useSaved;
                field.classList.toggle('disabled-field', useSaved);
            });
        }

        checkbox.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>

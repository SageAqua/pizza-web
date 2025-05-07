<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

$userId = $_SESSION['user_id'];
$orderId = $_GET['id'] ?? $_GET['uid'] ?? null;

if (!$orderId) {
    echo "Ungültige Bestell-ID.";
    exit;
}

// Stornierung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $checkStmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$orderId, $userId]);
    $orderStatus = $checkStmt->fetchColumn();

    if ($orderStatus === 'offen') {
        $cancelStmt = $pdo->prepare("UPDATE orders SET status = 'storniert' WHERE id = ? AND user_id = ?");
        $cancelStmt->execute([$orderId, $userId]);

        //anstatt header() -> JavaScript-Weiterleitung
        echo "<script>window.location.href = '?page=order_details&id=" . $orderId . "';</script>";
        exit;
    }
}


// Bestellung laden
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$orderStmt->execute([$orderId, $userId]);
$order = $orderStmt->fetch();

if (!$order) {
    echo "Bestellung nicht gefunden.";
    exit;
}

$itemsStmt = $pdo->prepare("
    SELECT p.name, p.image, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();
?>

<section class="section deals-dark text-white">
    <div class="container">
        <div class="text-end mt-4">
            <a href="?page=profile" class="btn btn-outline-light">
                <i class="fa-solid fa-arrow-left me-1"></i> Zurück zur Profilübersicht
            </a>
        </div>

        <h2 class="mb-5 text-center" style="color: #FF6600;">
            <i class="fa-solid fa-box-open me-2"></i> Bestelldetails #<?= htmlspecialchars($order['id']) ?>
        </h2>

        <div class="mb-4 text-center">
            <strong>Status:</strong>
            <?php
            $statusColor = [
                'offen' => 'warning',
                'bezahlt' => 'success',
                'storniert' => 'danger'
            ];
            ?>
            <span class="badge bg-<?= $statusColor[$order['status']] ?? 'secondary' ?>">
                <?= ucfirst(htmlspecialchars($order['status'])) ?>
            </span>

            <?php if ($order['status'] === 'offen'): ?>
                <div class="text-center mt-3">
                    <form method="post" onsubmit="return confirm('Möchtest du diese Bestellung wirklich stornieren?');">
                        <button type="submit" name="cancel_order" class="btn btn-danger">
                            <i class="fa-solid fa-ban me-1"></i> Bestellung stornieren
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-striped">
                <thead>
                <tr>
                    <th>Produkt</th>
                    <th>Menge</th>
                    <th>Einzelpreis</th>
                    <th>Gesamt</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= (int)$item['quantity'] ?></td>
                        <td><?= number_format($item['price'], 2, ',', '.') ?> €</td>
                        <td><?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?> €</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

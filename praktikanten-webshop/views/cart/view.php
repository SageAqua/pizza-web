<?php
require_once __DIR__ . '/../../config/db.php';

session_start();
$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['session_id'] ?? null;

$stmt = $pdo->prepare("SELECT ci.product_id, ci.quantity, p.name, p.price, p.image 
                       FROM cart_items ci
                       JOIN products p ON ci.product_id = p.id
                       WHERE " . ($userId ? "ci.user_id = ?" : "ci.session_id = ?"));
$stmt->execute([$userId ?? $sessionId]);
$cartItems = $stmt->fetchAll();

$cart = [];
foreach ($cartItems as $item) {
    $cart[$item['product_id']] = [
        'name' => $item['name'],
        'image' => $item['image'],
        'price' => $item['price'],
        'quantity' => $item['quantity']
    ];
}

?>

<section class="section deals-dark text-white">
    <div class="container">
        <h2 class="mb-5 text-center" style="color: #FF6600;">
            <i class="fa-solid fa-cart-shopping me-2"></i> Dein Warenkorb
        </h2>

        <?php if (empty($cart)): ?>
            <p class="text-center">Dein Warenkorb ist leer.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table text-white align-middle">
                    <thead>
                    <tr style="color: #FF6600;">
                        <th>Bild</th>
                        <th>Produkt</th>
                        <th>Preis</th>
                        <th>Menge</th>
                        <th>Gesamt</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $productId => $item): ?>
                        <tr style="background-color: #1f2425;">
                            <td>
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-thumbnail" style="max-width: 60px;">
                            </td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= number_format($item['price'], 2, ',', '.') ?> €</td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?> €</td>
                            <td>
                                <form action="/controllers/cart/remove.php" method="post">
                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                    <button class="btn btn-outline-light btn-sm">
                                        <i class="fa-solid fa-trash"></i> Entfernen
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <a href="?page=checkout" class="btn-orange btn px-4 py-2">
                    <i class="fa-solid fa-credit-card me-2"></i> Zur Kasse
                </a>
            </div>
        <?php endif; ?>

        <div class="text-end mt-3">
            <a href="?page=home" class="btn btn-outline-light">
                <i class="fa-solid fa-arrow-left me-1"></i> Zurück
            </a>
        </div>
    </div>
</section>


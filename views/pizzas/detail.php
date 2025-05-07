<?php
require_once __DIR__ . '/../../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<p class='text-center mt-5'>Keine Pizza-ID angegeben.</p>";
    include_once __DIR__ . '/../layout/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$pizza = $stmt->fetch();

if (!$pizza) {
    echo "<p class='text-center mt-5'>Pizza nicht gefunden.</p>";
    include_once __DIR__ . '/../layout/footer.php';
    exit;
}
?>

<section class="section deals-dark text-white">
    <div class="container">
        <div class="row align-items-center">
            <!-- Pizza Bild -->
            <div class="col-lg-6 mb-4 mb-lg-0 text-center">
                <img src="<?= htmlspecialchars($pizza['image']) ?>" class="img-fluid rounded shadow" alt="<?= htmlspecialchars($pizza['name']) ?>">
            </div>

            <!-- Details -->
            <div class="col-lg-6">
                <div class="pizza-detail-wrapper">
                    <h2><?= htmlspecialchars($pizza['name']) ?></h2>
                    <p class="mb-3"><?= htmlspecialchars($pizza['description']) ?></p>

                    <div class="mb-3">
                        <strong>Größe:</strong>
                        <div class="pizza-size-options mt-2">
                            <button class="btn btn-outline-light">Medium</button>
                            <button class="btn btn-outline-light active">Large</button>
                            <button class="btn btn-outline-light">XL</button>
                        </div>
                    </div>

                    <p class="pizza-price">Preis: <?= number_format($pizza['price'], 2, ',', '.') ?> €</p>

                    <form class="add-to-cart-form" data-name="<?= htmlspecialchars($pizza['name']) ?>" method="post">
                        <input type="hidden" name="product_id" value="<?= $pizza['id'] ?>">
                        <button type="submit" class="btn btn-orange w-100 mt-3 mb-2">
                            <i class="fa-solid fa-cart-shopping me-1"></i> In den Warenkorb
                        </button>
                    </form>

                    <a href="javascript:history.back()" class="btn btn-outline-light w-100">
                        <i class="fa-solid fa-arrow-left me-2"></i> Zurück zur Übersicht
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

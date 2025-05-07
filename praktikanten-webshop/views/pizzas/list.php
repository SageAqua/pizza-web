<?php
$stmt = $pdo->query("SELECT id, name, description, price, image FROM products WHERE active = 1");
$pizzen = $stmt->fetchAll();
?>

<section class="section deals-dark text-white">
    <div class="container">
        <h2 class="text-center mb-5" style="color: #FF6600;">
            <i class="fa-solid fa-utensils me-2"></i> Unser Menü
        </h2>
        <div class="row g-4">
            <?php foreach ($pizzen as $pizza): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 bg-dark border-0 shadow rounded-4 overflow-hidden position-relative hover-shadow transition">
                        <img src="<?= htmlspecialchars($pizza['image']) ?>" class="card-img-top img-fluid" alt="<?= htmlspecialchars($pizza['name']) ?>" style="height: 180px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-uppercase text-orange"><?= htmlspecialchars($pizza['name']) ?></h5>
                            <p class="card-text small text-light"><?= htmlspecialchars($pizza['description']) ?></p>
                            <div class="mt-auto">
                                <p class="fw-bold text-white fs-5 mb-3">ab <?= number_format($pizza['price'], 2, ',', '.') ?> €</p>
                                <a href="?page=pizzas/detail&id=<?= $pizza['id'] ?>" class="btn btn-outline-light w-100 mb-2">
                                    <i class="fa-solid fa-eye me-1"></i> Details anzeigen
                                </a>
                                <form class="add-to-cart-form" data-name="<?= htmlspecialchars($pizza['name']) ?>" method="post">
                                    <input type="hidden" name="product_id" value="<?= $pizza['id'] ?>">
                                    <button type="submit" class="btn btn-orange w-100">
                                        <i class="fa-solid fa-cart-plus me-1"></i> In den Warenkorb
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<?php


require_once __DIR__ . '/../config/db.php';

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

if (!$isAdmin) {
    $stmt = $pdo->query("
    SELECT 
        p.id, 
        p.name, 
        p.description, 
        p.image, 
        p.price, 
        SUM(oi.quantity) AS total_quantity
    FROM 
        order_items oi
    JOIN 
        products p ON oi.product_id = p.id 
    WHERE 
        p.active = 1 AND p.category_id = 1
    GROUP BY 
        p.id
    ORDER BY 
        total_quantity DESC
    LIMIT 3
");
    $pizzen = $stmt->fetchAll();

    $stmtDeals = $pdo->query("
        SELECT 
            id, 
            name, 
            description, 
            image, 
            price
        FROM 
            products
        WHERE 
            active = 1 AND category_id = 8
    ");
    $deals = $stmtDeals->fetchAll();
}
?>

<?php if ($isAdmin): ?>
    <!-- Nur für Admin -->
    <section class="container my-5">
        <h2 class="text-center mb-4" style="color: #FF6600;">Admin Dashboard</h2>

        <div class="row g-4">
            <div class="col-md-4">
                <a href="?page=admin/pizzas" class="btn btn-orange w-100 p-4 fs-5">
                    🍕 Artikel verwalten
                </a>
            </div>
            <div class="col-md-4">
                <a href="?page=admin/categories" class="btn btn-orange w-100 p-4 fs-5">
                    📂 Kategorien verwalten
                </a>
            </div>
            <div class="col-md-4">
                <a href="?page=admin/users" class="btn btn-orange w-100 p-4 fs-5">
                    👥 Benutzer verwalten
                </a>
            </div>
            <div class="col-md-4">
                <a href="?page=admin/orders" class="btn btn-orange w-100 p-4 fs-5">
                    📦 Bestellungen anzeigen
                </a>
            </div>

            <!-- Neue Buttons für Generieren -->
<!--            <div class="col-md-4">-->
<!--                <a href="../controllers/pizzas/create.php?generate=products" class="btn btn-orange w-100 p-4 fs-5">-->
<!--                    🍕 500 Pizzen generieren-->
<!--                </a>-->
<!--            </div>-->
<!--            <div class="col-md-4">-->
<!--                <a href="../controllers/pizzas/create.php?generate=drinks" class="btn btn-orange w-100 p-4 fs-5">-->
<!--                    🥤 250 Getränke generieren-->
<!--                </a>-->
<!--            </div>-->
<!--            <div class="col-md-4">-->
<!--                <a href="../controllers/pizzas/create.php?generate=salads" class="btn btn-orange w-100 p-4 fs-5">-->
<!--                    🥗 250 Salate generieren-->
<!--                </a>-->
<!--            </div>-->
<!--            <div class="col-md-4">-->
<!--                <a href="../controllers/users/generate_dummy_data.php" class="btn btn-orange w-100 p-4 fs-5">-->
<!--                    👤 100 Dummy-Benutzer + Bestellungen generieren-->
<!--                </a>-->
<!--            </div>-->
        </div>
    </section>
<?php else: ?>

    <!-- Normale Benutzeransicht -->
    <section class="container-fluid px-0">
        <div class="bg-dark py-5">
            <h2 class="text-center mb-0 text-uppercase" style="color: #FF6600; font-weight: bold;">
                3 Meist bestellte Pizzen der letzte Zeit
            </h2>
        </div>

        <div id="pizzaCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($pizzen as $index => $pizza): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>"
                         style="height: 500px; background-image: url('<?= htmlspecialchars($pizza['image']) ?>'); background-size: cover; background-position: center; position: relative;">

                        <div class="carousel-overlay d-flex align-items-center justify-content-center" style="background-color: rgba(0, 0, 0, 0.6); height: 100%;">
                            <div class="text-center text-white px-3">
                                <h2 class="mb-3" style="color: #FF6600;"><?= htmlspecialchars($pizza['name']) ?></h2>
                                <p class="lead"><?= htmlspecialchars($pizza['description']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#pizzaCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#pizzaCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>



    <section class="section deals-dark text-white">
        <div class="container">
            <h2 class="text-center mb-5" style="color: #FF6600;">UNSERE DEALS</h2>
            <div class="deals-carousel d-flex overflow-auto gap-4 pb-3">
                <?php foreach ($deals as $deal): ?>
                    <div class="deal-card flex-shrink-0 shadow p-3 text-center d-flex flex-column" style="width: 220px;">
                        <img src="<?= htmlspecialchars($deal['image']) ?>" class="img-fluid mb-2 rounded" alt="<?= htmlspecialchars($deal['name']) ?>">
                        <div class="flex-grow-1">
                            <h5 class="fw-bold text-uppercase" style="color: #FF6600;"><?= htmlspecialchars($deal['name']) ?></h5>
                            <p class="deal-desc mb-1"><?= htmlspecialchars($deal['description']) ?></p>
                            <p class="fw-bold text-white mb-2">ab <?= number_format($deal['price'], 2, ',', '.') ?> €</p>
                        </div>
                        <div class="mt-auto">
                            <a href="?page=pizzas/detail&id=<?= $deal['id'] ?>" class="btn btn-outline-light w-100 mb-2">
                                Details anzeigen
                            </a>
                            <form class="add-to-cart-form" data-name="<?= htmlspecialchars($deal['name']) ?>" method="post">
                                <input type="hidden" name="product_id" value="<?= $deal['id'] ?>">
                                <button type="submit" class="btn btn-orange w-100 mt-3">
                                    <i class="fa-solid fa-cart-shopping me-1"></i> In den Warenkorb
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php endif; ?>

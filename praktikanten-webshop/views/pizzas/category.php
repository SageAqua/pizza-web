<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Admin-check
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$backLink = $isAdmin ? '/public/index.php?page=admin/categories' : '/public/index.php?page=home';

// Kategories
$category = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$category->execute([$categoryId]);
$cat = $category->fetch();

// Produkte
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
$priceFilter = $_GET['price_filter'] ?? '';

$sql = "SELECT * FROM products WHERE category_id = :category_id AND active = 1";
$params = ['category_id' => $categoryId];

// Suche
if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR description LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

// Preisfilter
if ($priceFilter === 'under5') {
    $sql .= " AND price < 5";
} elseif ($priceFilter === '5to10') {
    $sql .= " AND price BETWEEN 5 AND 10";
} elseif ($priceFilter === 'over10') {
    $sql .= " AND price > 10";
}

// Sortierung
switch ($sort) {
    case 'name_asc':
        $sql .= " ORDER BY name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    default:
        $sql .= " ORDER BY name ASC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

?>

<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container my-5">
    <a href="<?= $backLink ?>" class="btn btn-outline-light mb-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Zurück
    </a>
    <h2 class="text-center mb-5" style="color: #FF6600;">
        Kategorie: <?= htmlspecialchars($cat['name'] ?? 'Unbekannt') ?>
    </h2>
    <form method="get" class="row g-3 mb-4">
        <input type="hidden" name="page" value="category">
        <input type="hidden" name="id" value="<?= $categoryId ?>">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Produkt suchen..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="">Sortieren nach</option>
                <option value="name_asc" <?= ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Name A–Z</option>
                <option value="name_desc" <?= ($_GET['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Name Z–A</option>
                <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Preis aufsteigend</option>
                <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Preis absteigend</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="price_filter" class="form-select">
                <option value="">Preis filtern</option>
                <option value="under5" <?= ($_GET['price_filter'] ?? '') === 'under5' ? 'selected' : '' ?>>Unter 5 €</option>
                <option value="5to10" <?= ($_GET['price_filter'] ?? '') === '5to10' ? 'selected' : '' ?>>5–10 €</option>
                <option value="over10" <?= ($_GET['price_filter'] ?? '') === 'over10' ? 'selected' : '' ?>>Über 10 €</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-orange w-100">
                <i class="fa-solid fa-filter me-1"></i> Anwenden
            </button>
        </div>
    </form>

    <?php if (empty($products)): ?>
        <p class="text-center text-muted">Keine Produkte in dieser Kategorie.</p>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $pizza): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 bg-dark border-0 shadow rounded-4 overflow-hidden position-relative hover-shadow transition">
                        <img src="<?= htmlspecialchars($pizza['image']) ?>" class="card-img-top img-fluid" alt="<?= htmlspecialchars($pizza['name']) ?>" style="height: 180px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-uppercase text-orange"><?= htmlspecialchars($pizza['name']) ?></h5>
                            <p class="card-text small text-light"><?= htmlspecialchars($pizza['description']) ?></p>
                            <div class="mt-auto">
                                <p class="fw-bold text-white fs-5 mb-3"><?= number_format($pizza['price'], 2, ',', '.') ?> €</p>
                                <?php if (!$isAdmin): ?>
                                    <a href="?page=pizzas/detail&id=<?= $pizza['id'] ?>" class="btn btn-outline-light w-100 mb-2">
                                        <i class="fa-solid fa-eye me-1"></i> Details anzeigen
                                    </a>
                                    <form class="add-to-cart-form" method="post" action="/public/index.php?page=cart/add">
                                        <input type="hidden" name="product_id" value="<?= $pizza['id'] ?>">
                                        <button type="submit" class="btn btn-orange w-100">
                                            <i class="fa-solid fa-cart-plus me-1"></i> In den Warenkorb
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


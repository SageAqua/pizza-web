<?php
require_once __DIR__ . '/../../config/db.php';

$editMode = isset($_GET['id']);
$pizza = [
    'name' => '',
    'description' => '',
    'price' => '',
    'image' => '',
    'active' => 1
];

if ($editMode) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $pizza = $stmt->fetch();
}
?>

<section class="container my-5">
    <a href="/public/index.php?page=admin/pizzas" class="btn btn-outline-light">
        <i class="fa-solid fa-arrow-left me-1"></i> Abbrechen
    </a>
    <h2 class="text-center mb-4" style="color: #FF6600;">
        <?= $editMode ? '🍕 Pizza bearbeiten' : '🍕 Neue Pizza erstellen' ?>
    </h2>

    <form action="../controllers/pizzas/<?= $editMode ? 'edit' : 'create' ?>.php" method="post" enctype="multipart/form-data" class="bg-dark text-white p-4 rounded shadow-sm">
        <?php if ($editMode): ?>
            <input type="hidden" name="id" value="<?= $pizza['id'] ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="name" class="form-label">Name der Artikel</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($pizza['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Beschreibung</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($pizza['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Preis (€)</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($pizza['price']) ?>" required>
        </div>
        <?php
        $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
        ?>

        <div class="mb-3">
            <label for="category_id" class="form-label">Kategorie</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $editMode && $pizza['category_id'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Bild (optional)</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
            <?php if (!empty($pizza['image'])): ?>
                <img src="<?= htmlspecialchars($pizza['image']) ?>" alt="Vorschau" class="img-fluid mt-2 rounded" width="150">
            <?php endif; ?>
        </div>

        <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="active" name="active" value="1" <?= $pizza['active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="active">Aktiv</label>
        </div>

        <button type="submit" class="btn btn-orange w-100">
            <?= $editMode ? 'Änderungen speichern' : 'Pizza erstellen' ?>
        </button>
    </form>
</section>

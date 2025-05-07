<?php
require_once __DIR__ . '/../../config/db.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'])) {
        $name = trim($_POST['name']);
        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
        }
    }

    if (isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];

        // Prüfen, ob Produkte in dieser Kategorie vorhanden sind
        $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $check->execute([$deleteId]);
        $productCount = $check->fetchColumn();

        if ($productCount > 0) {
            // Produkte vorhanden → Nicht löschen, Nachricht setzen
            $_SESSION['error'] = "Kategorie kann nicht gelöscht werden – es sind noch Produkte vorhanden.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$deleteId]);
        }

        header("Location: /public/index.php?page=admin/categories");
        exit;
    }


    header("Location: /public/index.php?page=admin/categories");
    exit;
}
?>


<div class="container my-5">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <a href="/public/index.php?page=home" class="btn btn-outline-light">
        <i class="fa-solid fa-arrow-left me-1"></i> Abbrechen
    </a>
    <h2 class="text-center mb-4" style="color: #FF6600;">📂 Kategorien verwalten</h2>

    <form method="POST" class="d-flex mb-4 gap-2">
        <input type="text" name="name" class="form-control" placeholder="Neue Kategorie hinzufügen..." required>
        <button type="submit" class="btn btn-orange"><i class="fa-solid fa-plus me-1"></i> Hinzufügen</button>
    </form>
    <ul class="list-group">
        <?php foreach ($categories as $cat): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-white">
                <a href="/public/index.php?page=category&id=<?= $cat['id'] ?>" class="text-white text-decoration-none">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Wirklich löschen?')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

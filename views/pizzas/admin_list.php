<?php
require_once __DIR__ . '/../../config/db.php';

// Kategorien abrufen
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Filter aus der URL
$filterCategoryId = $_GET['category_id'] ?? null;
$statusFilter = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$direction = $_GET['direction'] ?? 'desc';

$allowedSort = ['name', 'created_at'];
$allowedDirection = ['asc', 'desc'];

if (!in_array($sort, $allowedSort)) $sort = 'created_at';
if (!in_array($direction, $allowedDirection)) $direction = 'desc';

// Produkte holen
$params = [];
$sql = "SELECT * FROM products WHERE 1=1";

if ($filterCategoryId) {
    $sql .= " AND category_id = ?";
    $params[] = $filterCategoryId;
}

if ($statusFilter === 'active') {
    $sql .= " AND active = 1";
} elseif ($statusFilter === 'inactive') {
    $sql .= " AND active = 0";
}

$sql .= " ORDER BY $sort $direction";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pizzas = $stmt->fetchAll();
$anzahl = count($pizzas);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="?page=home" class="btn btn-outline-light">
            <i class="fa-solid fa-arrow-left me-1"></i> Zurück zur Übersicht
        </a>
        <h2 class="text-center flex-grow-1 mb-0" style="color: #FF6600;">🍕 Artikel verwalten</h2>
        <div style="width: 170px;"></div>
    </div>

    <div class="text-end mb-4">
        <div class="text-white mb-2"><?= $anzahl ?> Artikel gefunden</div>
        <a href="?page=pizzas/create" class="btn btn-orange px-4 py-2 rounded shadow">
            <i class="fa-solid fa-plus me-2"></i> Neue Artikel
        </a>

        <!-- Kategorie-Filter -->
        <form method="get" class="d-inline-block me-2">
            <input type="hidden" name="page" value="admin/pizzas">
            <?php if ($statusFilter): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <?php endif; ?>
            <select name="category_id" class="form-select bg-dark text-light border border-orange rounded-3 px-3 py-2 shadow-sm" style="width: 200px;" onchange="this.form.submit()">
                <option value="">Alle Kategorien</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filterCategoryId == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Status-Filter -->
        <form method="get" class="d-inline-block me-2">
            <input type="hidden" name="page" value="admin/pizzas">
            <?php if ($filterCategoryId): ?>
                <input type="hidden" name="category_id" value="<?= $filterCategoryId ?>">
            <?php endif; ?>
            <select name="status" class="form-select bg-dark text-light border border-orange rounded-3 px-3 py-2 shadow-sm" style="width: 200px;" onchange="this.form.submit()">
                <option value="">Alle Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Nur aktiv</option>
                <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Nur inaktiv</option>
            </select>
        </form>
        <!-- Bulk-Aktionen -->
        <form method="post" id="bulkForm" class="d-inline-block">
            <button formaction="../controllers/pizzas/bulk_activate.php" type="submit" class="btn btn-outline-success px-3 py-2 rounded shadow" onclick="return confirm('Ausgewählte Artikel aktivieren?');">
                <i class="fa-solid fa-check me-1"></i> Aktivieren
            </button>
            <button formaction="../controllers/pizzas/bulk_deactivate.php" type="submit" class="btn btn-outline-warning px-3 py-2 rounded shadow" onclick="return confirm('Ausgewählte Artikel deaktivieren?');">
                <i class="fa-solid fa-ban me-1"></i> Deaktivieren
            </button>
            <button formaction="../controllers/pizzas/bulk_delete.php" type="submit" class="btn btn-outline-danger px-3 py-2 rounded shadow" onclick="return confirm('Ausgewählte Artikel löschen?');">
                <i class="fa-solid fa-trash me-1"></i> Löschen
            </button>
        </form>
    </div>

    <div class="table-scroll-limit table-responsive rounded shadow-sm scroll-orange" style="max-height: 520px; overflow-y: auto;">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="table-dark text-orange border-bottom border-orange">
            <tr>
                <th><div class="form-check d-flex justify-content-center">
                        <input class="form-check-input styled-checkbox" type="checkbox" onclick="toggleCheckboxes(this)">
                    </div>
                </th>
                <th scope="col">Bild</th>
                <th scope="col">
                    <a href="?page=admin/pizzas<?=
                    ($filterCategoryId ? '&category_id=' . $filterCategoryId : '') .
                    ($statusFilter ? '&status=' . $statusFilter : '') ?>&sort=name&direction=<?=
                    ($sort == 'name' && $direction == 'asc' ? 'desc' : 'asc') ?>" class="text-orange text-decoration-none">
                        Name <?= $sort == 'name' ? ($direction == 'asc' ? '↑' : '↓') : '' ?>
                    </a>
                </th>
                <th scope="col">Beschreibung</th>
                <th scope="col">Preis</th>
                <th scope="col">Status</th>
                <th scope="col">
                    <a href="?page=admin/pizzas<?=
                    ($filterCategoryId ? '&category_id=' . $filterCategoryId : '') .
                    ($statusFilter ? '&status=' . $statusFilter : '') ?>&sort=created_at&direction=<?=
                    ($sort == 'created_at' && $direction == 'asc' ? 'desc' : 'asc') ?>" class="text-orange text-decoration-none">
                        Erstellt <?= $sort == 'created_at' ? ($direction == 'asc' ? '↑' : '↓') : '' ?>
                    </a>
                </th>
                <th scope="col">Aktionen</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($pizzas as $pizza): ?>
                <tr>
                    <td>
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input styled-checkbox" type="checkbox" name="product_ids[]" value="<?= $pizza['id'] ?>" form="bulkForm">
                        </div>
                    </td>
                    <td>
                        <img src="<?= htmlspecialchars($pizza['image']) ?>" alt="Pizza" width="80" class="rounded shadow-sm">
                    </td>
                    <td class="fw-bold text-orange"><?= htmlspecialchars($pizza['name']) ?></td>
                    <td><?= htmlspecialchars($pizza['description']) ?></td>
                    <td><?= number_format($pizza['price'], 2, ',', '.') ?> €</td>
                    <td>
                        <?php if ($pizza['active']): ?>
                            <span class="badge bg-success">Aktiv</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inaktiv</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="text-white-50 small"><?= date('d.m.Y', strtotime($pizza['created_at'])) ?></span>
                    </td>
                    <td class="d-flex flex-column gap-2">
                        <a href="?page=pizzas/edit&id=<?= $pizza['id'] ?>" class="btn btn-sm btn-warning text-white w-100">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Bearbeiten
                        </a>
                        <form action="../controllers/pizzas/delete.php" method="post" onsubmit="return confirm('Pizza wirklich löschen?');">
                            <input type="hidden" name="id" value="<?= $pizza['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger w-100">
                                <i class="fa-solid fa-trash me-1"></i> Löschen
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleCheckboxes(source) {
        const checkboxes = document.querySelectorAll('input[name="product_ids[]"]');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }
</script>

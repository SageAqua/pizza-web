<?php
require_once __DIR__ . '/../../config/db.php';

if (!empty($_POST['product_ids']) && is_array($_POST['product_ids'])) {
    $ids = $_POST['product_ids'];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("UPDATE products SET active = 0 WHERE id IN ($in)");
    $stmt->execute($ids);
}

header('Location: ../../public/index.php?page=admin/pizzas');
exit;

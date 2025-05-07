<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/index.php?page=cart');
    exit;
}

$productId = $_POST['product_id'] ?? null;

if (!$productId) {
    header('Location: ../public/index.php?page=cart');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['session_id'] ?? null;

$stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE product_id = ? AND " . ($userId ? "user_id = ?" : "session_id = ?"));
$stmt->execute([$productId, $userId ?? $sessionId]);
$item = $stmt->fetch();

if ($item) {
    if ($item['quantity'] > 1) {
        $update = $pdo->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE id = ?");
        $update->execute([$item['id']]);
    } else {
        $delete = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
        $delete->execute([$item['id']]);
    }
}

unset($_SESSION['cart'][$productId]);

header('Location: /public/index.php?page=cart');
exit;

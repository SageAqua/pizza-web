<?php
session_start();
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = bin2hex(random_bytes(16));
}

require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/index.php?page=home');
    exit;
}

$productId = $_POST['product_id'] ?? null;

if (!$productId) {
    header('Location: ../../public/index.php?page=home');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ../../public/index.php?page=home');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['session_id'];

$checkStmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE product_id = ? AND " . ($userId ? "user_id = ?" : "session_id = ?"));
$checkStmt->execute([$productId, $userId ?? $sessionId]);
$existingItem = $checkStmt->fetch();

if ($existingItem) {
    $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?");
    $updateStmt->execute([$existingItem['id']]);
    $quantity = $existingItem['quantity'] + 1;
} else {
    $insertStmt = $pdo->prepare("INSERT INTO cart_items (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, 1)");
    $insertStmt->execute([$userId, $sessionId, $productId]);
    if ($insertStmt->rowCount() === 0) {
        die('INSERT hat nicht funktioniert!');
    }

    $quantity = 1;
}
$cartStmt = $pdo->prepare("SELECT ci.product_id, ci.quantity, p.name, p.image FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id 
    WHERE " . ($userId ? "ci.user_id = ?" : "ci.session_id = ?"));
$cartStmt->execute([$userId ?? $sessionId]);
$items = $cartStmt->fetchAll();

$cart = [];
foreach ($items as $item) {
    $cart[$item['product_id']] = [
        'name' => $item['name'],
        'image' => $item['image'],
        'quantity' => $item['quantity']
    ];
}
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'name' => $product['name'],
    'quantity' => $quantity,
    'cart' => $cart,
]);
exit;


<?php
require_once __DIR__ . '/../../config/db.php';

$id = $_POST['id'];
$name = $_POST['name'];
$desc = $_POST['description'];
$price = $_POST['price'];
$active = isset($_POST['active']) ? 1 : 0;

$imagePath = '';

if (!empty($_FILES['image']['tmp_name'])) {
    $uploadDir = '../../public/assets/img/';
    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $imagePath = 'public/assets/img/' . $filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image=?, active=? WHERE id=?");
    $stmt->execute([$name, $desc, $price, $imagePath, $active, $id]);
} else {
    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, active=? WHERE id=?");
    $stmt->execute([$name, $desc, $price, $active, $id]);
}

header("Location: ../../public/index.php?page=admin/pizzas");
exit;

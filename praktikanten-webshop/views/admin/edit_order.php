<?php
require_once __DIR__ . '/../../config/db.php';

// Bestellung abrufen
$orderId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bestellung aktualisieren
    $paid = isset($_POST['paid']) ? 1 : 0;
    $delivery = isset($_POST['delivery']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE orders SET paid = ?, delivery = ? WHERE id = ?");
    $stmt->execute([$paid, $delivery, $orderId]);

    // Weiterleitung nach der Aktualisierung
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bestellung bearbeiten</title>
</head>
<body>

<h1>Bestellung bearbeiten</h1>

<form method="post">
    <label for="paid">Bezahlt:</label>
    <input type="checkbox" id="paid" name="paid" <?= $order['paid'] ? 'checked' : '' ?>><br>

    <label for="delivery">Lieferung:</label>
    <input type="checkbox" id="delivery" name="delivery" <?= $order['delivery'] ? 'checked' : '' ?>><br>

    <button type="submit">Bestellung aktualisieren</button>
</form>

</body>
</html>




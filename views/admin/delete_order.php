<?php
require_once __DIR__ . '/../../config/db.php';

$orderId = $_GET['id'];

// Bestellung löschen
$stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
$stmt->execute([$orderId]);

// Weiterleitung nach dem Löschen
header("Location: orders.php");
exit();


session_start();

// Überprüfen, ob der Benutzer ein Admin ist
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Wenn der Benutzer kein Admin ist, weiterleiten
    header("Location: /index.php");
    exit();
}






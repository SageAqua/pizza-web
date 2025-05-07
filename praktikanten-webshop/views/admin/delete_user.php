<?php
session_start();

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /public/index.php?page=login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<div class='container text-center my-5 text-danger'>Ungültige Anfrage.</div>";
    exit();
}

$id = $_POST['id'] ?? null;

if (!$id) {
    echo "<div class='container text-center my-5 text-danger'>Keine Benutzer-ID übergeben.</div>";
    exit();
}

if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
    echo "<div class='container text-center my-5 text-warning'>
            <h3>❌ Du kannst dich nicht selbst löschen!</h3>
            <a href='/public/index.php?page=admin/users' class='btn btn-outline-light mt-3'>Zurück</a>
          </div>";
    exit();
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header('Location: /public/index.php?page=admin/users');
exit();

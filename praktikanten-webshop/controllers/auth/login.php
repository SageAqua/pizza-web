<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Bitte fülle alle Felder aus.']);
    exit;
}

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Benutzer nicht gefunden.']);
    exit;
}

if (!password_verify($password, $user['passwort'])) {
    header('Location: ../../public/index.php?page=pizzas');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['rolle']; // z. B. kunde, admin etc.

if ($user['rolle'] === 'admin') {
    echo json_encode(['success' => true, 'redirect' => 'index.php?page=home']);
} else {
    echo json_encode(['success' => true, 'redirect' => 'index.php?page=pizzas']);
}
exit;

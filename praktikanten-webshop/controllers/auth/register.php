<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (empty($firstname) || empty($lastname) || empty($email) || empty($address) || empty($password) || empty($password_confirm)) {
    echo json_encode(['success' => false, 'message' => 'Bitte fülle alle Felder aus.']);
    exit;
}

if ($password !== $password_confirm) {
    echo json_encode(['success' => false, 'message' => 'Die Passwörter stimmen nicht überein.']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Passwort muss mindestens 8 Zeichen lang sein.']);
    exit;
}

if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Passwort muss mindestens einen Großbuchstaben enthalten.']);
    exit;
}

if (!preg_match('/[a-z]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Passwort muss mindestens einen Kleinbuchstaben enthalten.']);
    exit;
}

if (!preg_match('/[!._]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Passwort muss mindestens eines der folgenden Sonderzeichen enthalten: ! . _']);
    exit;
}

if (preg_match('/[^a-zA-Z0-9!._]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Passwort enthält ungültige Zeichen. Erlaubt sind nur Buchstaben, Zahlen und folgende Sonderzeichen: ! . _']);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (vorname, nachname, email, adresse, passwort, rolle)
        VALUES (?, ?, ?, ?, ?, 'user')";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$firstname, $lastname, $email, $address, $password_hash]);
    echo json_encode(['success' => true]);
    exit;
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern: ' . $e->getMessage()]);
    exit;
}

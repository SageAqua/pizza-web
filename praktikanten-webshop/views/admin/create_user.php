<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../layout/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /public/index.php?page=login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vorname   = $_POST['vorname'];
    $nachname  = $_POST['nachname'];
    $email     = $_POST['email'];
    $rolle     = $_POST['rolle'];
    $passwort  = password_hash($_POST['passwort'], PASSWORD_DEFAULT);

    $full_name = $_POST['full_name'];
    $street    = $_POST['street'];
    $zip       = $_POST['zip'];
    $city      = $_POST['city'];
    $country   = $_POST['country'];

    $stmt = $pdo->prepare("INSERT INTO users (vorname, nachname, email, passwort, rolle) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$vorname, $nachname, $email, $passwort, $rolle]);

    $user_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, full_name, street, zip, city, country) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $full_name, $street, $zip, $city, $country]);

    header('Location: /public/index.php?page=admin/users');
    exit();
}
?>

<div class="container my-5">
    <h2 class="text-center mb-4" style="color: #FF6600;">➕ Neuen Benutzer anlegen</h2>

    <form method="post" class="p-4 bg-dark text-white rounded shadow-sm mx-auto" style="max-width: 700px;">
        <div class="row">
            <div class="mb-3 col-md-6">
                <label for="vorname" class="form-label">Vorname</label>
                <input type="text" name="vorname" id="vorname" class="form-control" required>
            </div>
            <div class="mb-3 col-md-6">
                <label for="nachname" class="form-label">Nachname</label>
                <input type="text" name="nachname" id="nachname" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-Mail</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <div class="row">
            <div class="mb-3 col-md-6">
                <label for="rolle" class="form-label">Rolle</label>
                <select name="rolle" id="rolle" class="form-select" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-3 col-md-6">
                <label for="passwort" class="form-label">Passwort</label>
                <input type="password" name="passwort" id="passwort" class="form-control" required>
            </div>
        </div>

        <hr class="my-4 text-secondary">

        <h4 class="text-orange mb-3">Adresse</h4>

        <div class="mb-3">
            <label for="full_name" class="form-label">Vollständiger Name</label>
            <input type="text" name="full_name" id="full_name" class="form-control">
        </div>

        <div class="mb-3">
            <label for="street" class="form-label">Straße</label>
            <input type="text" name="street" id="street" class="form-control">
        </div>

        <div class="row">
            <div class="mb-3 col-md-4">
                <label for="zip" class="form-label">PLZ</label>
                <input type="text" name="zip" id="zip" class="form-control">
            </div>
            <div class="mb-3 col-md-4">
                <label for="city" class="form-label">Stadt</label>
                <input type="text" name="city" id="city" class="form-control">
            </div>
            <div class="mb-3 col-md-4">
                <label for="country" class="form-label">Land</label>
                <input type="text" name="country" id="country" class="form-control">
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="/public/index.php?page=admin/users" class="btn btn-outline-light">
                <i class="fa-solid fa-arrow-left me-1"></i> Zurück
            </a>
            <button type="submit" class="btn btn-orange">
                <i class="fa-solid fa-user-plus me-1"></i> Benutzer speichern
            </button>
        </div>
    </form>
</div>


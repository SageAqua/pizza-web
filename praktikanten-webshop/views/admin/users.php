<?php

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /public/index.php?page=login');
    exit();
}

$stmt = $pdo->query("SELECT id, vorname AS username, email, rolle AS role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="?page=home" class="btn btn-outline-light">
            <i class="fa-solid fa-arrow-left me-1"></i> Zurück zur Übersicht
        </a>
        <h2 class="text-center flex-grow-1 mb-0" style="color: #FF6600;">👥 Benutzerverwaltung</h2>
        <div style="width: 170px;"></div> <!-- Platzhalter für Zentrierung -->
    </div>
    <div class="text-end mb-4">
        <a href="?page=admin/create_user" class="btn btn-orange px-4 py-2 rounded shadow">
            <i class="fa-solid fa-user-plus me-2"></i> Neuen Benutzer
        </a>
    </div>
    <div class="table-scroll-limit table-responsive rounded shadow-sm scroll-orange" style="max-height: 520px; overflow-y: auto;">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead class="table-dark text-orange border-bottom border-orange">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Benutzername</th>
                <th scope="col">Email</th>
                <th scope="col">Rolle</th>
                <th scope="col">Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td class="fw-bold text-orange"><?= htmlspecialchars($user['username']) ?></td>
                    <td ><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge bg-danger">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">User</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-flex flex-column gap-2">
                        <a href="?page=admin/edit_user&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning text-white w-100">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Bearbeiten
                        </a>
                        <form action="?page=admin/delete_user" method="post" onsubmit="return confirm('Benutzer wirklich löschen?');" class="d-inline">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger w-100 mt-1">
                                <i class="fa-solid fa-trash me-1"></i> Löschen
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


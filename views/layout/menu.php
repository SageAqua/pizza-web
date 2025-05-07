<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';

$categoryStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$menuCategories = $categoryStmt->fetchAll();

$pdo->prepare("DELETE FROM cart_items WHERE user_id IS NULL AND created_at < NOW() - INTERVAL 1 DAY")->execute();
$cartCount = 0;

$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['session_id'] ?? null;
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

$stmt = $pdo->prepare("SELECT SUM(quantity) AS total FROM cart_items WHERE " . ($userId ? "user_id = ?" : "session_id = ?"));
$stmt->execute([$userId ?? $sessionId]);
$result = $stmt->fetch();

$cartCount = $result['total'] ?? 0;

?>


<nav class="navbar navbar-expand-lg px-3 fixed-top"> <a class="navbar-brand" href="?page=home">
        <?= $isAdmin ? '🛠 Admin Dashboard' : '🍕 Praktikanten-Webshop' ?>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse d-none d-lg-block">
        <ul class="navbar-nav ms-auto">
            <?php if (!$isAdmin): ?>
                <li class="nav-item position-relative">
                    <a class="nav-link position-relative" href="?page=cart" id="cartLink">
                        <i class="fa-solid fa-cart-shopping me-1"></i> Warenkorb
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="<?= $cartCount == 0 ? 'display: none;' : '' ?>">
                            <?= $cartCount ?>
                        </span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (!$isAdmin): ?>
                <?php if (!$isAdmin): ?>
                    <?php foreach ($menuCategories as $cat): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=category&id=<?= $cat['id'] ?>">
                                <i class="fa-solid fa-concierge-bell me-1"></i> <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!$isAdmin): ?>
                    <li class="nav-item"><a class="nav-link" href="?page=profile"><i class="fa-solid fa-user me-2"></i> Profil</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="../controllers/auth/logout.php">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="?page=register"><i class="fa-solid fa-user-plus"></i> Registrierung</a></li>
                <li class="nav-item"><a class="nav-link" href="?page=login"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
            <?php endif; ?>
        </ul>

    </div>
</nav>
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel" style="width: 260px;">
    <div class="offcanvas-header">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body px-2">
        <ul class="nav flex-column">
            <?php if (!$isAdmin): ?>
                <li class="nav-item position-relative">
                    <a class="nav-link position-relative" href="?page=cart" id="cartLinkMobile">
                        <i class="fa-solid fa-cart-shopping me-2"></i> Warenkorb
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="<?= $cartCount == 0 ? 'display: none;' : '' ?>">
                                <?= $cartCount ?>
                        </span>
                    </a>
                </li>
            <?php endif; ?>


            <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=home">
                        <i class="fa-solid fa-gear me-2"></i> Admin Dashboard
                    </a>
                </li>
            <?php endif; ?>


            <?php if (!$isAdmin): ?>
                <li class="nav-item">
                <?php if (!$isAdmin): ?>
                    <?php foreach ($menuCategories as $cat): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=category&id=<?= $cat['id'] ?>">
                                <i class="fa-solid fa-concierge-bell me-2"></i> <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>


            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!$isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=profile">
                            <i class="fa-solid fa-user me-2"></i> Profil
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=logout">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="?page=register">
                        <i class="fa-solid fa-user-plus me-2"></i> Registrierung
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=login">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Login
                    </a>
                </li>
            <?php endif; ?>


        </ul>
    </div>
</div>


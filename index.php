<?php
session_start();
require_once '../config/db.php';

$page = $_GET['page'] ?? 'home';

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function show403() {
    http_response_code(403);
    echo "<section class='container my-5'>";
    echo "<h2 class='text-center' style='color: #FF6600;'>403 – Zugriff verweigert</h2>";
    echo "<p class='text-center' style='color: white;'>Du hast keine Berechtigung, diese Seite zu betreten.</p>";
    echo "</section>";
    include '../views/layout/footer.php';
    exit;
}

include '../views/layout/header.php';
include '../views/layout/menu.php';

switch ($page) {
    case 'home':
        include '../views/home.php';
        break;

    case 'pizzas':
        include '../views/pizzas/list.php';
        break;

    case 'pizzas/detail':
        include '../views/pizzas/detail.php';
        break;

    case 'register':
        include '../views/auth/register.php';
        break;

    case 'login':
        include '../views/auth/login.php';
        break;

    case 'profile':
        include '../views/users/profile.php';
        break;

    case 'logout':
        require '../controllers/auth/logout.php';
        exit;

    case 'cart':
        include '../views/cart/view.php';
        break;

    case 'add-to-cart':
        include '../controllers/cart/add.php';
        break;

    case 'remove-from-cart':
        include '../controllers/cart/remove.php';
        break;

    case 'checkout':
        include '../controllers/cart/checkout.php';
        break;

    // Admin-Bereich
    case 'admin/categories':
        if (!isAdmin()) show403();
        include '../views/admin/categories.php';
        break;

    case 'admin/pizzas':
        if (!isAdmin()) show403();
        include '../views/pizzas/admin_list.php';
        break;

    case 'pizzas/create':
        if (!isAdmin()) show403();
        include '../views/pizzas/form.php';
        break;

    case 'pizzas/edit':
        if (!isAdmin()) show403();
        include '../views/pizzas/form.php';
        break;

    case 'admin/users':
        if (!isAdmin()) show403();
        include '../views/admin/users.php';
        break;

    case 'admin/edit_user':
        if (!isAdmin()) show403();
        include '../views/admin/edit_user.php';
        break;

    case 'admin/delete_user':
        if (!isAdmin()) show403();
        include '../views/admin/delete_user.php';
        break;

    case 'admin/create_user':
        if (!isAdmin()) show403();
        include '../views/admin/create_user.php';
        break;

    case 'admin/orders':
        if (!isAdmin()) show403();
        include '../views/admin/orders.php';
        break;

    case 'category':
        include '../views/pizzas/category.php';
        break;

    case 'order_details':
        include '../views/users/order_details.php';
        break;

    default:
        echo "<section class='container my-5'>";
        echo "<h2 class='text-center' style='color: #FF6600;'>404 – Seite nicht gefunden</h2>";
        echo "</section>";
        break;
}

include '../views/layout/footer.php';

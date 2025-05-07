<?php
require_once __DIR__ . '/../../config/db.php';

if (isset($_GET['generate'])) {

    if ($_GET['generate'] == 'products') {
        $categoryId = 1;
        $count = 500;
        $defaultName = 'pizza';
    } elseif ($_GET['generate'] == 'drinks') {
        $categoryId = 3;
        $count = 250;
        $defaultName = 'drink';
    } elseif ($_GET['generate'] == 'salads') {
        $categoryId = 7;
        $count = 250;
        $defaultName = 'salad';
    } else {
        die("❌ Fehler: Unbekannter Typ.");
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE category_id = ? AND name LIKE '%#%'");
    $stmt->execute([$categoryId]);

    if ($defaultName === 'pizza') {
        $names = ["Margherita", "Salami", "Hawaii", "Funghi", "Prosciutto", "BBQ Chicken", "Tuna", "Veggie", "Diavolo", "Quattro Formaggi"];
        $descriptions = [
            "Leckere Pizza mit frischen Zutaten.",
            "Unser Klassiker, beliebt bei Jung und Alt.",
            "Fruchtige Ananas auf herzhaftem Schinken.",
            "Frische Champignons auf Tomatensauce.",
            "Herzhafter Schinken trifft auf zarten Käse."
        ];
    } elseif ($defaultName === 'drink') {
        $names = ["Cola", "Fanta", "Sprite", "Eistee", "Wasser", "Orangensaft", "Apfelsaft", "Energy Drink", "Limo", "Tonic Water"];
        $descriptions = [
            "Erfrischendes Getränk für heiße Tage.",
            "Klassisches Softdrink-Highlight.",
            "Eiskalt serviert ein Genuss.",
            "Fruchtige Erfrischung pur.",
            "Spritziger Geschmack für jeden Moment."
        ];
    } elseif ($defaultName === 'salad') {
        $names = ["Caesar Salad", "Griechischer Salat", "Italienischer Salat", "Garten Salat", "Nizza Salat", "Bunter Salat", "Tomaten Mozzarella", "Rucola Salat", "Krautsalat", "Kartoffelsalat"];
        $descriptions = [
            "Frisch und knackig, ideal für den Sommer.",
            "Mit frischem Gemüse und feinem Dressing.",
            "Vitaminreicher Genuss für jeden Tag.",
            "Leichte Mahlzeit voller Geschmack.",
            "Klassiker unter den Salaten, einfach lecker."
        ];
    }

    $uploadDir = '../../public/assets/img/';
    $templateImagePath = $uploadDir . 'template_' . $defaultName . '.jpg'; // Nur 1 Bild benutzen!

    for ($i = 1; $i <= $count; $i++) {
        $name = $names[array_rand($names)] . " #" . $i;
        $description = $descriptions[array_rand($descriptions)];
        $price = rand(300, 900) / 100;
        $active = 1;

        $imagePath = $templateImagePath; // Immer dasselbe Bild benutzen

        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, image, active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$categoryId, $name, $description, $price, $imagePath, $active]);
    }

    echo "✅ $count neue $defaultName Produkte erfolgreich generiert!";
    exit;
}

$categoryId = $_POST['category_id'] ?? null;
$name = $_POST['name'] ?? '';
$desc = $_POST['description'] ?? '';
$price = $_POST['price'] ?? 0;
$active = isset($_POST['active']) ? 1 : 0;

$imagePath = '';

if (!empty($_FILES['image']['tmp_name'])) {
    $uploadDir = '../../public/assets/img/';
    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
    $imagePath = $uploadDir . $filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
}

if (empty($imagePath)) {
    $imagePath = '../../public/assets/img/default_pizza.jpg';
}

$stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, image, active) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$categoryId, $name, $desc, $price, $imagePath, $active]);

header("Location: ../../public/index.php?page=admin/pizzas");
exit;
?>

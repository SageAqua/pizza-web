<?php
require_once __DIR__ . '/../../config/db.php';

function randomFirstName() {
    $first = ['Max', 'Anna', 'Lukas', 'Lea', 'Tim', 'Laura', 'Jan', 'Mia', 'Ben', 'Sara'];
    return $first[array_rand($first)];
}

function randomLastName() {
    $last = ['Müller', 'Schmidt', 'Schneider', 'Fischer', 'Weber', 'Hoffmann', 'Wagner', 'Becker', 'Koch', 'Richter'];
    return $last[array_rand($last)];
}

function randomEmail($firstName, $lastName) {
    $base = strtolower($firstName . '.' . $lastName);
    return $base . rand(1000,9999) . '@example.com';
}

// ❗ Alte Dummy-User löschen
$stmt = $pdo->query("
    SELECT id FROM users WHERE email LIKE '%@example.com'
");
$dummyUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Lösche alle Bestellungen dieser Dummy-User
if (!empty($dummyUsers)) {
    // Lösche order_items -> orders -> users
    $inQuery = implode(',', array_fill(0, count($dummyUsers), '?'));

    // order_items löschen (über orders)
    $stmt = $pdo->prepare("
        DELETE oi FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id IN ($inQuery)
    ");
    $stmt->execute($dummyUsers);

    // orders löschen
    $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id IN ($inQuery)");
    $stmt->execute($dummyUsers);

    // users löschen
    $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($inQuery)");
    $stmt->execute($dummyUsers);
}

// ❗ Neue Dummy-User + Bestellungen generieren
for ($i = 0; $i < 100; $i++) {
    $firstName = randomFirstName();
    $lastName = randomLastName();
    $email = randomEmail($firstName, $lastName);
    $password = password_hash('Test1234.', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (vorname, nachname, email, passwort, rolle) VALUES (?, ?, ?, ?, 'user')");
    $stmt->execute([$firstName, $lastName, $email, $password]);
    $userId = $pdo->lastInsertId();

    $orderCount = rand(1, 3);
    for ($j = 0; $j < $orderCount; $j++) {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, status, total, created_at) VALUES (?, 'bezahlt', 0, NOW())");
        $stmt->execute([$userId]);
        $orderId = $pdo->lastInsertId();

        $orderTotal = 0;
        $itemCount = rand(1, 5);
        for ($k = 0; $k < $itemCount; $k++) {
            $productStmt = $pdo->query("SELECT id, price FROM products WHERE active = 1 ORDER BY RAND() LIMIT 1");
            $product = $productStmt->fetch();
            if ($product) {
                $quantity = rand(1, 3);
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $product['id'], $quantity, $product['price']]);
                $orderTotal += $product['price'] * $quantity;
            }
        }

        // Update order total
        $stmt = $pdo->prepare("UPDATE orders SET total = ? WHERE id = ?");
        $stmt->execute([$orderTotal, $orderId]);
    }
}

echo "✅ Alte Dummy-Daten gelöscht und 100 neue Dummy-Benutzer und Bestellungen erfolgreich generiert!";

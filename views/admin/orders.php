<?php
require_once __DIR__ . '/../../config/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $updateStmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $updateStmt->execute([
        ':status' => $_POST['new_status'],
        ':id'     => $_POST['order_id']
    ]);
}

$dateFilter = isset($_GET['date']) ? $_GET['date'] : null;
$sql = "SELECT * FROM orders";
if ($dateFilter) {
    $sql .= " WHERE created_at >= :dateFilter";
    $dateFilter .= ' 00:00:00'; // Tagesbeginn
}
$stmt = $pdo->prepare($sql);
if (isset($dateFilter)) {
    $stmt->bindValue(':dateFilter', $dateFilter);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Bestellungen verwalten</h1>

<table>
    <tr>
        <th>ID</th>
        <th>User-ID</th>
        <th>Adress-ID</th>
        <th>Status</th>
        <th>Datum</th>
        <th>Aktion</th>
    </tr>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $row): ?>
            <?php

            $statusColor = match ($row['status']) {
                'bezahlt' => 'green',
                'offen' => 'red',
                'storniert' => 'gray',
                default => 'black'
            };
            ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['address_id']) ?></td>
                <td>
                    <!-- Status mit dynamischer Hintergrundfarbe -->
                    <span style="background-color: <?= $statusColor ?>; color: white; padding: 4px 8px; border-radius: 4px;">
                        <?= htmlspecialchars($row['status']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>

                    <form method="post" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                        <select name="new_status">
                            <option value="offen" <?= $row['status'] === 'offen' ? 'selected' : '' ?>>offen</option>
                            <option value="bezahlt" <?= $row['status'] === 'bezahlt' ? 'selected' : '' ?>>bezahlt</option>
                            <option value="storniert" <?= $row['status'] === 'storniert' ? 'selected' : '' ?>>storniert</option>
                        </select>
                        <button type="submit">Ändern</button>
                    </form>


                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete" onclick="return confirm('Sind Sie sicher, dass Sie diese Bestellung löschen möchten?')">Löschen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6">Keine Bestellungen gefunden.</td>
        </tr>
    <?php endif; ?>
</table>



<?php

if (isset($_POST['delete']) && isset($_POST['delete_id'])) {

    $deleteStmt = $pdo->prepare("DELETE FROM orders WHERE id = :id");
    $deleteStmt->execute([
        ':id' => $_POST['delete_id']
    ]);


    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>


<style>
    body {
        font-family: Arial, sans-serif;
    }

    h1, h2 {
        text-align: center;
        color: #FF6600;
    }

    table {
        border-collapse: collapse;
        width: 90%;
        margin: 20px auto;
    }

    th, td {
        border: 1px solid #aaa;
        padding: 8px;
        text-align: center;
    }

    th {
        background-color: #ddd;
    }

    form {
        text-align: center;
        margin-bottom: 20px;
    }

    select, button {
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
    }

    button {
        background-color: #FF6600;
        color: white;
        cursor: pointer;
    }

    button:hover {
        background-color: #e65c00;
    }

    footer {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
        color: #888;
    }
</style>

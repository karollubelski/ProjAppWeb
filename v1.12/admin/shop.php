<?php
// Startujemy sesję
session_start();
require '../cfg.php';

// Połączenie z bazą danych
$dsn = 'mysql:host=localhost;dbname=moja_strona;charset=utf8mb4';
$user = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

// Funkcja do dodawania produktów do koszyka
function addToCart($id, $quantity) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = :id AND dostepnosc = 'dostepny'");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Produkt niedostępny.";
        return;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $id) {
            $item['quantity'] += $quantity;
            return;
        }
    }

    $_SESSION['cart'][] = [
        'id' => $product['id'],
        'name' => $product['tytul'],
        'price' => $product['cena_netto'],
        'vat' => $product['vat'],
        'quantity' => $quantity
    ];
}

// Funkcja do usuwania produktów z koszyka
function removeFromCart($id) {
    if (!isset($_SESSION['cart'])) return;

    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] === $id) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            return;
        }
    }
}

// Funkcja do edycji ilości produktów
function updateQuantity($id, $quantity) {
    if (!isset($_SESSION['cart'])) return;

    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $id) {
            $item['quantity'] = $quantity;
            if ($item['quantity'] <= 0) {
                removeFromCart($id);
            }
            return;
        }
    }
}

// Funkcja do wyświetlania koszyka
function showCart() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo "<p>Koszyk jest pusty.</p>";
        return;
    }

    $total = 0;
    echo "<table border='1'>";
    echo "<tr><th>Produkt</th><th>Cena netto</th><th>VAT</th><th>Ilość</th><th>Wartość brutto</th><th>Akcje</th></tr>";

    foreach ($_SESSION['cart'] as $item) {
        $brutto = ($item['price'] * (1 + $item['vat'] / 100)) * $item['quantity'];
        $total += $brutto;

        echo "<tr>";
        echo "<td>{$item['name']}</td>";
        echo "<td>{$item['price']} PLN</td>";
        echo "<td>{$item['vat']}%</td>";
        echo "<td>{$item['quantity']}</td>";
        echo "<td>" . number_format($brutto, 2) . " PLN</td>";
        echo "<td>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='remove_id' value='{$item['id']}'>";
        echo "<button type='submit'>Usuń</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }

    echo "<tr><td colspan='4'><strong>Total</strong></td><td>" . number_format($total, 2) . " PLN</td><td></td></tr>";
    echo "</table>";
}

// Pobranie kategorii
function getCategories() {
    global $pdo;

    $stmt = $pdo->query("SELECT * FROM kategorie");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pobranie produktów dla wybranej kategorii
function getProducts($categoryId = null) {
    global $pdo;

    if ($categoryId) {
        $stmt = $pdo->prepare("SELECT * FROM produkty WHERE kategoria_id = :categoryId AND dostepnosc = 'dostepny'");
        $stmt->execute(['categoryId' => $categoryId]);
    } else {
        $stmt = $pdo->query("SELECT * FROM produkty WHERE dostepnosc = 'dostepny'");
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obsługa formularzy
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_id'])) {
        addToCart($_POST['add_id'], $_POST['quantity']);
    }

    if (isset($_POST['remove_id'])) {
        removeFromCart($_POST['remove_id']);
    }

    if (isset($_POST['update_id'])) {
        updateQuantity($_POST['update_id'], $_POST['quantity']);
    }
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Sklep internetowy</title>
</head>
<body>
    <h1 style="text-align: center;">Sklep z elektroniką</h1>

    <div style="display: flex;">
        <div style="width: 20%;">
            <h2>Kategorie</h2>
            <ul>
                <li><a href="?">Wszystkie</a></li>
                <?php foreach (getCategories() as $category): ?>
                    <li><a href="?category=<?= $category['id'] ?>"><?= $category['nazwa'] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="width: 60%;">
            <h2>Produkty</h2>
            <?php $products = getProducts($_GET['category'] ?? null); ?>
            <?php if ($products): ?>
                <ul>
                    <?php foreach ($products as $product): ?>
                        <li>
                            <img src="../img/<?= $product['zdjecie'] ?>" alt="<?= $product['tytul'] ?>" style="width: 100px; height: auto;">
                            <strong><?= $product['tytul'] ?></strong> - <?= $product['cena_netto'] ?> PLN netto
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="add_id" value="<?= $product['id'] ?>">
                                <input type="number" name="quantity" value="1" min="1" style="width: 50px;">
                                <button type="submit">Dodaj do koszyka</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Brak produktów w tej kategorii.</p>
            <?php endif; ?>
        </div>

        <div style="width: 20%;">
            <h2>Koszyk</h2>
            <?php showCart(); ?>
        </div>
    </div>
</body>
</html>

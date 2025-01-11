<?php
//logowanie przed wejsciem do sklepu
require_once 'auth.php';
requireLogin();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moja_strona";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Funkcja do obliczania ceny brutto
function cenaBrutto($cenaNetto, $vat) {
    return $cenaNetto * (1 + $vat / 100);
}

// session_start();


// Inicjalizacja koszyka
if (!isset($_SESSION['koszyk'])) {
    $_SESSION['koszyk'] = [];
    initCart(); // Wczytaj koszyk z bazy danych
}

// Funkcja inicjalizacji koszyka z bazy danych
function initCart() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return;

    try {
        $stmt = $pdo->prepare("
            SELECT ci.product_id, ci.quantity, p.* 
            FROM cart_items ci 
            JOIN produkty p ON ci.product_id = p.id 
            WHERE ci.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $_SESSION['koszyk'] = [];
        foreach ($cartItems as $item) {
            $_SESSION['koszyk'][$item['product_id']] = [
                'id' => $item['product_id'],
                'tytul' => $item['tytul'],
                'cena_netto' => $item['cena_netto'],
                'vat' => $item['vat'],
                'ilosc' => $item['quantity'],
                'zdjecie' => $item['zdjecie']
            ];
        }
    } catch (PDOException $e) {
        error_log("Error initializing cart: " . $e->getMessage());
    }
}



// Funkcja dodawania do koszyka
function addToCart($produktId, $ilosc) {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return;

    try {
        // Sprawdź czy produkt już jest w koszyku w bazie danych
        $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'product_id' => $produktId
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Aktualizuj ilość w bazie danych
            $stmt = $pdo->prepare("
                UPDATE cart_items 
                SET quantity = quantity + :quantity 
                WHERE user_id = :user_id AND product_id = :product_id
            ");
        } else {
            // Dodaj nowy produkt do bazy danych
            $stmt = $pdo->prepare("
                INSERT INTO cart_items (user_id, product_id, quantity) 
                VALUES (:user_id, :product_id, :quantity)
            ");
        }

        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'product_id' => $produktId,
            'quantity' => $ilosc
        ]);

        // Aktualizuj sesję
        if (isset($_SESSION['koszyk'][$produktId])) {
            $_SESSION['koszyk'][$produktId]['ilosc'] += $ilosc;
        } else {
            // Pobierz dane produktu z bazy
            $stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = :produktId");
            $stmt->execute(['produktId' => $produktId]);
            $produkt = $stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION['koszyk'][$produktId] = [
                'id' => $produkt['id'],
                'tytul' => $produkt['tytul'],
                'cena_netto' => $produkt['cena_netto'],
                'vat' => $produkt['vat'],
                'ilosc' => $ilosc,
                'zdjecie' => $produkt['zdjecie']
            ];
        }
    } catch (PDOException $e) {
        error_log("Error adding to cart: " . $e->getMessage());
    }
}

// Funkcja usuwania z koszyka
function removeFromCart($produktId) {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return;

    try {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'product_id' => $produktId
        ]);

        unset($_SESSION['koszyk'][$produktId]);
    } catch (PDOException $e) {
        error_log("Error removing from cart: " . $e->getMessage());
    }
}

// Funkcja opróżniania koszyka
function clearCart() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return;

    try {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);

        $_SESSION['koszyk'] = [];
    } catch (PDOException $e) {
        error_log("Error clearing cart: " . $e->getMessage());
    }
}

// Funkcja aktualizacji ilości produktów
function updateCartQuantity($produktId, $nowaIlosc) {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return;

    try {
        if ($nowaIlosc <= 0) {
            removeFromCart($produktId);
            return;
        }

        $stmt = $pdo->prepare("
            UPDATE cart_items 
            SET quantity = :quantity 
            WHERE user_id = :user_id AND product_id = :product_id
        ");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'product_id' => $produktId,
            'quantity' => $nowaIlosc
        ]);

        $_SESSION['koszyk'][$produktId]['ilosc'] = $nowaIlosc;
    } catch (PDOException $e) {
        error_log("Error updating cart quantity: " . $e->getMessage());
    }
}

// // Funkcja usuwania z koszyka
// function removeFromCart($produktId) {
//     unset($_SESSION['koszyk'][$produktId]);
// }

// // Funkcja opróżniania koszyka
// function clearCart() {
//     $_SESSION['koszyk'] = [];
// }


// Funkcja pobierania zawartości koszyka
function getCartItems() {
    return $_SESSION['koszyk'];
}


// Funkcja obliczania sumy koszyka
function calculateTotal() {
    $suma = 0;
    foreach ($_SESSION['koszyk'] as $produkt) {
        $suma += cenaBrutto($produkt['cena_netto'], $produkt['vat']) * $produkt['ilosc'];
    }
    return $suma;
}


function buildCategoryTree($categories, $parentId = 0) {
    $branch = [];
    
    foreach ($categories as $category) {
        if ($category['matka'] == $parentId) {
            $children = buildCategoryTree($categories, $category['id']);
            if ($children) {
                $category['subcategories'] = $children;
            }
            $branch[] = $category;
        }
    }
    
    return $branch;
}

function getCategories() {
    global $pdo;
    
    try {
        // Pobierz wszystkie kategorie
        $stmt = $pdo->query("SELECT * FROM kategorie ORDER BY matka, nazwa");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Zbuduj drzewo kategorii
        return buildCategoryTree($categories);
        
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

function renderCategory($category) {
    $html = '<li class="main-category">';
    $html .= '<div class="category-header">';
    
    if (isset($category['subcategories']) && !empty($category['subcategories'])) {
        $html .= '<button class="toggle-subcategories" onclick="toggleSubcategories(this)">';
        $html .= '<svg class="arrow-icon" viewBox="0 0 24 24" width="18" height="18">';
        $html .= '<path d="M9 18l6-6-6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        $html .= '</svg>';
        $html .= '</button>';
    }
    
    $html .= '<a href="?category=' . $category['id'] . '">' . htmlspecialchars($category['nazwa']) . '</a>';
    $html .= '</div>';
    
    if (isset($category['subcategories']) && !empty($category['subcategories'])) {
        $html .= '<ul class="subcategories">';
        foreach ($category['subcategories'] as $subCategory) {
            $html .= renderCategory($subCategory);
        }
        $html .= '</ul>';
    }
    
    $html .= '</li>';
    return $html;
}

function getAllSubcategoryIds($categoryId) {
    global $pdo;
    $ids = [$categoryId];
    
    $stmt = $pdo->prepare("SELECT id FROM kategorie WHERE matka = :categoryId");
    $stmt->execute(['categoryId' => $categoryId]);
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($subcategories as $subcategory) {
        $ids = array_merge($ids, getAllSubcategoryIds($subcategory['id']));
    }
    
    return $ids;
}


// Pobranie produktów dla wybranej kategorii
function getProducts($categoryId = null) {
    global $pdo;
    if ($categoryId) {
        // Pobierz ID wszystkich podkategorii
        $categoryIds = getAllSubcategoryIds($categoryId);
        $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
        
        $sql = "SELECT * FROM produkty WHERE kategoria_id IN ($placeholders) AND dostepnosc = 'dostepny'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($categoryIds);
    } else {
        $stmt = $pdo->query("SELECT * FROM produkty WHERE dostepnosc = 'dostepny'");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Filtrowanie po kategorii
$kategoriaId = isset($_GET['category']) ? $_GET['category'] : null;
$produkty = getProducts($kategoriaId);

// Obsługa żądań
if (isset($_POST['dodaj_do_koszyka'])) {
    addToCart($_POST['produkt_id'], $_POST['ilosc']);
}

if (isset($_GET['usun_z_koszyka'])) {
    removeFromCart($_GET['usun_z_koszyka']);
}

if (isset($_POST['aktualizuj_ilosc'])) {
    $produktId = $_POST['produkt_id'];
    $nowaIlosc = $_POST['ilosc'];
    updateCartQuantity($produktId, $nowaIlosc);
}

if (isset($_POST['oproznikoszyk'])) {
    clearCart();
}

if (isset($_GET['logout'])) {
    logout();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Sklep</title>
    <!-- <style></style> -->
    <link rel="stylesheet" href="../css/shop.css">
    <script src="../js/cart.js"></script>
    <script src="../js/menu.js" type="text/javascript"></script>
    <script src="../js/categories.js"></script>
    <script src="../js/dataModal.js"></script>
</head>
<body>
    <div class="container">
        <h1>Amazon't</h1>
        <!-- <img src="../img/image.png" alt="Logo" class="logo"> -->

        <!-- Panel wyświetlania kategorii -->
        <div class="kategorie">
            <h2>Kategorie</h2>
            <ul class="category-menu">
                <li><a href="?">Wszystkie</a></li>
                <?php 
                $categories = getCategories();
                foreach ($categories as $category) {
                    echo renderCategory($category);
                }
                ?>
            </ul>
        </div>

        <div class="produkty">
            <?php if (empty($produkty)): ?>
                <p>Brak produktów w tej kategorii.</p>
            <?php else: ?>
                <?php foreach($produkty as $row): ?>
                    <div class="produkt">
                        <h3><?php echo $row['tytul']; ?></h3>
                        <img src="<?php echo $row['zdjecie']; ?>" alt="<?php echo $row['tytul']; ?>" width="100">
                        <p><?php echo $row['opis']; ?></p>
                        <p>Cena brutto: <?php echo cenaBrutto($row['cena_netto'], $row['vat']); ?> zł</p>
                        <form method="post" action="">
                            <input type="hidden" name="produkt_id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="ilosc" value="1" min="1">
                            <button type="submit" name="dodaj_do_koszyka">Dodaj do koszyka</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
    </div>


    <!-- Nawigacja użytkownika -->
    <div class="user-panel">
        <div class="user-button" onclick="toggleUserMenu()">
            <img src="../img/user.png" alt="User" class="user-icon">
            <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <div class="user-menu" id="userMenu">
                <!-- panel zamówień  -->
                <a href="../shop/orders_panel.php">Zamówienia</a>
                <!-- panel zmiany hasla -->
                <a href="../shop/change_password.html">Zmiana hasła</a>
                <!-- logout -->
                <a href="?logout=1">Wyloguj się</a>
            </div>
        </div>
    </div>


    <!-- Ikona koszyka -->
    <div class="koszyk-ikona" onclick="toggleCart()">
        <img src="../img/shopping-cart.png" alt="Koszyk" class="cart-icon">
        <span class="cart-tooltip">Koszyk</span>
        <!-- <span><?php echo count($_SESSION['koszyk']); ?> produktów</span> -->
    </div>


    <!-- Modal koszyka -->
    <div class="koszyk-modal" id="koszykModal">
        <div class="koszyk">
            <button class="koszyk-zamknij" onclick="toggleCart()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <h2>Koszyk</h2>
            <?php if (empty($_SESSION['koszyk'])): ?>
                <p>Koszyk jest pusty.</p>
            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Cena brutto</th>
                            <th>Ilość</th>
                            <th>Suma</th>
                            <th>Usuń</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($_SESSION['koszyk'] as $produkt): ?>
                            <tr>
                                <td><img src="<?php echo $produkt['zdjecie']; ?>" alt="<?php echo $produkt['tytul']; ?>" style="max-width: 50px;"> <?php echo $produkt['tytul']; ?></td>                               
                                <td><?php echo cenaBrutto($produkt['cena_netto'], $produkt['vat']); ?> zł</td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="produkt_id" value="<?php echo $produkt['id']; ?>">
                                        <input type="number" name="ilosc" value="<?php echo $produkt['ilosc']; ?>" min="0">
                                        <button type="submit" name="aktualizuj_ilosc">Aktualizuj</button>
                                    </form>
                                </td>
                                <td><?php echo cenaBrutto($produkt['cena_netto'], $produkt['vat']) * $produkt['ilosc']; ?> zł</td>
                                <td><a href="?usun_z_koszyka=<?php echo $produkt['id']; ?>">Usuń</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="koszyk-suma">Suma: <?php echo calculateTotal(); ?> zł</p>
                
                <div class="koszyk-buttons">
                    <form method="post" style="display: inline;">
                        <button type="submit" name="oproznikoszyk">Opróżnij koszyk</button>
                    </form>
                    <button type="button" onclick="showOrderForm()">Kup</button>
                </div>

            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal z formularzem zamówienia -->
    <div class="order-form-modal" id="orderFormModal" style="display: none;">
        <div class="order-form">
            <h3>Dane do zamówienia</h3>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach($errors as $error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="../shop/data_modal.php">
                <div class="form-group">
                    <label for="firstName">Imię:</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>
                
                <div class="form-group">
                    <label for="lastName">Nazwisko:</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Adres:</label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="phone">Numer telefonu:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-buttons">
                    <button type="button" onclick="hideOrderForm()">Anuluj</button>
                    <button type="submit" name="submit_order">Złóż zamówienie</button>
                </div>
            </form>
        </div>
    </div>


</body>
</html>
<?php
// Sprawdź czy sesja jest już rozpoczęta
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funkcja do obliczania ceny brutto
function cenaBrutto($cenaNetto, $vat) {
    return $cenaNetto * (1 + $vat / 100);
}

// Inicjalizacja koszyka
if (!isset($_SESSION['koszyk'])) {
    $_SESSION['koszyk'] = [];
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
        $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'product_id' => $produktId
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $pdo->prepare("
                UPDATE cart_items 
                SET quantity = quantity + :quantity 
                WHERE user_id = :user_id AND product_id = :product_id
            ");
        } else {
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

        if (isset($_SESSION['koszyk'][$produktId])) {
            $_SESSION['koszyk'][$produktId]['ilosc'] += $ilosc;
        } else {
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
?>
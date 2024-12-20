<?php
// ajax_cart.php
session_start();
require_once 'cfg.php'; // Twój plik konfiguracyjny

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$response = ['success' => false];

switch ($_GET['action']) {
    case 'add':
        $product_id = intval($_GET['product_id']);
        
        // Sprawdzenie dostępności produktu
        $stmt = $link->prepare("SELECT * FROM produkty WHERE id = ? AND status = 'dostepny' AND ilosc > 0 LIMIT 1");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if ($product) {
            if (!isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = [
                    'quantity' => 1,
                    'price' => $product['cena_netto'],
                    'vat' => $product['vat']
                ];
            } else {
                // Sprawdź czy nie przekraczamy dostępnej ilości
                if ($_SESSION['cart'][$product_id]['quantity'] < $product['ilosc']) {
                    $_SESSION['cart'][$product_id]['quantity']++;
                }
            }
            $response['success'] = true;
            $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
        }
        break;

    case 'update':
        $product_id = intval($_GET['product_id']);
        $change = intval($_GET['change']);
        
        if (isset($_SESSION['cart'][$product_id])) {
            $new_quantity = $_SESSION['cart'][$product_id]['quantity'] + $change;
            
            if ($new_quantity > 0) {
                // Sprawdzenie dostępności w magazynie
                $stmt = $link->prepare("SELECT ilosc FROM produkty WHERE id = ? LIMIT 1");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
                
                if ($new_quantity <= $product['ilosc']) {
                    $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                    $response['success'] = true;
                }
            } else {
                unset($_SESSION['cart'][$product_id]);
                $response['success'] = true;
            }
            $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
        }
        break;

    case 'remove':
        $product_id = intval($_GET['product_id']);
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $response['success'] = true;
            $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
        }
        break;

    case 'get_cart':
        if (empty($_SESSION['cart'])) {
            echo "<p style='text-align: center; padding: 20px;'>Koszyk jest pusty</p>";
            exit;
        }

        $total = 0;
        $output = "";
        
        if (!empty($_SESSION['cart'])) {
            $product_ids = array_keys($_SESSION['cart']);
            $ids_str = str_repeat('?,', count($product_ids) - 1) . '?';
            $types = str_repeat('i', count($product_ids));
            
            $stmt = $link->prepare("SELECT * FROM produkty WHERE id IN ($ids_str)");
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $products = $stmt->get_result();
            
            while ($product = $products->fetch_assoc()) {
                $cart_item = $_SESSION['cart'][$product['id']];
                $price_brutto = $product['cena_netto'] * (1 + $product['vat'] / 100);
                $subtotal = $price_brutto * $cart_item['quantity'];
                $total += $subtotal;
                
                $output .= "
                <div style='border-bottom: 1px solid #eee; padding: 10px 0; display: flex; align-items: center; justify-content: space-between;'>
                    <div style='flex: 1;'>
                        <h4 style='margin: 0;'>" . htmlspecialchars($product['tytul']) . "</h4>
                        <p style='margin: 5px 0;'>" . number_format($price_brutto, 2) . " zł</p>
                    </div>
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        <button onclick='updateQuantity({$product['id']}, -1)' style='background: #f0f0f0; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>-</button>
                        <span>{$cart_item['quantity']}</span>
                        <button onclick='updateQuantity({$product['id']}, 1)' style='background: #f0f0f0; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>+</button>
                        <button onclick='removeFromCart({$product['id']})' style='background: #ff4444; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>🗑️</button>
                    </div>
                </div>
                <div style='margin-top: 10px;'>
                    <p style='font-size: 0.9em; color: #666;'>Wartość: " . number_format($subtotal, 2) . " zł</p>
                </div>";
            }
            
            $output .= "
            <div style='margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;'>
                <div style='display: flex; justify-content: space-between; font-weight: bold;'>
                    <span>Suma całkowita:</span>
                    <span>" . number_format($total, 2) . " zł</span>
                </div>
                <button onclick='proceedToCheckout()' style='width: 100%; margin-top: 15px; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;'>
                    Przejdź do kasy
                </button>
            </div>";
        }
        
        echo $output;
        exit;
        break;
}

if ($_GET['action'] !== 'get_cart') {
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
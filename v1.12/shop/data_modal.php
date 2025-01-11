<?php
require_once '../shop/auth.php';
require_once 'vendor/autoload.php';
require_once '../shop/cart_functions.php';
require_once '../cfg.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $errors = [];
    
    // Funkcja sanityzacji
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    // Pobierz i sanityzuj dane
    $firstName = sanitizeInput($_POST['firstName'] ?? '');
    $lastName = sanitizeInput($_POST['lastName'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    // Walidacja
    if (empty($firstName)) $errors[] = "Imię jest wymagane";
    if (empty($lastName)) $errors[] = "Nazwisko jest wymagane";
    if (empty($address)) $errors[] = "Adres jest wymagany";
    if (empty($phone)) $errors[] = "Numer telefonu jest wymagany";
    if (!$email) $errors[] = "Poprawny adres email jest wymagany";
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Oblicz sumę zamówienia
            $total = 0;
            foreach ($_SESSION['koszyk'] as $produkt) {
                $cenaBrutto = $produkt['cena_netto'] * (1 + $produkt['vat'] / 100);
                $total += $cenaBrutto * $produkt['ilosc'];
            }
            
            // Zapisz główne dane zamówienia
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, 
                    first_name, 
                    last_name, 
                    address, 
                    phone, 
                    email, 
                    total_amount, 
                    status, 
                    created_at
                ) VALUES (
                    :user_id,
                    :first_name,
                    :last_name,
                    :address,
                    :phone,
                    :email,
                    :total,
                    'new',
                    NOW()
                )
            ");
            
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'total' => $total
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Zapisz produkty zamówienia
            $stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    quantity,
                    price_net,
                    vat,
                    title,
                    image
                ) VALUES (
                    :order_id,
                    :product_id,
                    :quantity,
                    :price_net,
                    :vat,
                    :title,
                    :image
                )
            ");
            
            foreach ($_SESSION['koszyk'] as $produkt) {
                $stmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $produkt['id'],
                    'quantity' => $produkt['ilosc'],
                    'price_net' => $produkt['cena_netto'],
                    'vat' => $produkt['vat'],
                    'title' => $produkt['tytul'],
                    'image' => $produkt['zdjecie']
                ]);
            }
            
            // Wyślij email
            $orderDetails = "Nowe zamówienie:\n\n";
            $orderDetails .= "Dane klienta:\n";
            $orderDetails .= "Imię i nazwisko: $firstName $lastName\n";
            $orderDetails .= "Adres: $address\n";
            $orderDetails .= "Telefon: $phone\n";
            $orderDetails .= "Email: $email\n\n";
            
            $orderDetails .= "Zamówione produkty:\n";
            
            foreach ($_SESSION['koszyk'] as $produkt) {
                $cenaBrutto = $produkt['cena_netto'] * (1 + $produkt['vat'] / 100);
                $suma = $cenaBrutto * $produkt['ilosc'];
                $orderDetails .= sprintf(
                    "%s - %d szt. x %.2f zł = %.2f zł\n",
                    $produkt['tytul'],
                    $produkt['ilosc'],
                    $cenaBrutto,
                    $suma
                );
            }
            
            $orderDetails .= "\nSuma całkowita: " . number_format($total, 2) . " zł";
            
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'tmail4230@gmail.com'; // Replace with your email
            $mail->Password = 'fzgy vnmv zofq nfoo'; // Replace with your password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom('tmail4230@gmail.com', 'Sklep Amazon\'t');
            $mail->addAddress('tmail4230@gmail.com'); // Replace with admin email
            
            // Content
            $mail->isHTML(false);
            $mail->Subject = 'Nowe zamówienie - Amazon\'t';
            $mail->Body = $orderDetails;
            
            $mail->send();
            
            $pdo->commit();
            clearCart();
            
            header('Location: ../shop/cart.php');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Wystąpił błąd podczas przetwarzania zamówienia. Spróbuj ponownie później.";
            error_log($e->getMessage());
        }
    }
}
?>
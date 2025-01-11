<?php
session_start();

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Walidacja hasła po stronie serwera
    if (strlen($new_password) < 8) {
        die('Hasło musi mieć co najmniej 8 znaków.');
    }
    
    if (!preg_match('/[A-Z]/', $new_password)) {
        die('Hasło musi zawierać przynajmniej jedną wielką literę.');
    }
    
    if (!preg_match('/[0-9]/', $new_password)) {
        die('Hasło musi zawierać przynajmniej jedną cyfrę.');
    }
    
    if ($new_password !== $confirm_password) {
        die('Hasła nie są identyczne.');
    }
    
    try {
        // Połączenie z bazą danych
        $db = new PDO("mysql:host=localhost;dbname=moja_strona", "root", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Sprawdzenie aktualnego hasła
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!password_verify($current_password, $user['password'])) {
            die('Aktualne hasło jest nieprawidłowe.');
        }
        
        // Aktualizacja hasła
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        
        // Przekierowanie z komunikatem sukcesu
        $_SESSION['success_message'] = 'Hasło zostało zmienione pomyślnie.';
        header('Location: ../shop/cart.php');
        exit();
        
    } catch(PDOException $e) {
        die('Wystąpił błąd podczas zmiany hasła: ' . $e->getMessage());
    }
}
?>
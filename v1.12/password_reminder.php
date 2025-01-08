<?php
require 'vendor/autoload.php'; // Załadowanie PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Konfiguracja bazy danych
$dbHost = 'localhost';
$dbName = 'moja_strona';
$dbUser = 'root';
$dbPassword = '';

// Funkcja generowania bezpiecznego, losowego hasła
function generateSecurePassword($length = 12) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $password;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zamniałeś hasła?</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #282c34;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .reset-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background-color: #2c3e50;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: white;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 300;
            font-size: 24px;
        }
        .form-control {
            border-color: #34495e;
            background-color: #34495e;
            color: white; 
        }
        .btn-primary {
            width: 100%;
            background-color: #1abc9c; 
            border: none;
            padding: 0.75rem;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #16a085;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2 class="reset-header">Zapomniałeś hasła?</h2>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

            if (!$email) {
                echo '<div class="alert alert-danger">Nieprawidłowy adres email.</div>';
            } else {
                try {
                    // Połączenie z bazą danych
                    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Sprawdzenie czy email istnieje w bazie administratorów
                    $stmt = $pdo->prepare("SELECT id FROM administrators WHERE email = ?");
                    $stmt->execute([$email]);

                    if ($stmt->rowCount() > 0) {
                        // Generowanie nowego hasła
                        $newPassword = generateSecurePassword();
                        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                        // Aktualizacja hasła w bazie danych
                        $updateStmt = $pdo->prepare("UPDATE administrators SET password = ? WHERE email = ?");
                        $updateStmt->execute([$hashedPassword, $email]);

                        // Wysłanie hasła mailem
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'tmail4230@gmail.com';
                        $mail->Password = 'fzgy vnmv zofq nfoo';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('tmail4230@gmail.com', 'Administrator');
                        $mail->addAddress($email);
                        $mail->Subject = 'Reset Hasła';
                        $mail->Body = "Twoje nowe hasło to: $newPassword\n\nZaloguj się i zmień je niezwłocznie.";

                        $mail->send();

                        // Komunikat z przekierowaniem
                        echo '<div class="alert alert-success">
                                Nowe hasło zostało wysłane na Twój email. 
                                Nastąpi automatyczne przekierowanie do strony głównej za 5 sekund.
                              </div>';
                        
                        // Automatyczne przekierowanie do strony głównej
                        echo '<script>
                                setTimeout(function() {
                                    window.location.href = "index.php";
                                }, 4500);
                              </script>';
                        exit;
                    } else {
                        echo '<div class="alert alert-warning">Email nie został znaleziony w bazie administratorów.</div>';
                    }
                } catch(PDOException $e) {
                    echo '<div class="alert alert-danger">Błąd bazy danych: ' . $e->getMessage() . '</div>';
                } catch(Exception $e) {
                    echo '<div class="alert alert-danger">Błąd wysyłania emaila: ' . $mail->ErrorInfo . '</div>';
                }
            }
        }
        ?>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Adres Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Resetuj Hasło</button>
        </form>
    </div>
</body>
</html>
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Konfiguracja połączenia z bazą danych
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'moja_strona';

// Funkcja generująca losowe hasło
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        // Sprawdzenie czy email istnieje w bazie
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Generowanie nowego hasła
            $newPassword = generateRandomPassword();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Aktualizacja hasła w bazie
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $updateStmt->execute([$hashedPassword, $email]);
            
            // Konfiguracja PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Konfiguracja serwera
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'tmail4230@gmail.com';
                $mail->Password   = 'fzgy vnmv zofq nfoo';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                // Adresaci
                $mail->setFrom('tmail4230@gmail.com', 'Amazont');
                $mail->addAddress($email);

                // Treść
                $mail->isHTML(true);
                $mail->Subject = 'Reset hasła';
                $mail->Body    = "
                    <h2>Reset hasła</h2>
                    <p>Witaj <strong>{$user['username']}</strong>,</p>
                    <p>Twoje nowe hasło do konta to: <strong>{$newPassword}</strong></p>
                    <p>Ze względów bezpieczeństwa zalecamy zmianę hasła po zalogowaniu się do systemu.</p>
                    <p><br><br>Pozdrawiamy,<br>Zespół sklepu Amazon't</p>
                ";
                $mail->AltBody = "Witaj {$user['username']},\n\nTwoje nowe hasło to: {$newPassword}\n\nZe względów bezpieczeństwa zalecamy zmianę hasła po zalogowaniu się do systemu.\n\nPozdrawiamy,\nZespół sklepu";

                $mail->send();
                echo '<div class="alert alert-success">
                        Nowe hasło zostało wysłane na podany adres email.
                      </div>';
                //Przekierowanie do panelu logowania
                echo '<script>
                      setTimeout(function() {
                          window.location.href = "../shop/login.php";
                      }, 3000);
                    </script>';

            } catch (Exception $e) {
                throw new Exception("Błąd podczas wysyłania emaila: " . $mail->ErrorInfo);
            }
        } else {
            echo '<div class="alert alert-danger">
                    Podany adres email nie istnieje w naszej bazie.
                  </div>';
        }
    } catch(Exception $e) {
        echo '<div class="alert alert-danger">
                Wystąpił błąd podczas resetowania hasła: ' . $e->getMessage() . '
              </div>';
    }
}
?>
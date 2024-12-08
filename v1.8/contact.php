<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Wczytaj autoloader Composer
require 'vendor/autoload.php';

/**
 * Funkcja do wysyłania wiadomości e-mail.
 *
 * @param string $emailOdbiorcy Adres e-mail odbiorcy
 * @param string $temat Temat wiadomości
 * @param string $tresc Treść wiadomości w formacie HTML
 * @param string $emailNadawcy Opcjonalny adres nadawcy
 * @param string $nazwaNadawcy Opcjonalna nazwa nadawcy
 * @return bool Zwraca true w przypadku powodzenia, false w razie błędu
 */
function WyslijMail($emailOdbiorcy, $temat, $tresc, $emailNadawcy = 'tmail4230@gmail.com', $nazwaNadawcy = 'My_site') {
    $mail = new PHPMailer(true);

    try {
        // Ustawienia serwera SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Podaj serwer SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'tmail4230@gmail.com'; // Adres e-mail
        $mail->Password = 'fzgy vnmv zofq nfoo'; // Haslo do apki
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587;

        // Ustawienia nadawcy i odbiorcy
        $mail->setFrom($emailNadawcy, $nazwaNadawcy);
        $mail->addAddress($emailOdbiorcy);

        // Treść wiadomości
        $mail->isHTML(true);
        $mail->Subject = $temat;
        $mail->Body = $tresc;

        // Wyślij wiadomość
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Walidacja podstawowa
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        die('Wszystkie pola są wymagane.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Podano nieprawidłowy adres e-mail.');
    }

    // Szablon treści maila
    $tresc = "
        <p>Imię: $name</p>
        <p>Email: $email</p>
        <p>Wiadomość:</p>
        <p>$message</p>
    ";

    // Wyślij e-mail
    if (WyslijMail('tmail4230@gmail.com', $subject, $tresc)) {
        echo '<p style="color: green;">Wiadomość została wysłana!</p>';
        header("Refresh: 2; url=index.php");
        exit; // Zatrzymanie dalszego wykonywania skryptu
    } else {
        echo '<p style="color: red;">Nie udało się wysłać wiadomości. Spróbuj ponownie później.</p>';
    }
} else {
    echo 'Nieautoryzowany dostęp.';
}
?>


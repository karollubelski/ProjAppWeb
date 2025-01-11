<?php
// Połączenie z bazą danych
$host = 'localhost';
$dbname = 'moja_strona';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

$login = 'admin123';
$password = 'admin123';
$email = 'tmail4230@gmail.com';

// ja dodaje z hashowaniem bo i tak w razie resetu wysyła nowe haslo
$hashed_password = password_hash($password, PASSWORD_DEFAULT); // hashowanie

$sql = "INSERT INTO administrators (login, password, email) VALUES (:login, :password, :email)";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':login' => $login,
    ':password' => $password,
    ':email' => $email
]);

echo "Administrator został dodany pomyślnie!";
?>

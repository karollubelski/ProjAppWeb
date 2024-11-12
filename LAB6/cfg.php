<?php
// Dane połączenia z bazą danych
$host = 'localhost';
$dbname = 'moja_strona';
$username = 'root';  // domyślny użytkownik w XAMPP to zazwyczaj 'root'
$password = '';      // zazwyczaj bez hasła

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

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

// $menadzerProduktow = new MenadzerProduktow($db);

// Dodawanie produktu
$menadzerProduktow->dodajProdukt([
    'tytul' => 'Przykładowy produkt',
    'opis' => 'Opis produktu',
    'data_wygasniecia' => '2024-12-31',
    'cena_netto' => 99.99,
    'stawka_vat' => 23.00,
    'ilosc_w_magazynie' => 10,
    'status_dostepnosci' => 'dostepny',
    'kategoria_id' => 1,
    'gabaryty' => '10x20x30',
    'url_zdjecia' => '/images/produkt.jpg'
]);
?>
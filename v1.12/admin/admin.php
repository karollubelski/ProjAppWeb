<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" href="../css/admin_panel.css">
</head>
<body>

<?php
session_start();
require '../cfg.php';

// Funkcja hashowania hasła
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Funkcja weryfikacji hasła
function verifyPassword($input_password, $stored_hash) {
    return password_verify($input_password, $stored_hash);
}

// Funkcja generowania tokenu resetu hasła
function generateResetToken() {
    return bin2hex(random_bytes(50));
}

// Funkcja wylogowania
function Wyloguj() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Połączenie z bazą danych
$db = new mysqli("localhost", "root", "", "moja_strona");
if ($db->connect_error) {
    die("<p style='color: red;'>Błąd połączenia z bazą danych: " . htmlspecialchars($db->connect_error) . "</p>");
}

// Logowanie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['login'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');

    // Weryfikacja logowania
    $stmt = $db->prepare("SELECT password FROM administrators WHERE login = ?");
    $stmt->bind_param("s", $loginInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (verifyPassword($passwordInput, $admin['password'])) {
            session_regenerate_id();
            $_SESSION['logged_in'] = true;
            $_SESSION['admin_login'] = $loginInput;
        } else {
            echo "<p style='color: red;'>Nieprawidłowe dane logowania.</p>";
        }
    }
}

// Formularz logowania
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo "<h2>Logowanie Administratora</h2>";
    echo "
    <form method='POST'>
        <label>Login: <input type='text' name='login' required></label><br>
        <label>Hasło: <input type='password' name='password' required></label><br>
        <input type='submit' value='Zaloguj'>
    </form>";
    exit;
}

// Funkcja listowania podstron
function ListaPodstron($db) {
    $query = "SELECT id, page_title FROM page_list";
    $result = $db->query($query);
    echo "<a href='?action=add'>Dodaj nową podstronę</a><br><br>";
    echo "<table border='1'><tr><th>ID</th><th>Tytuł</th><th>Akcje</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row['id']) . "</td>
            <td>" . htmlspecialchars($row['page_title']) . "</td>
            <td>
                <a href='?action=edit&id=" . urlencode($row['id']) . "'>Edytuj</a> | 
                <a href='?action=delete&id=" . urlencode($row['id']) . "'>Usuń</a>
            </td>
        </tr>";
    }
    echo "</table>";
}

// Funkcja edycji podstrony
function EdytujPodstrone($db, $id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['page_title']);
        $content = trim($_POST['page_content']);
        $status_strony = isset($_POST['status_strony']) ? 1 : 0;

        $stmt = $db->prepare("UPDATE page_list SET page_title = ?, page_content = ?, status_strony = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssii", $title, $content, $status_strony, $id);
        $stmt->execute();

        echo "<p>Podstrona została zaktualizowana.</p>";
        echo "<a href='?action=list'>Powrót do listy</a>";
    } else {
        $stmt = $db->prepare("SELECT page_title, page_content, status_strony FROM page_list WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        echo "
        <h2>Edytuj podstronę</h2>
        <form method='POST'>
            <label>Tytuł: <input type='text' name='page_title' value='" . htmlspecialchars($result['page_title']) . "'></label><br>
            <label>Treść: <textarea name='page_content'>" . htmlspecialchars($result['page_content']) . "</textarea></label><br>
            <label>Aktywna: <input type='checkbox' name='status_strony' " . ($result['status_strony'] ? 'checked' : '') . "></label><br>
            <input type='submit' value='Zapisz zmiany'>
            <a href='?action=list'><button type='button'>Anuluj</button></a>
        </form>";
    }
}

// Funkcja dodawania nowej podstrony
function DodajNowaPodstrone($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['page_title']);
        $content = trim($_POST['page_content']);
        $status_strony = isset($_POST['status_strony']) ? 1 : 0;

        $stmt = $db->prepare("INSERT INTO page_list (page_title, page_content, status_strony) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $status_strony);
        $stmt->execute();

        echo "<p>Nowa podstrona została dodana.</p>";
        echo "<a href='?action=list'>Powrót do listy</a>";
    } else {
        echo "
        <h2>Dodaj nową podstronę</h2>
        <form method='POST'>
            <label>Tytuł: <input type='text' name='page_title'></label><br>
            <label>Treść: <textarea name='page_content'></textarea></label><br>
            <label>Aktywna: <input type='checkbox' name='status_strony'></label><br>
            <input type='submit' value='Dodaj podstronę'>
            <a href='?action=list'><button type='button'>Anuluj</button></a>
        </form>";
    }
}

// Funkcja usuwania podstrony
function UsunPodstrone($db, $id) {
    $stmt = $db->prepare("DELETE FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<p>Podstrona została usunięta.</p>";
    echo "<a href='?action=list'>Powrót do listy</a>";
}

// Funkcja wyświetlania drzewa kategorii
function WyswietlKategorie($db, $matkaId = 0, $glebokosc = 0) {
    $stmt = $db->prepare("SELECT id, nazwa FROM kategorie WHERE matka = ? ORDER BY nazwa");
    $stmt->bind_param("i", $matkaId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo str_repeat("&nbsp;", $glebokosc * 4) . htmlspecialchars($row['id']) . " - " . htmlspecialchars($row['nazwa']) . " 
        <a href='?action=edit_category&id=" . $row['id'] . "'>Edytuj</a> | 
        <a href='?action=delete_category&id=" . $row['id'] . "'>Usuń</a><br>";

        // Rekurencja dla podkategorii
        WyswietlKategorie($db, $row['id'], $glebokosc + 1);
    }
}

// Funkcja dodawania kategorii
function DodajKategorie($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $kategorie = explode("\n", $_POST['kategorie']); // pobierane kategorie z pola tekstowego
        $matkaId = intval($_POST['matka']); // ID matki kategorii

        foreach ($kategorie as $kategoria) {
            $kategoria = trim($kategoria);
            if (!empty($kategoria)) {
                $stmt = $db->prepare("INSERT INTO kategorie (nazwa, matka) VALUES (?, ?)");
                $stmt->bind_param("si", $kategoria, $matkaId);
                $stmt->execute();
            }
        }

        echo "<p>Dodano kategorie.</p>";
        echo "<a href='?action=manage_categories'>Powrót do zarządzania kategoriami</a>";
        return;
    }

    echo "<h2>Struktura kategorii</h2>";
    WyswietlKategorie($db); // Wyświetlenie drzewa kategorii

    echo "
    <h2>Dodaj nowe kategorie</h2>
    <form method='POST'>
        <label>Kategorie (każda w nowej linii):<br>
            <textarea name='kategorie' rows='10' cols='30'></textarea>
        </label><br>
        <label>Matka kategorii (ID): 
            <input type='number' name='matka' value='0'>
        </label><br><br>
        <input type='submit' value='Dodaj kategorie'>
        <a href='?action=manage_categories'><button type='button'>Anuluj</button></a>
    </form>";
}

// Funkcja edytowania kategorii
function EdytujKategorie($db, $id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nazwa = trim($_POST['nazwa']);
        $matka = intval($_POST['matka']) ?: 0;

        if ($id === $matka) {
            echo "<p style='color: red;'>Kategoria nie może być matką samej siebie.</p>";
            return;
        }

        $stmt = $db->prepare("UPDATE kategorie SET nazwa = ?, matka = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("sii", $nazwa, $matka, $id);
        $stmt->execute();

        echo "<p>Kategoria została zaktualizowana.</p>";
        echo "<a href='?action=manage_categories'>Powrót do zarządzania kategoriami</a>";
    } else {
        $stmt = $db->prepare("SELECT nazwa, matka FROM kategorie WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $category = $stmt->get_result()->fetch_assoc();
        
        echo "
        <h2>Edytuj kategorię</h2>
        <form method='POST'>
            <label>Nazwa: <input type='text' name='nazwa' value='" . htmlspecialchars($category['nazwa']) . "'></label><br>
            <label>Matka (ID): <input type='number' name='matka' value='" . $category['matka'] . "'></label><br>
            <input type='submit' value='Zapisz zmiany'> 
            <a href='?action=manage_categories'><button type='button'>Anuluj</button></a>
        </form>";

        echo "<h2>Struktura kategorii</h2>";
        WyswietlKategorie($db);
    }
}

// Funkcja rekurencyjnego usuwania kategorii
function UsunKategorieRekurencyjnie($db, $id) {
    $stmt = $db->prepare("SELECT id FROM kategorie WHERE matka = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        UsunKategorieRekurencyjnie($db, $row['id']);
    }

    $stmt = $db->prepare("DELETE FROM kategorie WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<p>Kategoria i jej podkategorie zostały usunięte.</p>";
}

// Klasa zarządzająca produktami
class ZarzadzajProduktami {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Dodawanie nowego produktu
    public function DodajProdukt() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tytul = trim($_POST['tytul']);
            $opis = trim($_POST['opis']);
            $cena_netto = floatval($_POST['cena_netto']);
            $vat = floatval($_POST['vat']);
            $ilosc = intval($_POST['ilosc']);
            $dostepnosc = trim($_POST['dostepnosc']);
            $kategoria_id = intval($_POST['kategoria']);
            $gabaryt = trim($_POST['gabaryt']);
            $zdjecie = $_POST['zdjecie']; // Pobieranie linku do zdjęcia
            $data_utworzenia = date('Y-m-d H:i:s');
            $data_wygasniecia = $_POST['data_wygasniecia'];

            // --- Walidacja URL ---
            if (!filter_var($zdjecie, FILTER_VALIDATE_URL)) {
                echo "<p>Nieprawidłowy URL zdjęcia.</p>";
                return;
            }


            $stmt = $this->db->prepare("INSERT INTO produkty (tytul, opis, data_utworzenia, data_modyfikacji, 
                data_wygasniecia, cena_netto, vat, ilosc, dostepnosc, kategoria_id, gabaryt, zdjecie) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $data_modyfikacji = $data_utworzenia;
            $stmt->bind_param("sssssddiisss", $tytul, $opis, $data_utworzenia, $data_modyfikacji, 
                $data_wygasniecia, $cena_netto, $vat, $ilosc, $dostepnosc, $kategoria_id, $gabaryt, $zdjecie);
            
            if ($stmt->execute()) {
                echo "<p>Produkt został dodany pomyślnie.</p>";
            } else {
                echo "<p>Wystąpił błąd podczas dodawania produktu: " . $stmt->error . "</p>";
            }
        }

        // Formularz dodawania produktu
        $kategorie = $this->PobierzKategorie();
        echo "
        <h2>Dodaj nowy produkt</h2>
        <form method='POST'>
           <label>Tytuł: <input type='text' name='tytul' required></label><br>
            <label>Opis: <textarea name='opis' required></textarea></label><br>
            <label>Cena netto: <input type='number' step='0.01' name='cena_netto' required></label><br>
            <label>VAT (%): <input type='number' step='0.01' name='vat' required></label><br>
            <label>Ilość: <input type='number' name='ilosc' required></label><br>
            <label>Status dostępności: 
                <select name='dostepnosc' required>
                    <option value='dostepny'>Dostępny</option>
                    <option value='niedostepny'>Niedostępny</option>
                    <option value='oczekujacy'>Oczekujący</option>
                </select>
            </label><br>
            <label>Kategoria: 
                <select name='kategoria' required>
                    " . $this->GenerujOpcjeKategorii($kategorie) . "
                </select>
            </label><br>
            <label>Gabaryt: 
                <select name='gabaryt' required>
                    <option value='maly'>Mały</option>
                    <option value='sredni'>Średni</option>
                    <option value='duzy'>Duży</option>
                </select>
            </label><br>
            <label>Link do zdjęcia: <input type='text' name='zdjecie'></label><br>
            <label>Data wygaśnięcia: <input type='date' name='data_wygasniecia'></label><br>
            <a href='?action=products'><button type='button'>Anuluj</button></a>           
            <input type='submit' value='Dodaj produkt'>

        </form>";
    }

    // Usuwanie produktu
    public function UsunProdukt($id) {
        $stmt = $this->db->prepare("DELETE FROM produkty WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<p>Produkt został usunięty.</p>";
        } else {
            echo "<p>Wystąpił błąd podczas usuwania produktu.</p>";
        }
    }

    // Edycja produktu
    public function EdytujProdukt($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tytul = trim($_POST['tytul']);
            $opis = trim($_POST['opis']);
            $cena_netto = floatval($_POST['cena_netto']);
            $vat = floatval($_POST['vat']);
            $ilosc = intval($_POST['ilosc']);
            $dostepnosc = trim($_POST['dostepnosc']);
            $kategoria_id = intval($_POST['kategoria']);
            $gabaryt = trim($_POST['gabaryt']);
            $zdjecie = trim($_POST['zdjecie']);
            $data_wygasniecia = !empty($_POST['data_wygasniecia']) ? $_POST['data_wygasniecia'] : NULL;
            $data_modyfikacji = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("UPDATE produkty SET tytul = ?, opis = ?, data_modyfikacji = ?, 
            data_wygasniecia = ?, cena_netto = ?, vat = ?, ilosc = ?, dostepnosc = ?, 
            kategoria_id = ?, gabaryt = ?, zdjecie = ? WHERE id = ? LIMIT 1");
        
            $stmt->bind_param("ssssddissssi", $tytul, $opis, $data_modyfikacji, $data_wygasniecia, 
                $cena_netto, $vat, $ilosc, $dostepnosc, $kategoria_id, $gabaryt, $zdjecie, $id);
            
            if ($stmt->execute()) {
                echo "<p>Produkt został zaktualizowany.</p>";
            } else {
                echo "<p>Wystąpił błąd podczas aktualizacji produktu.</p>";
            }
        }

        // Pobieranie danych produktu
        $stmt = $this->db->prepare("SELECT * FROM produkty WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $produkt = $stmt->get_result()->fetch_assoc();
        
        $kategorie = $this->PobierzKategorie();
        echo "
        <h2>Edytuj produkt</h2>
        <form method='POST' enctype='multipart/form-data'>
            <label>Tytuł: <input type='text' name='tytul' value='" . htmlspecialchars($produkt['tytul']) . "' required></label><br>
            <label>Opis: <textarea name='opis' required>" . htmlspecialchars($produkt['opis']) . "</textarea></label><br>
            <label>Cena netto: <input type='number' step='0.01' name='cena_netto' value='" . $produkt['cena_netto'] . "' required></label><br>
            <label>VAT (%): <input type='number' step='0.01' name='vat' value='" . $produkt['vat'] . "' required></label><br>
            <label>Ilość: <input type='number' name='ilosc' value='" . $produkt['ilosc'] . "' required></label><br>
            <label>Status dostępności: 
                <select name='dostepnosc' required>
                    <option value='dostepny' " . ($produkt['dostepnosc'] == 'dostepny' ? 'selected' : '') . ">Dostępny</option>
                    <option value='niedostepny' " . ($produkt['dostepnosc'] == 'niedostepny' ? 'selected' : '') . ">Niedostępny</option>
                    <option value='oczekujacy' " . ($produkt['dostepnosc'] == 'oczekujacy' ? 'selected' : '') . ">Oczekujący</option>
                </select>
            </label><br>
            <label>Kategoria: 
                <select name='kategoria' required>
                    " . $this->GenerujOpcjeKategorii($kategorie, $produkt['kategoria_id']) . "
                </select>
            </label><br>
            <label>Gabaryt: 
                <select name='gabaryt' required>
                    <option value='maly' " . ($produkt['gabaryt'] == 'maly' ? 'selected' : '') . ">Mały</option>
                    <option value='sredni' " . ($produkt['gabaryt'] == 'sredni' ? 'selected' : '') . ">Średni</option>
                    <option value='duzy' " . ($produkt['gabaryt'] == 'duzy' ? 'selected' : '') . ">Duży</option>
                </select>
            </label><br>
            <label>Link do zdjęcia: <input type='text' name='zdjecie' value='" . htmlspecialchars($produkt['zdjecie']) . "'></label><br>
            <label>Data wygaśnięcia: <input type='date' name='data_wygasniecia' value='" . $produkt['data_wygasniecia'] . "'></label><br>
            <a href='?action=products'><button type='button'>Anuluj</button></a>
            <input type='submit' value='Zapisz zmiany'>
        </form>";
    }

    // Wyświetlanie listy produktów
    public function PokazProdukty() {
        // Obsługa filtrowania i wyszukiwania
        $where = [];
        $params = [];
        $types = "";

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $where[] = "(p.tytul LIKE ? OR p.opis LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }

        if (isset($_GET['kategoria']) && !empty($_GET['kategoria'])) {
            $where[] = "p.kategoria_id = ?";
            $params[] = $_GET['kategoria'];
            $types .= "i";
        }

        if (isset($_GET['dostepnosc']) && !empty($_GET['dostepnosc'])) {
            $where[] = "p.dostepnosc = ?";
            $params[] = $_GET['dostepnosc'];
            $types .= "s";
        }

        $whereClause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

        // Sortowanie
        $orderBy = isset($_GET['sort']) ? $_GET['sort'] : 'data_utworzenia';
        $orderDir = isset($_GET['dir']) && $_GET['dir'] === 'asc' ? 'ASC' : 'DESC';
        $allowedColumns = ['tytul', 'cena_netto', 'ilosc', 'data_utworzenia'];
        
        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'data_utworzenia';
        }

        $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
                 FROM produkty p 
                 LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                 $whereClause 
                 ORDER BY p.$orderBy $orderDir";

        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

       // Wyświetlanie formularza wyszukiwania
       echo "<div class='controls' style='margin: 20px auto; padding: 20px; background: #f5f5f5; border-radius: 5px; max-width: 100%;'>";
       echo "<h2 style='text-align: center; margin-bottom: 20px;'>Lista produktów</h2>";
       
       // Przycisk "Dodaj nowy produkt" 
       echo "<div style='text-align: left; margin-bottom: 20px;'>";
       echo "<a href='?action=add_products' class='button' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; display: inline-block;'>➕ Dodaj nowy produkt</a>";
       echo "</div>";
       
       // Formularz wyszukiwania
       echo "<form method='GET' style='display: flex; flex-direction: column; gap: 15px; max-width: 800px; margin: 0 auto;'>";
       echo "<input type='hidden' name='action' value='products'>";
       
       // Kontener na pola formularza
       echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;'>";
       
       // Pole wyszukiwania
       echo "<div style='display: flex; flex-direction: column;height: 50px;'>";
    //    echo "<label style='margin-bottom: 5px; font-weight: bold;'>Szukaj:</label>";
       echo "<input type='text' name='search' placeholder='Szukaj produktu...' value='" . htmlspecialchars($_GET['search'] ?? '') . "' style='padding: 8px; border: 1px solid #ddd; border-radius: 3px; width: 100%;height:100%'>";
       echo "</div>";
        
        // Filtr kategorii
        echo "<select name='kategoria' style='padding: 8px;'>";
        echo "<option value=''>Wszystkie kategorie</option>";
        echo $this->GenerujOpcjeKategorii($this->PobierzKategorie(), $_GET['kategoria'] ?? null);
        echo "</select>";
        
        // Filtr statusu
        echo "<select name='dostepnosc' style='padding: 8px;'>";
        echo "<option value=''>Wszystkie statusy</option>";
        echo "<option value='dostepny'" . (($_GET['dostepnosc'] ?? '') === 'dostepny' ? ' selected' : '') . ">Dostępne</option>";
        echo "<option value='niedostepny'" . (($_GET['dostepnosc'] ?? '') === 'niedostepny' ? ' selected' : '') . ">Niedostępne</option>";
        echo "<option value='oczekujacy'" . (($_GET['dostepnosc'] ?? '') === 'oczekujacy' ? ' selected' : '') . ">Oczekujące</option>";
        echo "</select>";
        
        echo "<button type='submit' style='padding: 8px 15px; background: #2196F3; color: white; border: none; border-radius: 3px; cursor: pointer;'>🔍 Szukaj</button>";
        echo "</form></div>";

        // Tabela produktów
        echo "<div style='overflow-x: auto;'>";
        echo "<table style='width: 100%; border-collapse: collapse; background: white;'>
            <thead style='background: #f8f9fa;'>
            <tr>
                <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>ID</th>
                <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>
                    <a href='?action=products&sort=tytul&dir=" . ($orderBy === 'tytul' && $orderDir === 'ASC' ? 'desc' : 'asc') . "' style='color: inherit; text-decoration: none;'>
                        Tytuł " . ($orderBy === 'tytul' ? ($orderDir === 'ASC' ? '↑' : '↓') : '') . "
                    </a>
                </th>
                <th style='padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;'>
                    <a href='?action=products&sort=cena_netto&dir=" . ($orderBy === 'cena_netto' && $orderDir === 'ASC' ? 'desc' : 'asc') . "' style='color: inherit; text-decoration: none;'>
                        Cena netto " . ($orderBy === 'cena_netto' ? ($orderDir === 'ASC' ? '↑' : '↓') : '') . "
                    </a>
                </th>
                <th style='padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;'>Cena brutto</th>
                <th style='padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;'>
                    <a href='?action=products&sort=ilosc&dir=" . ($orderBy === 'ilosc' && $orderDir === 'ASC' ? 'desc' : 'asc') . "' style='color: inherit; text-decoration: none;'>
                        Stan " . ($orderBy === 'ilosc' ? ($orderDir === 'ASC' ? '↑' : '↓') : '') . "
                    </a>
                </th>
                <th style='padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;'>Status</th>
                <th style='padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;'>Kategoria</th>
                <th style='padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;'>Akcje</th>
            </tr>
            </thead>
            <tbody>";

        while ($row = $result->fetch_assoc()) {
            $cena_z_vat = $row['cena_netto'] * (1 + $row['vat'] / 100);
            $dostepnosc = $this->SprawdzDostepnosc($row);

            $status_style = match ($dostepnosc) {
                'dostepny' => 'color: #28a745; background: #e8f5e9; padding: 4px 8px; border-radius: 4px;',
                'Oczekujący' => 'color: #dc3545; background: #ffa500; padding: 4px 8px; border-radius: 4px;',
                'niedostepny' => 'color: #000000; background: #dc3545; padding: 4px 8px; border-radius: 4px;',
                default => 'color: #000000.; background: #28a745; padding: 4px 8px; border-radius: 4px;'
            };

            echo "<tr style='border-bottom: 1px solid #dee2e6;'>
                <td style='padding: 12px;'>" . htmlspecialchars($row['id']) . "</td>
                <td style='padding: 12px;'>" . htmlspecialchars($row['tytul']) . "</td>
                <td style='padding: 12px; text-align: right;'>" . number_format($row['cena_netto'], 2) . " zł</td>
                <td style='padding: 12px; text-align: right;'>" . number_format($cena_z_vat, 2) . " zł</td>
                <td style='padding: 12px; text-align: center;'>" . $row['ilosc'] . " szt.</td>
                <td style='padding: 12px; text-align: center;'>
                    <span style='" . $status_style . "'>" . htmlspecialchars($dostepnosc) . "</span>
                </td>
                <td style='padding: 12px;'>" . htmlspecialchars($row['kategoria_nazwa']) . "</td>
                <td style='padding: 12px; text-align: center;'>
                    <a href='?action=edit_products&id=" . $row['id'] . "' style='color: #2196F3; text-decoration: none; margin-right: 10px;' title='Edytuj'>✏️</a>
                    <a href='?action=delete_products&id=" . $row['id'] . "' onclick='return confirm(\"Czy na pewno chcesz usunąć ten produkt?\");' style='color: #dc3545; text-decoration: none;' title='Usuń'>🗑️</a>
                </td>
            </tr>";
        
        
        }
        echo "</tbody></table></div>";
        
        if ($result->num_rows === 0) {
            echo "<p style='text-align: center; padding: 20px; color: #666;'>Nie znaleziono produktów spełniających kryteria wyszukiwania.</p>";
        }
        
        echo "</div>";
    }

    // Pomocnicze metody
    private function PobierzKategorie() {
        $query = "SELECT id, nazwa, matka FROM kategorie ORDER BY nazwa";
        return $this->db->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    private function GenerujOpcjeKategorii($kategorie, $selected_id = null) {
        $options = "";
        foreach ($kategorie as $kat) {
            $selected = ($selected_id == $kat['id']) ? 'selected' : '';
            $options .= "<option value='{$kat['id']}' {$selected}>" . htmlspecialchars($kat['nazwa']) . "</option>";
        }
        return $options;
    }

    private function SprawdzDostepnosc($produkt) {
        if ($produkt['dostepnosc'] == 'niedostepny') {
            return 'Niedostępny';
        }
        if ($produkt['ilosc'] <= 0) {
            return 'Brak w magazynie';
        }
        if (!empty($produkt['data_wygasniecia']) && strtotime($produkt['data_wygasniecia']) < time()) {
            return 'Wygasły';
        }
        return 'Dostępny';
    }
}


// Obsługa działań
$action = $_GET['action'] ?? 'fun_selection';

switch ($action) {
    case 'fun_selection':
        echo "<h2>CMS</h2>";
        echo "<a href='?action=products'>Zarządzanie produktami<br></a>";
        echo "<a href='?action=manage_categories'>Zarządzanie kategoriami<br></a>";
        echo "<a href='?action=list'>Lista podstron</a><br><br>";
        break;
    
    case 'list':
        echo "<h2>CMS</h2>";
        ListaPodstron($db);
        break;
    case 'edit':
        EdytujPodstrone($db, intval($_GET['id'] ?? 0));
        break;
    case 'delete':
        UsunPodstrone($db, intval($_GET['id'] ?? 0));
        break;
    case 'add':
        DodajNowaPodstrone($db);
        break;
    case 'manage_categories':
        echo "<h2>Zarządzanie kategoriami</h2>";
        echo "<a href='?action=add_category'>Dodaj kategorię</a><br><br>";
        WyswietlKategorie($db);
        break;
    case 'add_category':
        DodajKategorie($db);
        break;
    case 'edit_category':
        EdytujKategorie($db, intval($_GET['id'] ?? 0));
        break;
    case 'delete_category':
        UsunKategorieRekurencyjnie($db, intval($_GET['id'] ?? 0));
        break;
    case 'products':
        $produkty = new ZarzadzajProduktami($db);
        $produkty->PokazProdukty();
        break;
    case 'add_products':
        $productManager = new ZarzadzajProduktami($db);
        $productManager->DodajProdukt();
        break;
    case 'delete_products':
        $productManager = new ZarzadzajProduktami($db);
        $id = intval($_GET['id'] ?? 0);
        $productManager->UsunProdukt($id);
        break;
    case 'edit_products':
        $productManager = new ZarzadzajProduktami($db);
        $id = intval($_GET['id'] ?? 0);
        $productManager->EdytujProdukt($id);
        break;
    case 'logout':
        Wyloguj();
        break;
    default:
        echo "<p>Nieznane działanie.</p>";
        break;
}
?>

    <div class='logout-icon'>
        <a href='?action=logout'><img src='../img/logout.png' alt='Wyloguj'></a>
    </div>

    <div class="navigation-buttons">
        <button onclick="window.location.href='../admin/admin.php'">Powrót do CMS</button>
        <button onclick="window.location.href='../index.php'">Powrót do strony</button>
    </div>

</body>
</html>

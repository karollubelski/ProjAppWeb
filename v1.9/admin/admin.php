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
    echo "<a href='?action=manage_categories'>Zarządzanie kategoriami<br></a>";
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
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $db->prepare("UPDATE page_list SET page_title = ?, page_content = ?, status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssii", $title, $content, $status, $id);
        $stmt->execute();

        echo "<p>Podstrona została zaktualizowana.</p>";
        echo "<a href='?action=list'>Powrót do listy</a>";
    } else {
        $stmt = $db->prepare("SELECT page_title, page_content, status FROM page_list WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        echo "
        <h2>Edytuj podstronę</h2>
        <form method='POST'>
            <label>Tytuł: <input type='text' name='page_title' value='" . htmlspecialchars($result['page_title']) . "'></label><br>
            <label>Treść: <textarea name='page_content'>" . htmlspecialchars($result['page_content']) . "</textarea></label><br>
            <label>Aktywna: <input type='checkbox' name='status' " . ($result['status'] ? 'checked' : '') . "></label><br>
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
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $db->prepare("INSERT INTO page_list (page_title, page_content, status) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $status);
        $stmt->execute();

        echo "<p>Nowa podstrona została dodana.</p>";
        echo "<a href='?action=list'>Powrót do listy</a>";
    } else {
        echo "
        <h2>Dodaj nową podstronę</h2>
        <form method='POST'>
            <label>Tytuł: <input type='text' name='page_title'></label><br>
            <label>Treść: <textarea name='page_content'></textarea></label><br>
            <label>Aktywna: <input type='checkbox' name='status'></label><br>
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
        $kategorie = explode("\n", $_POST['kategorie']); // Pobieramy kategorie z pola tekstowego
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


// Obsługa działań
$action = $_GET['action'] ?? 'list';

switch ($action) {
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

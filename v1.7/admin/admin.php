<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php
session_start();
require '../cfg.php';

// Funkcja hashowania hasła (dodatkowe zabezpieczenie)
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Funkcja weryfikacji hasła
function verifyPassword($input_password, $stored_hash) {
    return password_verify($input_password, $stored_hash);
}

// Funkcja generowania tokenu resetowania hasła
function generateResetToken() {
    return bin2hex(random_bytes(50));
}

function FormularzLogowania($error = '') {
    echo "<h2>Logowanie Administratora</h2>";
    if ($error) echo "<p style='color: red;'>$error</p>";
    echo "
        <form method='POST'>
            <label>Login: <input type='text' name='login' required></label><br>
            <label>Hasło: <input type='password' name='password' required></label><br>
            <input type='submit' value='Zaloguj'>
        </form>
    ";
}

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
    die("Błąd połączenia z bazą danych: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['login'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');

    $stmt = $db->prepare("SELECT password FROM administrators WHERE login = ?");
    $stmt->bind_param("s", $loginInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (verifyPassword($passwordInput, $admin['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['admin_login'] = $loginInput;
        } else {
            FormularzLogowania("Nieprawidłowe dane logowania.");
            exit;
        }
    } else {
        FormularzLogowania("Nieprawidłowe dane logowania.");
        exit;
    }
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    FormularzLogowania();
    exit;
}

// Funkcja wyświetlania listy podstron
function ListaPodstron($db) {
    $query = "SELECT id, page_title FROM page_list";
    $result = $db->query($query);
    echo "<h2>Lista podstron</h2>";
    echo "<a href='?action=add'>Dodaj nową podstronę</a><br><br>";
    echo "<table border='1'><tr><th>ID</th><th>Tytuł</th><th>Akcje</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['page_title']}</td>
            <td>
                <a href='?action=edit&id={$row['id']}'>Edytuj</a> | 
                <a href='?action=delete&id={$row['id']}'>Usuń</a>
            </td>
        </tr>";
    }
    echo "</table>";
}

// Funkcja edycji podstrony
function EdytujPodstrone($db, $id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['page_title'];
        $content = $_POST['page_content'];
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $db->prepare("UPDATE page_list SET page_title = ?, page_content = ?, status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssii", $title, $content, $status, $id);
        $stmt->execute();

        echo "<p>Podstrona została zaktualizowana.</p>";
    } else {
        $stmt = $db->prepare("SELECT page_title, page_content, status FROM page_list WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        echo "
        <h2>Edytuj podstronę</h2>
        <form method='POST'>
            <label>Tytuł: <input type='text' name='page_title' value='{$result['page_title']}'></label><br>
            <label>Treść: <textarea name='page_content'>{$result['page_content']}</textarea></label><br>
            <label>Aktywna: <input type='checkbox' name='status' " . ($result['status'] ? 'checked' : '') . "></label><br>
            <input type='submit' value='Zapisz zmiany'>
            <a href='?action=list'><button type='button'>Anuluj</button></a>
        </form>";
    }
}

// Funkcja dodawania nowej podstrony
function DodajNowaPodstrone($db) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['page_title'];
        $content = $_POST['page_content'];
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

// Obsługa działań
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'list':
            ListaPodstron($db);
            break;
        case 'edit':
            EdytujPodstrone($db, $_GET['id']);
            break;
        case 'delete':
            UsunPodstrone($db, $_GET['id']);
            break;
        case 'add':
            DodajNowaPodstrone($db);
            break;
        case 'logout':
            Wyloguj();
            break;
        default:
            echo "<p>Nieznane działanie.</p>";
    }
} else {
    echo "<div><a href='?action=list'>Lista podstron</a></div>";
}
echo "<div><a href='?action=logout'>Wyloguj</a></div>";
?>

<button style="margin-top: 10px;padding:10px 15px;" onclick="window.location.href='../index.php?idp=glowna'">Powrót</button>

</body>
</html>
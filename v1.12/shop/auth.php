<?php
session_start();

// polaczenie z baza
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moja_strona";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function registerUser($username, $email, $password) {
    global $pdo;
    
    try {
        // sprawdza uzytkownik istnieje w bazie danych
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            return "Username or email already exists";
        }
        
        // hashowanie 
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // dodawanie nowego uzytkownika
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);
        
        return true;
    } catch (PDOException $e) {
        return "Registration error: " . $e->getMessage();
    }
}

function loginUser($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        
        return "Invalid username or password";
    } catch (PDOException $e) {
        return "Login error: " . $e->getMessage();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// wymagane logowanie
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>
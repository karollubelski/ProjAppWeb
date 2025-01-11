<?php
// login.php - Login page
require_once 'auth.php';

$error = '';

if (isLoggedIn()) {
    header('Location: ../shop/cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $result = loginUser($_POST['username'], $_POST['password']);
    if ($result === true) {
        header('Location: ../shop/cart.php');
        exit();
    } else {
        $error = $result;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Amazon't</title>
    <link rel="stylesheet" href="../css/shop.css">
</head>
<body>
    <div class="auth-container">
        <h2>Login to Amazon't</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" class="auth-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" name="login">Login</button>
        </form>
        
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p>Forgot your password? <a href="../shop/password_reset.html">Reset password</a></p>
    </div>
</body>
</html>
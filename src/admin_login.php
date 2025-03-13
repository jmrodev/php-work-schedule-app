<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = $_POST['admin_user'] ?? '';
    $adminPassword = $_POST['admin_password'] ?? '';

    if ($adminUser === 'admin' && $adminPassword === 'zarini') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time(); // Set the time of login
        header('Location: register.php');
        exit;
    } else {
        echo "Usuario o contraseña de administrador incorrectos.";
    }
}
?>

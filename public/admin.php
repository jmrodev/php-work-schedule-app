<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = $_POST['admin_user'] ?? '';
    $adminPassword = $_POST['admin_password'] ?? '';

    // Replace these with your actual admin credentials
    $validAdminUser = 'admin';
    $validAdminPassword = 'zarini';

    if ($adminUser === $validAdminUser && $adminPassword === $validAdminPassword) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ../src/index.php');
        exit;
    } else {
        $message = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso de Administrador</title>
    <link rel="stylesheet" href="./styles.css">
</head>

<body>
    <header>
        <ul>
            <li>
                <a href="../index.php">Ir a la página de entrada/salida</a>
            </li>
        </ul>
    </header>
    <main>
        <h2>Acceso de Administrador</h2>
        <form action="../src/principal.php" method="POST">
            <label for="admin_user">Usuario:</label>
            <input type="text" id="admin_user" name="admin_user" required>

            <label for="admin_password">Contraseña:</label>
            <input type="password" id="admin_password" name="admin_password" required>

            <button type="submit">Ingresar</button>
        </form>
        <p>
            <?php if (isset($message)) : ?>
                <?php echo htmlspecialchars($message); ?>
            <?php endif; ?>
        </p>
    </main>
</body>

</html>
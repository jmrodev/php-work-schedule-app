<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: ../public/index.html');
    exit;
}

// Check for session timeout (3 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 180)) {
    session_unset();
    session_destroy();
    header('Location: ../public/index.html');
    exit;
}
$_SESSION['last_activity'] = time(); // Update last activity time

// Ruta al archivo JSON
$workersFile = __DIR__ . '/workers.json';

// Cargar trabajadores existentes
$workers = [];
if (file_exists($workersFile)) {
    $workers = json_decode(file_get_contents($workersFile), true);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Trabajadores</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
</head>
<body>
    <h1>Registros de Trabajadores</h1>

    <?php if (empty($workers)) : ?>
        <p class="no-records">No hay trabajadores registrados.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Número de Trabajador</th>
                    <th>Nombre</th>
                    <th>Registros (Entrada/Salida)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($workers as $worker) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($worker['worker_number']); ?></td>
                        <td><?php echo htmlspecialchars($worker['worker_name']); ?></td>
                        <td>
                            <?php if (empty($worker['logs'])) : ?>
                                <span class="no-records">No hay registros.</span>
                            <?php else : ?>
                                <ul>
                                    <?php foreach ($worker['logs'] as $log) : ?>
                                        <li>
                                            <?php echo htmlspecialchars($log['type'] === 'in' ? 'Entrada' : 'Salida'); ?>
                                            a las <?php echo htmlspecialchars($log['time']); ?>
                                            <?php if (isset($log['hours_worked'])) : ?>
                                                (<?php echo htmlspecialchars($log['hours_worked']); ?>)
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="index.php">Volver a la página de entrada/salida</a></p>
    <p><a href="register.php">Ir a la página de registro</a></p>

    <form method="POST" action="logout.php">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
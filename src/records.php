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

// Function to group logs by month
function groupLogsByMonth($logs) {
    $groupedLogs = [];
    foreach ($logs as $log) {
        $month = date('Y-m', strtotime($log['time']));
        if (!isset($groupedLogs[$month])) {
            $groupedLogs[$month] = [];
        }
        $groupedLogs[$month][] = $log;
    }
    return $groupedLogs;
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
        <pre>
Nombre,Legajo,Día,Hora Entrada,Hora Salida
<?php foreach ($workers as $worker) : ?>
    <?php if (!empty($worker['logs'])) : ?>
        <?php $groupedLogs = groupLogsByMonth($worker['logs']); ?>
        <?php foreach ($groupedLogs as $month => $logs) : ?>
            <?php foreach ($logs as $log) : ?>
                <?php if ($log['type'] === 'in') : ?>
                    <?php $inTime = $log['time']; ?>
                <?php else : ?>
                    <?php $outTime = $log['time']; ?>
                    <?php $day = date('Y-m-d', strtotime($inTime)); ?>
                    <?php $inHour = date('H:i:s', strtotime($inTime)); ?>
                    <?php $outHour = date('H:i:s', strtotime($outTime)); ?>
                    <?php echo htmlspecialchars($worker['worker_name']) . ',' . htmlspecialchars($worker['worker_number']) . ',' . $day . ',' . $inHour . ',' . $outHour . "\n"; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endforeach; ?>
        </pre>
    <?php endif; ?>

    <p><a href="index.php">Volver a la página de entrada/salida</a></p>
    <p><a href="register.php">Ir a la página de registro</a></p>

    <form method="POST" action="logout.php">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
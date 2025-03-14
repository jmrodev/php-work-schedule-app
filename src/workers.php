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
    <title>Lista del personal</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Lista del personal</h1>
        <?php if (empty($workers)) : ?>
            <p class="no-records">No hay personal registrado.</p>
        <?php else : ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Legajo</th>
                        <th>Día</th>
                        <th>Hora de Entrada</th>
                        <th>Hora de Salida</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workers as $worker) : ?>
                        <?php $rowspan = count($worker['work_schedule']); ?>
                        <?php $firstRow = true; ?>
                        <?php foreach ($worker['work_schedule'] as $day => $schedule) : ?>
                            <tr class="worker-row">
                                <?php if ($firstRow) : ?>
                                    <td rowspan="<?php echo $rowspan; ?>"><a href="worker_details.php?worker_number=<?php echo htmlspecialchars($worker['worker_number']); ?>"><?php echo htmlspecialchars($worker['worker_name']); ?></a></td>
                                    <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($worker['worker_number']); ?></td>
                                    <?php $firstRow = false; ?>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($day); ?></td>
                                <td><?php echo htmlspecialchars($schedule['start']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['end']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>

</html>
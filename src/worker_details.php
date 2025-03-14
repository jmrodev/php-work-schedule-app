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

// Obtener el número de trabajador de la solicitud
$workerNumber = $_GET['worker_number'] ?? '';

$workerDetails = null;
foreach ($workers as $worker) {
    if ($worker['worker_number'] === $workerNumber) {
        $workerDetails = $worker;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Detalles</h1>
        <div class="card-container">
            <div class="card-worker card">
                <?php if ($workerDetails) : ?>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($workerDetails['worker_name']); ?></p>
                    <p><strong>Legajo:</strong> <?php echo htmlspecialchars($workerDetails['worker_number']); ?></p>
                    <p><strong>Fecha de Nacimiento:</strong> <?php echo htmlspecialchars($workerDetails['date_of_birth']); ?></p>
                    <p><strong>Teléfono Celular:</strong> <a href="tel:<?php echo htmlspecialchars($workerDetails['cell_phone']); ?>"><?php echo htmlspecialchars($workerDetails['cell_phone']); ?></a></p>
                    <p><strong>Dirección:</strong> <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($workerDetails['address']); ?>" target="_blank"><?php echo htmlspecialchars($workerDetails['address']); ?></a></p>
                    <p><strong>Cargo:</strong> <?php echo htmlspecialchars($workerDetails['position']); ?></p>
                    <p><strong>¿Es maestra?:</strong> <?php echo $workerDetails['is_teacher'] ? 'Sí' : 'No'; ?></p>
                    <?php if ($workerDetails['is_teacher']) : ?>
                        <p><strong>Sala a cargo:</strong> <?php echo htmlspecialchars($workerDetails['room_in_charge']); ?></p>
                    <?php endif; ?>
            </div>
            <div class="table-detail card">
                <h2>Horario de trabajo</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Día</th>
                            <th>Hora de Entrada</th>
                            <th>Hora de Salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workerDetails['work_schedule'] as $day => $schedule) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($day); ?></td>
                                <td><?php echo htmlspecialchars($schedule['start']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['end']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else : ?>
            <p class="no-records">Trabajador no encontrado.</p>
        <?php endif; ?>
        </div>
    </main>
</body>

</html>
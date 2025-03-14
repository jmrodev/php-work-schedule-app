<?php
session_start();

// Ruta al archivo JSON
$workersFile = __DIR__ . '/data/workers.json';

$workers = [];
if (file_exists($workersFile)) {
    $workers = json_decode(file_get_contents($workersFile), true);
}

// Procesar el formulario de entrada/salida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workerNumber = $_POST['worker_number'] ?? '';
    $workerPassword = $_POST['worker_password'] ?? '';

    // Validar el número de trabajador (4 o 5 dígitos) y la contraseña (3 dígitos)
    if (preg_match('/^\d{4,5}$/', $workerNumber) && preg_match('/^\d{3}$/', $workerPassword)) {
        $workerFound = false;

        foreach ($workers as &$worker) {
            if ($worker['worker_number'] === $workerNumber && isset($worker['worker_password']) && $worker['worker_password'] === $workerPassword) {
                $workerFound = true;
                $currentTime = date('Y-m-d H:i:s');
                $currentTimeFormatted = date('H:i:s', strtotime($currentTime));

                // Verificar si el último registro es una entrada o salida
                $lastLog = end($worker['logs']);
                if (!$lastLog || $lastLog['type'] === 'out') {
                    // Registrar entrada
                    $worker['logs'][] = ['type' => 'in', 'time' => $currentTime];
                    $message = "<p>Hola: " . htmlspecialchars($worker['worker_name']) . "</p><p>Entrada $currentTimeFormatted.</p>";
                } else {
                    // Registrar salida y calcular horas trabajadas
                    $lastLogTime = new DateTime($lastLog['time']);
                    $currentLogTime = new DateTime($currentTime);
                    $interval = $lastLogTime->diff($currentLogTime);
                    $hoursWorked = $interval->format('%h horas %i minutos');

                    $worker['logs'][] = ['type' => 'out', 'time' => $currentTime, 'hours_worked' => $hoursWorked];
                    $message = "<p>Adiós: " . htmlspecialchars($worker['worker_name']) . "</p><p>Salida $currentTimeFormatted.</p><p>Tabajaste: $hoursWorked.</p>";
                }

                // Guardar en el archivo JSON
                file_put_contents($workersFile, json_encode($workers, JSON_PRETTY_PRINT));
                break;
            }
        }

        if (!$workerFound) {
            $message = "<p>Número de trabajador o contraseña incorrectos.</p>";
        }
    } else {
        $message = "<p>Número de trabajador o contraseña inválidos.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada/Salida</title>
    <link rel="stylesheet" href="./public/styles.css"> <!-- Estilos CSS -->
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="./public/admin.php">Acceso de Administrador</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Entrada/Salida</h1>

        <form method="POST" action="">
            <label for="worker_number">Legajo</label>
            <span>(4 o 5 dígitos):</span>
            <input type="text" id="worker_number" name="worker_number" required pattern="\d{4,5}" maxlength="5">

            <label for="worker_password">Contraseña</label>
            <span>(3 dígitos):</span>
            <input type="text" id="worker_password" name="worker_password" required pattern="\d{3}" maxlength="3">

            <button type="submit">Aceptar</button>
        </form>
        <?php if (isset($message)) : ?>
            <div><?php echo $message; ?></div>
        <?php endif; ?>
    </main>
</body>

</html>
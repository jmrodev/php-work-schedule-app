<?php
session_start();

// Ruta al archivo JSON
$workersFile = __DIR__ . '/workers.json';

$workers = [];
if (file_exists($workersFile)) {
    $workers = json_decode(file_get_contents($workersFile), true);
}

// Procesar el formulario de entrada/salida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workerNumber = $_POST['worker_number'] ?? '';
    $workerPassword = $_POST['worker_password'] ?? '';

    // Validar el número de trabajador (5 dígitos) y la contraseña (3 dígitos)
    if (preg_match('/^\d{5}$/', $workerNumber) && preg_match('/^\d{3}$/', $workerPassword)) {
        $workerFound = false;

        foreach ($workers as &$worker) {
            if ($worker['worker_number'] === $workerNumber && isset($worker['worker_password']) && $worker['worker_password'] === $workerPassword) {
                $workerFound = true;
                $currentTime = date('Y-m-d H:i:s');

                // Verificar si el último registro es una entrada o salida
                $lastLog = end($worker['logs']);
                if (!$lastLog || $lastLog['type'] === 'out') {
                    // Registrar entrada
                    $worker['logs'][] = ['type' => 'in', 'time' => $currentTime];
                    echo "Entrada registrada a las $currentTime.";
                } else {
                    // Registrar salida y calcular horas trabajadas
                    $lastLogTime = new DateTime($lastLog['time']);
                    $currentLogTime = new DateTime($currentTime);
                    $interval = $lastLogTime->diff($currentLogTime);
                    $hoursWorked = $interval->format('%h horas %i minutos');

                    $worker['logs'][] = ['type' => 'out', 'time' => $currentTime, 'hours_worked' => $hoursWorked];
                    echo "Salida registrada a las $currentTime. Horas trabajadas: $hoursWorked.";
                }

                // Guardar en el archivo JSON
                file_put_contents($workersFile, json_encode($workers, JSON_PRETTY_PRINT));
                break;
            }
        }

        if (!$workerFound) {
            echo "Número de trabajador o contraseña incorrectos.";
        }
    } else {
        echo "Número de trabajador o contraseña inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada/Salida</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <h1>Entrada/Salida</h1>
        <form method="POST" action="">
            <label for="worker_number">Legajo</label>
            <span>(5 dígitos):</span>

            <input type="text" id="worker_number" name="worker_number" required pattern="\d{5}" maxlength="5">

            <label for="worker_password">Contraseña</label>
            <span>(3 dígitos):</span>
            <input type="text" id="worker_password" name="worker_password" required pattern="\d{3}" maxlength="3">

            <button type="submit">Aceptar</button>
        </form>
    </main>
</body>

</html>
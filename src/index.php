<?php
session_start();

// Ruta al archivo JSON
$workersFile = __DIR__ . '/workers.json';

// Cargar trabajadores existentes
$workers = [];
if (file_exists($workersFile)) {
    $workers = json_decode(file_get_contents($workersFile), true);
}

// Procesar el formulario de entrada/salida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workerNumber = $_POST['worker_number'] ?? '';

    // Validar el número de trabajador (5 dígitos)
    if (preg_match('/^\d{5}$/', $workerNumber)) {
        $workerFound = false;

        foreach ($workers as &$worker) {
            if ($worker['worker_number'] === $workerNumber) {
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
            echo "Trabajador no encontrado.";
        }
    } else {
        echo "Número de trabajador inválido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Entrada/Salida</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
</head>
<body>
    <h1>Control de Entrada/Salida</h1>
    <form method="POST" action="">
        <label for="worker_number">Número de trabajador (5 dígitos):</label>
        <input type="text" id="worker_number" name="worker_number" required pattern="\d{5}" maxlength="5">
        
        <button type="submit">Marcar Entrada/Salida</button>
    </form>

    <p><a href="register.php">Ir a la página de registro</a></p>
</body>
</html>
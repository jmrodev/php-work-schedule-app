<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workerNumber5 = $_POST['workerNumber5'];
    $workerNumber3 = $_POST['workerNumber3'];
    $workerPassword = $_POST['workerPassword'];

    if (empty($workerNumber5) || empty($workerNumber3) || empty($workerPassword)) {
        echo "Números de trabajador o contraseña inválidos.";
        exit;
    }

    $workersFile = '../workers.json';
    if (!file_exists($workersFile)) {
        echo "No hay personal registrado.";
        exit;
    }

    $workers = json_decode(file_get_contents($workersFile), true);
    if (!is_array($workers)) {
        $workers = [];
    }

    $workerNumber = $workerNumber5 . $workerNumber3;
    $currentTime = date('Y-m-d H:i:s');

    foreach ($workers as &$worker) {
        if ($worker['worker_number'] == $workerNumber && $worker['worker_password'] == $workerPassword) {
            if (empty($worker['logs']) || end($worker['logs'])['type'] === 'out') {
                $worker['logs'][] = ['type' => 'in', 'time' => $currentTime];
                echo "Hora de entrada registrada: $currentTime";
            } else {
                $lastLog = end($worker['logs']);
                $lastLogTime = new DateTime($lastLog['time']);
                $currentLogTime = new DateTime($currentTime);
                $interval = $lastLogTime->diff($currentLogTime);
                $hoursWorked = $interval->format('%h horas %i minutos');

                $worker['logs'][] = ['type' => 'out', 'time' => $currentTime, 'hours_worked' => $hoursWorked];
                echo "Hora de salida registrada: $currentTime. Horas trabajadas: $hoursWorked";
            }
            file_put_contents($workersFile, json_encode($workers, JSON_PRETTY_PRINT));
            exit;
        }
    }

    echo "Trabajador no encontrado o contraseña incorrecta.";
}

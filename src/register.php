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

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workerNumber = $_POST['worker_number'] ?? '';
    $workerName = $_POST['worker_name'] ?? '';
    $workerPassword = $_POST['worker_password'] ?? '';

    // Validar el número de trabajador (5 dígitos), el nombre y la contraseña (3 dígitos)
    if (preg_match('/^\d{5}$/', $workerNumber) && !empty($workerName) && preg_match('/^\d{3}$/', $workerPassword)) {
        // Verificar si el trabajador ya está registrado
        $workerExists = false;
        foreach ($workers as $worker) {
            if ($worker['worker_number'] === $workerNumber) {
                $workerExists = true;
                break;
            }
        }

        if ($workerExists) {
            echo "El trabajador ya está registrado.";
        } else {
            // Registrar nuevo trabajador
            $workers[] = [
                'worker_number' => $workerNumber,
                'worker_name' => $workerName,
                'worker_password' => $workerPassword,
                'logs' => [] // Array para almacenar entradas y salidas
            ];

            // Guardar en el archivo JSON
            file_put_contents($workersFile, json_encode($workers, JSON_PRETTY_PRINT));
            echo "Trabajador registrado exitosamente.";
        }
    } else {
        echo "Número de trabajador, nombre o contraseña inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Trabajadores</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
</head>
<body>
    <h1>Nuevo personal</h1>
    <form method="POST" action="">
        <label for="worker_number">Número de legajo:</label>
        <input type="text" id="worker_number" name="worker_number" required pattern="\d{5}" maxlength="5">
        
        <label for="worker_name">Nombre:</label>
        <input type="text" id="worker_name" name="worker_name" required>
        
        <label for="worker_password">Contraseña :</label>
        <span>(3 dígitos ultimos del DNI)</span>
        <input type="text" id="worker_password" name="worker_password" required pattern="\d{3}" maxlength="3">
        
        <button type="submit">Registrar Trabajador</button>
    </form>

    <p><a href="index.php">Ir a la página de entrada/salida</a></p>
    <p><a href="records.php">Lista de asistencia</a></p>
    <form method="POST" action="logout.php">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
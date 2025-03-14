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
$holidaysFile = __DIR__ . '/holidays.json';

// Cargar trabajadores existentes
$workers = [];
if (file_exists($workersFile)) {
    $workers = json_decode(file_get_contents($workersFile), true);
}

// Cargar feriados existentes
$holidays = [];
if (file_exists($holidaysFile)) {
    $holidays = json_decode(file_get_contents($holidaysFile), true);
}

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_worker'])) {
    $workerNumber = $_POST['worker_number'] ?? '';
    $workerName = $_POST['worker_name'] ?? '';
    $workerPassword = $_POST['worker_password'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $cellPhone = $_POST['cell_phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $position = $_POST['position'] ?? '';
    $isTeacher = isset($_POST['is_teacher']) ? true : false;
    $roomInCharge = $_POST['room_in_charge'] ?? '';
    $workSchedule = $_POST['work_schedule'] ?? [];

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
            $workers[] = [
                'worker_number' => $workerNumber,
                'worker_name' => $workerName,
                'worker_password' => $workerPassword,
                'date_of_birth' => $dateOfBirth,
                'cell_phone' => $cellPhone,
                'address' => $address,
                'position' => $position,
                'is_teacher' => $isTeacher,
                'room_in_charge' => $roomInCharge,
                'work_schedule' => $workSchedule,
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
    <title>Registro</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
    <script>
        function autoCompleteSchedule() {
            const mondayStart = document.getElementById('lunes_start').value;
            const mondayEnd = document.getElementById('lunes_end').value;
            if (mondayStart && mondayEnd) {
                const days = ['martes', 'miércoles', 'jueves', 'viernes'];
                days.forEach(day => {
                    document.getElementById(day + '_start').value = mondayStart;
                    document.getElementById(day + '_end').value = mondayEnd;
                });
            }
        }

        function toggleRoomInCharge() {
            const isTeacher = document.getElementById('is_teacher').checked;
            const roomInChargeField = document.getElementById('room_in_charge_field');
            roomInChargeField.style.display = isTeacher ? 'block' : 'none';
        }
    </script>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <h1>Ingreso de datos</h1>
        <form method="POST" action="">
            <label for="worker_number">Número de legajo:</label>
            <input type="text" id="worker_number" name="worker_number" required pattern="\d{5}" maxlength="5">

            <label for="worker_name">Nombre:</label>
            <input type="text" id="worker_name" name="worker_name" required>

            <label for="worker_password">Contraseña :</label>
            <span>(3 dígitos ultimos del DNI)</span>
            <input type="text" id="worker_password" name="worker_password" required pattern="\d{3}" maxlength="3">

            <label for="date_of_birth">Fecha de Nacimiento:</label>
            <input type="date" id="date_of_birth" name="date_of_birth">

            <label for="cell_phone">Teléfono Celular:</label>
            <input type="text" id="cell_phone" name="cell_phone">

            <label for="address">Dirección:</label>
            <input type="text" id="address" name="address">

            <label for="position">Cargo:</label>
            <input type="text" id="position" name="position">

            <label for="is_teacher">¿Es maestra?</label>
            <input type="checkbox" id="is_teacher" name="is_teacher" onclick="toggleRoomInCharge()">

            <div id="room_in_charge_field" style="display: none;">
                <label for="room_in_charge">Sala a cargo:</label>
                <input type="text" id="room_in_charge" name="room_in_charge">
            </div>

            <label>Horario de trabajo:</label>
            <table>
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Hora de Entrada</th>
                        <th>Hora de Salida</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                    foreach ($days as $day) {
                        $dayLower = strtolower($day);
                        $disabled = ($dayLower === 'sábado' || $dayLower === 'domingo') ? 'disabled' : '';
                        echo "<tr>
                                <td>$day</td>
                                <td><input type='time' id='{$dayLower}_start' name='work_schedule[$day][start]' $disabled></td>
                                <td><input type='time' id='{$dayLower}_end' name='work_schedule[$day][end]' $disabled onblur='autoCompleteSchedule()'></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>

            <button type="submit" name="register_worker">Registrar</button>
        </form>
    </main>
</body>

</html>
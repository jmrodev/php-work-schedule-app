<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: ../public/admin.html');
    exit;
}

// Check for session timeout (3 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 180)) {
    session_unset();
    session_destroy();
    header('Location: ../public/admin.html');
    exit;
}
$_SESSION['last_activity'] = time(); // Update last activity time

// Ruta al archivo JSON
$workersFile = __DIR__ . '/workers.json';

// Cargar trabajadores existentes
$workers = [];
if (file_exists($workersFile)) {
    $workers = json_decode(file_get_contents($workersFile), true);
    if (!is_array($workers)) {
        $workers = [];
    }
}

// Your existing code that uses $workers
// For example:
foreach ($workers as $worker) {
    // Process each worker
}

// Ruta al archivo JSON
$holidaysFile = __DIR__ . '/holidays.json';

// Cargar feriados existentes
$holidays = [];
if (file_exists($holidaysFile)) {
    $holidays = json_decode(file_get_contents($holidaysFile), true);
}

// Procesar el formulario de configuración de feriados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_holiday'])) {
    $holidayDate = $_POST['holiday_date'] ?? '';

    if (!empty($holidayDate)) {
        $holidays[] = $holidayDate;
        file_put_contents($holidaysFile, json_encode($holidays, JSON_PRETTY_PRINT));
        echo "Feriado agregado exitosamente.";
    } else {
        echo "Fecha de feriado inválida.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Feriados</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
</head>
<body>
    
    <?php include 'header.php'; ?>
    <main>
        <h1>Configuración de Feriados</h1>
        <form method="POST" action="">
            <label for="holiday_date">Fecha de feriado:</label>
            <input type="date" id="holiday_date" name="holiday_date" required>
            
            <button type="submit" name="add_holiday">Agregar Feriado</button>
        </form>

        <div class="holiday-list">
            <h2>Lista de Feriados</h2>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($holidays as $holiday) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($holiday); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

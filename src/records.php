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

// Pagination settings
$logsPerPage = 10;
$totalLogs = 0;
foreach ($workers as $worker) {
    if (!empty($worker['logs'])) {
        $totalLogs += count($worker['logs']);
    }
}
$totalPages = ceil($totalLogs / $logsPerPage);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $logsPerPage;
$endIndex = $startIndex + $logsPerPage;

// Get filters from request
$filters = [
    'months' => $_GET['months'] ?? [],
];
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

    <form id="form-filter" method="GET" action="">
        <label>Mostrar por mes:</label>
        <div class="checkbox-group" id="months"> 
            <?php for ($i = 1; $i <= 12; $i++) : ?>
                <input type="checkbox" id="month<?php echo $i; ?>" name="months[]" value="<?php echo $i; ?>" <?php if (in_array($i, $filters['months'])) echo 'checked'; ?>>
                <label for="month<?php echo $i; ?>"><?php echo $i; ?></label>
            <?php endfor; ?>
        </div>
        <button type="submit">Filtrar</button>
    </form>

    <?php if (empty($workers)) : ?>
        <p class="no-records">No hay trabajadores registrados.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Legajo</th>
                    <th>Día</th>
                    <th>Hora Entrada</th>
                    <th>Hora Salida</th>
                    <th>Horas Trabajadas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $logCount = 0;
                foreach ($workers as $worker) :
                    if (!empty($worker['logs'])) :
                        $groupedLogs = groupLogsByMonth($worker['logs']);
                        foreach ($groupedLogs as $month => $logs) :
                            $monthNumber = (int)date('m', strtotime($month));
                            if (empty($filters['months']) || in_array($monthNumber, $filters['months'])) :
                                foreach ($logs as $log) :
                                    if ($logCount >= $startIndex && $logCount < $endIndex) :
                                        if ($log['type'] === 'in') :
                                            $inTime = $log['time'];
                                        else :
                                            $outTime = $log['time'];
                                            $day = date('Y-m-d', strtotime($inTime));
                                            $inHour = date('H:i:s', strtotime($inTime));
                                            $outHour = date('H:i:s', strtotime($outTime));
                                            $hoursWorked = $log['hours_worked'] ?? '';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($worker['worker_name']); ?></td>
                                                <td><?php echo htmlspecialchars($worker['worker_number']); ?></td>
                                                <td><?php echo $day; ?></td>
                                                <td><?php echo $inHour; ?></td>
                                                <td><?php echo $outHour; ?></td>
                                                <td><?php echo $hoursWorked; ?></td>
                                            </tr>
                                            <?php
                                        endif;
                                    endif;
                                    $logCount++;
                                endforeach;
                            endif;
                        endforeach;
                    endif;
                endforeach;
                ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($currentPage > 1) : ?>
                <a href="?page=<?php echo $currentPage - 1; ?>&<?php echo http_build_query(['months' => $filters['months']]); ?>">&laquo; Anterior</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(['months' => $filters['months']]); ?>" <?php if ($i == $currentPage) echo 'class="active"'; ?>><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($currentPage < $totalPages) : ?>
                <a href="?page=<?php echo $currentPage + 1; ?>&<?php echo http_build_query(['months' => $filters['months']]); ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <p><a href="index.php">Volver a la página de entrada/salida</a></p>
    <p><a href="register.php">Ir a la página de registro</a></p>

    <form method="POST" action="logout.php">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
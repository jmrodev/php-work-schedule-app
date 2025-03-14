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

// Function to group logs by month
function groupLogsByMonth($logs)
{
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
    'workers' => $_GET['workers'] ?? [],
    'weeks' => $_GET['weeks'] ?? [],
];

// Function to determine the week of the month
function getWeekOfMonth($date)
{
    $firstOfMonth = strtotime(date('Y-m-01', strtotime($date)));
    $dayOfMonth = date('j', strtotime($date));
    return ceil(($dayOfMonth + date('w', $firstOfMonth)) / 7);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Estilos CSS -->
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <h1>Registros</h1>

        <div class="container">
            <aside>
                <form id="form-filter" method="GET" action="">
                    <span>Mostrar por mes:</span>
                    <div class="checkbox-group" id="months">
                        <?php for ($i = 1; $i <= 12; $i++) : ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="month<?php echo $i; ?>" name="months[]" value="<?php echo $i; ?>" <?php if (in_array($i, $filters['months'])) echo 'checked'; ?>>
                                <label for="month<?php echo $i; ?>"><?php echo $i; ?></label>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <span>Mostrar por trabajador:</span>
                    <div class="checkbox-group" id="workers">
                        <?php foreach ($workers as $worker) : ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="worker<?php echo $worker['worker_number']; ?>" name="workers[]" value="<?php echo $worker['worker_number']; ?>" <?php if (in_array($worker['worker_number'], $filters['workers'])) echo 'checked'; ?>>
                                <label for="worker<?php echo $worker['worker_number']; ?>"><?php echo htmlspecialchars($worker['worker_name']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <span>Mostrar por semana:</span>
                    <div class="checkbox-group" id="weeks">
                        <?php for ($i = 1; $i <= 4; $i++) : ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="week<?php echo $i; ?>" name="weeks[]" value="<?php echo $i; ?>" <?php if (in_array($i, $filters['weeks'])) echo 'checked'; ?>>
                                <label for="week<?php echo $i; ?>">Semana <?php echo $i; ?></label>
                            </div>
                        <?php endfor; ?>
                        <div class="checkbox-item">
                            <input type="checkbox" id="weekRest" name="weeks[]" value="rest" <?php if (in_array('rest', $filters['weeks'])) echo 'checked'; ?>>
                            <label for="weekRest">Resto</label>
                        </div>
                    </div>
                    <button type="submit">Filtrar</button>
                </form>
            </aside>
            <main>
                <?php if (empty($workers)) : ?>
                    <p class="no-records">No hay personal registrado.</p>
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
                                if (!empty($worker['logs']) && (empty($filters['workers']) || in_array($worker['worker_number'], $filters['workers']))) :
                                    $groupedLogs = groupLogsByMonth($worker['logs']);
                                    foreach ($groupedLogs as $month => $logs) :
                                        $monthNumber = (int)date('m', strtotime($month));
                                        if (empty($filters['months']) || in_array($monthNumber, $filters['months'])) :
                                            foreach ($logs as $log) :
                                                $weekOfMonth = getWeekOfMonth($log['time']);
                                                if (empty($filters['weeks']) || in_array($weekOfMonth, $filters['weeks']) || (in_array('rest', $filters['weeks']) && $weekOfMonth > 4)) :
                                                    if ($logCount >= $startIndex && $logCount < $endIndex) :
                                                        if ($log['type'] === 'in') :
                                                            $inTime = $log['time'];
                                                        elseif (isset($inTime)) : // Ensure $inTime is set before using it
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
                                                endif;
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
                            <a href="?page=<?php echo $currentPage - 1; ?>&<?php echo http_build_query(['months' => $filters['months'], 'workers' => $filters['workers'], 'weeks' => $filters['weeks']]); ?>">&laquo; Anterior</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(['months' => $filters['months'], 'workers' => $filters['workers'], 'weeks' => $filters['weeks']]); ?>" <?php if ($i == $currentPage) echo 'class="active"'; ?>><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($currentPage < $totalPages) : ?>
                            <a href="?page=<?php echo $currentPage + 1; ?>&<?php echo http_build_query(['months' => $filters['months'], 'workers' => $filters['workers'], 'weeks' => $filters['weeks']]); ?>">Siguiente &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </main>
</body>

</html>
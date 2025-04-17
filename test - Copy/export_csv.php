<?php
// export_csv.php
require_once __DIR__ . '../models\Log.php';

// Fetch logs from the database
$log = new Log();
$logs = $log->getLogs();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="logs_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, ['ID', 'Level', 'Input', 'Timestamp', 'IP Address', 'User Agent', 'Suspicious']);

// Add log data to CSV
foreach ($logs as $log) {
    fputcsv($output, [
        $log['id'],
        $log['level'],
        $log['input'],
        $log['timestamp'],
        $log['ip_address'],
        $log['user_agent'],
        $log['suspicious'] ? 'Yes' : 'No'
    ]);
}

// Close output stream
fclose($output);
exit;
?>
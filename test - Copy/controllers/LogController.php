<?php
// controllers/LogController.php
require_once __DIR__ . '/../models/Log.php';

class LogController {
    private $model;

    public function __construct() {
        session_start();

        $_SESSION['admin_logged_in'] = true;

        $this->model = new Log();
    }

    public function index() {
        // Get current page from URL, default to 1
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20; // Number of items per page

        // Fetch logs for current page
        $logs = $this->model->getLogs($page, $perPage);

        // Get total number of logs for pagination
        $totalLogs = $this->model->getTotalLogs();
        $totalPages = ceil($totalLogs / $perPage);

        // Pass all data to view
        require __DIR__ . '/../views/Log.phtml';
    }

    public function exportCSV() {
        $this->checkAuth();

        $logs = $this->model->getAllLogsForExport();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="logs_export_'.date('Y-m-d_H-i-s').'.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Level', 'Input', 'Timestamp', 'IP Address', 'User Agent', 'Failed Attempts', 'Status']);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['level'],
                $log['input'],
                $log['timestamp'],
                $log['ip_address'],
                $log['user_agent'],
                $log['failed_attempts'],
                $log['suspicious'] ? 'Suspicious' : 'Safe'
            ]);
        }

        fclose($output);
        exit;
    }

    private function checkAuth() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
    }

    private function getGraphData() {
        return [
            'ip_distribution' => $this->model->getIpAddressDistribution(),
            'level_distribution' => $this->model->getLevelDistribution(),
            'suspicious_distribution' => $this->model->getSuspiciousDistribution()
        ];
    }

    public function showGraphs() {
        $this->checkAuth();
        $graphData = $this->getGraphData();
        require __DIR__ . '/../views/Log.phtml';
    }
}
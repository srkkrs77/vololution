<?php
// Включаем режим API, чтобы Evo не рисовала сайт
define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);

// Подключаем ядро Evo
include_once(__DIR__ . '/../../../index.php');

// Проверяем, что админ авторизован
if (!isset($_SESSION['mgrValidated'])) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied');
}

if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filePath = MODX_BASE_PATH . 'assets/backups/' . $file;
    
    if (file_exists($filePath)) {
        // Очищаем буферы вывода, если они случайно открылись
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Отдаем файл
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }
}

header('HTTP/1.1 404 Not Found');
die('File not found');
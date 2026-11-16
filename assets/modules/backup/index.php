<?php
if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
    die('<b>INCLUDE_ORDERING_ERROR</b>');
}

set_time_limit(0);
require_once(__DIR__ . '/Manager.php');

$_lang = [];
$lang = $modx->config['manager_language'] ?? 'english';
$langFile = __DIR__ . '/lang/' . $lang . '.php';
if (file_exists($langFile)) {
    include_once $langFile;
} else {
    include_once __DIR__ . '/lang/english.php';
}

$manager = new Manager($modx);
$message = '';
$backupDir = MODX_BASE_PATH . 'assets/backups/';

// Обработка удаления файла
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filePath = $backupDir . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
        $message = '<div style="color: green; padding: 10px; background: #eeffee; border-left: 4px solid green; margin-bottom: 20px;">Файл ' . $file . ' удален.</div>';
    }
}

// Обработка создания бэкапа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $includeDb = isset($_POST['include_db']);
    $includeFiles = isset($_POST['include_files']);

    if (!$includeDb && !$includeFiles) {
        $message = '<div style="color: red; padding: 10px; background: #ffeeee; border-left: 4px solid red; margin-bottom: 20px;">' . $_lang['backup_no_options'] . '</div>';
    } else {
        try {
            $filename = $manager->createBackup($includeDb, $includeFiles);
            $message = '<div style="color: green; padding: 10px; background: #eeffee; border-left: 4px solid green; margin-bottom: 20px;">' . $_lang['backup_success'] . '</div>';
        } catch (Exception $e) {
            $message = '<div style="color: red; padding: 10px; background: #ffeeee; border-left: 4px solid red; margin-bottom: 20px;">' . $_lang['backup_error'] . $e->getMessage() . '</div>';
        }
    }
}

// Получаем список существующих бэкапов
$backups = [];
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
            $backups[] = [
                'name' => $file,
                'size' => round(filesize($backupDir . $file) / (1024 * 1024), 2) . ' MB',
                'date' => date('Y-m-d H:i:s', filemtime($backupDir . $file))
            ];
        }
    }
    // Сортируем по убыванию даты (новые сверху)
    usort($backups, function($a, $b) { return strcmp($b['date'], $a['date']); });
}

// Формируем HTML-список бэкапов
$backupListHtml = '<h3>Существующие бэкапы:</h3>';
if (empty($backups)) {
    $backupListHtml .= '<p>Архивов пока нет.</p>';
} else {
    $backupListHtml .= '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr style="background: #eee; text-align: left;">
            <th style="padding: 8px; border: 1px solid #ddd;">Файл</th>
            <th style="padding: 8px; border: 1px solid #ddd;">Размер</th>
            <th style="padding: 8px; border: 1px solid #ddd;">Дата</th>
            <th style="padding: 8px; border: 1px solid #ddd;">Действия</th>
        </tr>';
    foreach ($backups as $b) {
        $downloadUrl = MODX_SITE_URL . 'assets/modules/backup/download.php?file=' . urlencode($b['name']);
        $deleteUrl = 'index.php?a=112&id=' . $_GET['id'] . '&action=delete&file=' . urlencode($b['name']);
        
        $backupListHtml .= '<tr>
            <td style="padding: 8px; border: 1px solid #ddd;">' . $b['name'] . '</td>
            <td style="padding: 8px; border: 1px solid #ddd;">' . $b['size'] . '</td>
            <td style="padding: 8px; border: 1px solid #ddd;">' . $b['date'] . '</td>
            <td style="padding: 8px; border: 1px solid #ddd;">
                <a href="' . $downloadUrl . '" style="color: #337ab7; text-decoration: none; margin-right: 15px;">⬇️ Скачать</a>
                <a href="' . $deleteUrl . '" onclick="return confirm(\'Вы уверены?\');" style="color: #d9534f; text-decoration: none;">❌ Удалить</a>
            </td>
        </tr>';
    }
    $backupListHtml .= '</table>';
}

$html = '
<div style="padding: 20px; font-family: Arial, sans-serif; max-width: 900px;">
    <h2>' . $_lang['backup_title'] . '</h2>
    <p>' . $_lang['backup_desc'] . '</p>
    
    ' . $message . '

    <form method="post" style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 30px;">
        <input type="hidden" name="action" value="create">
        <h3 style="margin-top: 0;">' . $_lang['backup_options'] . '</h3>
        
        <div style="margin-bottom: 15px;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="include_db" value="1" checked style="margin-right: 10px; width: 18px; height: 18px;">
                <span style="font-size: 16px;">' . $_lang['backup_include_db'] . '</span>
            </label>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="include_files" value="1" checked style="margin-right: 10px; width: 18px; height: 18px;">
                <span style="font-size: 16px;">' . $_lang['backup_include_files'] . '</span>
            </label>
        </div>

        <button type="submit" style="padding: 10px 20px; background: #5cb85c; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">
            ' . $_lang['backup_btn_create'] . '
        </button>
    </form>

    <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        ' . $backupListHtml . '
    </div>
</div>
';

echo $html;
<?php
/**
 * Модуль резервного копирования (Точка входа).
 * Backup Module (Entry point).
 */

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
    die('<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the EVO Content Manager.');
}

// Увеличиваем время выполнения скрипта, чтобы сервер не оборвал бэкап
// Increase script execution time so the server doesn't kill the backup
set_time_limit(0);

// Подключаем класс напрямую (автономно, без Composer)
// Include class directly (standalone, without Composer)
require_once(__DIR__ . '/Manager.php');

// Загружаем язык админки
// Load manager language
$_lang = [];
$lang = $modx->config['manager_language'] ?? 'english';
$langFile = __DIR__ . '/lang/' . $lang . '.php';

if (file_exists($langFile)) {
    include_once $langFile;
} else {
    include_once __DIR__ . '/lang/english.php'; // Fallback
}

// Создаем экземпляр менеджера, передавая ему конфиг Evo
// Create manager instance passing Evo config
$config = $modx->config;

// В Evo имя базы иногда не попадает в $modx->config, поэтому берем его из глобальной переменной
// In Evo the database name sometimes doesn't get into $modx->config, so we take it from the global variable
if (empty($config['dbase'])) {
    $config['dbase'] = $GLOBALS['dbase'] ?? $modx->db->config['dbase'] ?? '';
}

$manager = new Manager($config);
$message = '';

// Обработка создания бэкапа
// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $includeDb = isset($_POST['include_db']);
    $includeFiles = isset($_POST['include_files']);

    if (!$includeDb && !$includeFiles) {
        $message = '<div style="color: red; padding: 10px; background: #ffeeee; border-left: 4px solid red; margin-bottom: 20px;">' . $_lang['backup_no_options'] . '</div>';
    } else {
        try {
            $zipPath = $manager->createBackup($includeDb, $includeFiles);
            $filename = basename($zipPath);
            $downloadUrl = MODX_SITE_URL . 'assets/cache/' . $filename;
            
            $message = '<div style="color: green; padding: 10px; background: #eeffee; border-left: 4px solid green; margin-bottom: 20px;">
                ' . $_lang['backup_success'] . '<br><br>
                <a href="' . $downloadUrl . '" target="_blank" class="btn btn-primary" style="padding: 8px 15px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;">
                    ⬇️ Скачать ' . $filename . '
                </a>
            </div>';
        } catch (Exception $e) {
            $message = '<div style="color: red; padding: 10px; background: #ffeeee; border-left: 4px solid red; margin-bottom: 20px;">' . $_lang['backup_error'] . $e->getMessage() . '</div>';
        }
    }
}

// Интерфейс модуля
// Module UI
$html = '
<div style="padding: 20px; font-family: Arial, sans-serif; max-width: 800px;">
    <h2>' . $_lang['backup_title'] . '</h2>
    <p>' . $_lang['backup_desc'] . '</p>
    
    ' . $message . '

    <form method="post" style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
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
</div>
';

echo $html;
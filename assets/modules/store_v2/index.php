<?php
/**
 * VoloLution Store v2
 * Автономный модуль. Без src/, без Composer. Чистый PHP.
 * 
 * @category    module
 * @version     2.0.0
 * @internal    @modx_category Manager and Admin
 * @internal    @installset base
 * @lastupdate  17/11/2026
 */

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
    die('Access denied');
}
if (!isset($modx) || !is_object($modx)) {
    die('MODX is not available');
}

// Определяем язык
$mgrLang = $modx->config['manager_language'] ?? 'english';
$isRu = (strpos($mgrLang, 'russian') === 0);

// CSRF-токен (используем штатную функцию из нашего патча ядра)
$csrfToken = function_exists('getCurrentCsrfToken') ? getCurrentCsrfToken() : '';

// URL для AJAX-запросов к api.php этого же модуля
$apiUrl = MODX_SITE_URL . 'assets/modules/store_v2/api.php';
?>
<!DOCTYPE html>
<html lang="<?= $isRu ? 'ru' : 'en' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VoloLution Store v2</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    
    <style>
        /* Базовые стили каркаса */
        body { font-family: Arial, sans-serif; background: #f3f5f7; color: #222; margin: 0; padding: 20px; }
        .sv2-wrap { max-width: 960px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .sv2-header { border-bottom: 1px solid #e0e4e8; padding-bottom: 16px; margin-bottom: 24px; }
        .sv2-title { margin: 0; font-size: 20px; font-weight: 600; }
        .sv2-badge { display: inline-block; background: #eaf3fb; color: #3178c6; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 8px; }
        .sv2-status { margin-top: 16px; padding: 12px; background: #f8fafc; border-left: 3px solid #3178c6; border-radius: 4px; font-size: 14px; color: #475467; }
    </style>
</head>
<body>
    <div class="sv2-wrap">
        <div class="sv2-header">
            <h1 class="sv2-title">
                VoloLution Store v2
                <span class="sv2-badge">v2.0.0-alpha</span>
            </h1>
        </div>
        
        <div class="sv2-status" id="store-status">
            <?= $isRu ? 'Инициализация модуля... Проверка связи с API.' : 'Module initializing... Checking API connection.' ?>
        </div>

        <!-- Сюда JS будет загружать каталог -->
        <div id="store-catalog"></div>
    </div>

    <script>
        window.StoreV2 = {
            apiUrl: '<?= MODX_SITE_URL ?>assets/modules/store_v2/api.php',
            isRu: <?= $isRu ? 'true' : 'false' ?>,
            csrfToken: '<?= $csrfToken ?>'
        };
    </script>
    <script src="<?= MODX_SITE_URL ?>assets/modules/store_v2/app.js"></script>
</body>
</html>
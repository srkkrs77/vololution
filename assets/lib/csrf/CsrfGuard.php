<?php
/**
 * CSRF Guard для коннекторов модулей.
 * CSRF Guard for module connectors.
 */

if (!function_exists('csrfGuardCheck')) {
    /**
     * Проверяет CSRF-токен для AJAX-запросов модулей.
     * Checks CSRF token for module AJAX requests.
     */
    function csrfGuardCheck()
    {
        // Работаем только с POST-запросами.
        // Work only with POST requests.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        // Если функции Evo еще не загружены, подгружаем конфиг.
        // If Evo functions are not loaded yet, include config.
        if (!function_exists('validateCsrfToken')) {
            // Ищем путь к корню сайта (в зависимости от глубины файла модуля).
            // Find the site root path (depending on module file depth).
            $basePath = defined('MODX_BASE_PATH') ? MODX_BASE_PATH : dirname(__DIR__, 3) . '/';
            if (file_exists($basePath . 'manager/includes/config.inc.php')) {
                require_once $basePath . 'manager/includes/config.inc.php';
            }
        }

        // Проверяем токен. Если он невалиден — выдаем 403.
        // Check the token. If invalid — return 403.
        if (function_exists('validateCsrfToken') && !validateCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: text/plain; charset=UTF-8');
            exit('Invalid CSRF token.');
        }
    }
}
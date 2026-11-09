<?php
/**
 * VoloLution Store API — прокси каталога
 * Читает catalog.json с GitHub, отдаёт в формате store.js
 */

define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);
include_once(__DIR__ . '/../../../index.php');

$modx->db->connect();
if (empty($modx->config)) {
    $modx->getSettings();
}
if (!isset($_SESSION['mgrValidated'])) {
    header('HTTP/1.1 403 Forbidden');
    die('No access');
}
if (!$modx->hasPermission('exec_module')) {
    header('HTTP/1.1 403 Forbidden');
    die('No access');
}

header('Content-Type: application/json; charset=UTF-8');

$CATALOG_URL = 'https://raw.githubusercontent.com/srkkrs77/vololution/main/packages/catalog.json';
$BASE_URL    = 'https://github.com/srkkrs77/vololution/raw/main/packages/';

$get = isset($_GET['get']) ? preg_replace('/[^a-z_]/', '', strtolower($_GET['get'])) : 'start';

// Заглушки для эндпоинтов, которых у нас нет
$stubs = [
    'login'        => ['result' => false],
    'verifyuser'   => ['result' => false],
    'logout'       => ['result' => true],
    'download'     => ['result' => true],
    'saveuser'     => ['result' => true],
    'exituser'     => ['result' => true],
    'get_own_list' => [],
    'get_category' => [],
    'get_list'     => [],
];
if (isset($stubs[$get])) {
    echo json_encode($stubs[$get], JSON_UNESCAPED_UNICODE);
    exit;
}

// Загружаем каталог
$raw = @file_get_contents($CATALOG_URL);
$catalog = $raw ? json_decode($raw, true) : null;

if (empty($catalog['packages'])) {
    echo json_encode(['error' => 'catalog_unavailable'], JSON_UNESCAPED_UNICODE);
    exit;
}

$catToType = [
    'snippets' => 'snippet',
    'plugins'  => 'plugin',
    'modules'  => 'module',
    'tvs'      => 'snippet',
    'lib'      => 'snippet',
];

$catTitles = [
    'snippet' => 'Сниппеты',
    'plugin'  => 'Плагины',
    'module'  => 'Модули',
];

$mgrLang = substr($modx->config['manager_language'] ?? 'english', 0, 2);
if ($mgrLang !== 'ru') $mgrLang = 'en';

$allcategory = ['snippet' => [], 'plugin' => [], 'module' => []];
$category    = [];

foreach ($catalog['packages'] as $p) {
    $cat     = $p['category'] ?? 'snippets';
    $type    = $catToType[$cat] ?? 'snippet';
    $fileUrl = $BASE_URL . rawurlencode($p['file']);

    $desc = $p['description'] ?? '';
    if (is_array($desc)) {
        $desc = $desc[$mgrLang] ?? $desc['en'] ?? (reset($desc) ?: '');
    }
    if (!empty($p['patched'])) {
        $desc .= ' · VoloLution';
    }

    $allcategory[$type][] = [
        'id'           => $p['alias'],
        'cid'          => $p['alias'],
        'title'        => $p['name'],
        'description'  => $desc,
        'type'         => $type,
        'name_in_modx' => $p['name_in_modx'] ?? $p['name'],
        'version'      => $p['version'],
        'date'         => $catalog['updated'] ?? date('Y-m-d'),
        'author'       => $p['author'],
        'downloads'    => '0',
        'image'        => '',
        'url'          => [
            'fieldValue' => [[
                'file'    => $fileUrl,
                'version' => $p['version'],
                'date'    => $catalog['updated'] ?? '',
            ]],
        ],
        'cls'          => 'pack_install',
    ];
}

foreach ($allcategory as $type => $items) {
    $category[$type] = [
        'id'    => $type,
        'title' => $catTitles[$type] ?? $type,
        'count' => count($items),
        'tpl'   => 'list',
    ];
}

echo json_encode([
    'allcategory' => $allcategory,
    'category'    => $category,
    'version'     => $catalog['version'] ?? '1.0',
    'user'        => null,
], JSON_UNESCAPED_UNICODE);
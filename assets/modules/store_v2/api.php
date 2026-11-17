<?php
/**
 * VoloLution Store v2 — API
 * Читает catalog.json с GitHub, отдаёт каталог + список установленных пакетов.
 */

define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);
require_once dirname(__DIR__, 3) . '/index.php';

$modx->db->connect();
if (empty($modx->config)) {
    $modx->getSettings();
}

if (!isset($_SESSION['mgrValidated'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

if (!$modx->hasPermission('exec_module')) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'No permission']);
    exit;
}

if (function_exists('validateCsrfToken')) {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    if ($token !== '' && !validateCsrfToken($token)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}

$CATALOG_URL = 'https://raw.githubusercontent.com/srkkrs77/vololution/main/packages/catalog.json';

$mgrLang = substr($modx->config['manager_language'] ?? 'english', 0, 2);
if ($mgrLang !== 'ru') {
    $mgrLang = 'en';
}

$catToType = [
    'snippets' => 'snippet',
    'plugins'  => 'plugin',
    'modules'  => 'module',
    'tvs'      => 'snippet',
    'lib'      => 'snippet',
];

$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true,
        'header' => "User-Agent: VoloLution-Store/2.0\r\n"
    ]
]);
$json = @file_get_contents($CATALOG_URL, false, $context);

if ($json === false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Cannot fetch catalog']);
    exit;
}

$catalog = json_decode($json, true);
if (!is_array($catalog) || empty($catalog['packages'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Invalid catalog format']);
    exit;
}

$installed = ['snippets' => [], 'plugins' => [], 'modules' => []];
$tables = [
    'snippets' => $modx->getFullTableName('site_snippets'),
    'plugins'  => $modx->getFullTableName('site_plugins'),
    'modules'  => $modx->getFullTableName('site_modules'),
];
foreach ($tables as $type => $table) {
    $rs = $modx->db->select('name', $table);
    while ($row = $modx->db->getRow($rs)) {
        $installed[$type][$row['name']] = true;
    }
}

$packages = [];
foreach ($catalog['packages'] as $pkg) {
    $category = $pkg['category'] ?? '';
    $type = $catToType[$category] ?? 'snippet';
    $nameInModx = $pkg['name_in_modx'] ?? $pkg['name'];

    $desc = '';
    if (isset($pkg['description']) && is_array($pkg['description'])) {
        $desc = $pkg['description'][$mgrLang] ?? $pkg['description']['en'] ?? '';
    } elseif (is_string($pkg['description'] ?? null)) {
        $desc = $pkg['description'];
    }

    $dbType = $type === 'snippet' ? 'snippets' : ($type === 'plugin' ? 'plugins' : 'modules');
    $isInstalled = isset($installed[$dbType][$nameInModx]);

    $packages[] = [
        'name'         => $pkg['name'],
        'alias'        => $pkg['alias'] ?? '',
        'type'         => $type,
        'version'      => $pkg['version'] ?? '',
        'description'  => $desc,
        'author'       => $pkg['author'] ?? '',
        'homepage'     => $pkg['homepage'] ?? '',
        'file'         => $pkg['file'] ?? '',
        'patched'      => !empty($pkg['patched']),
        'name_in_modx' => $nameInModx,
        'installed'    => $isInstalled,
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success'  => true,
    'lang'     => $mgrLang,
    'updated'  => $catalog['updated'] ?? '',
    'total'    => count($packages),
    'packages' => $packages,
]);
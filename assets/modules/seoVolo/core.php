<?php
/**
 * seoVolo Settings — module core
 *
 * @package    seoVolo
 * @version    1.0.4
 * @license    GNU GPL
 */

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true || !$modx->hasPermission('exec_module')) {
    die('No access');
}

$mgrLang = $modx->config['manager_language'] ?? 'english';
$langPrefix = substr($mgrLang, 0, 2);
if (!in_array($langPrefix, ['ru', 'en', 'de'])) {
    $langPrefix = 'en';
}

$svLang = [];
$langFile = __DIR__ . '/lang/' . $langPrefix . '.php';
if (is_file($langFile)) {
    include $langFile;
} else {
    include __DIR__ . '/lang/en.php';
}

$moduleId  = (int)($_GET['id'] ?? 0);
$moduleUrl = 'index.php?a=112&id=' . $moduleId;

$fieldOrder = [
    'seo_org_name',
    'seo_org_logo',
    'seo_org_phone',
    'seo_org_email',
    'seo_org_address',
    'og_default_image',
    'seo_title_separator',
    'seo_twitter_site',
    'seo_twitter_creator',
];

$fileFields = [
    'seo_org_logo'     => 'images',
    'og_default_image' => 'images',
];

$toggleOrder = [
    'seo_keywords_enabled',
    'seo_hreflang_enabled',
];

$table = $modx->getFullTableName('system_settings');

// === Сохранение ===
$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seovolo_save'])) {
    $all = array_merge($fieldOrder, $toggleOrder);
    foreach ($all as $name) {
        $value = isset($_POST[$name]) ? trim((string)$_POST[$name]) : '';
        $exists = $modx->db->getValue(
            $modx->db->select('COUNT(*)', $table, "setting_name = '" . $modx->db->escape($name) . "'")
        );
        if ($exists) {
            $modx->db->update(['setting_value' => $value], $table, "setting_name = '" . $modx->db->escape($name) . "'");
        } else {
            $modx->db->insert(['setting_name' => $name, 'setting_value' => $value], $table);
        }
    }

    // Сброс кэша настроек — критично, без этого значения не подхватятся
    $cacheFile = MODX_BASE_PATH . 'assets/cache/siteCache.idx.php';
    if (is_file($cacheFile)) {
        @unlink($cacheFile);
    }
    if (method_exists($modx, 'clearCache')) {
        @$modx->clearCache();
    }

    $saved = true;
}

// === Чтение значений напрямую из БД ===
$allNames = array_merge($fieldOrder, $toggleOrder);
$escaped = [];
foreach ($allNames as $n) {
    $escaped[] = "'" . $modx->db->escape($n) . "'";
}
$rs = $modx->db->select('setting_name, setting_value', $table, 'setting_name IN (' . implode(',', $escaped) . ')');
$values = [];
while ($row = $modx->db->getRow($rs, 'assoc')) {
    $values[$row['setting_name']] = (string)$row['setting_value'];
}

$val = function ($k) use ($values) { return $values[$k] ?? ''; };
$esc = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$L   = function ($key) use ($svLang) { return $svLang[$key] ?? $key; };

$browser = $modx->config['which_browser'] ?? 'mcpuk';
$managerUrl = rtrim($modx->config['site_url'], '/') . '/manager/';

?>
<!DOCTYPE html>
<html lang="<?= $esc($langPrefix) ?>">
<head>
    <meta charset="utf-8">
    <title><?= $esc($L('title')) ?></title>
    <link rel="stylesheet" href="media/style/<?= $esc($modx->config['manager_theme']) ?>/style.css">
    <?= csrfTokenMeta() ?>
    <style>
        .sv-wrap { max-width: 900px; margin: 20px; }
        .sv-wrap h1 { font-size: 22px; margin-bottom: 20px; }
        .sv-table { width: 100%; border-collapse: collapse; }
        .sv-table th { text-align: left; width: 30%; padding: 10px; vertical-align: top; border-bottom: 1px dotted #ccc; }
        .sv-table td { padding: 10px; border-bottom: 1px dotted #ccc; }
        .sv-table input[type=text], .sv-table select { width: 70%; }
        .sv-table small { color: #888; }
        .sv-table .comment { color: #888; font-size: 12px; margin-top: 5px; }
        .sv-actions { margin-top: 20px; }
        .sv-alert { background: #d4edda; color: #155724; padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; }
        .sv-browse-btn { margin-left: 5px; padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>
<div class="sv-wrap">
    <h1><?= $esc($L('title')) ?></h1>

    <?php if ($saved): ?>
        <div class="sv-alert"><?= $esc($L('saved')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $esc($moduleUrl) ?>">
        <table class="sv-table">
            <?php foreach ($fieldOrder as $name): ?>
                <?php $meta = $svLang[$name] ?? [$name, '']; ?>
                <tr>
                    <th><?= $esc($meta[0]) ?><br><small>[<?= $esc($name) ?>]</small></th>
                    <td>
                        <input type="text" name="<?= $esc($name) ?>" id="<?= $esc($name) ?>" value="<?= $esc($val($name)) ?>">
                        <?php if (isset($fileFields[$name])): ?>
                            <button type="button" class="sv-browse-btn"
                                onclick="svBrowse('<?= $esc($name) ?>', '<?= $esc($fileFields[$name]) ?>')">
                                Browse
                            </button>
                        <?php endif; ?>
                        <div class="comment"><?= $esc($meta[1]) ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php foreach ($toggleOrder as $name): ?>
                <tr>
                    <th><?= $esc($L($name)) ?><br><small>[<?= $esc($name) ?>]</small></th>
                    <td>
                        <select name="<?= $esc($name) ?>">
                            <option value="0"<?= $val($name) !== '1' ? ' selected' : '' ?>><?= $esc($L('no')) ?></option>
                            <option value="1"<?= $val($name) === '1' ? ' selected' : '' ?>><?= $esc($L('yes')) ?></option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="sv-actions">
            <input type="hidden" name="seovolo_save" value="1">
            <button type="submit" class="btn btn-success"><?= $esc($L('save')) ?></button>
        </div>
    </form>
</div>

<script>
var svLastField = null;
var svManagerUrl = <?= json_encode($managerUrl) ?>;
var svBrowser = <?= json_encode($browser) ?>;

function svBrowse(fieldId, type) {
    svLastField = fieldId;
    var url = svManagerUrl + 'media/browser/' + svBrowser + '/browser.php?Type=' + type;
    var w = Math.round(screen.width * 0.7);
    var h = Math.round(screen.height * 0.7);
    var left = Math.round((screen.width - w) / 2);
    var top = Math.round((screen.height - h) / 2);
    var opts = 'toolbar=no,status=no,resizable=yes,dependent=yes,'
             + 'width=' + w + ',height=' + h + ',left=' + left + ',top=' + top;
    window.open(url, 'svBrowseWindow', opts);
}

function SetUrl(url) {
    if (!svLastField) return;
    var input = document.getElementById(svLastField);
    if (input) {
        input.value = url;
        input.dispatchEvent(new Event('change'));
    }
    svLastField = null;
}
</script>

</body>
</html>
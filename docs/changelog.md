# 📋 CHANGELOG — VoloLution CMS

**Сборка:** Evolution CMS 1.4.37 (Community Edition)  
**Цель:** эталонный дистрибутив — ядро + патчи + пакеты  
**Среда:** Windows + Laragon, PHP 8.5.10, nginx, MySQL 8.0.30  
**Сайт:** https://vol.test/ | БД: vol, префикс gyl8_  
**GitHub:** github.com/srkkrs77/vololution

---

## РАЗДЕЛ 1. ПРОПАТЧЕННЫЕ ФАЙЛЫ ЯДРА

| # | Файл | Что сделано |
|---|---|---|
| 1 | `manager/includes/protect.inc.php` | Убран `E_STRICT` |
| 2 | `manager/includes/extenders/dbapi.mysqli.class.inc.php` | `SET SESSION sql_mode='';` + `escape_string($s ?? '')` |
| 3 | `assets/lib/class.modxRTEbridge.php` | `manager_language ?? 'english'` |
| 4 | `manager/includes/preload.functions.inc.php` | CSRF-функции + безопасные cookie сессии |
| 5 | `manager/includes/header.inc.php` | CSRF-мета-тег + JS + базовые `??`-правки |
| 6 | `manager/includes/accesscontrol.inc.php` | CSRF-проверка POST + whitelist |
| 7 | `install/config.inc.tpl` | CSRF-функции + `!empty($modx->config['debug'])` |
| 8 | `manager/includes/config.inc.php` | `!empty($modx->config['debug'])` |
| 9 | `manager/actions/mutate_content.dynamic.php` | 4 правки `?? 0` / `?? 'none'` |
| 10 | `assets/plugins/codemirror/codemirror.plugin.php` | 2 правки `?? 0` / `?? 'none'` |
| 11 | `assets/plugins/managermanager/mm.inc.php` | `datepicker_offset ?? ''` |
| 12 | `assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.class.php` | `$string{0}` → `$string[0]` |
| 13 | `assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.functions.php` | `$string{0}` → `$string[0]` |

`manager/index.php` — **не менялся**.

---

## РАЗДЕЛ 2. ПАТЧИ ЯДРА — ПОСТРОЧНО

### 2.1 `manager/includes/protect.inc.php`

**Строка 6:**
```diff
- error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
+ error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
2.2 manager/includes/extenders/dbapi.mysqli.class.inc.php
Метод connect(), после query("{$connection_method} {$charset}"):

diff
  $this->conn->query("{$connection_method} {$charset}");
+ $this->conn->query("SET SESSION sql_mode='';");
  $tend = $modx->getMicroTime();
Метод escape(), строка ~129:

diff
  if (!is_array($s)) {
-     return $this->conn->escape_string($s);
+     return $this->conn->escape_string($s ?? '');
  }
2.3 assets/lib/class.modxRTEbridge.php
Метод initLang(), строка ~807:

diff
- $lang_name = !empty($_SESSION['mgrUsrConfigSet']['manager_language']) ? $_SESSION['mgrUsrConfigSet']['manager_language'] : $modx->config['manager_language'];
+ $lang_name = !empty($_SESSION['mgrUsrConfigSet']['manager_language']) ? $_SESSION['mgrUsrConfigSet']['manager_language'] : ($modx->config['manager_language'] ?? 'english');
2.4 manager/includes/header.inc.php — базовые ??-правки
Строки 6–8:

diff
  if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) { ... }
- $mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';
+ $modx_manager_charset = $modx_manager_charset ?? 'UTF-8';
+ $mxla = $modx_lang_attribute ?? 'en';
2.5 manager/actions/mutate_content.dynamic.php
Строка 601:

diff
- if($modx->config['use_breadcrumbs']) {
+ if(($modx->config['use_breadcrumbs'] ?? 0)) {
Строки 909–910:

diff
- $richtexteditorIds[$modx->config['which_editor']][] = 'ta';
- $richtexteditorOptions[$modx->config['which_editor']]['ta'] = '';
+ $which_editor = $modx->config['which_editor'] ?? 'none';
+ $richtexteditorIds[$which_editor][] = 'ta';
+ $richtexteditorOptions[$which_editor]['ta'] = '';
Строка ~918:

diff
- ($modx->config['which_editor'] == $editor ? ' selected="selected"' : '')
+ (($modx->config['which_editor'] ?? 'none') == $editor ? ' selected="selected"' : '')
Строка ~942:

diff
- $editor = $modx->config['which_editor'];
+ $editor = $modx->config['which_editor'] ?? 'none';
Строка 1375:

diff
- if ($modx->config['group_tvs'] > 2 && $templateVariablesOutput) {
+ if (($modx->config['group_tvs'] ?? 0) > 2 && $templateVariablesOutput) {
Строка 1383:

diff
- if($use_udperms == 1) {
+ if(($use_udperms ?? 0) == 1) {
2.6 assets/plugins/codemirror/codemirror.plugin.php
Строка 45:

diff
- } elseif ($modx->config['manager_theme_mode'] == 3 || $modx->config['manager_theme_mode'] == 4) {
+ } elseif (($modx->config['manager_theme_mode'] ?? 0) == 3 || ($modx->config['manager_theme_mode'] ?? 0) == 4) {
Строка 52:

diff
- $srte = ($modx->config['use_editor'] ? $modx->config['which_editor'] : 'none');
+ $srte = ($modx->config['use_editor'] ? ($modx->config['which_editor'] ?? 'none') : 'none');
2.7 ManagerManager / phpThumb
assets/plugins/managermanager/mm.inc.php, строка 212:

diff
- $modx->config['datepicker_offset']
+ $modx->config['datepicker_offset'] ?? ''
assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.class.php:

diff
- $filename{0}       →  $filename[0]
- $filename{1}       →  $filename[1]
- $which_convert{0}  →  $which_convert[0]
Удалён недостижимый break; после return $gdimg_source;.

assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.functions.php:

diff
- $string{$i}
+ $string[$i]

- $strtr_preg_quote[$escapeables{$i}]
+ $strtr_preg_quote[$escapeables[$i]]
РАЗДЕЛ 3. CSRF-ЗАЩИТА
3.1 manager/includes/preload.functions.inc.php
Метод startCMSSession() — было:

php
session_set_cookie_params($cookieExpiration, $cookiePath, $cookieDomain, $secure, true);
session_start();
$key = "modx.mgr.session.cookie.lifetime";
Стало:

php
session_set_cookie_params([
    'lifetime' => $cookieExpiration,
    'path'     => $cookiePath,
    'domain'   => $cookieDomain,
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
if (!isset($_SESSION['evo_sid_hash']) || $_SESSION['evo_sid_hash'] !== md5(session_id())) {
    session_regenerate_id(true);
    $_SESSION['evo_sid_hash'] = md5(session_id());
}
$key = "modx.mgr.session.cookie.lifetime";
csrf_token() и csrf_field() — было:

php
function csrf_token()
{
    if (isset($_SESSION)) {
        if (empty($_SESSION['_token'])) {
            $string = '';
            while (($len = strlen($string)) < 40) {
                $size = 40 - $len;
                $bytes = random_bytes($size);
                $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
            }
            $_SESSION['_token'] = $string;
        }
        return $_SESSION['_token'];
    }
    throw new RuntimeException('Application session store not set.');
}

function csrf_field()
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}
Стало:

php
function csrf_token()
{
    if (function_exists('getCurrentCsrfToken')) {
        return getCurrentCsrfToken();
    }
    if (isset($_SESSION)) {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
    throw new RuntimeException('Application session store not set.');
}

function csrf_field()
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
3.2 install/config.inc.tpl — CSRF-функции
Вставить перед include_once(MODX_MANAGER_PATH . 'includes/preload.functions.inc.php');

php
// === CSRF TOKEN MANAGEMENT ===
if (!function_exists('generateCsrfToken')) {

    define('CSRF_TOKEN_MAX', 10);
    define('CSRF_TOKEN_KEEP', 5);

    function generateCsrfToken()
    {
        $tokens = $_SESSION['csrf_tokens'] ?? [];
        if (count($tokens) >= CSRF_TOKEN_MAX) {
            $tokens = array_slice($tokens, -CSRF_TOKEN_KEEP, null, true);
        }
        $token = bin2hex(random_bytes(32));
        $tokens[$token] = true;
        $_SESSION['csrf_tokens'] = $tokens;
        return $token;
    }

    function getCurrentCsrfToken()
    {
        $tokens = $_SESSION['csrf_tokens'] ?? [];
        if (empty($tokens)) {
            return generateCsrfToken();
        }
        $keys = array_keys($tokens);
        return end($keys);
    }

    function getRequestCsrfToken()
    {
        return $_POST['csrf_token'] ?? $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    function validateCsrfToken($token = null)
    {
        if ($token === null) {
            $token = getRequestCsrfToken();
        }
        $tokens = $_SESSION['csrf_tokens'] ?? [];
        return !empty($token) && isset($tokens[$token]);
    }

    function checkCsrfToken($token = null)
    {
        if (validateCsrfToken($token)) {
            return;
        }

        $action = $_REQUEST['a'] ?? 'none';
        $userId = isset($GLOBALS['modx']) && method_exists($GLOBALS['modx'], 'getLoginUserID')
            ? $GLOBALS['modx']->getLoginUserID()
            : 'not logged in';

        $logMsg = 'CSRF token validation failed'
            . ' | action: ' . $action
            . ' | method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown')
            . ' | ip: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
            . ' | user_id: ' . $userId;

        if (isset($GLOBALS['modx']) && is_numeric($userId)) {
            $GLOBALS['modx']->logEvent(0, 3, $logMsg, 'CSRF Token Validation Failed');
        }

        $errorMsg = 'Invalid CSRF token. Please refresh the page and try again.';
        if (!empty($GLOBALS['modx']->config['debug'])) {
            $errorMsg .= "\n\nDebug: action=" . $action
                . ' | method=' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown')
                . ' | valid_tokens=' . count($_SESSION['csrf_tokens'] ?? []);
        }

        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain; charset=UTF-8');
        exit($errorMsg);
    }

    function clearCsrfTokens()
    {
        $_SESSION['csrf_tokens'] = null;
    }

    function csrfTokenField($token = null)
    {
        if ($token === null) {
            $token = getCurrentCsrfToken();
        }
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    function csrfTokenMeta($token = null)
    {
        if ($token === null) {
            $token = getCurrentCsrfToken();
        }
        return '<meta name="csrf-token" content="'
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
3.3 manager/includes/accesscontrol.inc.php
Внутри else { ... } после $modx->updateValidatedUserSession();:

php
// === CSRF check for POST ===
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfAction = isset($_REQUEST['a']) ? (int)$_REQUEST['a'] : 0;
    $csrfExcept = array(8, 67, 112, 118);
    $isCoreAjax = isset($_REQUEST['updateMsgCount']) || isset($_REQUEST['ajaxa']);
    if (!$isCoreAjax && !in_array($csrfAction, $csrfExcept, true)) {
        checkCsrfToken();
    }
}
// === /CSRF check ===
Whitelist:

8 — logout

67 — remove locks

112 — execute module

118 — settings ajax

3.4 manager/includes/header.inc.php — инъекция токена
Meta-тег — после <title>Evolution CMS</title>:

php
<?= csrfTokenMeta() ?>
JS-блок — перед <script src="media/script/main.js">:

html
<script>
(function() {
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;
    var csrfToken = token.getAttribute('content');

    function injectToken(form) {
        if (!form || form.tagName.toLowerCase() !== 'form') return;
        if ((form.method || '').toLowerCase() !== 'post') return;
        if (form.querySelector('input[name="csrf_token"]') || form.querySelector('input[name="_token"]')) return;
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = csrfToken;
        form.appendChild(input);
    }

    document.addEventListener('submit', function(e) {
        injectToken(e.target);
    }, true);

    var origSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function() {
        injectToken(this);
        return origSubmit.apply(this, arguments);
    };

    if (window.MutationObserver) {
        new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                m.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) return;
                    if (node.tagName === 'FORM') injectToken(node);
                    if (node.querySelectorAll) {
                        node.querySelectorAll('form').forEach(injectToken);
                    }
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    }

    var origFetch = window.fetch;
    window.fetch = function(url, opts) {
        opts = opts || {};
        opts.headers = opts.headers || {};
        if (opts.headers instanceof Headers) {
            opts.headers.set('X-CSRF-TOKEN', csrfToken);
        } else {
            opts.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        return origFetch.call(this, url, opts);
    };

    var origOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function() {
        this.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        return origOpen.apply(this, arguments);
    };
})();
</script>
3.5 manager/includes/config.inc.php и install/config.inc.tpl
В checkCsrfToken():

diff
- if (isset($GLOBALS['modx']) && $GLOBALS['modx']->config('debug', 0)) {
+ if (!empty($GLOBALS['modx']->config['debug'])) {
РАЗДЕЛ 4. ПАКЕТЫ С ПРАВКАМИ (5)
4.1 MultiTV — 10 правок в 2 файлах
Файл: assets/tvs/multitv/includes/multitv.class.php

Строка 39:

diff
- $this->language = $this->loadLanguage($this->modx->config['manager_language']);
+ $this->language = $this->loadLanguage($this->modx->config['manager_language'] ?? 'english');
Строка 53 (BolmerCMS):

diff
- $this->cmsinfo['thumbsdir'] = ($this->modx->config['thumbsDir']) ? $this->modx->config['thumbsDir'] . '/' : '';
+ $this->cmsinfo['thumbsdir'] = !empty($this->modx->config['thumbsDir']) ? $this->modx->config['thumbsDir'] . '/' : '';
Строка 60 (Evolution):

diff
- $this->cmsinfo['thumbsdir'] = ($this->modx->config['thumbsDir']) ? $this->modx->config['thumbsDir'] . '/' : '';
+ $this->cmsinfo['thumbsdir'] = !empty($this->modx->config['thumbsDir']) ? $this->modx->config['thumbsDir'] . '/' : '';
Строка 106:

diff
- $this->tvTemplates = 'templates' . $tvDefinitions['tpl_config'];
+ $this->tvTemplates = 'templates' . ($tvDefinitions['tpl_config'] ?? '');
Строка 151:

diff
- 'editor' => $this->modx->config['which_editor'],
+ 'editor' => $this->modx->config['which_editor'] ?? 'none',
Строка 270:

diff
- $this->templates = $settings[$this->tvTemplates];
+ $this->templates = $settings[$this->tvTemplates] ?? array();
Строка 443:

diff
- $this->richeditor = $this->modx->config['which_editor'];
+ $this->richeditor = $this->modx->config['which_editor'] ?? 'none';
Строки 616 и 631 (horizontal, vertical):

diff
- if ($this->fields[$fieldname]['width']) {
+ if (!empty($this->fields[$fieldname]['width'])) {
Строки 1218 и 1219 (compareSort):

diff
- $val_a = strtotime($a[$this->sortkey]);
- $val_b = strtotime($b[$this->sortkey]);
+ $val_a = strtotime((string)($a[$this->sortkey] ?? ''));
+ $val_b = strtotime((string)($b[$this->sortkey] ?? ''));
Строка 1271 (getMultiValue):

diff
- $tvOutput = $tvOutput[$this->tvName];
+ $tvOutput = $tvOutput[$this->tvName] ?? '';
Файл: assets/tvs/multitv/settings/default.setting.inc.php

Строка 11:

diff
- if (!$mmActive && !$GLOBALS['mtvjquery']) {
+ if (!$mmActive && empty($GLOBALS['mtvjquery'])) {
4.2 FormResults — 4 правки в 2 файлах
Файл: assets/modules/formresults/core/src/FormResults.php

Строки 8–10 (после use-блоков):

php
$autoload = MODX_BASE_PATH . 'vendor/autoload.php';
if (is_file($autoload)) { require_once $autoload; }
Файл: assets/modules/formresults/core/templates/forms_list.tpl

Строка 23:

diff
- <td><a href="<?= $moduleUrl ?>&type=<?= $form['alias'] ?>"><?= htmlspecialchars($form['caption']) ?></td>
+ <td><a href="<?= $moduleUrl ?>&type=<?= $form['alias'] ?? '' ?>"><?= htmlspecialchars($form['caption'] ?? '') ?></a></td>
Строка 25:

diff
- <?= $form['results_total'] ?>
+ <?= $form['results_total'] ?? 0 ?>
Строка 26:

diff
- <?= $form['last_result'] ?>
+ <?= $form['last_result'] ?? '' ?>
Инструкция: при установке переименовать config/*.sample.php → *.php.

4.3 SimpleTube — 2 правки в 2 файлах
Файл: assets/plugins/simpletube/lib/controller.class.php

Строка 43:

diff
- $params = array(..., 'forceDownload' => $forceDownload, 'lang'=>$lang, 'ytApiKey'=>$ytApiKey, 'vkAccessToken'=>$vkAccessToken);
+ $params = array(..., 'forceDownload' => $forceDownload ?? '', 'lang' => $lang ?? 'ru', 'ytApiKey' => $ytApiKey ?? '', 'vkAccessToken' => $vkAccessToken ?? '');
Файл: assets/snippets/simpletube/lib/SimpleTube/simpletube.class.php

Строка 25:

diff
- if (empty($cfg['ytApiKey'])) $cfg['ytApiKey'] = $pluginParams['ytApiKey'];
+ if (empty($cfg['ytApiKey'])) $cfg['ytApiKey'] = $pluginParams['ytApiKey'] ?? '';
4.4 evoSearch — 5 правок в 1 файле
Файл: assets/plugins/evoSearch/plugin.class.php

Метод emptyExcluded:

diff
- if ($excluded = $this->modx->config['evoSearch_exclude'])
+ if ($excluded = ($this->modx->config['evoSearch_exclude'] ?? ''))
Метод makeSQLForSelectWords:

diff
- if (isset($this->modx->config['evoSearch_table_prefix']))
+ if (!empty($this->modx->config['evoSearch_table_prefix']))
Метод __construct:

diff
- $this->cfg = $modx->config['evoSearch']
+ $this->cfg = $modx->config['evoSearch'] ?? []
Метод getDicts:

diff
- $dict = $this->modx->config['evoSearch_dict']
+ $dict = $this->modx->config['evoSearch_dict'] ?? ''
Метод Words2BaseForm:

diff
- $excluded = $this->cfg['excludeWords']
+ $excluded = $this->cfg['excludeWords'] ?? ''
4.5 editDocs — 5 групп правок в 2 файлах
Файл: assets/lib/MODxAPI/modResource.php

Метод save():

diff
- in_array($_SESSION['mgrRole'], [1])
+ in_array($_SESSION['mgrRole'], [1, 4])
Файл: assets/modules/editdocs/editdocs.class.php

Группа 1 — max_rows ?? 0 (3 места):

diff
- $this->table($this->clearEmptyRows($sheetData), $this->params['max_rows'])
+ $this->table($this->clearEmptyRows($sheetData), $this->params['max_rows'] ?? 0)

- $this->table($_SESSION['data'], $this->params['max_rows'])
+ $this->table($_SESSION['data'], $this->params['max_rows'] ?? 0)
Группа 2 — fputcsv (4 места):

diff
- fputcsv($file, $header, $dm);
+ fputcsv($file, $header, $dm, '"', '\\');

- fputcsv($file_temp, $header, $dm);
+ fputcsv($file_temp, $header, $dm, '"', '\\');

- fputcsv($file, $import, $dm);
+ fputcsv($file, $import, $dm, '"', '\\');

- fputcsv($file_temp, $import_tmp, $dm);
+ fputcsv($file_temp, $import_tmp, $dm, '"', '\\');
Группа 3 — need_xls (2 места):

diff
- $out = $_SESSION['export_start'] . '|' . $_SESSION['export_total']. '|' . $_POST['need_xls'] ;
+ $out = $_SESSION['export_start'] . '|' . $_SESSION['export_total'] . '|' . ($_POST['need_xls'] ?? '') ;

- if($_POST['need_xls']==1) {
+ if(($_POST['need_xls'] ?? 0) == 1) {
Группа 4 — защита $_POST/$_FILES:

diff
# editDoc()
- $id = $_POST['id'];          →  $id = $_POST['id'] ?? 0;
- $data = $_POST['dat'];       →  $data = $_POST['dat'] ?? '';
- $pole = $_POST['pole'];      →  $pole = $_POST['pole'] ?? '';

# getAllList()
- $_POST['bigparent']          →  $_POST['bigparent'] ?? ''
- $_POST['tree']               →  $_POST['tree'] ?? ''
- $_POST['orderas']            →  $_POST['orderas'] ?? ''

# uploadFile()
- $_FILES["myfile"]["error"]   →  $_FILES["myfile"]["error"] ?? 0

# massMove()
- $_POST['parent1']            →  $_POST['parent1'] ?? ''
- $_POST['parent2']            →  $_POST['parent2'] ?? ''
Группа 5 — вспомогательная защита:

diff
# importReady()
- if ($_POST['tpl'] != 'file')          →  if (($_POST['tpl'] ?? 'file') != 'file')
- $testing = $_POST['test'];            →  $testing = $_POST['test'] ?? false;

# megaPrepare()
- foreach ($_POST['sravxls'] as ...)    →  if (!empty($_POST['sravxls']) && !empty($_POST['sravbd'])) { foreach ... }

# smallPrepare()
- $_POST['replacement']                 →  $_POST['replacement'] ?? ''
- $data[$_POST['replace']]              →  $data[$_POST['replace']] ?? ''

# unpublished()
- $_POST['unpub']                       →  $_POST['unpub'] ?? 0

# saveConfig()
- $params['save_config']                →  $params['save_config'] ?? ''
- $params['folder']                     →  $params['folder'] ?? ''

# treeCategories()
- $_POST['parimp']                      →  $_POST['parimp'] ?? 0
РАЗДЕЛ 5. ПАКЕТЫ БЕЗ ПРАВОК (9)
#	Пакет	Тип	Назначение
1	DocLister	сниппет	Универсальный вывод документов
2	FormLister	сниппет	Обработка форм
3	SimpleGallery	сниппет	Простая галерея изображений
4	EvoBabel	плагин	Мультиязычность
5	evoFileManagerDialog	плагин	Диалог файлового менеджера
6	FirstChildRedirect	плагин	Редирект родителя на первого потомка
7	MonthDate	сниппет	Вывод даты по-русски
8	WebP Converter	плагин	Автоконвертация JPG/PNG в WebP
9	DLSiblings	сниппет	Вывод соседних документов (prev/next)
РАЗДЕЛ 6. ОКРУЖЕНИЕ
MySQL: 8.0.30 (Laragon 6 не поддерживает 8.4).

nginx: C:\laragon\etc\nginx\sites-enabled\vol.test.conf.

PHP: 8.5.10.

Strict mode: предупреждение в installer не блокирует установку.

РАЗДЕЛ 7. СТРУКТУРА ПРОЕКТА
text
_my_extras/
├── core/
├── optional/
├── custom/
└── legacy/

_my_patches/
├── README.md
└── CHANGELOG.md

GitHub: github.com/srkkrs77/vololution
├── packages/
├── extras-module/
└── dist/
РАЗДЕЛ 8. ROADMAP
Этап 1 — SEO + живой поиск
SEO-плагин: canonical, meta, OG/Twitter, Schema.org, sitemap.xml, robots.txt, hreflang, breadcrumbs.

TV-поле с индикатором длины description.

Живой поиск (AJAX autocomplete) с настраиваемым выводом.

Этап 3 — Социальный слой
Комментарии (модерация + авторизация).

Авторизация (email/password + соцсети: VK, Google, Facebook, Яндекс, Mail.ru).

Этап 5 — Магазин
Каталог, корзина, оформление.

Платёжные системы: ЮKassa, Robokassa, Тинькофф.

Доставка: СДЭК, Почта России, самовывоз.

Этап 6 — Долгосрочные
Мультисайтовость.

Мультиязычность (расширение EvoBabel).

Этап 7 — Инфраструктура
Модуль бэкапа.

PDO-адаптер.

Свой установщик.

Закрытие CSRF-дыр — см. раздел 9.1.

РАЗДЕЛ 9. ТЕХДОЛГ
9.1 CSRF — 4 задачи
Задача A. Ротация токенов на login/logout
Где вызывать clearCsrfTokens() (4 точки):

manager/includes/accesscontrol.inc.php — при успешном логине менеджера

manager/processors/logout.processor.php — при логауте менеджера

manager/includes/accesscontrol-not-mgr.inc.php — при логине web-пользователя

Плагин web-логаута — при выходе web-пользователя

Что делать: после успешного логина и перед редиректом при логауте вызвать:

php
if (function_exists('clearCsrfTokens')) {
    clearCsrfTokens();
}
Критерий готовности: после logout старый токен → 403, после login — новый токен в meta-теге.

Задача B. Origin / Referer check
Где: install/config.inc.tpl, функция checkCsrfToken(), перед header('HTTP/1.1 403 Forbidden');

Что вставить:

php
$origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
if ($origin !== '') {
    $siteUrl = defined('MODX_SITE_URL') ? MODX_SITE_URL : '';
    if ($siteUrl && strpos($origin, $siteUrl) !== 0) {
        if (isset($GLOBALS['modx']) && is_numeric($userId)) {
            $GLOBALS['modx']->logEvent(0, 3, 'CSRF origin mismatch: ' . $origin, 'CSRF Origin Failed');
        }
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain; charset=UTF-8');
        exit('Invalid origin.');
    }
}
Критерий готовности: POST с чужого Origin → 403 + запись в Event log.

Задача C. Защита connector'ов модулей
Аудит connector'ов — открыть папки и найти файлы:

assets/modules/store/ — искать connector.php, ajax.php, action.php

assets/tvs/multitv/ — искать connector.php, ajax.php

assets/plugins/evoSearch/ — искать ajax.php, controller.php

assets/modules/editdocs/ — искать ajax.php, connector.php

Подход (выбран вариант B):

Создать общий guard-класс:

php
<?php
// assets/lib/csrf/CsrfGuard.php
if (!function_exists('csrfGuardCheck')) {
    function csrfGuardCheck() {
        if (!function_exists('validateCsrfToken')) {
            require_once MODX_MANAGER_PATH . 'includes/config.inc.php';
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (!validateCsrfToken()) {
            header('HTTP/1.1 403 Forbidden');
            exit('Invalid CSRF token.');
        }
    }
}
В каждом connector в начало:

php
require_once MODX_BASE_PATH . 'assets/lib/csrf/CsrfGuard.php';
csrfGuardCheck();
Прокинуть токен в JS connector'ов: подключить csrfTokenMeta() в HTML-обёртку + скопировать JS-перехват из header.inc.php.

Критерий готовности: установка пакета через Store работает, запрос без токена → 403.

Задача D. Убрать дублирование JS в header.inc.php
Где: manager/includes/header.inc.php

Что: два CSRF-JS-блока. Объединить в один:

html
<script>
(function() {
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;
    var csrfToken = token.getAttribute('content');
    var managerOrigin = window.location.origin;

    function isSameOrigin(url) {
        try { return new URL(url, window.location.href).origin === managerOrigin; }
        catch (e) { return false; }
    }

    function isStateChanging(method) {
        method = String(method || 'GET').toUpperCase();
        return method !== 'GET' && method !== 'HEAD' && method !== 'OPTIONS';
    }

    function injectToken(form) {
        if (!form || form.tagName.toLowerCase() !== 'form') return;
        if ((form.method || '').toLowerCase() !== 'post') return;
        if (form.querySelector('input[name="csrf_token"]') || form.querySelector('input[name="_token"]')) return;
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = csrfToken;
        form.appendChild(input);
    }

    document.addEventListener('submit', function(e) { injectToken(e.target); }, true);

    var origSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function() {
        injectToken(this);
        return origSubmit.apply(this, arguments);
    };

    if (window.MutationObserver) {
        new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                m.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) return;
                    if (node.tagName === 'FORM') injectToken(node);
                    if (node.querySelectorAll) node.querySelectorAll('form').forEach(injectToken);
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    }

    var origFetch = window.fetch;
    if (origFetch) {
        window.fetch = function(url, opts) {
            opts = opts || {};
            var method = opts.method || 'GET';
            if (isStateChanging(method) && isSameOrigin(typeof url === 'string' ? url : url.url)) {
                if (opts.headers instanceof Headers) opts.headers.set('X-CSRF-TOKEN', csrfToken);
                else { opts.headers = opts.headers || {}; opts.headers['X-CSRF-TOKEN'] = csrfToken; }
            }
            return origFetch.call(this, url, opts);
        };
    }

    var origOpen = XMLHttpRequest.prototype.open;
    var origSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function(method, url) {
        this._evoCsrfMethod = method;
        this._evoCsrfUrl = url;
        return origOpen.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function() {
        if (isStateChanging(this._evoCsrfMethod) && isSameOrigin(this._evoCsrfUrl)) {
            try { this.setRequestHeader('X-CSRF-TOKEN', csrfToken); } catch (e) {}
        }
        return origSend.apply(this, arguments);
    };
})();
</script>
Критерий готовности: в header.inc.php — один CSRF-JS-блок. Работает form.submit(), формы, fetch, XHR.

9.2 Пакеты
editdocs.class.php — остались места без ??, не падают.

save_content.processor.php, строка 614 — $use_udperms без ??, warning был один раз.

РАЗДЕЛ 10. РЕЗУЛЬТАТЫ ТЕСТИРОВАНИЯ
Дата: 07.10.2026
Сайт: vol.test | БД: vol, префикс gyl8_

#	Тест	Результат
1	Сохранение документа	✅
2	Сохранение шаблона	✅
3	Сохранение TV	✅
4	Сохранение конфигурации	✅
5	Logout / login	✅
6	TinyMCE4	✅
7	CodeMirror	✅
8	Установка всех 14 пакетов	✅
9	CSRF-403 на битом токене	✅
10	Лог ошибок	✅ чист
РАЗДЕЛ 11. ФИЛОСОФИЯ ПРОЕКТА
VoloLution CMS — не форк Evolution CMS, а сборка:

text
Ядро Evolution CMS 1.4.37 (Community Edition)
+ 13 пропатченных файлов ядра
+ CSRF-защита и безопасные сессии
+ 5 пропатченных пакетов
+ 9 проверенных пакетов без правок
+ собственные дополнения (в планах)
= рабочая CMS под PHP 8.5
Принципы:

Эволюция, не революция.

Глубина там, где нужно — CSRF/безопасность правим ядро.

Свои дополнения — сразу на PHP 8.5.

Документируем каждый патч.

Плагины и сниппеты — где можно, ядро — где нужно.

РАЗДЕЛ 12. CE-ЭКОСИСТЕМА
Ядро:

Репозиторий: github.com/evocms-community/evolution

Последний релиз CE 1.4.36 — 13.11.2025

Официальная 1.4 (evo.im) — только security fixes

CE продолжает развиваться

Сообщество:

Telegram: @evo_cms

Форум: community.evocms.ru

Ресурс: evocms.ru

Разработчики:

Pathologic — DocLister, FormLister, MultiCategories. GitHub: github.com/Pathologic

Мониторинг безопасности:

github.com/extras-evolution/security-fix

Базы CVE: opencve.io, attackerkb.com

Проверка раз в месяц

План реагирования на уязвимость:

Проверить релизы CE.

Проверить коммиты в evocms-community/evolution.

Спросить в Telegram @evo_cms.

Тест патча на vol.test.

Внести в дистрибутив + CHANGELOG.

РАЗДЕЛ 13. СЛЕДУЮЩИЕ ШАГИ
Создать docs/roadmap.md и docs/CE-community.md на GitHub.

Свой Extras-модуль (отвязка от extras.evo.im).

Отвязка от обновлений Evo.

Закрытие CSRF-дыр (раздел 9.1).

Этап 1 — sSeo, живой поиск.

РАЗДЕЛ 14. СВОЙ EXTRAS-STORE — ОТВЯЗКА ОТ EVO.IM
=================================================
Подробное описание всех правок по файлам.

14.1. Цель и результат
----------------------
Заменить источник каталога пакетов:
  было:  extras.evo.im / extras.evocms.ru
  стало: github.com/srkkrs77/vololution/packages

Убрать наследие evo.im: fancybox, логин, self-update,
чёрный список, декоративные картинки MODX,
сравнение версий.

Сохранить UI и AJAX-установку оригинального Store.

14.2. Репозиторий пакетов
-------------------------
GitHub:        github.com/srkkrs77/vololution
Папка:         /packages/
Файл каталога: /packages/catalog.json

16 ZIP с именами в формате <alias>.zip:
  DLSiblings.zip, DocLister.zip, FirstChildRedirect.zip,
  FormLister.zip, FormResults.zip, MonthDate.zip,
  MultiCategories.zip, SimpleGallery.zip, SimpleTube.zip,
  editDocs.zip, evoBabel.zip, evoFileManagerDialog.zip,
  evoSearch.zip, multiTV.zip, phpThumb.zip, webP.zip

Все переименованы из исходных имён вида
«DocLister-master.zip», «FormLister-1.21.2.zip»,
«evobabel-0.2-master.zip» в единый lowercase-формат.

Распределение по категориям:
  Сниппеты:    DocLister, FormLister, DLSiblings,
               MonthDate, FirstChildRedirect
  Плагины:     SimpleGallery, SimpleTube, evoSearch,
               EvoBabel, evoFileManagerDialog,
               WebP Converter, MultiCategories
  TV:          MultiTV
  Модули:      FormResults, editDocs
  Библиотеки:  phpThumb

Поле name_in_modx задано там, где имя в БД
отличается от name:
  multiTV  → "multiTV"
  phpthumb → "phpthumb"
  evoBabel → "evoBabel"

14.3. ПРАВКИ core.php
---------------------
Файл: assets/modules/store/core.php

14.3.1. Версия модуля
Было:  $version = "0.1.3";
Стало: $version = "1.0.0";

14.3.2. Удалён fallback на extras.evo.im
Было (в case 'install'):
    $url = "http://extras.evo.im/get.php?get=file&cid=".$id;
Стало:
    $Store->errors[] = 'URL пакета не передан (file is empty)';
    $Store->quit();

14.3.3. Класс Store — свойство errors
Было:
    class Store{
        public $lang;
        public $language;
Стало:
    class Store{
        public $lang;
        public $language;
        public $errors = [];

  Причина: динамическое свойство $Store->errors[] в PHP 8.2+
  вызывает deprecated warning.

14.3.4. Метод downloadFile() — переписан
Изменения:
  - fopen($url, "rb") → @fopen(...) без warning
  - проверка $newf после fopen (было — без проверки)
  - добавлен curl_close($ch)
  - catch (Exception $e) → catch (\Exception $e)
  - понятные тексты ошибок

14.3.5. Обнаружение установленных пакетов
Было (в default case):
    while($row = $modx->db->GetRow($result)) {
        $PACK[$value][$row['name']] = $Store->get_version($row['description']);
    }
Стало:
    while($row = $modx->db->GetRow($result)) {
        $PACK[$value][$row['name']] = '1';
    }

  Причина: get_version() искал <strong>версию</strong> в описании,
  но в большинстве .tpl-пакетов тега <strong> нет.
  Store считал такие пакеты «не установленными».
  Теперь проверка только по факту наличия имени в БД.

14.3.6. Прочие мелкие правки
  - $_POST['res'] ?? '' в case 'saveuser'
  - $_SESSION['mgrEmail'] ?? '' в default case
  - $dir = '' + проверка «Пустой архив» в блоке install
  - catch (\Exception $e) — глобальный класс

14.3.7. Функция get_version() — оставлена, но не вызывается
    function get_version($text){
        preg_match('/<strong>(.*)<\/strong>/s',$text, $match);
        return isset($match[1]) ? $match[1] : '';
    }
  Можно удалить в следующей итерации.

14.4. НОВЫЙ ФАЙЛ api.php
------------------------
Путь: assets/modules/store/api.php
Размер: ~140 строк

Назначение:
  Прокси каталога. Читает catalog.json с GitHub,
  отдаёт в формате, ожидаемом store.js.

14.4.1. Инициализация MODX
    define('MODX_API_MODE', true);
    define('IN_MANAGER_MODE', true);
    include_once(__DIR__ . '/../../../index.php');
    $modx->db->connect();
    if (empty($modx->config)) {
        $modx->getSettings();
    }

  Критично: файл вне папки manager/, без этого
  $modx и $_SESSION недоступны.

14.4.2. Проверки доступа
    if (!isset($_SESSION['mgrValidated'])) {
        header('HTTP/1.1 403 Forbidden');
        die('No access');
    }
    if (!$modx->hasPermission('exec_module')) {
        header('HTTP/1.1 403 Forbidden');
        die('No access');
    }

14.4.3. Константы URL
    $CATALOG_URL = 'https://raw.githubusercontent.com/.../catalog.json';
    $BASE_URL    = 'https://github.com/.../packages/';

14.4.4. Заглушки для неиспользуемых эндпоинтов
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

  Store.js иногда зовёт эти методы. Возвращаем
  пустые ответы вместо 404.

14.4.5. Маппинг категорий
    $catToType = [
        'snippets' => 'snippet',
        'plugins'  => 'plugin',
        'modules'  => 'module',
        'tvs'      => 'snippet',
        'lib'      => 'snippet',
    ];

  UI знает только три типа: snippet / plugin / module.
  MultiTV (tvs) и phpThumb (lib) идут в сниппеты.

14.4.6. Определение языка
    $mgrLang = substr($modx->config['manager_language'] ?? 'english', 0, 2);
    if ($mgrLang !== 'ru') $mgrLang = 'en';

  Описание пакета — объект {ru, en}. Выбор по языку менеджера.

14.4.7. Формат пакета для store.js
    [
        'id'           => alias,
        'cid'          => alias,
        'title'        => name,
        'description'  => строка (по языку),
        'type'         => snippet|plugin|module,
        'name_in_modx' => name_in_modx или name,
        'version'      => version,
        'date'         => updated из каталога,
        'author'       => author,
        'downloads'    => '0',
        'image'        => '',
        'url'          => ['fieldValue' => [[file, version, date]]],
        'cls'          => 'pack_install',
    ]

14.5. ПРАВКИ template/main.html
-------------------------------
Файл: assets/modules/store/template/main.html

14.5.1. Удалено в <head>
  - <meta name="keywords" content="jquery,ui,easy,...">
  - <meta name="description" content="easyui help you...">
  - <!--- <link ... store.css ...> --> (закомментированная)
  - <link rel="stylesheet" href=".../fancybox/jquery.fancybox.css">
  - <script src=".../fancybox/jquery.mousewheel-3.0.6.pack.js">
  - <script src=".../fancybox/jquery.fancybox.pack.js">

14.5.2. Изменено в <head>
  <title>Evolution CMS Store</title> → <title>VoloLution Store</title>

14.5.3. Добавлено в <head>
  <script>window.STORE_API = '[+site_url+]assets/modules/store/api.php';</script>
  (между jquery.min.js и store.js)

14.5.4. Удалено в <body>
  - <div id="actions"> — блок self-update
  - <div class="box mh" id="login"> — форма логина
  - <div class="box mh logined"> — блок своего репозитория
  - <div class="box"> с Copyright Bumkaka & Dmi3yy
  - <div class="item_header"> — сортировка

14.5.5. Закомментировано в <body>
  <!-- ========== FAQ (ОТКЛЮЧЕНО) ==========
  <div class="box">
      <h4>FAQ:</h4>
      <ul>[+faq+]</ul>
  </div>
  ========== /FAQ ========== -->

14.5.6. Удалено в скрытом блоке .tpl
  - <textarea name="hash">[+hash+]</textarea>
  - <div id="tpl_category2"> — для своего репозитория
  - <div id="tpl_cart"> — для репозитория

14.5.7. Переписан #tpl_list

Было:
    <div class="col-sm-4 catalog_item %cls%">
        <div class="item_content">
            <div class="info_block">
                <div id="catalog_img" class="catalog_thumb">
                    <span class="typesbadge">%type%</span>
                    <img class="img-fluid" src="" alt="">
                </div>
                ...
                <div class="info_extras">
                    <button ... onclick="window.parent.modx.popup({...})">
                        <i class="fa fa-info"></i> more
                    </button>
                </div>
                <div class="install_extras">
                    <a class="item-install">...</a>
                    <a class="item-reinstall">...</a>
                    <a class="item-update">...</a> [upd]
                    <select name="link"></select>
                </div>
            </div>
            <div class="info"> версия и загрузки </div>
            ...

Стало:
    <div class="col-sm-4 catalog_item %cls% type-%type%">
        <div class="item_content">
            <div class="info_block">
                <div id="catalog_img" class="catalog_thumb">
                    <span class="typesbadge">%type%</span>
                </div>
                <div class="catalog_info">
                    <h3>%title%</h3>
                    <span class="descript">%description%</span>
                    <div class="extra-meta">
                        <span class="meta-item"><i class="fa fa-tag"></i> %version%</span>
                        <span class="meta-item"><i class="fa fa-user"></i> %author%</span>
                        <span class="meta-item"><i class="fa fa-clock-o"></i> %date%</span>
                    </div>
                    <div class="install_extras">
                        <a class="item-install">...</a>
                        <a class="item-reinstall">...</a>
                        <select name="link"></select>
                    </div>
                </div>
            </div>
            ...
        </div>
    </div>

Отличия:
  + класс type-%type% на корневом div (для CSS-иконок)
  + .extra-meta с тремя иконками внутри карточки
  - <img class="img-fluid">
  - .info_extras с кнопкой «more»
  - кнопка item-update
  - отдельный блок .info внизу карточки

14.6. ПРАВКИ js/store.js
-----------------------
Файл: assets/modules/store/js/store.js

14.6.1. Удалено полностью (~150 строк)
  - var blockedPackages = [...] (≈40 строк)
  - var packageDescriptions = {...} (≈100 строк)
  - function getLocalizedDescription(title, defaultDescription)
  - function filterBlocked(data)

14.6.2. Удалены методы store
  - store.update()           — self-update
  - store.verifyUser()       — логин
  - store.showUserForms()    — логин
  - store.logout()           — логин
  - store.login()            — логин
  - store.get_category()
  - store.get_list()
  - store.get_own_list()
  - store.updateUserPack()
  - store.updateUserCategory()

14.6.3. Удалены свойства store
  - categories: {}   — не использовалось
  - catalog: null    — не использовалось

14.6.4. Изменён store.extend()
Было:
    extend:function(obj1){
        hash = '';
        if ($('[name="hash"]').val() != '') {
            res = eval('('+$('[name="hash"]').val()+')');
            hash = res.hash;
        }
        param = { hash:hash, lang:$('[name="language"]').val() };
        return $.extend(obj1,param);
    },
Стало:
    extend: function(obj1) {
        param = { lang: $('[name="language"]').val() };
        return $.extend(obj1, param);
    },

14.6.5. Изменён store.query()
Было:
    url:'https://extras.evocms.ru/get.php?get=' + action,
Стало:
    url: window.STORE_API + '?get=' + action,

14.6.6. Удалены вызовы filterBlocked()
Было (в store.init):
    if (data.allcategory) {
        $.each(data.allcategory, function(catId, packages) {
            data.allcategory[catId] = filterBlocked(packages);
        });
    }
Стало: блок удалён.

Было (в store.query success):
    data = filterBlocked(data);
    callback(data);
Стало:
    callback(data);

14.6.7. Упрощён store.install()
Удалена ветка для «package» (установка через fancybox):
    if ($(elm).attr('data-method') == "package") {
        $.fancybox.open({href: install_url, type: 'iframe'});
    } else { ... AJAX ... }

Оставлена только AJAX-установка.

14.6.8. Изменён обработчик кликов
Было:
    $('a.item-reinstall,a.item-update').live('click', function() {...});
Стало:
    $('a.item-reinstall').live('click', function() {...});

  Обработчик item-update удалён — кнопки этой больше нет.

14.6.9. Переписан блок в parse_list_item()
Было:
    if (store.types[array.type]) {
        if (store.types[array.type][array.name_in_modx]) {
            array.current_version = store.types[array.type][array.name_in_modx];
            if (store.types[array.type][array.name_in_modx] < array.version) {
                array.cls = 'pack_update';
            }
            if (store.types[array.type][array.name_in_modx] == array.version) {
                array.cls = 'pack_reinstall';
            }
        }
    }

Стало:
    if (array.type) {
        var type = array.type;
        type = type == 'snippet' ? 'snippets' : type;
        type = type == 'module' ? 'modules' : type;
        type = type == 'plugin' ? 'plugins' : type;

        if (store.types[type] && typeof store.types[type][array.name_in_modx] !== 'undefined') {
            array.cls = 'pack_reinstall';
        }
    }

  Полностью убрано сравнение версий. Проверка только
  по факту наличия имени в БД.

14.7. ПРАВКИ css/style.css
--------------------------
Файл: assets/modules/store/css/style.css

14.7.1. Удалены селекторы
  .github, .infobadge, .types, .warning,
  .info, .info span, .info .version/.autor/.date/.download
  и их псевдоэлементы ::before,
  .info_extras, .img-fluid,
  .paginate, .grid TD, .grid TH, .text-info,
  .error, .loginbut, .loginul, .cart_list,
  .catalog_item.col-lg-12 .install_extras

14.7.2. Добавлен .catalog_thumb::before
Иконка Font Awesome по типу, 5 правил:
  .type-snippets ::before → "\f121" (fa-code),  #3498db, фон #eaf3fb
  .type-plugins  ::before → "\f1e6" (fa-plug),  #27ae60, фон #eaf7ee
  .type-modules  ::before → "\f1b2" (fa-cube),  #8e44ad, фон #f2ebf9
  .type-tvs      ::before → "\f03a" (fa-list),  #e67e22, фон #fdf3e6
  .type-lib      ::before → "\f02d" (fa-book),  #7f8c8d, фон #f0f2f4

14.7.3. Добавлены
  .extra-meta, .meta-item — метаинформация в карточке
  .darkness .catalog_info h3, .darkness .descript,
  .darkness .extra-meta — расширенная тёмная тема

14.7.4. Изменены
  .item_content: добавлены display: flex, flex-direction: column
  .install_extras: position: absolute → часть flow
  .catalog_info: flex: 1, display: flex, flex-direction: column
  .liststyle: адаптирован под flex-структуру
  Шрифт иконок: 'Font Awesome 5 Free', font-weight: 900
  .typesbadge: переработан (компактнее, правый верхний угол)

14.8. ПРАВКИ install/assets/modules/store.tpl
---------------------------------------------
Файл: install/assets/modules/store.tpl

14.8.1. Маркер
Было:  // <?php  (с пробелом после //)
Стало: //<?php   (без пробела)

  Критично: парсер установщика Evo ищет именно //<?php.

14.8.2. Название модуля
Было:  Extras
Стало: VoloLution Store

14.8.3. Описание
Было:  first repository for Evolution CMS
Стало: Установка пакетов из репозитория VoloLution CMS
       Источник: github.com/srkkrs77/vololution/packages
       Основано на оригинальном Store от Bumkaka & Dmi3yy
       Модифицировано для VoloLution CMS

14.8.4. Атрибуты docblock
Было:
    @version     0.1.4
    @internal    @properties
    @internal    @guid store435243542tf542t5t
    @internal    @shareparams 1
    @internal    @dependencies requires files located at /assets/modules/store/
    @internal    @modx_category Manager and Admin
    @internal    @installset base, sample
    @lastupdate  25/11/2016

Стало:
    @version     1.0.0
    @internal    @modx_category Manager and Admin
    @internal    @dependencies requires files located at /assets/modules/store/
    @internal    @installset base, sample
    @lastupdate  07/10/2026

  Удалены: @properties, @guid, @shareparams.
  Причина: guid нужен только для апдейтов через evo.im.

14.8.5. Тело модуля
Было:
    //AUTHORS: Bumkaka & Dmi3yy
    include_once('../assets/modules/store/core.php');

Стало:
    // ORIGINAL AUTHORS: Bumkaka & Dmi3yy
    // MODIFIED BY: VoloLution CMS
    include_once(MODX_BASE_PATH . 'assets/modules/store/core.php');

  Относительный путь заменён на абсолютный через MODX_BASE_PATH.

14.9. ЧИСТКА installer/
-----------------------
Удалено 8 файлов:
  installer/index.php              точка входа UI-установщика
  installer/action.install.php     экран «Установка завершена»
  installer/action.load.php        заглушка (закомментирована)
  installer/action.options.php     экран выбора компонентов
  installer/instprocessor.php      старая версия, дубликат fast
  installer/style.css              стили UI
  installer/jquery-1.4.4.min.js    jQuery для UI

Удалены папки:
  installer/img/                  9 декоративных картинок
  installer/lang/*.inc.php        18 языков

Осталось 6 файлов:
  installer/functions.php
  installer/instprocessor-fast.php
  installer/setup.info.php
  installer/sqlParser.class.php
  installer/lang/russian-UTF8.inc.php
  installer/lang/english.inc.php

14.10. ПРАВКИ installer/instprocessor-fast.php
-----------------------------------------------
14.10.1. Выбор языка (было — хардкод)

Было:
    $_lang = array();
    $_params = array();
    require_once($modulePath."/lang/russian-UTF8.inc.php");

Стало:
    $_lang = array();
    $_params = array();
    $mgrLang = $modx->config['manager_language'] ?? 'english';
    $langFile = $modulePath . "/lang/" . $mgrLang . ".inc.php";
    if (!file_exists($langFile)) {
        $langFile = $modulePath . "/lang/english.inc.php";
    }
    require_once($langFile);

  Причина: старый хардкод требовал russian-UTF8.inc.php,
  что ломало английский интерфейс.

14.10.2. Остальные правки
  Техдолг НЕ закрыт (см. раздел 14.13):
  - 16 вызовов mysql_error() — удалена в PHP 7
  - $count_new_name, $props, $dbase могут быть undefined
  - getCreateDbCategory($category, $sqlParser) — лишний аргумент
  Правим по факту ошибки.

14.11. ЧИСТКА корня Store
-------------------------
Удалено:
  js/fancybox/ — вся папка с пакетом fancybox v2.1.4,
                 хелперами (buttons, thumbs, media),
                 jquery.mousewheel-3.0.6

  img/body.jpg      — фон, не использовался
  img/github.png    — логотип, не использовался

  lang/italian.php
  lang/nederlands.php
  lang/nederlands-utf8.php

Осталось:
  js/jquery.min.js            jQuery 1.8.3 (для AJAX)
  js/store.js                 основная логика

  img/loader.gif              используется .loader в CSS
  img/loading.png             используется #loading в CSS

  lang/russian-UTF8.php       подключается core.php
  lang/english.php            fallback

14.12. Логика «установлен / не установлен»
-------------------------------------------
Store НЕ сравнивает версии пакетов.

Проверка:
  1. core.php читает имена из site_snippets,
     site_plugins, site_modules.
  2. Записывает в JSON как $PACK[тип][имя] = '1'.
  3. store.js смотрит — есть ли имя в этом объекте.

Результат:
  Есть в БД  → класс pack_reinstall → кнопка «Переустановить»
  Нет в БД   → класс pack_install   → кнопка «Установить»

Почему убрали сравнение версий:
  - Версия в описании пакета (из @version в .tpl) —
    произвольная, часто без тега <strong>.
  - Версия в catalog.json — наша, отдельная.
  - Сравнение разных чисел давало ложные результаты:
    '2.1' < '1.0.0' в строках = false,
    '2.1' == '1.0.0' = false,
    класс оставался pack_install,
    хотя пакет установлен.

Обновление до новой версии — просто нажатие
«Переустановить». Магазин скачает свежий ZIP с GitHub
и перезапишет пакет.

Уведомление о новых версиях — задача на будущее
(см. раздел 14.14, п.5).

14.13. Обновление карточки после установки
-------------------------------------------
В store.install() после успеха AJAX-запроса:

    el.closest('.catalog_item')
        .removeClass('item-install')
        .addClass('item-reinstall');

Карточка мгновенно меняет кнопку «Установить» на
«Переустановить» без перезагрузки страницы.

14.14. ТЕХДОЛГ (не критично, фиксируем)
----------------------------------------
1. instprocessor-fast.php — 16 вызовов mysql_error()
   Функция удалена в PHP 7. Сработает только при SQL-ошибке
   и приведёт к Fatal. Пока не всплывало — не патчим.

2. instprocessor-fast.php — $count_new_name, $props, $dbase
   могут быть undefined в редких ветках (overwrite=false,
   пустой список plugins, TV с assignments).
   Warning в логе, не критично.

3. instprocessor-fast.php — getCreateDbCategory($category, $sqlParser)
   вызывается с двумя аргументами, принимает один.
   PHP игнорирует лишний.

4. CSRF — api.php и core.php не проверяют токен.
   Раздел 9.1, Задача C. Кросс-доменный POST может
   дёрнуть эндпоинты от имени админа.

5. Нет уведомлений об обновлениях пакетов.
   Пользователь не знает, что в catalog.json появилась
   новая версия. Решение на будущее: писать установленную
   версию в assets/cache/store/installed.json и сверять
   при открытии.

6. Установка «из архива» (сайдбар) не проверяет содержимое.
   Если пользователь зальёт ZIP со зловредным PHP —
   он попадёт на сайт. Ограничение — только админ.

14.15. РЕЗУЛЬТАТЫ ТЕСТИРОВАНИЯ
------------------------------
Дата: 07.10.2026
URL:  https://vol.test/manager/
БД:   vol, префикс gyl8_
PHP:  8.5.10, Laragon, MySQL 8.0.30

Проверено:
  ✅ Подключение модуля через site_modules
  ✅ Загрузка каталога через api.php с GitHub
  ✅ Категории: Сниппеты 7, Плагины 6, Модули 2
  ✅ Поиск по названию
  ✅ Переключение вида: список / 2 колонки / 3 колонки
  ✅ Установка MonthDate (сниппет)
  ✅ Установка DLSiblings (сниппет)
  ✅ Установка SimpleGallery (плагин)
  ✅ Определение установленных пакетов
  ✅ Кнопка «Переустановить» появляется после установки
  ✅ Работа всех трёх категорий UI
  ✅ PHP error_log — пусто

Не проверено (пакеты в очереди):
  ⬜ Установка остальных 13 пакетов
  ⬜ Мультиязычные описания (переключение языка Evo)
  ⬜ Установка MultiTV (сложный TV-пакет)
  ⬜ Установка editDocs (сложный модуль)
  ⬜ Установка из локального ZIP через сайдбар
CHANGELOG-11.md — Раздел 14.1–14.7
Часть 11 из 14. Свой Extras-Store: цель, репозиторий, core.php, api.php, main.html, store.js, style.css.

markdown
## РАЗДЕЛ 14. СВОЙ EXTRAS-STORE — ОТВЯЗКА ОТ EVO.IM

**Статус:** ✅ выполнено (07.10.2026).  
**Авторы оригинала:** Bumkaka & Dmi3yy.  
**Модифицировано:** VoloLution CMS.

### 14.1. Цель и результат

**Заменить источник каталога пакетов:**

| Было | Стало |
|---|---|
| `extras.evo.im` | `github.com/srkkrs77/vololution/packages` |
| `extras.evocms.ru` | то же |

**Убрать наследие `evo.im`:**
- Fancybox (модальное окно установки)
- Логин
- Self-update
- Чёрный список пакетов
- Декоративные картинки MODX
- Сравнение версий

**Сохранить:**
- UI оригинального Store
- AJAX-установку пакетов

---

### 14.2. Репозиторий пакетов

- **GitHub:** `github.com/srkkrs77/vololution`
- **Папка:** `/packages/`
- **Файл каталога:** `/packages/catalog.json`

**16 ZIP с именами `<alias>.zip`:**

`DLSiblings.zip`, `DocLister.zip`, `FirstChildRedirect.zip`, `FormLister.zip`, `FormResults.zip`, `MonthDate.zip`, `MultiCategories.zip`, `SimpleGallery.zip`, `SimpleTube.zip`, `editDocs.zip`, `evoBabel.zip`, `evoFileManagerDialog.zip`, `evoSearch.zip`, `multiTV.zip`, `phpThumb.zip`, `webP.zip`

Все переименованы из исходных имён вида `DocLister-master.zip`, `FormLister-1.21.2.zip`, `evobabel-0.2-master.zip` в единый lowercase-формат.

**Распределение по категориям:**

| Категория | Пакеты |
|---|---|
| **Сниппеты** | DocLister, FormLister, DLSiblings, MonthDate, FirstChildRedirect |
| **Плагины** | SimpleGallery, SimpleTube, evoSearch, EvoBabel, evoFileManagerDialog, WebP Converter, MultiCategories |
| **TV** | MultiTV |
| **Модули** | FormResults, editDocs |
| **Библиотеки** | phpThumb |

**Поле `name_in_modx`** — там, где имя в БД отличается от `name`:
- `multiTV` → `"multiTV"`
- `phpthumb` → `"phpthumb"`
- `evoBabel` → `"evoBabel"`

---

### 14.3. Правки `core.php`

**Файл:** `assets/modules/store/core.php`

#### 14.3.1. Версия модуля

```diff
- $version = "0.1.3";
+ $version = "1.0.0";
14.3.2. Удалён fallback на extras.evo.im
Было (в case 'install'):

php
$url = "http://extras.evo.im/get.php?get=file&cid=".$id;
Стало:

php
$Store->errors[] = 'URL пакета не передан (file is empty)';
$Store->quit();
14.3.3. Класс Store — свойство errors
Было:

php
class Store {
    public $lang;
    public $language;
}
Стало:

php
class Store {
    public $lang;
    public $language;
    public $errors = [];
}
Причина: динамическое свойство $Store->errors[] в PHP 8.2+ вызывает deprecated warning. Объявляем явно.

14.3.4. Метод downloadFile() — переписан
Изменения:

fopen($url, "rb") → @fopen(...) — без warning при ошибке.

Добавлена проверка $newf после fopen (было — без проверки).

Добавлен curl_close($ch) — закрытие ресурса.

catch (Exception $e) → catch (\Exception $e) — глобальный класс.

Понятные тексты ошибок.

14.3.5. Обнаружение установленных пакетов
Было (в default case):

php
while ($row = $modx->db->GetRow($result)) {
    $PACK[$value][$row['name']] = $Store->get_version($row['description']);
}
Стало:

php
while ($row = $modx->db->GetRow($result)) {
    $PACK[$value][$row['name']] = '1';
}
Причина: get_version() искал <strong>версию</strong> в описании, но в большинстве .tpl-пакетов тега <strong> нет. Store считал такие пакеты «не установленными». Теперь проверка только по факту наличия имени в БД.

14.3.6. Прочие мелкие правки
$_POST['res'] ?? '' в case 'saveuser'

$_SESSION['mgrEmail'] ?? '' в default case

$dir = '' + проверка «Пустой архив» в блоке install

catch (\Exception $e) — глобальный класс везде

14.3.7. Функция get_version() — оставлена, но не вызывается
php
function get_version($text) {
    preg_match('/<strong>(.*)<\/strong>/s', $text, $match);
    return isset($match[1]) ? $match[1] : '';
}
Можно удалить в следующей итерации.

14.4. Новый файл api.php
Путь: assets/modules/store/api.php
Размер: ~140 строк

Назначение: прокси каталога. Читает catalog.json с GitHub, отдаёт в формате, ожидаемом store.js.

14.4.1. Инициализация MODX
php
define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);
include_once(__DIR__ . '/../../../index.php');
$modx->db->connect();
if (empty($modx->config)) {
    $modx->getSettings();
}
Критично: файл вне папки manager/, без этого $modx и $_SESSION недоступны.

14.4.2. Проверки доступа
php
if (!isset($_SESSION['mgrValidated'])) {
    header('HTTP/1.1 403 Forbidden');
    die('No access');
}
if (!$modx->hasPermission('exec_module')) {
    header('HTTP/1.1 403 Forbidden');
    die('No access');
}
14.4.3. Константы URL
php
$CATALOG_URL = 'https://raw.githubusercontent.com/srkkrs77/vololution/main/packages/catalog.json';
$BASE_URL    = 'https://github.com/srkkrs77/vololution/raw/main/packages/';
14.4.4. Заглушки для неиспользуемых эндпоинтов
php
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
store.js иногда зовёт эти методы. Возвращаем пустые ответы вместо 404.

14.4.5. Маппинг категорий
php
$catToType = [
    'snippets' => 'snippet',
    'plugins'  => 'plugin',
    'modules'  => 'module',
    'tvs'      => 'snippet',
    'lib'      => 'snippet',
];
UI знает только три типа: snippet / plugin / module. MultiTV (tvs) и phpThumb (lib) идут в сниппеты.

14.4.6. Определение языка
php
$mgrLang = substr($modx->config['manager_language'] ?? 'english', 0, 2);
if ($mgrLang !== 'ru') $mgrLang = 'en';
Описание пакета — объект {ru, en}. Выбор по языку менеджера.

14.4.7. Формат пакета для store.js
php
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
14.5. Правки template/main.html
Файл: assets/modules/store/template/main.html

14.5.1. Удалено в <head>
<meta name="keywords" content="jquery,ui,easy,...">

<meta name="description" content="easyui help you...">

Закомментированный <link> на store.css

<link rel="stylesheet" href=".../fancybox/jquery.fancybox.css">

<script src=".../fancybox/jquery.mousewheel-3.0.6.pack.js">

<script src=".../fancybox/jquery.fancybox.pack.js">

14.5.2. Изменено в <head>
diff
- <title>Evolution CMS Store</title>
+ <title>VoloLution Store</title>
14.5.3. Добавлено в <head>
html
<script>window.STORE_API = '[+site_url+]assets/modules/store/api.php';</script>
Между jquery.min.js и store.js.

14.5.4. Удалено в <body>
<div id="actions"> — блок self-update

<div class="box mh" id="login"> — форма логина

<div class="box mh logined"> — блок своего репозитория

<div class="box"> с Copyright Bumkaka & Dmi3yy

<div class="item_header"> — сортировка

14.5.5. Закомментировано в <body>
html
<!-- ========== FAQ (ОТКЛЮЧЕНО) ==========
<div class="box">
    <h4>FAQ:</h4>
    <ul>[+faq+]</ul>
</div>
========== /FAQ ========== -->
14.5.6. Удалено в скрытом блоке .tpl
<textarea name="hash">[+hash+]</textarea>

<div id="tpl_category2"> — для своего репозитория

<div id="tpl_cart"> — для репозитория

14.5.7. Переписан #tpl_list
Было:

html
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

html
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

➕ Класс type-%type% на корневом div (для CSS-иконок).

➕ .extra-meta с тремя иконками внутри карточки.

➖ <img class="img-fluid">

➖ .info_extras с кнопкой «more»

➖ Кнопка item-update

➖ Отдельный блок .info внизу карточки

14.6. Правки js/store.js
Файл: assets/modules/store/js/store.js

14.6.1. Удалено полностью (~150 строк)
var blockedPackages = [...] (~40 строк) — чёрный список.

var packageDescriptions = {...} (~100 строк) — описания.

function getLocalizedDescription(title, defaultDescription) — выбор языка.

function filterBlocked(data) — фильтрация заблокированных.

14.6.2. Удалены методы store
store.update() — self-update

store.verifyUser() — логин

store.showUserForms() — логин

store.logout() — логин

store.login() — логин

store.get_category()

store.get_list()

store.get_own_list()

store.updateUserPack()

store.updateUserCategory()

14.6.3. Удалены свойства store
categories: {} — не использовалось

catalog: null — не использовалось

14.6.4. Изменён store.extend()
Было:

javascript
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

javascript
extend: function(obj1) {
    param = { lang: $('[name="language"]').val() };
    return $.extend(obj1, param);
},
Убран eval() и hash — наследие авторизации.

14.6.5. Изменён store.query()
Было:

javascript
url:'https://extras.evocms.ru/get.php?get=' + action,
Стало:

javascript
url: window.STORE_API + '?get=' + action,
14.6.6. Удалены вызовы filterBlocked()
Было (в store.init):

javascript
if (data.allcategory) {
    $.each(data.allcategory, function(catId, packages) {
        data.allcategory[catId] = filterBlocked(packages);
    });
}
Стало: блок удалён.

Было (в store.query success):

javascript
data = filterBlocked(data);
callback(data);
Стало:

javascript
callback(data);
14.6.7. Упрощён store.install()
Удалена ветка для «package» (установка через fancybox):

javascript
if ($(elm).attr('data-method') == "package") {
    $.fancybox.open({href: install_url, type: 'iframe'});
} else { ... AJAX ... }
Оставлена только AJAX-установка.

14.6.8. Изменён обработчик кликов
Было:

javascript
$('a.item-reinstall,a.item-update').live('click', function() {...});
Стало:

javascript
$('a.item-reinstall').live('click', function() {...});
Обработчик item-update удалён — кнопки этой больше нет.

14.6.9. Переписан блок в parse_list_item()
Было:

javascript
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

javascript
if (array.type) {
    var type = array.type;
    type = type == 'snippet' ? 'snippets' : type;
    type = type == 'module' ? 'modules' : type;
    type = type == 'plugin' ? 'plugins' : type;

    if (store.types[type] && typeof store.types[type][array.name_in_modx] !== 'undefined') {
        array.cls = 'pack_reinstall';
    }
}
Полностью убрано сравнение версий. Проверка только по факту наличия имени в БД.

14.7. Правки css/style.css
Файл: assets/modules/store/css/style.css

14.7.1. Удалены селекторы
.github, .infobadge, .types, .warning, .info, .info span, .info .version/.autor/.date/.download и их псевдоэлементы ::before, .info_extras, .img-fluid, .paginate, .grid TD, .grid TH, .text-info, .error, .loginbut, .loginul, .cart_list, .catalog_item.col-lg-12 .install_extras.

14.7.2. Добавлен .catalog_thumb::before
Иконка Font Awesome по типу, 5 правил:

Тип	Иконка	Цвет	Фон
.type-snippets	\f121 (fa-code)	#3498db	#eaf3fb
.type-plugins	\f1e6 (fa-plug)	#27ae60	#eaf7ee
.type-modules	\f1b2 (fa-cube)	#8e44ad	#f2ebf9
.type-tvs	\f03a (fa-list)	#e67e22	#fdf3e6
.type-lib	\f02d (fa-book)	#7f8c8d	#f0f2f4
14.7.3. Добавлены
.extra-meta, .meta-item — метаинформация в карточке.

.darkness .catalog_info h3, .darkness .descript, .darkness .extra-meta — расширенная тёмная тема.

14.7.4. Изменены
.item_content: добавлены display: flex, flex-direction: column.

.install_extras: position: absolute → часть flow.

.catalog_info: flex: 1, display: flex, flex-direction: column.

.liststyle: адаптирован под flex-структуру.

Шрифт иконок: 'Font Awesome 5 Free', font-weight: 900.

.typesbadge: переработан (компактнее, правый верхний угол).

text

**Конец части 11.**
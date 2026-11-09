### 4.4 evoSearch — 5 правок в 1 файле

**Автор:** Pathologic  
**CHANGELOG:** 4.4  
**Причина правок:** PHP 8.5-совместимость (`??`, `!empty()`).

---

#### Файл: `assets/plugins/evoSearch/plugin.class.php`

**Метод `emptyExcluded`:**

```diff
- if ($excluded = $this->modx->config['evoSearch_exclude'])
+ if ($excluded = ($this->modx->config['evoSearch_exclude'] ?? ''))
```

**Что делает:** проверяет настройку `evoSearch_exclude` (список исключённых слов).

---

**Метод `makeSQLForSelectWords`:**

```diff
- if (isset($this->modx->config['evoSearch_table_prefix']))
+ if (!empty($this->modx->config['evoSearch_table_prefix']))
```

**Что делает:** определяет префикс таблиц для SQL-запроса. `isset()` возвращал `true` для пустой строки, `!empty()` — правильнее.

---

**Метод `__construct`:**

```diff
- $this->cfg = $modx->config['evoSearch']
+ $this->cfg = $modx->config['evoSearch'] ?? []
```

**Что делает:** загружает общий конфиг плагина. Дефолт — пустой массив, чтобы не падало на `foreach`.

---

**Метод `getDicts`:**

```diff
- $dict = $this->modx->config['evoSearch_dict']
+ $dict = $this->modx->config['evoSearch_dict'] ?? ''
```

**Что делает:** словарь исключений для поиска.

---

**Метод `Words2BaseForm`:**

```diff
- $excluded = $this->cfg['excludeWords']
+ $excluded = $this->cfg['excludeWords'] ?? ''
```

**Что делает:** список исключённых слов при морфологическом разборе.

---

#### Итого по evoSearch

| Файл | Правок |
|---|---|
| `plugin.class.php` | 5 |
| **Итого** | **5** |

**При установке через Store:** ZIP `evoSearch.zip`, alias `evoSearch`, категория «Плагины».

**Важно:** evoSearch — бэкенд для живого поиска (Этап 1.2 roadmap).

---

### 4.5 editDocs — 5 групп правок в 2 файлах

**Авторы:** Pathologic + Grinyaha  
**CHANGELOG:** 4.5  
**Причина правок:** PHP 8.5-совместимость + роли + `fputcsv` (PHP 8.4+ требует escape-параметр).

---

#### Файл 1: `assets/lib/MODxAPI/modResource.php`

**Метод `save()` — роли с правом редактирования:**

```diff
- in_array($_SESSION['mgrRole'], [1])
+ in_array($_SESSION['mgrRole'], [1, 4])
```

**Что делает:** роль `1` — администратор. Роль `4` — редактор. Без этого редактор (роль 4) не мог сохранять документы через editDocs.

---

#### Файл 2: `assets/modules/editdocs/editdocs.class.php`

**Группа 1 — `max_rows ?? 0` (3 места):**

```diff
- $this->table($this->clearEmptyRows($sheetData), $this->params['max_rows'])
+ $this->table($this->clearEmptyRows($sheetData), $this->params['max_rows'] ?? 0)

- $this->table($_SESSION['data'], $this->params['max_rows'])
+ $this->table($_SESSION['data'], $this->params['max_rows'] ?? 0)
```

**Что делает:** защита от отсутствия `max_rows` при импорте/экспорте.

---

**Группа 2 — `fputcsv` (4 места):**

```diff
- fputcsv($file, $header, $dm);
+ fputcsv($file, $header, $dm, '"', '\\');

- fputcsv($file_temp, $header, $dm);
+ fputcsv($file_temp, $header, $dm, '"', '\\');

- fputcsv($file, $import, $dm);
+ fputcsv($file, $import, $dm, '"', '\\');

- fputcsv($file_temp, $import_tmp, $dm);
+ fputcsv($file_temp, $import_tmp, $dm, '"', '\\');
```

**Что делает:** в PHP 8.4+ параметры `$escape` и `$enclosure` для `fputcsv()` стали обязательными (deprecated warning при отсутствии). Явно указываем `'"'` (кавычка) и `'\\'` (бэкслеш).

---

**Группа 3 — `need_xls` (2 места):**

```diff
- $out = $_SESSION['export_start'] . '|' . $_SESSION['export_total']. '|' . $_POST['need_xls'] ;
+ $out = $_SESSION['export_start'] . '|' . $_SESSION['export_total'] . '|' . ($_POST['need_xls'] ?? '') ;

- if($_POST['need_xls']==1) {
+ if(($_POST['need_xls'] ?? 0) == 1) {
```

**Что делает:** защита от отсутствия `need_xls` при экспорте в XLS.

---

**Группа 4 — защита `$_POST` / `$_FILES`:**

```diff
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
```

**Что делает:** защита от undefined key в частых точках приёма POST-данных.

---

**Группа 5 — вспомогательная защита:**

```diff
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
```

**Что делает:** защита в редких ветках кода — config, mass-операции, tree.

---

#### Итого по editDocs

| Файл | Правок |
|---|---|
| `assets/lib/MODxAPI/modResource.php` | 1 |
| `assets/modules/editdocs/editdocs.class.php` | 5 групп (≈20 мест) |
| **Итого** | **21** |

**При установке через Store:** ZIP `editDocs.zip`, alias `editDocs`, категория «Модули».

---

### 4.6 MultiCategories — 2 правки в 1 файле

**Автор:** Pathologic  
**CHANGELOG:** 4.6  
**Причина правок:** PHP 8.5-совместимость (глобальный `\Exception`) + область видимости.

---

#### Файл: `assets/plugins/multicategories/lib/model.php`

```diff
- throw new Exception(...)
+ throw new \Exception(...)

- public $log;
+ protected $log;
```

**Что сделано:**

**1. `\Exception`** — в namespace-классе `Exception` ищется в текущем namespace. Если его там нет — Fatal. `\Exception` — глобальный класс PHP, всегда работает.

**2. `protected $log;`** — свойство `$log` должно быть защищённым, а не публичным. Это внутренний логгер класса, наружу его отдавать нельзя.

---

#### Итого по MultiCategories

| Файл | Правок |
|---|---|
| `lib/model.php` | 2 |
| **Итого** | **2** |

**При установке через Store:** ZIP `MultiCategories.zip`, alias `MultiCategories`, категория «Плагины».

---

### 4.7 WebP Converter — 4 правки (в коде плагина)

**Автор:** Pathologic  
**CHANGELOG:** 4.7  
**Дата правки:** 11.10.2026  
**Причина:** PHP 8.5 падал на `DOMDocument::loadHTML()` при пустом контенте + `HTML-ENTITIES` удалена в PHP 8.4+.

**Переведён из «пакетов без правок» в «патченные»** (было 6 → стало 7).

---

#### Проблема на проде

**Сайт:** `studio-milstein.de`  
**Симптом:** при открытии `/en/home.html`:

```
Fatal error: Uncaught ValueError: DOMDocument::loadHTML():
Argument #1 ($source) must not be empty
```

**Причина:** плагин `WebP Converter` на событии `OnWebPagePrerender` пытается парсить `$modx->documentOutput` через `DOMDocument::loadHTML()`. Если контент пустой — PHP 8.5 падает.

Плюс: `mb_convert_encoding($o, 'HTML-ENTITIES', 'UTF-8')` — кодировка `HTML-ENTITIES` удалена в PHP 8.4. Это тоже сломается.

---

#### Файлы с правками

**1. Код плагина в админке** (элемент плагина, не файл библиотеки).

**Где:** Админка → Элементы → Плагины → `WebP Converter` → поле «Код плагина».

**Было:**

```php
$o = &$modx->documentOutput;
$dom = new DOMDocument();
$internalErrors = libxml_use_internal_errors(true);
$dom->loadHTML(mb_convert_encoding($o, 'HTML-ENTITIES', 'UTF-8'));
```

**Стало:**

```php
$o = &$modx->documentOutput;

if (empty($o)) {
    break;
}

$dom = new DOMDocument();
$internalErrors = libxml_use_internal_errors(true);
$html = mb_encode_numericentity($o, [0x80, 0x10FFFF, 0, ~0], 'UTF-8');
$dom->loadHTML($html);
```

**Что сделано:**

1. `if (empty($o)) break;` — защита от пустого контента.
2. `mb_convert_encoding(..., 'HTML-ENTITIES', ...)` заменён на `mb_encode_numericentity(...)`. Функция доступна во всех версиях PHP 8.x.
3. Добавлено `libxml_clear_errors()` + восстановление `libxml_use_internal_errors` в конце блока.

**Дополнительно:**

```php
// Было:
if ((strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false or strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false) and webpconverter\basic_checks($modx)) {

// Стало:
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';

if ((strpos($accept, 'image/webp') !== false || strpos($ua, 'Firefox') !== false)
    && webpconverter\basic_checks($modx)) {
```

**Причина:** `$_SERVER['HTTP_ACCEPT']` может быть не установлен. Прямое обращение — warning в PHP 8.5.

**2. Дескриптор плагина `install/assets/plugins/webpconverter.tpl`** — обновлён, версия 1.0.0 → **1.1.0**. Тело плагина соответствует правкам в админке.

**3. Файл `assets/plugins/webpconverter/plugin.webpconverter.php`** — **не менялся**. Это библиотека функций, там всё корректно.

---

#### Полный код плагина после правки

```php
require_once(MODX_BASE_PATH . 'assets/plugins/webpconverter/plugin.webpconverter.php');

$e = &$modx->Event;
switch ($e->name) {
    case "OnWebPagePrerender":
        if ($modx->documentObject['contentType'] != 'text/html') {
            break;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ((strpos($accept, 'image/webp') !== false || strpos($ua, 'Firefox') !== false)
            && webpconverter\basic_checks($modx)) {

            $o = &$modx->documentOutput;

            if (empty($o)) {
                break;
            }

            $dom = new DOMDocument();
            $internalErrors = libxml_use_internal_errors(true);

            $html = mb_encode_numericentity($o, [0x80, 0x10FFFF, 0, ~0], 'UTF-8');
            $dom->loadHTML($html);

            foreach ($dom->getElementsByTagName('img') as $node) {
                webpconverter\serve_node($node, 'src', $modx, $debug);
            }
            foreach ($dom->getElementsByTagName('video') as $node) {
                webpconverter\serve_node($node, 'poster', $modx, $debug);
            }
            foreach ($dom->getElementsByTagName('source') as $node) {
                webpconverter\serve_node($node, 'srcset', $modx, $debug);
            }

            $html = $dom->saveHTML();

            if ($html !== false) {
                $o = html_entity_decode($html);
            }

            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);
        }
        break;
    default :
        return;
        break;
}
```

---

#### Где применено

| Копия | Что |
|---|---|
| Прод `studio-milstein.de` | Код плагина в админке — заменён |
| Рабочая `C:\laragon\www\vol.test\` | Код плагина в админке — заменить, дескриптор `install/assets/plugins/webpconverter.tpl` — заменить |
| Дистрибутив `D:\VoloLution-CMS-1.4.37\` | Дескриптор `install/assets/plugins/webpconverter.tpl` — заменить |
| Форк (после создания) | Оба файла — коммит |

---

#### Что проверено

- ✅ `/en/home.html` — открывается без Fatal error.
- ✅ `/de/studio.html` — открывается.
- ✅ Изображения на страницах конвертируются в WebP (если браузер поддерживает).
- ✅ PHP error_log — без ошибок `DOMDocument::loadHTML()`.

---

#### Итого по WebP Converter

| Файл | Правок |
|---|---|
| Код плагина (админка) | 4 |
| `install/assets/plugins/webpconverter.tpl` | 1 (версия, тело) |
| **Итого** | **5** |

**При установке через Store:** ZIP `webP.zip`, alias `webP`, категория «Плагины».

**Версия дескриптора:** 1.0.0 → **1.1.0**.

---

### 4.8. Сводная таблица патченных пакетов (обновлено 11.10.2026)

| # | Пакет | Файлов | Правок | CHANGELOG |
|---|---|---|---|---|
| 1 | **MultiTV** | 2 | 11 | 4.1 |
| 2 | **FormResults** | 2 | 4 | 4.2 |
| 3 | **SimpleTube** | 2 | 5 | 4.3 |
| 4 | **evoSearch** | 1 | 5 | 4.4 |
| 5 | **editDocs** | 2 | 21 | 4.5 |
| 6 | **MultiCategories** | 1 | 2 | 4.6 |
| 7 | **WebP Converter** | 2 | 5 | 4.7 |
| **Всего** | **7 пакетов** | **12 файлов** | **53 правки** | — |
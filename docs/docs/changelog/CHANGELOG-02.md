# CHANGELOG-02.md — Раздел 2.5–2.9

**Часть 2 из 14.** Патчи ядра: `mutate_content`, `codemirror`, `ManagerManager / phpThumb`, no-cache админки, кэш Evo.

**Обновлено:** 13.10.2026 (раздел 2.9 — настройки кэша).

---

### 2.5 `manager/actions/mutate_content.dynamic.php`

**Строка 601:**
```diff
- if($modx->config['use_breadcrumbs']) {
+ if(($modx->config['use_breadcrumbs'] ?? 0)) {
```

**Строки 909–910:**
```diff
- $richtexteditorIds[$modx->config['which_editor']][] = 'ta';
- $richtexteditorOptions[$modx->config['which_editor']]['ta'] = '';
+ $which_editor = $modx->config['which_editor'] ?? 'none';
+ $richtexteditorIds[$which_editor][] = 'ta';
+ $richtexteditorOptions[$which_editor]['ta'] = '';
```

**Строка ~918:**
```diff
- ($modx->config['which_editor'] == $editor ? ' selected="selected"' : '')
+ (($modx->config['which_editor'] ?? 'none') == $editor ? ' selected="selected"' : '')
```

**Строка ~942:**
```diff
- $editor = $modx->config['which_editor'];
+ $editor = $modx->config['which_editor'] ?? 'none';
```

**Строка 1375:**
```diff
- if ($modx->config['group_tvs'] > 2 && $templateVariablesOutput) {
+ if (($modx->config['group_tvs'] ?? 0) > 2 && $templateVariablesOutput) {
```

**Строка 1383:**
```diff
- if($use_udperms == 1) {
+ if(($use_udperms ?? 0) == 1) {
```

### 2.6 `assets/plugins/codemirror/codemirror.plugin.php`

**Строка 45:**
```diff
- } elseif ($modx->config['manager_theme_mode'] == 3 || $modx->config['manager_theme_mode'] == 4) {
+ } elseif (($modx->config['manager_theme_mode'] ?? 0) == 3 || ($modx->config['manager_theme_mode'] ?? 0) == 4) {
```

**Строка 52:**
```diff
- $srte = ($modx->config['use_editor'] ? $modx->config['which_editor'] : 'none');
+ $srte = ($modx->config['use_editor'] ? ($modx->config['which_editor'] ?? 'none') : 'none');
```

**Внимание:** это пакет CE (CodeMirror). При обновлении апстрима — заново наложить патч.

### 2.7 ManagerManager / phpThumb

**`assets/plugins/managermanager/mm.inc.php`, строка 212:**
```diff
- $modx->config['datepicker_offset']
+ $modx->config['datepicker_offset'] ?? ''
```

**Внимание:** это пакет CE (ManagerManager). При обновлении апстрима — заново наложить патч.

**`assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.class.php`:**
```diff
- $filename{0}       →  $filename[0]
- $filename{1}       →  $filename[1]
- $which_convert{0}  →  $which_convert[0]
```
Удалён недостижимый `break;` после `return $gdimg_source;`.

**`assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.functions.php`:**
```diff
- $string{$i}                          →  $string[$i]
- $strtr_preg_quote[$escapeables{$i}]  →  $strtr_preg_quote[$escapeables[$i]]
```

**Внимание:** это пакет CE (phpThumb внутри ManagerManager). При обновлении апстрима — заново наложить патч.

### 2.8 `manager/includes/header.inc.php` — no-cache для админки

**Дата:** 11.10.2026

**Проблема:** при переходе между документами админка отдавала закэшированную форму редактирования. На кастомных TV (`image_alt_caption`) поля оставались пустыми, пока не сделаешь Ctrl+Shift+R.

**Причина:** браузер кэшировал HTML-страницы админки. На разных документах показывал одну и ту же старую версию.

**Решение:** в `<head>` после `<meta http-equiv="Content-Type">` добавлено:

```html
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
```

**Что это НЕ делает:**
- Не влияет на безопасность.
- Не ломает сессии.
- Не трогает фронтенд сайта.

**Что это делает:**
- Браузер не кэширует страницы админки.
- Форма всегда грузится с сервера.
- Кастомные TV показывают актуальные значения.

**Где применено:**
- Прод `studio-milstein.de` — заменён файл.
- Локальная копия `C:\laragon\www\vol.test\manager\includes\header.inc.php` — заменить.
- Дистрибутив `D:\VoloLution-CMS-1.4.37\manager\includes\header.inc.php` — заменить.

### 2.9 Настройки кэша Evo — конфигурация прода

**Дата:** 13.10.2026

**Проблема:** после правок шаблонов на сайте «застревала» старая вёрстка. При `cache_type = 2` (учёт ID + `$_GET`) Evo создавал **отдельный кэш под каждую комбинацию GET-параметров**. Рекламные ссылки (`?utm_source=google`, `?gclid=...`, `?fbclid=...`) создавали отдельные кэши — половина со старой вёрсткой.

**Второй симптом:** SimpleGallery на проде заявил «не удалось обработать ответ сервера». Причина — были отключены `cache_plugins`, `cache_snippets`, `cache_chunks`. Их отключение ломает регистрацию плагинов и AJAX-endpoint.

**Причина:** в `Конфигурации → Сайт` был установлен `cache_type = 2`, а также были отключены кэши определений сниппетов/плагинов/чанков.

**Решение:**

| Настройка | Значение | Что кэширует |
|---|---|---|
| `cache_snippets` | `1` | Какие сниппеты есть, параметры |
| `cache_plugins` | `1` | Какие плагины есть, события |
| `cache_chunks` | `1` | Содержимое чанков |
| `cache_type` | **`1`** (только ID) | Готовый HTML |

**Что это даёт:**
- Один кэш на страницу, независимо от UTM/GET.
- UTM-метки работают — метрика ловит через JS в браузере.
- Правки видны сразу после сохранения (при `cache_type = 1` Evo сам чистит кэш документа).
- SimpleGallery работает.

**Что НЕ отключать:**
- `cache_snippets`, `cache_plugins`, `cache_chunks` — **всегда `1`.** Их отключение ломает SimpleGallery и другие пакеты.

**Отключение `cache_type = 0`** (полный отказ от кэша страниц) в Evo 1.4 **не имеет radio-кнопки в админке.** Только через SQL:
```sql
UPDATE eh2v_system_settings
SET setting_value = '0'
WHERE setting_name = 'cache_type';
```

**Но не рекомендовано.** Лучше `cache_type = 1`.

**Где применено:**
- Прод `studio-milstein.de` — настройки восстановлены.
- Локальная копия `C:\laragon\www\vol.test\` — проверить те же настройки.
- Дистрибутив `D:\VoloLution-CMS-1.4.37\` — при установке дистрибутива на чистой БД эти настройки нужно выставить вручную.

**Отклонённый плагин AutoCacheClear:**

**Дата:** 13.10.2026

**Идея:** плагин на события `OnDocFormSave`, `OnTempFormSave`, `OnChunkFormSave`, `OnSnipFormSave`, `OnPluginFormSave`, `OnTVFormSave`, `OnModuleFormSave` вызывает `$modx->clearCache('full')` — чтобы после правок кэш страниц чистился автоматически.

**Проблема:**
- `clearCache('full')` стирает всё, включая файлы, которые Evo использует в текущем процессе сохранения. После сохранения — **белая страница**, форма не открывается.
- `clearCache('siteContent')` — то же самое. Проверено.

**Итог:** плагин удалён. **Не создавать заново.**

**Правильное решение:** `cache_type = 1` + ручная очистка кэша (`Сайт → Очистить кэш`) после правок шаблонов.

**Чек-лист при установке дистрибутива:**

- [ ] `cache_snippets` = `1`
- [ ] `cache_plugins` = `1`
- [ ] `cache_chunks` = `1`
- [ ] `cache_type` = `1`

---

**Конец части 2.**
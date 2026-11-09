CHANGELOG-02.md — Раздел 2.5–2.7
Часть 2 из 14. Патчи ядра: mutate_content, codemirror, ManagerManager / phpThumb.

markdown
### 2.5 `manager/actions/mutate_content.dynamic.php`

**Строка 601:**
```diff
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
Внимание: это пакет CE (CodeMirror). При обновлении апстрима — заново наложить патч.

2.7 ManagerManager / phpThumb
assets/plugins/managermanager/mm.inc.php, строка 212:

diff
- $modx->config['datepicker_offset']
+ $modx->config['datepicker_offset'] ?? ''
Внимание: это пакет CE (ManagerManager). При обновлении апстрима — заново наложить патч.

assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.class.php:

diff
- $filename{0}       →  $filename[0]
- $filename{1}       →  $filename[1]
- $which_convert{0}  →  $which_convert[0]
Удалён недостижимый break; после return $gdimg_source;.

assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.functions.php:

diff
- $string{$i}                          →  $string[$i]
- $strtr_preg_quote[$escapeables{$i}]  →  $strtr_preg_quote[$escapeables[$i]]
Внимание: это пакет CE (phpThumb внутри ManagerManager). При обновлении апстрима — заново наложить патч.

### 2.8 `manager/includes/header.inc.php` — no-cache для админки

**Дата:** 11.10.2026

**Проблема:** при переходе между документами админка отдавала закэшированную форму редактирования. На кастомных TV (image_alt_caption) поля оставались пустыми, пока не сделаешь Ctrl+Shift+R.

**Решение:** в `<head>` после `<meta http-equiv="Content-Type">` добавлено:

```html
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>

**Конец части 2.**
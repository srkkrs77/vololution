CHANGELOG-05.md — Раздел 4.1 (MultiTV)
Часть 5 из 14. Пакеты с правками: MultiTV.

markdown
## РАЗДЕЛ 4. ПАКЕТЫ С ПРАВКАМИ (6)

### 4.1 MultiTV — 10 правок в 2 файлах

**Автор:** Pathologic  
**CHANGELOG:** 4.1  
**Причина правок:** PHP 8.5-совместимость (`??`, `!empty()`, string offsets).

---

#### Файл 1: `assets/tvs/multitv/includes/multitv.class.php`

**Строка 39 — язык менеджера:**

```diff
- $this->language = $this->loadLanguage($this->modx->config['manager_language']);
+ $this->language = $this->loadLanguage($this->modx->config['manager_language'] ?? 'english');
Строка 53 (BolmerCMS) — директория thumbnails:

diff
- $this->cmsinfo['thumbsdir'] = ($this->modx->config['thumbsDir']) ? $this->modx->config['thumbsDir'] . '/' : '';
+ $this->cmsinfo['thumbsdir'] = !empty($this->modx->config['thumbsDir']) ? $this->modx->config['thumbsDir'] . '/' : '';
Строка 60 (Evolution) — аналогично:

diff
- $this->cmsinfo['thumbsdir'] = ($this->modx->config['thumbsDir']) ? $this->modx->config['thumbsDir'] . '/' : '';
+ $this->cmsinfo['thumbsdir'] = !empty($this->modx->config['thumbsDir']) ? $this->modx->config['thumbsDir'] . '/' : '';
Строка 106 — конфиг шаблонов:

diff
- $this->tvTemplates = 'templates' . $tvDefinitions['tpl_config'];
+ $this->tvTemplates = 'templates' . ($tvDefinitions['tpl_config'] ?? '');
Строка 151 — редактор:

diff
- 'editor' => $this->modx->config['which_editor'],
+ 'editor' => $this->modx->config['which_editor'] ?? 'none',
Строка 270 — список шаблонов:

diff
- $this->templates = $settings[$this->tvTemplates];
+ $this->templates = $settings[$this->tvTemplates] ?? array();
Строка 443 — rich-редактор:

diff
- $this->richeditor = $this->modx->config['which_editor'];
+ $this->richeditor = $this->modx->config['which_editor'] ?? 'none';
Строки 616 и 631 (horizontal, vertical) — ширина поля:

diff
- if ($this->fields[$fieldname]['width']) {
+ if (!empty($this->fields[$fieldname]['width'])) {
Строки 1218 и 1219 (метод compareSort) — сортировка по дате:

diff
- $val_a = strtotime($a[$this->sortkey]);
- $val_b = strtotime($b[$this->sortkey]);
+ $val_a = strtotime((string)($a[$this->sortkey] ?? ''));
+ $val_b = strtotime((string)($b[$this->sortkey] ?? ''));
Строка 1271 (метод getMultiValue) — вывод TV:

diff
- $tvOutput = $tvOutput[$this->tvName];
+ $tvOutput = $tvOutput[$this->tvName] ?? '';
Файл 2: assets/tvs/multitv/settings/default.setting.inc.php
Строка 11 — флаг jQuery:

diff
- if (!$mmActive && !$GLOBALS['mtvjquery']) {
+ if (!$mmActive && empty($GLOBALS['mtvjquery'])) {
Причина: !$GLOBALS['mtvjquery'] даёт warning «Undefined array key mtvjquery», если флаг не установлен. empty() безопаснее.

Итого по MultiTV
Файл	Правок
multitv.class.php	10
default.setting.inc.php	1
Итого	11
При установке через Store: ZIP multiTV.zip, alias multiTV, name_in_modx: "multiTV".

text

**Конец части 5.**
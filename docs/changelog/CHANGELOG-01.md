CHANGELOG-01.md — Раздел 1 + Раздел 2.1–2.4
Часть 1 из 14. Заголовок + пропатченные файлы ядра + патчи ядра (первые 4).

markdown
# 📋 CHANGELOG — VoloLution CMS

**Сборка:** форк Evolution CMS 1.4.37 (Community Edition)  
**Апстрим:** `github.com/evocms-community/evolution` (тег `1.4.37`)  
**Наш репо:** `github.com/srkkrs77/vololution`  
**Цель:** форк с постепенной модернизацией под современные стандарты PHP (Composer, PSR, PDO, типизация). Без Laravel, Twig, Blade, SPA, AJAX-first.  
**Среда:** Windows + Laragon, PHP 8.5.10, nginx, MySQL 8.0.30  
**Сайт:** https://vol.test/ | **Прод:** studio-milstein.de | **БД:** vol, префикс gyl8_  
**Дата начала:** 07.10.2026  
**Переход на форк:** 10.10.2026

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
| 9 | `manager/actions/mutate_content.dynamic.php` | 6 правок `?? 0` / `?? 'none'` |
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
text

**Конец части 1.**


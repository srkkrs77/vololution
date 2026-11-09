CONTEXT-05.md — Что сделано: ядро + CSRF
Часть 5 из 19. Читать после CONTEXT.md, CONTEXT-01.md — CONTEXT-04.md.

## 5. ЧТО СДЕЛАНО: ЯДРО (14 ПАТЧЕЙ) + CSRF-ЗАЩИТА
### 5.1. 13 патчей ядра
#	Файл	Что сделано
1	manager/includes/protect.inc.php	Убран E_STRICT
2	manager/includes/extenders/dbapi.mysqli.class.inc.php	SET SESSION sql_mode=''; + escape_string($s ?? '')
3	assets/lib/class.modxRTEbridge.php	manager_language ?? 'english'
4	manager/includes/preload.functions.inc.php	CSRF-функции + безопасные cookie сессии
5	manager/includes/header.inc.php	CSRF-мета-тег + JS + базовые ??-правки
6	manager/includes/accesscontrol.inc.php	CSRF-проверка POST + whitelist
7	install/config.inc.tpl	CSRF-функции + !empty($modx->config['debug'])
8	manager/includes/config.inc.php	!empty($modx->config['debug'])
9	manager/actions/mutate_content.dynamic.php	6 правок ?? 0 / ?? 'none'
10	assets/plugins/codemirror/codemirror.plugin.php	2 правки ?? 0 / ?? 'none'
11	assets/plugins/managermanager/mm.inc.php	datepicker_offset ?? ''
12	assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.class.php	$string{0} → $string[0]
13	assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.functions.php	$string{0} → $string[0]
manager/index.php — не менялся.
| 14 | `index.php` (корень сайта) | GDPR-патч: отложенный запуск `startCMSSession()` (только для админки и авторизованных) |

Основные группы патчей:
- PHP 8.5-совместимость (E_STRICT, ??, string offsets).
- MySQL 8.x-совместимость (SET SESSION sql_mode='' в dbapi).
- CSRF-защита (токены, meta-тег, JS-перехват, SameSite cookie).

CHANGELOG: разделы 1–2 (построчно).

### 5.2. CSRF-защита
Файлы:

manager/includes/preload.functions.inc.php — сессии + csrf_token()

install/config.inc.tpl — CSRF-функции

manager/includes/accesscontrol.inc.php — проверка POST + whitelist [8, 67, 112, 118]

manager/includes/header.inc.php — meta-тег + JS-блок

manager/includes/config.inc.php — debug-правка

Что защищено:

Механизм	Где
session_set_cookie_params([...]) + SameSite=Lax	preload.functions.inc.php
session_regenerate_id(true) при первом входе	preload.functions.inc.php
Ротация токенов (10 в сессии, хранятся 5)	install/config.inc.tpl
CSRF-проверка POST с whitelist	accesscontrol.inc.php
Meta-тег <meta name="csrf-token">	header.inc.php
JS-перехват submit, form.submit(), fetch, XHR, MutationObserver	header.inc.php
Логирование провалов в Event Log	checkCsrfToken()
Whitelist (действия, не требующие токена):

8 — logout

67 — remove locks

112 — execute module

118 — settings ajax

### 5.3. Задача D техдолга CSRF — ✅ закрыта 11.10.2026
Проблема: в header.inc.php было два CSRF-JS-блока. Второй перехватывал XMLHttpRequest.prototype.open и вызывал setRequestHeader до origOpen.apply() — это ломало AJAX-запросы сторонних пакетов (SimpleGallery → InvalidStateError).

Решение: два блока объединены в один, setRequestHeader вызывается в send, добавлены isSameOrigin / isStateChanging, try/catch, HTMLFormElement.prototype.submit, MutationObserver.

CHANGELOG: раздел 19.

### 5.4. Осталось по CSRF
A. Ротация токенов на login/logout (4 точки) — ⬜

B. Origin / Referer check в checkCsrfToken() — ⬜

C. Защита connector'ов модулей (Store, MultiTV, evoSearch, editDocs) — ⬜

Подробно — CHANGELOG.md, раздел 9.1.

Конец части 5.
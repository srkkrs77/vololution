CHANGELOG-09.md — Раздел 8–9
Часть 9 из 14. Roadmap + техдолг (CSRF + пакеты).

markdown
## РАЗДЕЛ 8. ROADMAP

**Краткая сводка.** Актуальный roadmap — в `docs/roadmap.md` (обновлён под форк-модель 10.10.2026).

| Этап | Что |
|---|---|
| **Этап 0** | Форк и инфраструктура (🟡 в работе) |
| **Этап 1** | SEO + живой поиск |
| **Этап 3** | Социальный слой |
| **Этап 5** | Магазин |
| **Этап 6** | Долгосрочные (мультисайт, мультиязычность) |
| **Этап 7** | Инфраструктура (бэкап, PDO, установщик, CSRF, Store) |

### 8.1. Этап 0 — Форк (новый, добавлен 10.10.2026)

- 0.1 Форк на GitHub — 🟡 началось.
- 0.2 Инфраструктура (`composer.json`, `src/`, `.gitignore`) — ⬜.
- 0.3 SchemaGuard — плагин-сторож для таблиц пакетов — ⬜.

### 8.2. Этап 1 — SEO + живой поиск

- 1.1 SEO-пакет seoVolo — ✅ выполнено 09.10.2026.
- 1.2 Живой поиск (AJAX autocomplete) — ⬜ следующая задача.

### 8.3. Этап 3 — Социальный слой

- 3.1 Комментарии — ⬜.
- 3.2 Авторизация — ⬜.

### 8.4. Этап 5 — Магазин

- Каталог, корзина, оформление, платёжки, доставка — ⬜.
- **Философия:** классические формы, без AJAX-first. Корзина в сессии, оформление POST + redirect.

### 8.5. Этап 6 — Долгосрочные

- 6.1 Мультисайтовость — ⬜.
- 6.2 Мультиязычность (расширение EvoBabel) — ⬜.

### 8.6. Этап 7 — Инфраструктура

- 7.1 Модуль бэкапа — ⬜.
- 7.2 PDO-адаптер — ⬜ (два слоя: старый DBAPI + новый PDO в `src/Database/`).
- 7.3 Свой установщик — ⬜.
- 7.4 CSRF-дыры — 🟡 в работе (задачи A, B, C; D закрыта 11.10.2026).
- 7.5 Свой Extras-Store — ✅ выполнено.
- 7.6 Отвязка от обновлений Evo — ✅ выполнено (частично; в форк-модели — выборочный мерж).
- 7.7 CLI-скрипт для патчей — ❌ отменён (не нужен в форк-модели, есть `git merge`).

---

## РАЗДЕЛ 9. ТЕХДОЛГ

### 9.1 CSRF — 4 задачи

| # | Задача | Статус |
|---|---|---|
| **A** | Ротация токенов на login/logout (4 точки) | ⬜ |
| **B** | Origin / Referer check в `checkCsrfToken()` | ⬜ |
| **C** | Защита connector'ов модулей (Store, MultiTV, evoSearch, editDocs) | ⬜ |
| **D** | Убрать дублирование JS в `header.inc.php` | ✅ **ВЫПОЛНЕНО 11.10.2026** |

#### Задача A — Ротация токенов на login/logout

**Где вызывать `clearCsrfTokens()` (4 точки):**

1. `manager/includes/accesscontrol.inc.php` — при успешном логине менеджера.
2. `manager/processors/logout.processor.php` — при логауте менеджера.
3. `manager/includes/accesscontrol-not-mgr.inc.php` — при логине web-пользователя.
4. Плагин web-логаута — при выходе web-пользователя.

**Что вставить в каждую точку:**

```php
if (function_exists('clearCsrfTokens')) {
    clearCsrfTokens();
}
Критерий готовности:

После logout старый токен → 403.

После login — новый токен в meta-теге.

Задача B — Origin / Referer check
Где: install/config.inc.tpl, функция checkCsrfToken(), перед header('HTTP/1.1 403 Forbidden');.

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
Критерий готовности: POST с чужого Origin → 403 + запись в Event Log.

Тонкость: если $origin пуст (запрос не из браузера, например curl) — проверку пропускаем. Это by design — не блокировать CLI-инструменты.

Задача C — Защита connector'ов модулей
Где искать connector'ы:

assets/modules/store/ — connector.php, ajax.php, action.php

assets/tvs/multitv/ — connector.php, ajax.php

assets/plugins/evoSearch/ — ajax.php, controller.php

assets/modules/editdocs/ — ajax.php, connector.php

Подход (вариант B — общий guard-класс):

Создать assets/lib/csrf/CsrfGuard.php:

php
<?php
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
Прокинуть токен в JS connector'ов:

Подключить csrfTokenMeta() в HTML-обёртку.

Скопировать JS-перехват из header.inc.php (см. раздел 19).

Критерий готовности:

Установка пакета через Store работает.

Запрос без токена → 403.

Задача D — ✅ ВЫПОЛНЕНО 11.10.2026
См. CHANGELOG-19.

9.2 Пакеты
Остались места без ??, не падают:

editdocs.class.php — несколько мест.

save_content.processor.php, строка 614 — $use_udperms без ??. Warning был один раз.

Не критично. Правим по факту.

text

**Конец части 9.**
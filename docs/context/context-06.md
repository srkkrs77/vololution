# CONTEXT-06.md — Что сделано: пакеты

**Часть 6 из 19.** Читать после `CONTEXT.md`, `CONTEXT-01.md` — `CONTEXT-05.md`.

**Обновлено:** 13.10.2026 (FormLister → 8 патченных).

---

## 6. ЧТО СДЕЛАНО: ПАКЕТЫ

### 6.1. Восемь патченных пакетов

Правки в основном — PHP 8.5-совместимость: `??`, `!empty()`, string offsets, `\Exception`. Плюс случаи PHP 8.4+ (`mb_encode_numericentity`, `idn_to_ascii`, `fputcsv`).

| # | Пакет | Файлов | Правок | CHANGELOG |
|---|---|---|---|---|
| 1 | **MultiTV** | 2 | 11 | 4.1 |
| 2 | **FormResults** | 2 | 4 | 4.2 |
| 3 | **SimpleTube** | 2 | 5 | 4.3 |
| 4 | **evoSearch** | 1 | 5 | 4.4 |
| 5 | **editDocs** | 2 | 21 | 4.5 |
| 6 | **MultiCategories** | 1 | 2 | 4.6 |
| 7 | **WebP Converter** | 2 | 5 | 4.7 |
| 8 | **FormLister** | 1 | 1 | 4.8 |

**Кратко по каждому:**

**MultiTV:**
- `assets/tvs/multitv/includes/multitv.class.php` — 10 правок `??` / `!empty()` (строки 39, 53, 60, 106, 151, 270, 443, 616, 631, 1218–1219, 1271)
- `assets/tvs/multitv/settings/default.setting.inc.php` — `empty($GLOBALS['mtvjquery'])`

**FormResults:**
- `core/src/FormResults.php` — autoload через `vendor/autoload.php`
- `core/templates/forms_list.tpl` — 3 правки `??`
- Инструкция: при установке переименовать `config/*.sample.php` → `*.php`

**SimpleTube:**
- `lib/controller.class.php` — 4 правки `??`
- `lib/SimpleTube/simpletube.class.php` — 1 правка `??`

**evoSearch:**
- `plugin.class.php` — 5 правок (`emptyExcluded`, `makeSQLForSelectWords`, `__construct`, `getDicts`, `Words2BaseForm`)

**editDocs:**
- `assets/lib/MODxAPI/modResource.php` — роли `[1]` → `[1, 4]`
- `assets/modules/editdocs/editdocs.class.php` — 5 групп:
  1. `max_rows ?? 0` (3 места)
  2. `fputcsv($file, $data, $dm, '"', '\\')` (4 места)
  3. `need_xls ?? 0` (2 места)
  4. Защита `$_POST`/`$_FILES` (editDoc, getAllList, uploadFile, massMove)
  5. Вспомогательная защита (importReady, megaPrepare, smallPrepare, unpublished, saveConfig, treeCategories)

**MultiCategories:**
- `assets/plugins/multicategories/lib/model.php` — `\Exception`, `protected $log`

**WebP Converter** (11.10.2026):
- **Код плагина в админке** (элемент, не файл библиотеки):
  - `if (empty($o)) break;` — защита от пустого контента
  - `mb_convert_encoding(..., 'HTML-ENTITIES', ...)` → `mb_encode_numericentity($o, [0x80, 0x10FFFF, 0, ~0], 'UTF-8')` — в PHP 8.4+ `HTML-ENTITIES` удалена
  - `$_SERVER['HTTP_ACCEPT'] ?? ''`, `$_SERVER['HTTP_USER_AGENT'] ?? ''`
  - `libxml_clear_errors()` + восстановление `libxml_use_internal_errors`
- `install/assets/plugins/webpconverter.tpl` — дескриптор v1.1.0
- **Файл** `assets/plugins/webpconverter/plugin.webpconverter.php` — **не менялся**

**FormLister** (13.10.2026):
- `assets/snippets/FormLister/lib/Validator.php` — метод `sanitizeEmail()`:
  - `!empty($email)` — не обрабатывать пустую строку
  - `if (empty($_email[1])) return $email;` — нет домена → вернуть как есть
  - `try/catch (\ValueError)` — защита от невалидного IDN
- **Причина:** PHP 8.4+ бросает `ValueError` при передаче пустой строки в `idn_to_ascii()`. FormLister вызывал `idn_to_ascii` в `sanitizeEmail()`, не проверяя, пустое ли поле. При отправке формы с пустым email или без `@` — фатальная ошибка, белая страница, 500.

**Построчные diff'ы:** `CHANGELOG.md`, раздел 4.

### 6.2. Восемь пакетов без правок

| # | Пакет | Тип | Назначение |
|---|---|---|---|
| 1 | DocLister | сниппет | Универсальный вывод документов |
| 2 | SimpleGallery | плагин | Простая галерея изображений |
| 3 | EvoBabel | плагин | Мультиязычность |
| 4 | evoFileManagerDialog | плагин | Диалог файлового менеджера |
| 5 | FirstChildRedirect | сниппет | Редирект родителя на первого потомка |
| 6 | MonthDate | сниппет | Вывод даты по-русски |
| 7 | DLSiblings | сниппет | Вывод соседних документов (prev/next) |
| 8 | phpThumb | библиотека | Обработка изображений |

Эти пакеты **работают как есть** — без правок ядра или пакета.

**Изменения:**
- **WebP Converter (11.10.2026):** переведён в патченные (PHP 8.5 fix).
- **FormLister (13.10.2026):** переведён в патченные (PHP 8.4+ `idn_to_ascii`).

**Было 10 без правок** — стало **8**.

### 6.3. Проблема SimpleGallery на проде — 10.10.2026

**Сайт:** `studio-milstein.de`  
**Симптом:** при удалении ресурса в админке:

Table 'dbs16208630.eh2v_sg_images' doesn't exist
SQL > DELETE FROM dbs16208630.eh2v_sg_images WHERE sg_rid IN (1)


**Причина:** при установке SimpleGallery на хостинге не создалась таблица `sg_images`. Установщик пакета не выполнил SQL (точная причина не выяснена — права БД, версия MySQL, синтаксис).

**Временное решение:** таблица создана вручную SQL-запросом.

**Стратегическое решение:** SchemaGuard (часть 10 и часть 17) — плагин на `OnManagerPageInit`, проверяет и создаёт отсутствующие таблицы сторонних пакетов. Пакеты сторонних авторов (Pathologic, Grinyaha) **не патчим** — сторож работает снаружи.

### 6.4. SimpleGallery + AJAX + CSRF

SimpleGallery — плагин, работающий через AJAX (`assets/plugins/simplegallery/ajax.php`). Вкладка «Галерея» в документе грузится через XHR.

10.10.2026 на проде при открытии вкладки — ошибка `InvalidStateError: setRequestHeader`. Причина — сломанный CSRF-JS-блок в `header.inc.php` (см. часть 5.3). Исправлено 11.10.2026.

**13.10.2026 — второй случай.** SimpleGallery на проде заявил «не удалось обработать ответ сервера». Причина — были отключены `cache_plugins`, `cache_snippets`, `cache_chunks`. Их отключение ломает регистрацию плагинов и AJAX-endpoint. **Восстановлено в `1`.**

**Важно:** это не проблема SimpleGallery. Это была наша проблема. SimpleGallery сам по себе рабочий.

**Политика проекта:** AJAX в галерее **оправдан** (UX не страдает). Переписывать SimpleGallery под классические формы — **не будем**. См. часть 12 (философия простоты).

### 6.5. FormLister — патч на пустой email

**Дата:** 13.10.2026  
**Файл:** `assets/snippets/FormLister/lib/Validator.php`

**Проблема:** при отправке формы с пустым email — белая страница и 500. FormLister передавал пустую строку в `idn_to_ascii()`, что в PHP 8.4+ бросает `ValueError`.

**Решение:** добавить проверки `!empty($email)` и `if (empty($_email[1]))`, обернуть вызов `idn_to_ascii` в `try/catch`.

**См. часть 6.1** (кратко) и `CHANGELOG-07.md`, раздел 4.8 (полностью).

### 6.6. Кэширование Evo — настройки прода (13.10.2026)

**Проблема:** после правок шаблонов на сайте «застревала» старая вёрстка. При `cache_type = 2` (учёт ID + `$_GET`) Evo создавал отдельный кэш под каждую комбинацию UTM/GET-меток. Рекламные ссылки показывали старую версию.

**Решение:**

| Настройка | Значение |
|---|---|
| `cache_snippets` | `1` |
| `cache_plugins` | `1` |
| `cache_chunks` | `1` |
| `cache_type` | **`1`** (только ID) |

**Что это даёт:**
- Один кэш на страницу, независимо от UTM.
- UTM-метки работают — метрика ловит через JS.
- Правки видны сразу после сохранения.

**Что НЕ отключать:**
- `cache_snippets`, `cache_plugins`, `cache_chunks` — **всегда `1`.** Их отключение ломает SimpleGallery и другие пакеты.

**Отклонённый плагин AutoCacheClear:** создавали → не сработал (`clearCache('full')` и `clearCache('siteContent')` ломали сохранение формы). Удалён. **Не использовать.**

### 6.7. Авторы пакетов

- **Pathologic** — DocLister, FormLister, MultiCategories, editDocs (соавтор), SimpleGallery, SimpleTube, evoSearch, DLSiblings, MonthDate, phpThumb, WebP Converter.
- **Grinyaha** — editDocs.
- **Aharito** — DLSiblings (на основе DLPrevNext от Agel_Nash).
- **webber** — evoBabel.
- GitHub: `github.com/Pathologic`, `github.com/Grinyaha`, `github.com/aharito`.
### 6.8. Своя разработка: Сниппет cookieConsent (GDPR)
**Дата:** 09.11.2026

Для соблюдения законов EU (GDPR / TTDSG) был создан собственный сниппет `cookieConsent`.
- **Файл:** `assets/snippets/cookieConsent/snippet.cookieConsent.php` (v1.1.0).
- **Дескриптор для установщика:** `install/assets/snippets/cookieConsent.tpl`.
- **Функционал:** Выводит баннер согласия на 6 языках (RU, EN, DE, FR, ES, IT). Сохраняет выбор пользователя в `localStorage` (без установки cookie на сервере).
- **Связь с ядром:** Работает в паре с GDPR-патчем в `index.php` (отложенный старт сессии), чтобы до клика по баннеру сайт вообще не ставил cookie.
- **Документация:** `docs/libs/cookieConsent.md`.
---

**Конец части 6.**
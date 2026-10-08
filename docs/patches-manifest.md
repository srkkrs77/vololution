# 🔧 PATCHES MANIFEST — VoloLution CMS

**Сборка:** Evolution CMS 1.4.37 (CE)
**Окружение:** Windows + Laragon, PHP 8.5.10, MySQL 8.0.30, nginx
**Сайт:** vol.test | БД: vol, префикс gyl8_
**Последнее обновление манифеста:** 08.10.2026 (после SEO-пакета)
**Назначение:** реестр всех правок ядра и пакетов для быстрой сверки при выходе нового релиза CE.

---

## Как пользоваться

1. Вышел релиз CE 1.4.38 — видишь в RSS или на GitHub.
2. Открываешь diff: `github.com/evocms-community/evolution/compare/1.4.37...1.4.38`
3. Смотришь — есть ли в diff файлы из этого манифеста.
4. Если да — открываешь нашу правку, сравниваешь с новой версией файла.
5. Накатываешь: берёшь новую версию CE + накладываешь наш патч.
6. Тестируешь на `vol.test`.
7. Обновляешь `CHANGELOG.md`.

**Если файла из манифеста нет в diff — патч переносится автоматически, ничего не делаешь.**

---

## ЯДРО — 13 ФАЙЛОВ

### 1. `manager/includes/protect.inc.php`
- **Правка:** убран `E_STRICT` из `error_reporting()`
- **Строка:** 6
- **CHANGELOG:** 2.1

### 2. `manager/includes/extenders/dbapi.mysqli.class.inc.php`
- **Правка A:** добавлено `SET SESSION sql_mode='';` в `connect()`
- **Правка B:** `escape_string($s ?? '')` в методе `escape()`
- **CHANGELOG:** 2.2

### 3. `assets/lib/class.modxRTEbridge.php`
- **Правка:** `($modx->config['manager_language'] ?? 'english')` в `initLang()`
- **Строка:** ~807
- **CHANGELOG:** 2.3

### 4. `manager/includes/preload.functions.inc.php`
- **Правка A:** `session_set_cookie_params([...])` + SameSite=Lax в `startCMSSession()`
- **Правка B:** `session_regenerate_id(true)` при первом входе
- **Правка C:** `csrf_token()` через `getCurrentCsrfToken()`
- **Правка D:** `csrf_field()` с `htmlspecialchars()`
- **CHANGELOG:** 3.1

### 5. `manager/includes/header.inc.php`
- **Правка A:** CSRF meta-тег через `csrfTokenMeta()`
- **Правка B:** CSRF JS-блок (перехват форм, fetch, XHR)
- **Правка C:** `$modx_manager_charset ?? 'UTF-8'`
- **Правка D:** `$modx_lang_attribute ?? 'en'`
- **Техдолг:** два JS-блока (раздел 9.1, задача D)
- **CHANGELOG:** 2.4, 3.4

### 6. `manager/includes/accesscontrol.inc.php`
- **Правка:** CSRF-проверка POST с whitelist `[8, 67, 112, 118]`
- **CHANGELOG:** 3.3

### 7. `install/config.inc.tpl`
- **Правка A:** CSRF-функции (generateCsrfToken, validateCsrfToken и др.)
- **Правка B:** `!empty($modx->config['debug'])` в `checkCsrfToken()`
- **CHANGELOG:** 3.2, 3.5

### 8. `manager/includes/config.inc.php`
- **Правка:** `!empty($modx->config['debug'])` в `checkCsrfToken()`
- **CHANGELOG:** 3.5

### 9. `manager/actions/mutate_content.dynamic.php`
- **Правка A:** `($modx->config['use_breadcrumbs'] ?? 0)` — строка 601
- **Правка B:** `$which_editor = $modx->config['which_editor'] ?? 'none'` — строки 909–910
- **Правка C:** `($modx->config['which_editor'] ?? 'none')` — строка ~918
- **Правка D:** `$editor = $modx->config['which_editor'] ?? 'none'` — строка ~942
- **Правка E:** `($modx->config['group_tvs'] ?? 0)` — строка 1375
- **Правка F:** `($use_udperms ?? 0)` — строка 1383
- **CHANGELOG:** 2.5

### 10. `assets/plugins/codemirror/codemirror.plugin.php`
- **Правка A:** `($modx->config['manager_theme_mode'] ?? 0)` — строка 45
- **Правка B:** `($modx->config['which_editor'] ?? 'none')` — строка 52
- **Внимание:** это пакет CE, может обновиться вместе с ядром
- **CHANGELOG:** 2.6

### 11. `assets/plugins/managermanager/mm.inc.php`
- **Правка:** `$modx->config['datepicker_offset'] ?? ''` — строка 212
- **Внимание:** это пакет CE, может обновиться вместе с ядром
- **CHANGELOG:** 2.7

### 12. `assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.class.php`
- **Правка:** `$filename{0}` → `$filename[0]` (и другие аналоги)
- **Внимание:** это пакет CE, может обновиться вместе с ядром
- **CHANGELOG:** 2.7

### 13. `assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.functions.php`
- **Правка:** `$string{$i}` → `$string[$i]`, `$escapeables{$i}` → `$escapeables[$i]`
- **Внимание:** это пакет CE, может обновиться вместе с ядром
- **CHANGELOG:** 2.7

---

## ПАКЕТЫ С ПРАВКАМИ — 6 ПАКЕТОВ

### MultiTV
- `assets/tvs/multitv/includes/multitv.class.php` — 10 правок `??`
- `assets/tvs/multitv/settings/default.setting.inc.php` — 1 правка
- **CHANGELOG:** 4.1

### FormResults
- `assets/modules/formresults/core/src/FormResults.php` — autoload
- `assets/modules/formresults/core/templates/forms_list.tpl` — 3 правки `??`
- **CHANGELOG:** 4.2

### SimpleTube
- `assets/plugins/simpletube/lib/controller.class.php` — 4 правки `??`
- `assets/snippets/simpletube/lib/SimpleTube/simpletube.class.php` — 1 правка
- **CHANGELOG:** 4.3

### evoSearch
- `assets/plugins/evoSearch/plugin.class.php` — 5 правок `??`
- **CHANGELOG:** 4.4

### editDocs
- `assets/lib/MODxAPI/modResource.php` — роли `[1, 4]`
- `assets/modules/editdocs/editdocs.class.php` — 5 групп правок
- **CHANGELOG:** 4.5

### MultiCategories
- `assets/plugins/multicategories/lib/model.php` — `\Exception`, `protected $log`
- **CHANGELOG:** 4.6

---

## ПАКЕТЫ БЕЗ ПРАВОК — 10 ПАКЕТОВ

Не сломаются при обновлении CE. Перечислены для полноты.

| # | Пакет | Тип | CHANGELOG |
|---|---|---|---|
| 1 | DocLister | сниппет | 5 |
| 2 | FormLister | сниппет | 5 |
| 3 | SimpleGallery | плагин | 5 |
| 4 | EvoBabel | плагин | 5 |
| 5 | evoFileManagerDialog | плагин | 5 |
| 6 | FirstChildRedirect | сниппет | 5 |
| 7 | MonthDate | сниппет | 5 |
| 8 | WebP Converter | плагин | 5 |
| 9 | DLSiblings | сниппет | 5 |
| 10 | phpThumb | библиотека | — |

---

## СВОЙ STORE — отдельный модуль

Правки не затрагивают ядро CE. Не сломается при обновлении.
Все правки — в `CHANGELOG.md`, раздел 14.

Файлы:
- `assets/modules/store/core.php`
- `assets/modules/store/api.php` (новый)
- `assets/modules/store/template/main.html`
- `assets/modules/store/js/store.js`
- `assets/modules/store/css/style.css`
- `install/assets/modules/store.tpl`

---

## SEO-ПАКЕТ seoVolo — собственная разработка

**Не зависит от ядра CE.** Не сломается при обновлении.
Все правки — в `CHANGELOG.md`, раздел 15.

### Установочные дескрипторы (в дистрибутиве)

**11 TV в `install/assets/tvs/`:**
- `seo_title.tpl`
- `seo_description.tpl`
- `seo_keywords.tpl`
- `seo_og_image.tpl`
- `seo_canonical.tpl`
- `seo_noindex.tpl`
- `seo_schema_type.tpl`
- `seo_hreflang.tpl`
- `sitemap_priority.tpl`
- `sitemap_changefreq.tpl`
- `sitemap_exclude.tpl`

**Сниппет:**
- `install/assets/snippets/seoVolo.tpl`

**Плагин:**
- `install/assets/plugins/seoVolo.tpl`

**Изменённые чанки:**
- `install/assets/chunks/head.tpl` — v2.0.0 (вызов `[!seoVolo!]`)
- `install/assets/chunks/mm_rules.tpl` — v2.0.0 (вкладка SEO + 11 TV)

### Код (в assets/)

**Сниппет:**
- `assets/snippets/seoVolo/snippet.seoVolo.php`

**Плагин:**
- `assets/plugins/seoVolo/plugin.seoVolo.php`
- `assets/plugins/seoVolo/css/seo.css` (отложено)
- `assets/plugins/seoVolo/js/seo.js` (отложено)
- `assets/plugins/seoVolo/lang/ru.php`
- `assets/plugins/seoVolo/lang/en.php`

### Техдолг SEO-пакета

1. Индикатор длины description — отложен, нужен widget TV.
2. `sitemap_*` TV — создать в `DLSitemap` через параметры сниппета.
3. Системные настройки `seo_*` — не создаются автоматически.
4. Schema.org Product — без `seo_sku`, `seo_price`.
5. Hreflang — требует активного EvoBabel.

---

## ВНЕШНИЕ ИСТОЧНИКИ — что НЕ трогаем

- RSS-лента релизов CE: `github.com/evocms-community/evolution/releases.atom`
- RSS-лента безопасности: `github.com/extras-evolution/security-fix/releases.atom`
- Официальный сайт: `evo.im` — избегаем упоминаний в коде
- `github.com/evolution-cms` — старая ветка, не используем

---

## ЧЕК-ЛИСТ ПРИ ОБНОВЛЕНИИ CE

### ФАЗА 0 — ПОДГОТОВКА (до любых действий)

- [ ] 1. Убедиться, что релиз CE действительно вышел (не RC, не beta)
- [ ] 2. Прочитать Release Notes целиком, выписать критичные изменения
- [ ] 3. Проверить, требует ли новый релиз другой версии PHP
- [ ] 4. Сделать snapshot Laragon (Menu → Snapshot → New)
- [ ] 5. Дамп БД: `mysqldump -u root vol > vol_backup_YYYYMMDD.sql`
- [ ] 6. Скопировать всю папку vol.test в vol.test.bak
- [ ] 7. Зафиксировать текущее состояние в git:
       `git add . && git commit -m "Pre-upgrade 1.4.37 snapshot"`
       `git tag v1.4.37-vololution`
       `git push && git push --tags`
- [ ] 8. Открыть diff: `github.com/evocms-community/evolution/compare/1.4.37...X.Y.Z`
- [ ] 9. Сверить список изменённых файлов с patches-manifest.md
- [ ] 10. Составить план: какие наши патчи затронуты, какие нет

### ФАЗА 1 — ПРИМЕНЕНИЕ

- [ ] 11. Скачать релизный ZIP CE 1.4.38
- [ ] 12. Распаковать в отдельную папку (не в vol.test!)
- [ ] 13. Для каждого файла из манифеста:
       - сравнить наш патч с новой версией файла
       - если CE не менял файл → оставить наш
       - если менял → взять новый + наложить патч руками
       - если изменения CE конфликтуют → решить, что важнее
- [ ] 14. Скопировать обновлённые файлы в vol.test
- [ ] 15. Скопировать изменения CE, которых нет в манифесте (новые фичи, security)
- [ ] 15a. Проверить, обновил ли CE плагины:
        - CodeMirror (assets/plugins/codemirror/)
        - ManagerManager (assets/plugins/managermanager/)
        Если да — заново наложить патчи из пунктов 10, 11, 12, 13 манифеста.
- [ ] 16. Проверить, что install/assets/modules/store.tpl не перезаписан
- [ ] 17. Проверить, что assets/ не затронут CE (там наши пакеты)
- [ ] 17a. ВАЖНО: при распаковке ZIP CE — НЕ копировать папку `assets/` вообще.
        Наши пакеты и Store живут только в `assets/`. CE их не содержит.
- [ ] 17b. НЕ копировать папку `install/` — там наш store.tpl.
        Только если CE реально обновил установщик (редко).
- [ ] 17c. НЕ копировать `docs/`, `packages/`, `CONTEXT.md`, `CHANGELOG.md`,
        `patches-manifest.md` — это наша документация, CE её не касается.
- [ ] 17d. Проверить, что SEO-пакет (seoVolo) в `install/assets/` не затронут CE.

### ФАЗА 2 — ТЕСТИРОВАНИЕ (все пункты обязательны)

**Админка — ядро:**
- [ ] 18. Вход в админку — работает
- [ ] 19. Dashboard открывается без ошибок
- [ ] 20. Сохранение документа
- [ ] 21. Сохранение шаблона
- [ ] 22. Сохранение TV
- [ ] 23. Сохранение чанка
- [ ] 24. Сохранение сниппета
- [ ] 25. Сохранение конфигурации системы
- [ ] 26. Logout → Login
- [ ] 27. Смена пароля
- [ ] 28. Загрузка изображения
- [ ] 29. Загрузка файла
- [ ] 30. Менеджер файлов

**CSRF-защита:**
- [ ] 31. Форма без токена → 403
- [ ] 32. Форма с токеном → 200
- [ ] 33. AJAX-запрос с X-CSRF-TOKEN → 200
- [ ] 34. CSRF meta-тег в HTML админки присутствует
- [ ] 35. CSRF-JS блок в header работает (форма отправляется)

**Наши пакеты (по одному):**
- [ ] 36. VoloLution Store → открывается, каталог грузится
- [ ] 37. Store → установка тестового пакета (MonthDate)
- [ ] 38. Store → удаление и переустановка
- [ ] 39. Store → установка из локального ZIP
- [ ] 40. MultiTV → TV-поле с множественным списком
- [ ] 41. FormResults → модуль открывается
- [ ] 42. SimpleTube → сниппет работает на странице
- [ ] 43. evoSearch → поиск по сайту
- [ ] 44. editDocs → модуль открывается, импорт/экспорт
- [ ] 45. MultiCategories → вкладка в документе
- [ ] 46. DocLister → вывод списка на странице
- [ ] 47. FormLister → форма отправляется
- [ ] 48. SimpleGallery → галерея
- [ ] 49. evoBabel → мультиязычность
- [ ] 50. evoFileManagerDialog → диалог открывается
- [ ] 51. FirstChildRedirect → редирект работает
- [ ] 52. WebP Converter → конвертирует изображения
- [ ] 53. phpThumb → превью генерируются
- [ ] 54. DLSiblings → prev/next
- [ ] 55. MonthDate → дата выводится

**SEO-пакет seoVolo:**
- [ ] 55a. Вкладка SEO в форме документа
- [ ] 55b. Все 11 TV на вкладке
- [ ] 55c. Title генерируется с fallback
- [ ] 55d. Description с fallback и обрезкой
- [ ] 55e. Canonical (авто/через TV)
- [ ] 55f. Open Graph — все теги
- [ ] 55g. Twitter Cards — все теги
- [ ] 55h. Schema.org JSON-LD — `@graph`
- [ ] 55i. robots не выводится при `index, follow`
- [ ] 55j. hreflang (если включён)

**Фронтенд сайта:**
- [ ] 56. Главная открывается
- [ ] 57. Любая внутренняя страница открывается
- [ ] 58. Меню работает
- [ ] 59. Формы на фронте работают
- [ ] 60. Изображения грузятся
- [ ] 61. Кэш сбрасывается (Инструменты → Сбросить кэш)

**Логи и метрики:**
- [ ] 62. PHP error_log — пусто (или только старые записи)
- [ ] 63. Event log в Evo — нет новых ошибок
- [ ] 64. MySQL — все таблицы на месте: `SHOW TABLES LIKE 'gyl8_%';`
- [ ] 65. Версия ядра в админке (Настройки → Системная информация)

### ФАЗА 3 — ФИКСАЦИЯ

- [ ] 66. Обновить CHANGELOG.md — новый раздел «Обновление до 1.4.38»
- [ ] 67. Обновить patches-manifest.md — если появились новые патчи
- [ ] 68. Обновить CONTEXT.md — версия ядра
- [ ] 69. `git add . && git commit -m "Upgrade to CE 1.4.38"`
- [ ] 70. `git tag v1.4.38-vololution`
- [ ] 71. `git push && git push --tags`
- [ ] 72. Удалить vol.test.bak (или оставить на неделю)
- [ ] 73. Удалить дамп БД (или оставить на месяц)

### ФАЗА 4 — ОТКАТ (если что-то сломалось)

- [ ] 1. Остановить работу с сайтом (не паниковать)
- [ ] 2. Посмотреть PHP error_log — что именно упало
- [ ] 3. Если ошибка в 1–2 файлах:
       `git checkout HEAD~1 -- manager/includes/проблемный.php`
       или вручную восстановить из vol.test.bak
- [ ] 4. Если ошибок много:
       восстановить БД: `mysql -u root vol < vol_backup_YYYYMMDD.sql`
       восстановить файлы: скопировать всё из vol.test.bak
       или Laragon → Snapshot → Restore
- [ ] 5. Проверить, что сайт открывается
- [ ] 6. Проверить, что админка открывается
- [ ] 7. Записать в CHANGELOG: «Попытка обновления откачена, причина: ...»
- [ ] 8. Разобраться с конкретным патчем, который конфликтует
- [ ] 9. Повторить попытку, когда будет готов фикс

### ВРЕМЯ НА ОБНОВЛЕНИЕ

- Фаза 0 (подготовка): ~30 минут
- Фаза 1 (применение): ~1–2 часа
- Фаза 2 (тесты): ~1 час
- Фаза 3 (фиксация): ~15 минут

**Итого: минимум полдня.** Не запускать вечером на уставшую голову.

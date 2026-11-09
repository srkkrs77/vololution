# 🔧 PATCHES MANIFEST — VoloLution CMS

**Сборка:** форк Evolution CMS 1.4.37 (CE)  
**Апстрим:** `github.com/evocms-community/evolution` (тег `1.4.37`)  
**Наш репо:** `github.com/srkkrs77/vololution`  
**Окружение:** Windows + Laragon, PHP 8.5.10, MySQL 8.0.30, nginx  
**Сайт:** vol.test | **Прод:** studio-milstein.de | **БД:** vol, префикс gyl8_  
**Последнее обновление манифеста:** 13.10.2026  
**Назначение:** реестр файлов, которые **отличаются от апстрима CE**. Используется при `git merge` security-фиксов и релизов апстрима — чтобы понимать, где будут конфликты.

---

## Как пользоваться (форк-модель)

Патчи ядра теперь — обычные коммиты в форке. Ручной накат не нужен. Этот файл нужен для другого:

1. Вышел релиз или security-фикс апстрима.
2. Смотрим diff: `github.com/evocms-community/evolution/compare/1.4.37...X.Y.Z`.
3. **Сверяем список изменённых файлов с этим реестром.**
4. Если апстрим тронул файл из реестра — потенциальный конфликт. Разбираем вручную при `git merge`.
5. Если файла из реестра нет в diff — конфликта нет.

**Git-команды для мержа:**
```
git fetch upstream
git checkout -b merge/upstream-YYYY-MM
git merge upstream/1.4.x
# разрешаем конфликты
git checkout main
git merge merge/upstream-YYYY-MM
```

---

## ЯДРО — 13 ФАЙЛОВ (отличия от апстрима CE 1.4.37)

### 1. `manager/includes/protect.inc.php`
- **Правка:** убран `E_STRICT` из `error_reporting()`
- **Строка:** 6
- **CHANGELOG:** 2.1

### 2. `manager/includes/extenders/dbapi.mysqli.class.inc.php`
- **Правка A:** `SET SESSION sql_mode='';` в `connect()` — **MySQL 8.x-фикс**, не PHP.
  - Критичен для MySQL 8.0 и 8.4.
  - Без него Evo падает на первом запросе (`ONLY_FULL_GROUP_BY`, `STRICT_TRANS_TABLES`).
  - Проверено на Beget (MySQL 8.0) и Laragon.
  - На MySQL 5.7 — не обязателен, но не мешает.
- **Правка B:** `escape_string($s ?? '')` в `escape()` — PHP 8.5-фикс.
- **CHANGELOG:** 2.2

### 3. `assets/lib/class.modxRTEbridge.php`
- **Правка:** `($modx->config['manager_language'] ?? 'english')` в `initLang()`
- **Строка:** ~807
- **CHANGELOG:** 2.3

### 4. `manager/includes/preload.functions.inc.php`
- **Правка A:** `session_set_cookie_params([...])` + SameSite=Lax
- **Правка B:** `session_regenerate_id(true)`
- **Правка C:** `csrf_token()` через `getCurrentCsrfToken()`
- **Правка D:** `csrf_field()` с `htmlspecialchars()`
- **CHANGELOG:** 3.1

### 5. `manager/includes/header.inc.php`
- **Правка A:** CSRF meta-тег `<?= csrfTokenMeta() ?>`
- **Правка B:** CSRF JS-блок — **единственный**, объединён 11.10.2026
- **Правка C:** `$modx_manager_charset ?? 'UTF-8'`
- **Правка D:** `$modx_lang_attribute ?? 'en'`
- **Правка E:** no-cache мета-теги для админки (11.10.2026)
- **Задача D техдолга CSRF — ✅ закрыта** (дублирование JS устранено)
- **CHANGELOG:** 2.4, 2.8, 3.4, 19

### 6. `manager/includes/accesscontrol.inc.php`
- **Правка:** CSRF-проверка POST + whitelist `[8, 67, 112, 118]`
- **CHANGELOG:** 3.3

### 7. `install/config.inc.tpl`
- **Правка A:** CSRF-функции (`generateCsrfToken`, `getCurrentCsrfToken`, `getRequestCsrfToken`, `validateCsrfToken`, `checkCsrfToken`, `clearCsrfTokens`, `csrfTokenField`, `csrfTokenMeta`)
- **Правка B:** `!empty($modx->config['debug'])` в `checkCsrfToken()`
- **CHANGELOG:** 3.2, 3.5

### 8. `manager/includes/config.inc.php`
- **Правка:** `!empty($modx->config['debug'])` в `checkCsrfToken()`
- **CHANGELOG:** 3.5
- **ВНИМАНИЕ:** это локальный файл, **не коммитится** (в `.gitignore`). Правка хранится в `install/config.inc.tpl` и `docs/`.

### 9. `manager/actions/mutate_content.dynamic.php`
- **Правка A:** `($modx->config['use_breadcrumbs'] ?? 0)` — строка 601
- **Правка B:** `$which_editor = $modx->config['which_editor'] ?? 'none'` — 909–910
- **Правка C:** `($modx->config['which_editor'] ?? 'none')` — ~918
- **Правка D:** `$editor = $modx->config['which_editor'] ?? 'none'` — ~942
- **Правка E:** `($modx->config['group_tvs'] ?? 0)` — 1375
- **Правка F:** `($use_udperms ?? 0)` — 1383
- **CHANGELOG:** 2.5

### 10. `assets/plugins/codemirror/codemirror.plugin.php`
- **Правка A:** `($modx->config['manager_theme_mode'] ?? 0)` — 45
- **Правка B:** `($modx->config['which_editor'] ?? 'none')` — 52
- **Внимание:** это пакет CE
- **CHANGELOG:** 2.6

### 11. `assets/plugins/managermanager/mm.inc.php`
- **Правка:** `datepicker_offset ?? ''` — 212
- **Внимание:** это пакет CE
- **CHANGELOG:** 2.7

### 12. `assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.class.php`
- **Правка:** `$filename{0}` → `$filename[0]`
- **Внимание:** это пакет CE
- **CHANGELOG:** 2.7

### 13. `assets/plugins/managermanager/widgets/ddresizeimage/phpthumb.functions.php`
- **Правка:** `$string{$i}` → `$string[$i]`
- **Внимание:** это пакет CE
- **CHANGELOG:** 2.7

---

## ПАКЕТЫ С ПРАВКАМИ — 8 ПАКЕТОВ

### MultiTV
- `assets/tvs/multitv/includes/multitv.class.php` — 10 правок `??`
- `assets/tvs/multitv/settings/default.setting.inc.php` — 1 правка
- **CHANGELOG:** 4.1

### FormResults
- `assets/modules/formresults/core/src/FormResults.php` — autoload
- `assets/modules/formresults/core/templates/forms_list.tpl` — 3 правки
- **CHANGELOG:** 4.2

### SimpleTube
- `assets/plugins/simpletube/lib/controller.class.php` — 4 правки
- `assets/snippets/simpletube/lib/SimpleTube/simpletube.class.php` — 1 правка
- **CHANGELOG:** 4.3

### evoSearch
- `assets/plugins/evoSearch/plugin.class.php` — 5 правок
- **CHANGELOG:** 4.4

### editDocs
- `assets/lib/MODxAPI/modResource.php` — роли `[1, 4]`
- `assets/modules/editdocs/editdocs.class.php` — 5 групп
- **CHANGELOG:** 4.5

### MultiCategories
- `assets/plugins/multicategories/lib/model.php` — `\Exception`, `protected $log`
- **CHANGELOG:** 4.6

### WebP Converter (11.10.2026)
- **Код плагина в админке:**
  - `if (empty($o)) break;` — защита от пустого контента
  - `mb_convert_encoding(..., 'HTML-ENTITIES', ...)` → `mb_encode_numericentity(...)`
  - `$_SERVER['HTTP_ACCEPT'] ?? ''`, `$_SERVER['HTTP_USER_AGENT'] ?? ''`
  - `libxml_clear_errors()` + восстановление `libxml_use_internal_errors`
- `install/assets/plugins/webpconverter.tpl` — дескриптор v1.1.0
- `assets/plugins/webpconverter/plugin.webpconverter.php` — **не менялся**
- **CHANGELOG:** 4.7

### FormLister (13.10.2026)
- `assets/snippets/FormLister/lib/Validator.php` — метод `sanitizeEmail()`:
  - `!empty($email)` — не обрабатывать пустую строку
  - `if (empty($_email[1])) return $email;` — нет домена → вернуть как есть
  - `try/catch (\ValueError)` — защита от невалидного IDN
- **Причина:** PHP 8.4+ бросает `ValueError` при пустой строке в `idn_to_ascii()`
- **CHANGELOG:** 4.8

---

## ПАКЕТЫ БЕЗ ПРАВОК — 8 ПАКЕТОВ

| # | Пакет | Тип | CHANGELOG |
|---|---|---|---|
| 1 | DocLister | сниппет | 5 |
| 2 | SimpleGallery | плагин | 5 |
| 3 | EvoBabel | плагин | 5 |
| 4 | evoFileManagerDialog | плагин | 5 |
| 5 | FirstChildRedirect | сниппет | 5 |
| 6 | MonthDate | сниппет | 5 |
| 7 | DLSiblings | сниппет | 5 |
| 8 | phpThumb | библиотека | — |

**FormLister (13.10.2026):** переведён из «без правок» в «с правками» — PHP 8.4+ падал на пустом email.

**ВАЖНО (10.10.2026):** SimpleGallery на проде `studio-milstein.de` не создал таблицу `sg_images`. Решено вручную (SQL). **Стратегическое решение — SchemaGuard** (см. `docs/roadmap.md`, этап 0.3).

**WebP Converter (11.10.2026):** переведён из «без правок» в «с правками» — PHP 8.5 падал на `loadHTML()` при пустом контенте.

---

## СВОЙ STORE — отдельный модуль

Правки не затрагивают ядро CE. Не сломается при обновлении.

**Файлы с правками:**

- `assets/modules/store/core.php` — `$version = "1.0.0"`, удалён fallback на `extras.evo.im`, `$errors = []`, `copyFolder()` без строки `if (!$objects->getDepth() && $object->isFile()) continue;`, `downloadFile()` переписан, `$PACK[$value][$row['name']] = '1'`
- `assets/modules/store/api.php` — новый, прокси каталога
- `assets/modules/store/template/main.html` — без fancybox/логина/self-update, `window.STORE_API`, `.extra-meta` в карточке
- `assets/modules/store/js/store.js` — без `blockedPackages`/`packageDescriptions`, `window.STORE_API` в query, без сравнения версий
- `assets/modules/store/css/style.css` — иконки типов через `::before`, `.extra-meta`
- `assets/modules/store/installer/instprocessor-fast.php` — удалён `$dbase . '.'` из DELETE (строка ~209), выбор языка через `manager_language`
- `assets/modules/store/installer/setup.info.php` — `$params['input_options'] ?? ''` (строка 66)
- `assets/modules/store/installer/lang/` — оставлены только `russian-UTF8.inc.php` и `english.inc.php`
- `assets/modules/store/lang/` — оставлены только `russian-UTF8.php` и `english.php`
- `install/assets/modules/store.tpl` — переписан (VoloLution Store)

**CHANGELOG:** раздел 14.

---

## КАСТОМНЫЕ TV — собственные разработки

**Не зависит от ядра CE.** Не сломается при обновлении апстрима.

### image_alt_caption

**Тип:** кастомная TV через `custom input` + `@INCLUDE`.

**Файлы:**
- `assets/tvs/image_alt_caption/image_alt_caption.php` — рендер TV (три поля + превью)
- `assets/snippets/getImage/snippet.getImage.php` — сниппет вывода

**TV в админке:**
- **Тип ввода:** `Custom Input`
- **Возможные значения:** `@INCLUDE/assets/tvs/image_alt_caption/image_alt_caption.php`

**Сниппет вывода:** `getImage` — читает JSON, выводит `<figure><img loading="lazy"><figcaption>...</figcaption></figure>`.

**Вызов:**
```
[[getImage? &tv=`image_studio`]]
```

**Ограничения:**
1. Кэш браузера — решено патчем `header.inc.php` (пункт 5, правка E).
2. evoBabel — TV не копируется при синхронизации языков. Добавлять ID TV в настройку evoBabel.
3. Копирование документа — связь значений TV может не подтянуться.

**CHANGELOG:** раздел 20.

---

## КЭШИРОВАНИЕ EVO — настройки прода

**Проблема:** после правок шаблонов на сайте «застревала» старая вёрстка. При `cache_type = 2` (учёт ID + `$_GET`) Evo создавал отдельный кэш под каждую комбинацию UTM/GET-меток.

**Решение (13.10.2026):**

| Настройка | Значение |
|---|---|
| `cache_snippets` | `1` |
| `cache_plugins` | `1` |
| `cache_chunks` | `1` |
| `cache_type` | **`1`** (только ID) |

**Что это даёт:**
- Один кэш на страницу, независимо от UTM.
- UTM-метки работают — метрика ловит их через JS.
- Правки видны сразу после сохранения.

**Что НЕ отключать:**
- `cache_snippets`, `cache_plugins`, `cache_chunks` — **всегда `1`.** Их отключение ломает SimpleGallery и другие пакеты.

**Настройка через админку:** Элементы → Системные настройки → Конфигурация → вкладка «Сайт».

**Через SQL (если в интерфейсе нет опции):**
```sql
UPDATE eh2v_system_settings
SET setting_value = '1'
WHERE setting_name = 'cache_type';
```

**CHANGELOG:** раздел 2.9 (обновление).

**Отклонённый плагин AutoCacheClear:** создавали → не сработал (`clearCache('full')` и `clearCache('siteContent')` ломали сохранение). Удалён. Не использовать.

---

## КОРНЕВОЙ INSTALL/ — 2 ФАЙЛА

Отдельный слой от патчей ядра. Правки в установщике самой CMS.

### 1. `install/setup.info.php`
- **Правка A:** `(int)($params['shareparams'] ?? 0)` — строка ~168
- **Правка B:** `(int)($params['shareparams'] ?? 0) || !empty($params['dependencies'])` — строка ~173
- **Причина:** warning «Undefined array key `shareparams`»
- **CHANGELOG:** 16.1

### 2. `install/instprocessor.php`
- **Правка:** `$moduleChunk[0] ?? ''` (и 1, 3, 4) в блоке Install Chunks, строка ~471
- **Причина:** deprecated «Passing null to parameter #2» в PHP 8.5
- **CHANGELOG:** 16.2

**Важно:** патчить **и** `install/`, **и** `assets/modules/store/installer/` — это разные файлы, но с одинаковыми багами.

---

## SEO-ПАКЕТ seoVolo — собственная разработка

**Не зависит от ядра CE.** Не сломается при обновлении.

### Дескрипторы (в дистрибутиве)

**11 TV в `install/assets/tvs/`:**

| TV | Версия | Примечание |
|---|---|---|
| `seo_title.tpl` | 1.0.0 | — |
| `seo_description.tpl` | 1.0.0 | — |
| `seo_keywords.tpl` | 1.0.0 | — |
| `seo_og_image.tpl` | 1.0.0 | — |
| `seo_canonical.tpl` | 1.0.0 | — |
| `seo_noindex.tpl` | 1.0.0 | — |
| `seo_schema_type.tpl` | **1.0.7** | 40 типов Schema.org |
| `seo_hreflang.tpl` | 1.0.0 | — |
| `sitemap_priority.tpl` | **1.0.1** | `0.0\|\|0.1\|\|...\|\|1.0` |
| `sitemap_changefreq.tpl` | **1.0.1** | `always\|\|hourly\|\|...\|\|never` |
| `sitemap_exclude.tpl` | 1.0.0 | ❌ не работает с DLSitemap |

**Сниппет:** `install/assets/snippets/seoVolo.tpl` (v1.0.7)  
**Плагин:** `install/assets/plugins/seoVolo.tpl` — события `OnManagerPageInit,OnTempFormSave`  
**Модуль:** `install/assets/modules/seoVolo.tpl`

**Чанки:**
- `install/assets/chunks/head.tpl` — v2.0.0 (вызов `[!seoVolo!]`)
- `install/assets/chunks/mm_rules.tpl` — v2.0.0 (вкладка SEO + 11 TV)

### Код (в assets/)

- `assets/snippets/seoVolo/snippet.seoVolo.php` — **v1.0.7**
- `assets/plugins/seoVolo/plugin.seoVolo.php`
- `assets/modules/seoVolo/core.php` + `lang/ru.php`, `en.php`, `de.php`

### Версия 1.0.7 — что нового (12.10.2026)

- **Hreflang работает.** Через TV `relation` от evoBabel.
- **Язык страницы — динамически** из alias корневой папки языка.
- **`og:locale` и `inLanguage`** — из alias. Исключения: `en`, `ja`, `zh`, `ar`, `he`, `ko`.
- **Title separator** — с пробелами.
- **Breadcrumbs** — без языковых контейнеров.
- **`og:type`** — 40 маппингов.

### ТЕХДОЛГ SEO-пакета

1. **Индикатор длины description** — отложен.
2. **`sitemap_exclude`** — не работает с DLSitemap.
3. **Schema.org Product** — без `seo_sku`, `seo_price`.
4. **Хардкод регионов в `seoVolo_getLocale`** — для 6 языков.
5. **`docs/seoVolo-guide.md`** — устарел, переписать.

**CHANGELOG:** раздел 15.

---

## DLSitemap и DLSiblings — справочники

**Не патчи.** Собственная документация.

- **`docs/dlsitemap-guide.md`** — v1.0.0 от 11.10.2026.
- **`docs/dlsiblings-guide.md`** — v1.0.0 от 13.10.2026.

---

## ВНЕШНИЕ ИСТОЧНИКИ — что НЕ трогаем

- RSS-лента релизов CE: `github.com/evocms-community/evolution/releases.atom`
- RSS-лента безопасности: `github.com/extras-evolution/security-fix/releases.atom`
- Официальный сайт: `evo.im` — избегаем упоминаний в коде
- `github.com/evolution-cms` — старая ветка, не используем

---

## ЧЕК-ЛИСТ ПРИ МЕРЖЕ АПСТРИМА

### ФАЗА 0 — ПОДГОТОВКА (до любых действий)

- [ ] 1. Убедиться, что релиз/фикс действительно нужен (security — да, остальное — по ситуации)
- [ ] 2. Прочитать Release Notes целиком
- [ ] 3. Проверить, требует ли новый релиз другой версии PHP
- [ ] 4. `git status` в рабочей копии — всё закоммичено
- [ ] 5. Сделать snapshot Laragon (Menu → Snapshot → New)
- [ ] 6. Дамп БД: `mysqldump -u root vol > vol_backup_YYYYMMDD.sql`
- [ ] 7. Скопировать всю папку `vol.test` в `vol.test.bak`
- [ ] 8. `git fetch upstream`
- [ ] 9. Открыть diff: `github.com/evocms-community/evolution/compare/1.4.37...X.Y.Z`
- [ ] 10. Сверить список изменённых файлов с этим манифестом

### ФАЗА 1 — МЕРЖ

- [ ] 11. `git checkout -b merge/upstream-YYYY-MM`
- [ ] 12. `git merge upstream/1.4.x` (или другой тег)
- [ ] 13. Для каждого конфликта — сверить с реестром выше, разрешить вручную
- [ ] 14. Если апстрим обновил CodeMirror или ManagerManager — заново наложить наши правки (пункты 10–13)
- [ ] 15. Если апстрим обновил `install/setup.info.php` или `install/instprocessor.php` — заново наложить правки
- [ ] 16. Проверить, что `install/assets/modules/store.tpl` не перезаписан
- [ ] 17. Проверить, что `assets/` не затронут апстримом (там наши пакеты)
- [ ] 17a. ВАЖНО: при мёрже ZIP апстрима — НЕ копировать `assets/` вручную.
- [ ] 17b. НЕ копировать `install/` вручную — там наш `store.tpl`.
- [ ] 17c. НЕ копировать `docs/`, `packages/`, `CONTEXT.md`, `CHANGELOG*.md`, `patches-manifest.md`.
- [ ] 17d. Проверить, что SEO-пакет не затронут.

### ФАЗА 2 — ТЕСТИРОВАНИЕ

**Админка — ядро:**
- [ ] 18. Вход в админку
- [ ] 19. Dashboard без ошибок
- [ ] 20. Сохранение документа
- [ ] 21. Сохранение шаблона
- [ ] 22. Сохранение TV
- [ ] 23. Сохранение чанка
- [ ] 24. Сохранение сниппета
- [ ] 25. Сохранение конфигурации
- [ ] 26. Logout → Login
- [ ] 27. Смена пароля
- [ ] 28. Загрузка изображения
- [ ] 29. Загрузка файла
- [ ] 30. Менеджер файлов

**CSRF-защита:**
- [ ] 31. Форма без токена → 403
- [ ] 32. Форма с токеном → 200
- [ ] 33. AJAX с X-CSRF-TOKEN → 200
- [ ] 34. CSRF meta-тег присутствует
- [ ] 35. CSRF-JS блок работает
- [ ] 35a. SimpleGallery — вкладка открывается, AJAX работает

**Наши пакеты (по одному):**
- [ ] 36. VoloLution Store → каталог грузится
- [ ] 37. Store → установка пакета (MonthDate)
- [ ] 38. Store → переустановка
- [ ] 39. Store → установка из локального ZIP
- [ ] 40. MultiTV
- [ ] 41. FormResults
- [ ] 42. SimpleTube
- [ ] 43. evoSearch
- [ ] 44. editDocs
- [ ] 45. MultiCategories
- [ ] 46. DocLister
- [ ] 47. FormLister — **проверить отправку формы с пустым email** (13.10.2026)
- [ ] 48. SimpleGallery
- [ ] 49. EvoBabel
- [ ] 50. evoFileManagerDialog
- [ ] 51. FirstChildRedirect
- [ ] 52. WebP Converter
- [ ] 53. phpThumb
- [ ] 54. DLSiblings
- [ ] 55. MonthDate

**SEO-пакет seoVolo:**
- [ ] 55a. Вкладка SEO в форме документа
- [ ] 55b. Все 11 TV
- [ ] 55c. Title генерируется
- [ ] 55d. Description с обрезкой
- [ ] 55e. Canonical
- [ ] 55f. Open Graph
- [ ] 55g. Twitter Cards
- [ ] 55h. Schema.org JSON-LD
- [ ] 55i. `robots` не выводится при `index, follow`
- [ ] 55j. Модуль `seoVolo Settings` работает
- [ ] 55k. Настройки `seo_*` созданы автоматически
- [ ] 55l. TV `sitemap_priority` и `sitemap_changefreq` — чистые значения
- [ ] 55m. `sitemap.xml` — без самого себя и контейнеров
- [ ] 55n. Hreflang выводится
- [ ] 55o. `og:locale` и `inLanguage` корректны

**Кэш:**
- [ ] 55p. `cache_type = 1` (13.10.2026)
- [ ] 55q. `cache_snippets`, `cache_plugins`, `cache_chunks` — все `1`

**Фронтенд сайта:**
- [ ] 56. Главная открывается
- [ ] 57. Внутренняя страница открывается
- [ ] 58. Меню работает
- [ ] 59. Формы работают
- [ ] 60. Изображения грузятся
- [ ] 61. Кэш сбрасывается

**Логи и метрики:**
- [ ] 62. PHP error_log — пусто
- [ ] 63. Event log — нет ошибок
- [ ] 64. MySQL — все таблицы: `SHOW TABLES LIKE 'gyl8_%';`
- [ ] 65. Версия ядра в админке

### ФАЗА 3 — ФИКСАЦИЯ

- [ ] 66. Обновить `CHANGELOG.md` (соответствующие части)
- [ ] 67. Обновить `patches-manifest.md`
- [ ] 68. Обновить `CONTEXT-*.md` (соответствующие части)
- [ ] 69. `git checkout main`
- [ ] 70. `git merge merge/upstream-YYYY-MM`
- [ ] 71. `git push`
- [ ] 72. `git tag v1.4.37-vololution-N`
- [ ] 73. `git push --tags`
- [ ] 74. Удалить `vol.test.bak` через неделю
- [ ] 75. Удалить дамп БД через месяц

### ФАЗА 4 — ОТКАТ (если что-то сломалось)

- [ ] 1. Остановить работу
- [ ] 2. Посмотреть PHP error_log
- [ ] 3. Если ошибка в 1–2 файлах: `git checkout HEAD~1 -- manager/includes/...`
- [ ] 4. Если ошибок много: `git checkout main` + `git branch -D merge/upstream-YYYY-MM`, восстановить БД/файлы из бэкапа
- [ ] 5. Проверить сайт
- [ ] 6. Проверить админку
- [ ] 7. Записать в CHANGELOG
- [ ] 8. Разобраться с патчем, который конфликтует
- [ ] 9. Повторить попытку

### ВРЕМЯ НА МЕРЖ

- Фаза 0: ~30 минут
- Фаза 1: ~1–2 часа
- Фаза 2: ~1 час
- Фаза 3: ~15 минут

**Итого: минимум полдня.**
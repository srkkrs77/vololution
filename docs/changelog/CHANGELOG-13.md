## РАЗДЕЛ 15. SEO-ПАКЕТ seoVolo — СВОЯ РАЗРАБОТКА

**Статус:** ✅ выполнено 09.10.2026.  
**Не зависит от ядра CE.** Не сломается при обновлении апстрима.

### 15.1. Цель и результат

SEO-пакет для генерации мета-тегов из коробки. Устанавливается как часть дистрибутива (через `install/assets/`) и как отдельный пакет через Store.

**Что даёт:**

- `<title>`, `<meta description>`, `<meta robots>`, `<meta keywords>` (опционально)
- Canonical URL — авто или через TV
- Open Graph (7 тегов) + Twitter Cards (4–6 тегов)
- Schema.org JSON-LD: Organization, WebSite, WebPage, Article / NewsArticle / BlogPosting, Product, Service, FAQPage, BreadcrumbList
- Hreflang (включается настройкой, работает с EvoBabel)
- 11 TV на отдельной вкладке «SEO» в форме документа
- Отдельный модуль `seoVolo Settings` для управления настройками организации

### 15.2. Состав пакета

**11 TV (`install/assets/tvs/`):**

| TV | Версия |
|---|---|
| `seo_title.tpl` | 1.0.0 |
| `seo_description.tpl` | 1.0.0 |
| `seo_keywords.tpl` | 1.0.0 |
| `seo_og_image.tpl` | 1.0.0 |
| `seo_canonical.tpl` | 1.0.0 |
| `seo_noindex.tpl` | 1.0.0 |
| `seo_schema_type.tpl` | 1.0.0 |
| `seo_hreflang.tpl` | 1.0.0 |
| `sitemap_priority.tpl` | **1.0.1** (11.10.2026) |
| `sitemap_changefreq.tpl` | **1.0.1** (11.10.2026) |
| `sitemap_exclude.tpl` | 1.0.0 |

**Сниппет:**

- `install/assets/snippets/seoVolo.tpl`
- `assets/snippets/seoVolo/snippet.seoVolo.php`

**Плагин:**

- `install/assets/plugins/seoVolo.tpl` — события `OnManagerPageInit,OnTempFormSave`
- `assets/plugins/seoVolo/plugin.seoVolo.php`

**Модуль:**

- `install/assets/modules/seoVolo.tpl`
- `assets/modules/seoVolo/core.php`
- `assets/modules/seoVolo/lang/ru.php`, `en.php`, `de.php`

**Чанки (обновлены):**

- `install/assets/chunks/head.tpl` (v2.0.0) — вызов `[!seoVolo!]`
- `install/assets/chunks/mm_rules.tpl` (v2.0.0) — вкладка SEO + 11 TV

### 15.3. Файлы — создано / изменено

**Новые файлы (22):**

- 11 × `install/assets/tvs/*.tpl`
- `install/assets/snippets/seoVolo.tpl`
- `install/assets/plugins/seoVolo.tpl`
- `install/assets/modules/seoVolo.tpl`
- `assets/snippets/seoVolo/snippet.seoVolo.php`
- `assets/plugins/seoVolo/plugin.seoVolo.php`
- `assets/modules/seoVolo/core.php`
- `assets/modules/seoVolo/lang/ru.php`
- `assets/modules/seoVolo/lang/en.php`
- `assets/modules/seoVolo/lang/de.php`

**Изменённые файлы (2):**

- `install/assets/chunks/head.tpl` (v2.0.0)
- `install/assets/chunks/mm_rules.tpl` (v2.0.0)

**Обновлено 11.10.2026:**

- `install/assets/tvs/sitemap_priority.tpl` — v1.0.1 (правка `@input_options`)
- `install/assets/tvs/sitemap_changefreq.tpl` — v1.0.1 (правка `@input_options`)

### 15.4. Где подключается

**Frontend** — в `head.tpl`:

```
[!seoVolo!]
```

**Backend — форма документа:** вкладка SEO (создаётся ManagerManager через `mm_rules`).

**Backend — настройки:** Модули → `seoVolo Settings`.

### 15.5. Логика fallback

| Поле | Цепочка |
|---|---|
| **Title** | `seo_title` → `pagetitle — site_name` |
| **Description** | `seo_description` → `description` → `introtext` (160) → `content` (160) |
| **OG Image** | `seo_og_image` → `seo_org_logo` → `og_default_image` → первое изображение из контента |
| **Canonical** | `seo_canonical` → авто |
| **Robots** | выводится только если ≠ `index, follow` |

### 15.6. Системные настройки

Создаются автоматически плагином при первом входе в админку (`OnManagerPageInit`). Не требуют `setup.sql`. Всего 11 настроек:

- `seo_org_name`, `seo_org_logo`, `seo_org_phone`, `seo_org_email`, `seo_org_address`
- `og_default_image`
- `seo_title_separator`, `seo_keywords_enabled`, `seo_hreflang_enabled`
- `seo_twitter_site`, `seo_twitter_creator`

**Редактируются через модуль** Модули → `seoVolo Settings`.  
**Не показываются** в стандартной «Конфигурации» Evo (архитектурное ограничение CE 1.4 — вкладки конфигурации прописаны в PHP-файлах админки, не строятся из БД).

### 15.7. TV на вкладке SEO

Создаётся через `mm_rules.tpl`:

```php
mm_createTab('SEO', 'seo', '', '', '', '');
mm_moveFieldsToTab('seo_title,seo_description,seo_keywords,seo_og_image,seo_canonical,seo_noindex,seo_schema_type,seo_hreflang,sitemap_priority,sitemap_changefreq,sitemap_exclude', 'seo', '', '');
mm_widget_tags('seo_keywords', ',');
```

**Требуется:** включённый **ManagerManager**.

### 15.8. Плагин seoVolo

**События:** `OnManagerPageInit`, `OnTempFormSave`.

**Функции:**

- `seoVolo_ensureSettings()` — создаёт 11 настроек, если их нет.
- `seoVolo_attachTVsToTemplate()` — привязывает 11 TV к шаблону при сохранении.

### 15.9. Модуль seoVolo Settings

**Путь:** `assets/modules/seoVolo/core.php`  
**Дескриптор:** `install/assets/modules/seoVolo.tpl`

**Функции:**

- Форма редактирования 11 настроек.
- Кнопка **Browse** для выбора логотипа и OG-картинки через медиабраузер Evo.
- Чтение значений **напрямую из БД** (не из `$modx->config`) — обход кэша.
- После сохранения — сброс `assets/cache/siteCache.idx.php`.
- Мультиязычность: `ru`, `en`, `de` через `lang/`.

### 15.10. ОТЛОЖЕНО: индикатор длины description

Попытки через плагин (`OnDocFormPrerender`, `OnDocFormRender`, `OnSiteSettingsRender`) — **не сработали**.

**Правильный путь** — виджет TV (Evo `@output_widget`). Отложено.

Файлы `assets/plugins/seoVolo/css/seo.css`, `js/seo.js`, `lang/{ru,en}.php` — готовы, ждут подключения.

### 15.11. Техдолг SEO-пакета

**Обновлено 11.10.2026** (уточнение по sitemap).

1. **Индикатор длины description** — отложен (нужен виджет TV).

2. **`sitemap_priority` и `sitemap_changefreq` — ✅ РАБОТАЮТ** через DLSitemap из коробки.
   - DLSitemap по умолчанию читает параметры:
     - `priority` — `tv.sitemap_priority`
     - `changefreq` — `tv.sitemap_changefreq`
   - Если TV заполнены — перекрывают авто-расчёт по дате.
   - Если пусты — работает авто-расчёт:
     - ≤ 1 дня: `1.0` / `daily`
     - 2–7 дней: `0.75` / `weekly`
     - 8–30 дней: `0.50` / `weekly`
     - > 30 дней: `0.25` / `monthly`
   - Ничего дополнительно настраивать не нужно.
   - Подробно — `docs/dlsitemap-guide.md`.

3. **`sitemap_exclude` — ❌ НЕ РАБОТАЕТ.**
   - DLSitemap не читает эту TV (её нет в `snippet.DLSitemap.php`).
   - Варианты решения:
     - a. Параметр `&filters=` с JOIN (сложно, замедляет).
     - b. Правка `snippet.DLSitemap.php` (форк пакета).
     - c. Отложить.
   - **Отложено.** TV создана «на будущее».

4. **Schema.org Product** — без `seo_sku`, `seo_price` (TV не создаются).

5. **Hreflang** — требует активного EvoBabel.

### 15.12. Результаты тестирования (09.10.2026)

**Проверено:**

- ✅ Вкладка SEO в форме документа
- ✅ Все 11 TV
- ✅ Сниппет генерирует title / description / canonical / OG / Twitter / Schema.org
- ✅ `robots` не выводится при `index, follow`
- ✅ Модуль `seoVolo Settings` — форма, сохранение, Browse, языки ru/en/de
- ✅ Настройки создаются автоматически плагином
- ✅ PHP error_log — пусто (после патчей `install/`)

**Не проверено:**

- ⬜ Индикатор длины description
- ⬜ Hreflang на мультиязычном сайте
- ⬜ Schema.org Article / Product / FAQPage

### 15.13. Исправление TV sitemap (11.10.2026)

#### Проблема

В `sitemap.xml` попадал мусор:

```xml
<changefreq>always (every access)</changefreq>
<priority>1.0 (home)</priority>
<priority>0.5 (regular)</priority>
```

Стандарт `sitemaps.org` допускает только чистые значения:
- `changefreq`: `always`, `hourly`, `daily`, `weekly`, `monthly`, `yearly`, `never`
- `priority`: числа от `0.0` до `1.0`

Google в Search Console такие записи отметит как ошибки.

#### Причина

В TV `sitemap_changefreq` и `sitemap_priority` был **неправильный порядок** в `@input_options`:

**Было:**
```
always==always (every access)||hourly==hourly||...
1.0==1.0 (home)||0.5==0.5 (regular)||...
```

В Evo 1.4 формат `@input_options` — **`label==value`** (сначала видно в выпадашке, потом сохраняется в БД). Было наоборот — в БД сохранялся label с описанием в скобках.

#### Решение

**1. Файлы TV переписаны** (`install/assets/tvs/`):

**`sitemap_changefreq.tpl` — было:**
```
@input_options always==always (every access)||hourly==hourly||daily==daily||weekly==weekly||monthly==monthly (default)||yearly==yearly||never==never
```

**Стало:**
```
@input_options always||hourly||daily||weekly||monthly||yearly||never
```

**`sitemap_priority.tpl` — было:**
```
@input_options 1.0==1.0 (home)||0.5==0.5 (regular)||...
```

**Стало:**
```
@input_options 0.0||0.1||0.2||0.3||0.4||0.5||0.6||0.7||0.8||0.9||1.0
```

**Версия TV: 1.0.0 → 1.0.1.**

**2. В админке живого сайта** — обновлено поле «Элементы» (Elements) у TV.

**3. Почистить старые значения в БД** — SQL:

```sql
UPDATE eh2v_site_tmplvar_contentvalues
SET value = TRIM(SUBSTRING_INDEX(value, ' (', 1))
WHERE tmplvarid IN (
    SELECT id FROM eh2v_site_tmplvars
    WHERE name IN ('sitemap_changefreq', 'sitemap_priority')
);
```

`eh2v_` — префикс прода `studio-milstein.de`. На `vol.test` — `gyl8_`.

**4. Очистить кэш Evo** — Админка → Сайт → Очистить кэш.

#### Итог

`sitemap.xml` генерируется с чистыми значениями:

```xml
<changefreq>monthly</changefreq>
<priority>0.5</priority>
```

#### Урок

**Формат `@input_options` в Evo 1.4 — `label==value`.** Не наоборот. Если хочешь без label — просто значения через `||`. С описанием — `видно==сохраняется`.

---

## РАЗДЕЛ 16. ПАТЧИ КОРНЕВОГО INSTALL/

Правки в установщике самой CMS `install/` — отдельный слой от патчей ядра. Обнаружены при установке дистрибутива на чистую БД (09.10.2026).

### 16.1. `install/setup.info.php`

**Строка ~168 (блок модулей):**

```diff
- (int)$params['shareparams'],
+ (int)($params['shareparams'] ?? 0),
```

**Строка ~173 (проверка зависимостей):**

```diff
- if ((int)$params['shareparams'] || !empty($params['dependencies'])) {
+ if ((int)($params['shareparams'] ?? 0) || !empty($params['dependencies'])) {
```

**Причина:** warning «Undefined array key `shareparams`» при установке модулей без параметра `@shareparams`.

### 16.2. `install/instprocessor.php`

**Строка ~471 (блок Install Chunks):**

```diff
- $name = mysqli_real_escape_string($conn, $moduleChunk[0]);
- $desc = mysqli_real_escape_string($conn, $moduleChunk[1]);
- $category = mysqli_real_escape_string($conn, $moduleChunk[3]);
- $overwrite = mysqli_real_escape_string($conn, $moduleChunk[4]);
+ $name = mysqli_real_escape_string($conn, $moduleChunk[0] ?? '');
+ $desc = mysqli_real_escape_string($conn, $moduleChunk[1] ?? '');
+ $category = mysqli_real_escape_string($conn, $moduleChunk[3] ?? '');
+ $overwrite = mysqli_real_escape_string($conn, $moduleChunk[4] ?? '');
```

**Причина:** deprecated warning «Passing null to parameter #2 ($string)» в PHP 8.5.

### 16.3. Отличие от патчей Store

| Слой | Папка | Назначение |
|---|---|---|
| **Корневой install/** | `install/` | Установка самой CMS |
| **Store installer** | `assets/modules/store/installer/` | Установка пакетов через Store |

Оба инсталлера происходят от одного предка (MODX), отсюда схожие баги. **Патчить надо оба комплекта.**

---

## РАЗДЕЛ 17. СЛЕДУЮЩИЕ ШАГИ — АКТУАЛЬНО

**Обновлено 11.10.2026.**

1. **Форк на GitHub** — установка GitHub Desktop, создание форка `evocms-community/evolution` → `srkkrs77/vololution`, клонирование. См. `CHANGELOG-14`.
2. **Инфраструктура форка** — `composer.json`, `src/`, `.gitignore`. См. `CHANGELOG-14`.
3. **SchemaGuard** — плагин-сторож для таблиц сторонних пакетов. См. `CHANGELOG-14`.
4. **CSRF-техдолг** — раздел 9.1, задачи A, B, C (D закрыта 11.10.2026).
5. **Живой поиск (AJAX autocomplete)** — Этап 1.2 roadmap.
6. **Индикатор длины description** — через виджет TV.
7. **Синхронизация документации** — `docs/seoVolo-guide.md` переписать под модуль.
8. **Проверка пакетов через Store** — MultiTV, editDocs, FormResults.
9. **`sitemap_exclude`** — связать с DLSitemap (или через `&filters=`, или форк DLSitemap).

---

## РАЗДЕЛ 18. ФОРК И НОВАЯ АРХИТЕКТУРА

**Дата решения:** 10.10.2026. См. `CHANGELOG-14`.

---

## РАЗДЕЛ 19. ИСПРАВЛЕНИЕ CSRF-JS В HEADER.INC.PHP

**Дата:** 11.10.2026. См. `CHANGELOG-14`.

**Конец части 13.**
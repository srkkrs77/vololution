# CONTEXT-08.md — SEO-пакет seoVolo + кастомные TV

**Часть 8 из 19.** Читать после `CONTEXT.md`, `CONTEXT-01.md` — `CONTEXT-07.md`.

**Обновлено:** 12.10.2026 (seoVolo 1.0.7, hreflang, языки, og:type).

---

## 8. ЧТО СДЕЛАНО: SEO-ПАКЕТ seoVolo

**Статус:** ✅ выполнено 09.10.2026, обновлено 12.10.2026.  
**CHANGELOG:** раздел 15.  
**patches-manifest.md:** раздел «SEO-ПАКЕТ seoVolo».  
**Не зависит от ядра CE.** Не сломается при обновлении апстрима.

### 8.1. Что даёт

SEO-пакет для генерации мета-тегов из коробки.

- `<title>` с fallback
- `<meta description>` с fallback и обрезкой до 160
- `<link rel="canonical">` — авто или через TV
- Open Graph — 7+ тегов
- Twitter Cards — 4–6 тегов
- Schema.org JSON-LD: Organization, WebSite, WebPage, Article / NewsArticle / BlogPosting, Product, Service, FAQPage, BreadcrumbList
- Hreflang (через EvoBabel, отключаемый)
- `<meta name="robots">` — при `noindex`
- 11 TV на отдельной вкладке «SEO» в форме документа
- Отдельный модуль `seoVolo Settings` для управления настройками организации

### 8.2. Состав пакета

**11 TV (`install/assets/tvs/`):**

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
| `sitemap_priority.tpl` | **1.0.1** | порядок label/value |
| `sitemap_changefreq.tpl` | **1.0.1** | порядок label/value |
| `sitemap_exclude.tpl` | 1.0.0 | ❌ не работает с DLSitemap |

**Сниппет:**
- `install/assets/snippets/seoVolo.tpl` (v1.0.7)
- `assets/snippets/seoVolo/snippet.seoVolo.php` (v1.0.7)

**Плагин:**
- `install/assets/plugins/seoVolo.tpl` — события `OnManagerPageInit,OnTempFormSave`
- `assets/plugins/seoVolo/plugin.seoVolo.php`

**Модуль:**
- `install/assets/modules/seoVolo.tpl`
- `assets/modules/seoVolo/core.php`
- `assets/modules/seoVolo/lang/ru.php`, `en.php`, `de.php`

**Чанки:**
- `install/assets/chunks/head.tpl` (v2.0.0) — вызов `[!seoVolo!]`
- `install/assets/chunks/mm_rules.tpl` (v2.0.0) — вкладка SEO + 11 TV

### 8.3. Где подключается

- **Frontend:** `[!seoVolo!]` в `head.tpl` (некэшируемый вызов).
- **Backend (форма документа):** вкладка SEO через `mm_rules.tpl`. Требуется **активный ManagerManager**.
- **Backend (настройки):** Модули → `seoVolo Settings`.

### 8.4. Логика fallback

| Поле | Цепочка |
|---|---|
| **Title** | `seo_title` → `pagetitle — site_name` |
| **Description** | `seo_description` → `description` → `introtext` (160) → `content` (160) |
| **OG Image** | `seo_og_image` → `seo_org_logo` → `og_default_image` → первое изображение из контента |
| **Canonical** | `seo_canonical` → авто |
| **Robots** | не выводится при `index, follow` |

### 8.5. Системные настройки (11)

Создаются **автоматически плагином** при первом входе в админку (`OnManagerPageInit`). `setup.sql` не нужен.

- `seo_org_name`, `seo_org_logo`, `seo_org_phone`, `seo_org_email`, `seo_org_address`
- `og_default_image`
- `seo_title_separator`, `seo_keywords_enabled`, `seo_hreflang_enabled`
- `seo_twitter_site`, `seo_twitter_creator`

**Редактируются через модуль** Модули → `seoVolo Settings`. Не показываются в стандартной «Конфигурации» Evo (архитектурное ограничение CE 1.4).

### 8.6. TV на вкладке SEO (mm_rules.tpl)

```php
mm_createTab('SEO', 'seo', '', '', '', '');
mm_moveFieldsToTab('seo_title,seo_description,seo_keywords,seo_og_image,seo_canonical,seo_noindex,seo_schema_type,seo_hreflang,sitemap_priority,sitemap_changefreq,sitemap_exclude', 'seo', '', '');
mm_widget_tags('seo_keywords', ',');
8.7. Плагин seoVolo
События: OnManagerPageInit, OnTempFormSave.

Функции:

seoVolo_ensureSettings() — создаёт 11 настроек, если их нет.

seoVolo_attachTVsToTemplate() — привязывает 11 TV к новым шаблонам при сохранении.

8.8. Модуль seoVolo Settings
Путь: assets/modules/seoVolo/core.php
Дескриптор: install/assets/modules/seoVolo.tpl

Функции:

Форма редактирования 11 настроек.

Кнопка Browse для выбора логотипа и OG-картинки через медиабраузер Evo.

Чтение значений напрямую из БД (не из $modx->config) — обход кэша.

После сохранения — сброс assets/cache/siteCache.idx.php.

Мультиязычность: ru, en, de через lang/.

8.9. Отложено
Индикатор длины description. Попытки через плагин (OnDocFormPrerender, OnDocFormRender, OnSiteSettingsRender) — не сработали. Правильный путь — виджет TV (Evo @output_widget). Отложено.

Файлы assets/plugins/seoVolo/css/seo.css, js/seo.js, lang/{ru,en}.php — готовы, ждут подключения.

8.10. Техдолг SEO-пакета (обновлено 12.10.2026)
Индикатор длины description — отложен (нужен виджет TV).

sitemap_priority и sitemap_changefreq — ✅ РАБОТАЮТ через DLSitemap из коробки. Ничего настраивать не нужно. См. docs/dlsitemap-guide.md.

sitemap_exclude — ❌ НЕ РАБОТАЕТ. DLSitemap не читает эту TV из коробки. Отложено (нужна правка DLSitemap или параметр &filters).

Schema.org Product — без seo_sku, seo_price (TV не создаются).

Хардкод регионов в seoVolo_getLocale — для en, ja, zh, ar, he, ko. Для остальных языков locale строится автоматически.

8.11. Результаты тестирования (09.10.2026 + 12.10.2026)
Проверено:

✅ Вкладка SEO в форме документа

✅ Все 11 TV

✅ Сниппет генерирует title / description / canonical / OG / Twitter / Schema.org

✅ robots не выводится при index, follow

✅ Модуль seoVolo Settings — форма, сохранение, Browse, ru/en/de

✅ Настройки создаются автоматически плагином

✅ PHP error_log — пусто

✅ Hreflang работает на мультиязычном сайте (12.10.2026)

✅ og:locale и inLanguage — корректны для каждого языка (12.10.2026)

✅ Breadcrumbs — без языковых контейнеров (12.10.2026)

Не проверено:

⬜ Индикатор длины description

⬜ Schema.org Article / Product / FAQPage (с реальными данными)

⬜ Description с реальным контентом (сайт пустой)

8.12. Документация
docs/seoVolo-guide.md — устарел. Написан под старую концепцию «вкладка в Конфигурации». Нужно переписать под модуль. Задача в roadmap.

8.13. Исправление TV sitemap (11.10.2026)
Проблема: в sitemap.xml попадал мусор:

xml
<changefreq>always (every access)</changefreq>
<priority>1.0 (home)</priority>
Причина: в TV sitemap_changefreq и sitemap_priority был неправильный порядок в @input_options.

Было: always==always (every access).
Стало: always (every access)==always или просто always.

В Evo 1.4 формат @input_options — label==value (сначала видно, потом сохраняется).

Решение:

Файлы TV переписаны (v1.0.0 → v1.0.1). Формат — без подписей: always||hourly||....

В админке обновлено поле «Элементы» (Elements).

Обрезка старых значений в БД через SQL.

Очистка кэша.

Итог: sitemap.xml генерируется с чистыми значениями.

8.14. Обновление сниппета до 1.0.7 (12.10.2026)
Что изменилось:

Hreflang работает. Через TV relation от evoBabel. Раньше вызывалась несуществующая evoBabel_getTranslations — hreflang никогда не выводился.

Язык страницы — динамически из alias корневой папки языка (de, en, ru). Не из языка админки. Раньше og:locale на английской странице был ru_RU.

og:locale и inLanguage — строятся из alias по формуле lang_REGION. Исключения для en (→ en_US), ja (→ ja_JP), zh, ar, he, ko.

Title separator — принудительно с пробелами. Было home—Studio Milstein, стало home — Studio Milstein.

Breadcrumbs — не включают языковые контейнеры de, en, ru.

seoVolo_ogType — 40 маппингов Schema.org → Open Graph. Было 7.

TV seo_schema_type — 40 значений вместо 8.

Функции, добавленные в 1.0.7:

seoVolo_getPageLang($modx, $docId) — определяет язык по alias корня.

seoVolo_getLocale($langAlias) — строит og:locale и inLanguage.

8.15. Логика hreflang (12.10.2026)
Как работает:

Читает TV relation от evoBabel у текущего документа.

Формат значения: de:5||en:12 (alias языка : ID документа).

Парсит, получает URL каждого документа, выводит <link rel="alternate" hreflang="...">.

Плюс x-default — на канонический URL текущей страницы.

Fallback: если у документа нет своей TV relation — пробует родителя.

Включается: настройкой seo_hreflang_enabled в модуле seoVolo Settings.

Отключается на документе: TV seo_hreflang = exclude.

8.16. Логика языка страницы (12.10.2026)
Алгоритм:

Идём вверх по дереву документа до корня.

Читаем alias корневой папки (de, en, ru).

Строим og:locale и inLanguage.

Fallback: если alias пуст — язык менеджера, потом en.

Динамика: при добавлении нового языка (например, fr) — работает автоматически. Правок в коде не нужно.

Исключение: регион для некоторых языков. Список $specialRegions в функции seoVolo_getLocale. Сейчас: en → US, ja → JP, zh → CN, ar → SA, he → IL, ko → KR.

8.17. Кастомная TV image_alt_caption (11.10.2026)
Своя разработка. Не зависит от ядра CE.

Что это
TV с тремя полями в одной:

Изображение — путь + кнопка Browse + превью.

Alt — текстовое поле.

Подпись (caption) — текстовое поле.

Значение хранится как JSON в site_tmplvar_contentvalues.

Механизм
custom input + @INCLUDE — штатный способ Evo 1.4 для кастомных TV.

Файл: assets/tvs/image_alt_caption/image_alt_caption.php

TV в админке:

Тип ввода: Custom Input

Возможные значения: @INCLUDE/assets/tvs/image_alt_caption/image_alt_caption.php

Сниппет вывода
getImage — читает JSON, выводит <figure><img loading="lazy"><figcaption>...</figcaption></figure>.

Вызов:

text
[[getImage? &tv=`image_studio`]]
Ограничения
Кэш браузера. Форма редактирования кэшируется. Решено патчем header.inc.php — no-cache мета-теги (см. CHANGELOG-02.md, раздел 2.8).

evoBabel. При синхронизации языков TV не копируется. Нужно добавлять ID TV в настройку evoBabel «ids TV для синхронизации».

Копирование документа. При дублировании связь значений может не подтянуться.

В перспективе
Оформить как отдельный пакет:

Дескриптор install/assets/tvs/image_alt_caption.tpl

Сниппет install/assets/snippets/getImage.tpl

README

Добавить в Store как 17-й пакет.

CHANGELOG: раздел 20.

Конец части 8.
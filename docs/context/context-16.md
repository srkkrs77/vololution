# CONTEXT-16.md — Техмоменты: SEO, авторы пакетов, CE-экосистема

**Часть 16 из 19.** Читать после `CONTEXT.md`, `CONTEXT-01.md` — `CONTEXT-15.md`.

**Обновлено:** 12.10.2026 (seoVolo 1.0.7, hreflang, og:type).

---

## 16. КЛЮЧЕВЫЕ ТЕХНИЧЕСКИЕ МОМЕНТЫ (часть 2)

### 16.1. SEO-пакет — ключевые моменты

#### 16.1.1. Подключение

- **`head.tpl`** содержит `[!seoVolo!]` — **некэшируемый** вызов.
- **Вкладка SEO** в форме документа — через `mm_rules.tpl` + **активный ManagerManager**.
- **Настройки `seo_*`** — создаются плагином `OnManagerPageInit`, редактируются через модуль `seoVolo Settings`.

#### 16.1.2. Fallback-логика

| Поле | Цепочка |
|---|---|
| **Title** | `seo_title` → `pagetitle — site_name` |
| **Description** | `seo_description` → `description` → `introtext` (160) → `content` (160) |
| **OG Image** | `seo_og_image` → `seo_org_logo` → `og_default_image` → первое изображение из контента |
| **Canonical** | `seo_canonical` → авто |
| **Robots** | не выводится при `index, follow` |

#### 16.1.3. Системные настройки (11)

Создаются **автоматически** плагином при первом входе в админку (`OnManagerPageInit`). `setup.sql` не нужен.

**11 настроек:**
`seo_org_name`, `seo_org_logo`, `seo_org_phone`, `seo_org_email`, `seo_org_address`, `og_default_image`, `seo_title_separator`, `seo_keywords_enabled`, `seo_hreflang_enabled`, `seo_twitter_site`, `seo_twitter_creator`.

#### 16.1.4. Модуль seoVolo Settings

- Отдельный пункт меню: **Модули → seoVolo Settings**.
- Форма редактирования 11 настроек.
- **Кнопка Browse** для выбора логотипа и OG-картинки через медиабраузер Evo.
- **Чтение значений напрямую из БД** (не из `$modx->config`) — обход кэша.
- После сохранения — **сброс `assets/cache/siteCache.idx.php`**.
- Мультиязычность: `ru`, `en`, `de` через `lang/`.

#### 16.1.5. TV на вкладке SEO

Создаётся через `mm_rules.tpl`:

```php
mm_createTab('SEO', 'seo', '', '', '', '');
mm_moveFieldsToTab('seo_title,seo_description,seo_keywords,seo_og_image,seo_canonical,seo_noindex,seo_schema_type,seo_hreflang,sitemap_priority,sitemap_changefreq,sitemap_exclude', 'seo', '', '');
mm_widget_tags('seo_keywords', ',');
Требуется: активный ManagerManager.

16.1.6. Что генерирует сниппет (v1.0.7)
<title> с fallback.

<meta description> с fallback и обрезкой до 160.

<link rel="canonical"> — авто или через TV.

<meta name="robots"> — при noindex.

Open Graph — 7+ тегов.

Twitter Cards — 4–6 тегов.

Schema.org JSON-LD — Organization, WebSite, WebPage, Article / NewsArticle / BlogPosting, Product, Service, FAQPage, BreadcrumbList.

Hreflang — через TV relation от evoBabel, отключаемый.

16.1.7. Технология определения языка страницы (12.10.2026)
Функция seoVolo_getPageLang($modx, $docId):

Идёт вверх по дереву документа через getParentIds.

Берёт последний (корневой) родитель.

Читает его alias (de, en, ru).

Возвращает код языка.

Fallback: если alias пуст — язык менеджера, потом en.

Используется для: og:locale и Schema inLanguage.

Функция seoVolo_getLocale($langAlias):

Строит og:locale (например, de_DE) и inLanguage (например, de-DE).

Формула: lang_REGION, где REGION = strtoupper(lang).

Исключения для нестандартных: en → US, ja → JP, zh → CN, ar → SA, he → IL, ko → KR.

Динамика: при добавлении нового языка с alias = код языка — работает автоматически.

16.1.8. Логика hreflang (12.10.2026)
Функция seoVolo_getHreflang($modx, $docId):

Читает TV relation у текущего документа.

Формат: de:5||en:12 — alias языка : ID документа.

Fallback на родителя, если у документа нет своей TV.

Для каждого языка строит URL через makeUrl.

Возвращает массив ['lang' => ..., 'url' => ...].

Включается: настройкой seo_hreflang_enabled (модуль seoVolo Settings).
Отключается на документе: TV seo_hreflang = exclude.

Вывод: <link rel="alternate" hreflang="XX" href="..."> + x-default на канонический URL.

16.1.9. Маппинг Schema.org → Open Graph
Функция seoVolo_ogType($schemaType) — 40 маппингов. Основные:

Schema.org	og:type
Article, NewsArticle, BlogPosting, Recipe, Event, HowTo, JobPosting	article
Product, Offer, AggregateOffer	product
Person, ProfilePage	profile
Book	book
VideoObject, Movie, TVSeries, TVEpisode	video.*
MusicRecording, MusicAlbum, MusicPlaylist, MusicGroup	music.*
WebPage, AboutPage, ContactPage, FAQPage и т.д.	website
16.1.10. Техдолг SEO-пакета
Индикатор длины description — отложен (нужен виджет TV).

sitemap_exclude — ❌ не работает с DLSitemap.

Schema.org Product — без seo_sku, seo_price.

Хардкод регионов в seoVolo_getLocale — для 6 языков. Остальные — автоматически.

docs/seoVolo-guide.md — устарел, нужно переписать под модуль.

16.1.11. Документация SEO
docs/seoVolo-guide.md — устарел. Написан под старую концепцию «вкладка в Конфигурации». Нужно переписать под модуль. Задача в roadmap.

16.2. Авторы пакетов — кому доверяем
16.2.1. Pathologic
GitHub: github.com/Pathologic

Пакеты: DocLister, FormLister, MultiCategories, editDocs (соавтор), SimpleGallery, SimpleTube, evoSearch, DLSiblings, MonthDate, phpThumb, WebP Converter.

Репутация: быстрая реакция на запросы, активная поддержка.

16.2.2. Grinyaha
GitHub: github.com/Grinyaha

Пакеты: editDocs.

Документация: editdocs.grishin.net

16.2.3. Кому НЕ доверяем (или не берём пакеты)
См. часть 14.9 — список пакетов, которые не берём (bLang, ClientSettings, Doc Manager, evoCollection, SimplePolls, ElementsInTree, templatesEdit3).

16.2.4. Другие авторы
DivanDesign (github.com/DivanDesign, code.divandesign.ru):

mm_ddMultipleFields — виджет ManagerManager для повторяющихся полей.

ddGetMultipleField — сниппет для вывода структурированных данных.

ddTools, ddTypograph — библиотеки.

Не установлены. Могут понадобиться при сложных структурах данных.

webber (web-ber12@yandex.ru):

evoBabel — мультиязычность. Установлен у нас.

16.3. CE-экосистема
16.3.1. Апстрим (ядро)
Репозиторий: github.com/evocms-community/evolution

Последний известный релиз: CE 1.4.37 — 13.11.2025

Статус веток:

CE 1.4.x — активно развивается сообществом.

Официальная 1.4 (evo.im) — только security fixes, заморожена.

16.3.2. Совместимость
Extras из официального репозитория совместимы с CE.

Пакеты работают без изменений.

16.3.3. Сообщество
Ресурс	Ссылка	Назначение
Telegram	@evo_cms	Оперативная помощь, обсуждения
Форум	community.evocms.ru	Долгие обсуждения, гайды
Русскоязычный ресурс	evocms.ru	Новости, документация
16.3.4. Мониторинг безопасности
Источники:

GitHub: github.com/extras-evolution/security-fix — официальные патчи безопасности.

Базы CVE: opencve.io, attackerkb.com.

Релизы CE: проверка новых версий в evocms-community/evolution.

Частота проверки: раз в месяц.

16.3.5. План реагирования на уязвимость
Проверить релизы CE — возможно, уже выпущено исправление.

Проверить коммиты в evocms-community/evolution — возможно, фикс без релиза.

Спросить в Telegram @evo_cms.

Мерж патча в форк — отдельная ветка, тест на vol.test.

Зафиксировать в CHANGELOG.md (раздел «Патчи безопасности»).

16.3.6. Наша позиция
Используем CE — сознательный выбор.

Ветка активно поддерживается.

Сообщество живое и отзывчивое.

PHP 8.5 поддерживается (частично + наши патчи).

Extras совместимы.

Мы — форк, но не отщепенцы. Мержим security-фиксы, участвуем в жизни сообщества.

16.3.7. Что НЕ используем
evo.im — не упоминаем в коде.

github.com/evolution-cms — старая ветка, не используем.

github.com/evolution-cms/evolution — не наш апстрим.

extras.evo.im — не используем (у нас свой Store).

extras.evocms.ru — не используем.

16.4. Справочник DLSitemap (11.10.2026)
Своя документация. Не патч.

Файл: docs/dlsitemap-guide.md

Что: полный справочник по параметрам, плейсхолдерам, рецептам, типичным ошибкам.

Версия: 1.0.0 от 11.10.2026.

Ключевые моменты:

DLSitemap по умолчанию читает TV sitemap_priority и sitemap_changefreq.

Рабочий вызов на проде:

text
[!DLSitemap?
    &parents=`0`
    &maxDepth=`10`
    &addWhereList=`c.alias NOT IN ('sitemap.xml','de','en')`
    &tvList=`sitemap_priority,sitemap_changefreq`
    &dateFormat=`Y-m-d`
    &tpl=`@CODE:<url><loc>[+url+]</loc><lastmod>[+date+]</lastmod><changefreq>[+update+]</changefreq><priority>[+priority+]</priority></url>`
!]
Исключены: сам sitemap.xml, контейнеры языков de и en.

Плейсхолдер changefreq — [+update+], не [+changefreq+].

Конец части 16.
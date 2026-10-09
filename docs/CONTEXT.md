# CONTEXT — VoloLution CMS

**Файл для передачи контекста в новый чат. Читать первым.**

---

## 1. О ПРОЕКТЕ

**VoloLution CMS** — сборка на базе Evolution CMS 1.4.37 (Community Edition).

Не форк. Мы не копировали ядро в свой репозиторий и не ведём свою ветку с релизами. Берём готовый релиз CE 1.4.37 из официального репозитория, накладываем патчи поверх, добавляем пакеты. Обновления ядра — вручную, по манифесту. Это как Ubuntu ← Debian, XAMPP ← Apache.

**Цель:** рабочая CMS под PHP 8.5 с безопасностью и SEO из коробки.

**Репозиторий:** `github.com/srkkrs77/vololution`  
**Тестовый сайт:** `vol.test`  
**Стек:** Windows + Laragon, PHP 8.5.10, MySQL 8.0.30, nginx.

---

## 2. СТИЛЬ ОБЩЕНИЯ

- Коротко. Без воды. Без «возможно / может быть».
- Готовый код или точная инструкция — не рассуждения.
- Формат: «Сейчас:» → «Надо:».
- Не повторять уже сказанное.
- Если не знаешь — скажи «не знаю», не выдумывай.
- **ВЫДАВАТЬ ФАЙЛЫ ЦЕЛИКОМ.** Не кусками. Не «замени строку Х на Y».
- Пользователь не программист. Объяснять куда класть файл, что нажимать.
- Если патч — давать **полный файл**, не diff.
- Не гонять по кругу. Если не работает — признать, сменить подход, а не подкручивать.

---

## 3. СТРУКТУРА ПРОЕКТА

### 3.1. Две копии

| Копия | Путь | Назначение |
|---|---|---|
| **Рабочая** | `C:\laragon\www\vol.test\` | Тестирование, эксперименты |
| **Эталон (дистрибутив)** | `D:\VoloLution-CMS-1.4.37\` | Готовый дистрибутив для развёртывания |

**ВАЖНО:** пользователь правит **в двух местах**. Когда даёшь файл — указывай путь в обоих или уточняй. Пути указывать от корневых папок `assets/`, `manager/`, `install/`. Если файл в самом верху дистрибутива (рядом с `assets/`, `manager/`, `install/`) — просто `/`.

### 3.2. Структура дистрибутива
D:\VoloLution-CMS-1.4.37
├── assets/
│ ├── modules/
│ │ ├── store/ ← Extras-Store
│ │ └── seoVolo/ ← SEO-модуль настроек
│ ├── plugins/
│ │ └── seoVolo/ ← SEO-плагин
│ └── snippets/
│ └── seoVolo/ ← SEO-сниппет
├── install/ ← папка установщика CMS
│ ├── setup.sql ← SQL ядра
│ ├── setup.info.php ← патчен
│ ├── instprocessor.php ← патчен
│ ├── config.inc.tpl ← CSRF-функции
│ └── assets/
│ ├── chunks/ ← head.tpl, mm_rules.tpl
│ ├── modules/ ← seoVolo.tpl, store.tpl
│ ├── plugins/ ← seoVolo.tpl
│ ├── snippets/ ← seoVolo.tpl
│ └── tvs/ ← 11 × seo_*.tpl
├── manager/ ← ядро (13 патчей)
└── CHANGELOG.md, CONTEXT.md, docs/

text

---

## 4. ДВА ИНСТАЛЛЕРА — НЕ ПУТАТЬ

| # | Где | Что ставит | Файлы патчей |
|---|---|---|---|
| 1 | `install/` (папка установщика CMS) | Саму CMS | `setup.info.php`, `instprocessor.php` |
| 2 | `assets/modules/store/installer/` | Пакеты через Store | `setup.info.php`, `instprocessor-fast.php` |

Оба — от одного предка (MODX). Схожие баги. Патчить **оба**.

---

## 5. ЧТО УЖЕ СДЕЛАНО

### 5.1. Ядро — 13 патчей

См. `CHANGELOG.md`, разделы 1–2. Основное:
- PHP 8.5-совместимость (`E_STRICT`, `??`, string offsets)
- CSRF-защита (токены, meta-тег, JS-перехват, SameSite cookie)

### 5.2. CSRF-защита

- `manager/includes/preload.functions.inc.php` — сессии + `csrf_token()`
- `install/config.inc.tpl` — CSRF-функции
- `manager/includes/accesscontrol.inc.php` — проверка POST + whitelist `[8, 67, 112, 118]`
- `manager/includes/header.inc.php` — meta-тег + JS-блок
- `manager/includes/config.inc.php` — debug-правка

### 5.3. 6 патченных пакетов

MultiTV, FormResults, SimpleTube, evoSearch, editDocs, MultiCategories. См. `CHANGELOG.md`, раздел 4.

### 5.4. 10 пакетов без правок

DocLister, FormLister, SimpleGallery, EvoBabel, evoFileManagerDialog, FirstChildRedirect, MonthDate, WebP Converter, DLSiblings, phpThumb.

### 5.5. Свой Extras-Store

- Каталог: `github.com/srkkrs77/vololution/packages/catalog.json`
- Прокси: `assets/modules/store/api.php`
- 16 пакетов, мультиязычные описания
- Кнопки «Установить» / «Переустановить» (без сравнения версий)
- **CHANGELOG:** раздел 14

### 5.6. SEO-пакет seoVolo

**Сниппет** `seoVolo`:
- Title, description, canonical, robots
- Open Graph, Twitter Cards
- Schema.org JSON-LD (Organization, WebSite, WebPage, Article, Product, Service, FAQPage, BreadcrumbList)
- Hreflang (через EvoBabel, отключаемый)
- Fallback-логика для всех полей

**11 TV** на вкладке SEO в форме документа (через ManagerManager):
`seo_title`, `seo_description`, `seo_keywords`, `seo_og_image`, `seo_canonical`, `seo_noindex`, `seo_schema_type`, `seo_hreflang`, `sitemap_priority`, `sitemap_changefreq`, `sitemap_exclude`

**Плагин** `seoVolo`:
- События: `OnManagerPageInit`, `OnTempFormSave`
- Автосоздание 11 системных настроек в `system_settings`
- Авто-привязка TV к новым шаблонам

**Модуль** `seoVolo Settings`:
- Отдельный пункт меню **Модули → seoVolo Settings**
- Форма для 11 настроек
- Кнопка **Browse** для лого и OG-картинки
- Мультиязычность: `ru`, `en`, `de`
- Читает из БД напрямую (обход кэша), сбрасывает кэш после сохранения

**Файлы:**
- `install/assets/tvs/*.tpl` (11)
- `install/assets/snippets/seoVolo.tpl`
- `install/assets/plugins/seoVolo.tpl`
- `install/assets/modules/seoVolo.tpl`
- `install/assets/chunks/head.tpl` (v2.0.0)
- `install/assets/chunks/mm_rules.tpl` (v2.0.0)
- `assets/snippets/seoVolo/snippet.seoVolo.php`
- `assets/plugins/seoVolo/plugin.seoVolo.php`
- `assets/modules/seoVolo/core.php`
- `assets/modules/seoVolo/lang/{ru,en,de}.php`

**CHANGELOG:** раздел 15

### 5.7. Патчи корневого install/

- `install/setup.info.php` — 2 правки `?? 0` для shareparams
- `install/instprocessor.php` — 4 правки `?? ''` для chunks

**CHANGELOG:** раздел 16

---

## 6. ЧТО В РАБОТЕ

**Ничего критичного.** Основные компоненты работают.

**Отложено:**
- **Индикатор длины description** в TV `seo_description`. Плагинный подход не сработал. Правильный путь — виджет TV (Evo `@output_widget`). Файлы `assets/plugins/seoVolo/css/seo.css`, `js/seo.js`, `lang/` готовы, ждут подключения.
- **`sitemap_*` TV** — созданы, но DLSitemap нужно дополнительно настроить через параметры сниппета.
- **Schema.org Product** — без `seo_sku`, `seo_price` (TV не создаются).

---

## 7. ЧТО ДАЛЬШЕ (ПРИОРИТЕТ)

1. **Техдолг CSRF** (раздел 9.1 CHANGELOG):
   - Задача A: Ротация токенов на login/logout
   - Задача B: Origin / Referer check
   - Задача C: Защита connector'ов модулей (Store, MultiTV, evoSearch, editDocs)
   - Задача D: Убрать дублирование JS в `header.inc.php`
2. **Индикатор длины description** — через виджет TV.
3. **Живой поиск (AJAX autocomplete)** — Этап 1.2 roadmap.
4. **CLI-скрипт для патчей** — уже можно писать. Для автоматизации наката патчей на ядро при обновлении CE.
5. **Синхронизация документации** — `docs/seoVolo-guide.md` переписать под модуль (сейчас там про Конфигурацию).

---

## 8. ЧТО НЕ НАДО ПРЕДЛАГАТЬ

- **Соцсеть** — отказались.
- **SaaS-конструктор магазинов** — только полноценный модуль магазина.
- **Пакеты:** bLang, ClientSettings, Doc Manager, evoCollection, SimplePolls, ElementsInTree, templatesEdit3.
- **Вкладки в Конфигурации Evo** через JS-инжект — не работает, не пытаться.
- **`OnSiteSettingsRender`** — событие вызывается, но UI конфигурации Evo 1.4 строится из PHP-файлов, не из БД. Вкладки там не появятся.

---

## 9. КЛЮЧЕВЫЕ ТЕХНИЧЕСКИЕ МОМЕНТЫ

### 9.1. Evo 1.4 — архитектурные ограничения

- **Конфигурация не строится из БД.** Вкладки (Сайт, Дружественные URL и т.д.) прописаны в `manager/actions/mutate_settings/tab*.inc.php`.
- **Событие `OnSiteSettingsRender`** — вызывается, но вставляет только в свою вкладку «Сайт». Для новых полей непригодно.
- **`regClientCSS()` / `regClientScript()`** в форме документа **не работают** надёжно. Только `$modx->event->output()` или `echo` в контексте MM.
- **`$modx->config`** — кэшируется в `assets/cache/siteCache.idx.php`. После записи в `system_settings` напрямую — кэш **не обновляется сам**. Читать из БД или чистить кэш.
- **`DLSitemap`** не создаёт TV `sitemap_*` автоматически. Это делает наш SEO-пакет.

### 9.2. Патчи ядра

**Патчим файлы ядра напрямую.** Не через плагин на рантайме. Реестр — в `docs/patches-manifest.md`. При обновлении CE — накатываем вручную по манифесту. Для автоматизации наката — CLI-скрипт (можно писать).

### 9.3. Store

- Каталог: `github.com/srkkrs77/vololution/packages/catalog.json` (16 пакетов)
- `assets/modules/store/api.php` — прокси каталога
- `catalog.json`: поля `name`, `alias`, `version`, `category`, `description` (объект `{ru, en}`), `author`, `file`, `patched`, опционально `name_in_modx` и `status: "unchecked"`
- Store **не сравнивает версии** — определяет «установлен» по факту наличия имени в БД.

### 9.4. SEO-пакет

- **`head.tpl`** содержит `[!seoVolo!]` — некэшируемый вызов.
- **Вкладка SEO** в документе — через `mm_rules.tpl` + **активный ManagerManager**.
- **Настройки `seo_*`** — создаются плагином `OnManagerPageInit`, редактируются через модуль.
- **Fallback-логика:**
  - Title: `seo_title` → `pagetitle — site_name`
  - Description: `seo_description` → `description` → `introtext` (160) → `content` (160)
  - OG Image: `seo_og_image` → `seo_org_logo` → `og_default_image` → первое изображение из контента
  - Canonical: `seo_canonical` → авто
  - Robots: не выводится при `index, follow`

### 9.5. Доступ к пакетам (авторы)

- **Pathologic** — DocLister, FormLister, MultiCategories, editDocs (соавтор), SimpleGallery, SimpleTube, evoSearch, DLSiblings, MonthDate, phpThumb, WebP Converter.
- **Grinyaha** — editDocs.
- GitHub: `github.com/Pathologic`, `github.com/Grinyaha`.

### 9.6. CE-экосистема

- Ядро: `github.com/evocms-community/evolution`
- Мониторинг безопасности: `github.com/extras-evolution/security-fix`
- Telegram: `@evo_cms`
- Форум: `community.evocms.ru`

---

## 10. ПРАВИЛА ОБНОВЛЕНИЯ CE

- При выходе нового релиза CE — сверять `docs/patches-manifest.md` с diff.
- **НЕ обновляться автоматически** — только вручную через манифест.
- RSS-ленты CE (`security-fix` и `releases.atom`) оставлены как источник уведомлений.
- `evo.im` и `github.com/evolution-cms` — не используем.
- При обновлении — **патчить и `install/`, и `assets/modules/store/installer/`**.

---

## 11. ДОКУМЕНТАЦИЯ

| Файл | Где | Назначение |
|---|---|---|
| `CHANGELOG.md` | корень | Журнал всех правок (разделы 1–17) |
| `CONTEXT.md` | корень | Этот файл — для передачи контекста |
| `docs/patches-manifest.md` | docs | Реестр для сверки при обновлении CE |
| `docs/roadmap.md` | docs | План по этапам |
| `docs/CE-community.md` | docs | Экосистема CE |
| `docs/seoVolo-guide.md` | docs | Учебник по SEO-пакету (устарел — переписать под модуль) |

---

## 12. УРОКИ ЭТОЙ СЕССИИ

**Что я делал неправильно:**
- Давал куски кода вместо полных файлов.
- Не думал про кэш `$modx->config` при работе с `system_settings`.
- Не думал про кнопку Browse для полей картинок.
- Тратил время на нерабочие подходы (вкладка в Конфигурации).
- Не учитывал, что пользователь правит **в двух местах**.
- Не учитывал, что у Evo два инсталлера.

**Что делать в следующем чате:**
- Всё продумывать сразу: язык, кнопки, кэш, fallback.
- Давать целые файлы.
- Не биться в мёртвые архитектурные решения — сразу говорить «так нельзя, делаем по-другому».
- Указывать пути для обеих копий.

---

## 13. ПОЛЕЗНЫЕ ССЫЛКИ

- Наш репозиторий: `github.com/srkkrs77/vololution`
- Ядро CE: `github.com/evocms-community/evolution`
- Мониторинг безопасности: `github.com/extras-evolution/security-fix`
- Pathologic: `github.com/Pathologic`
- Форум: `community.evocms.ru`
- Telegram: `@evo_cms`
- Официальный сайт: `evo.im` (не используем в коде)

---

**Конец контекста.**

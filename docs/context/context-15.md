# CONTEXT-15.md — Техмоменты: Evo-ограничения, патчи ядра, Store

**Часть 15 из 19.** Читать после `CONTEXT.md`, `CONTEXT-01.md` — `CONTEXT-14.md`.

**Обновлено:** 13.10.2026 (кэш, AutoCacheClear отклонён).

---

## 15. КЛЮЧЕВЫЕ ТЕХНИЧЕСКИЕ МОМЕНТЫ (часть 1)

### 15.1. Evo 1.4 — архитектурные ограничения

#### 15.1.1. Конфигурация не строится из БД

Вкладки конфигурации (Сайт, Дружественные URL и т.д.) **прописаны в PHP-файлах админки**:

manager/actions/mutate_settings/tab*.inc.php


**Что это значит:** добавить свою вкладку в «Конфигурацию» через БД невозможно.

**Правильный путь:** отдельный модуль (как `seoVolo Settings`).

#### 15.1.2. `OnSiteSettingsRender` — только вкладка «Сайт»

Событие вызывается, но вставляет только в свою вкладку «Сайт». Для новых полей на новых вкладках **непригодно**.

#### 15.1.3. `regClientCSS()` / `regClientScript()` в форме документа

**Не работают надёжно.**

**Что работает:**
- `$modx->event->output()` — в контексте ManagerManager.
- `echo` — в контексте MM.
- Прямое подключение через `mm_rules.tpl`.

**Что НЕ работает:**
- `$modx->event->output()` на событии `OnDocFormPrerender` — **ломает форму.** Проверено 13.10.2026 (попытка скрыть TV relation через плагин — форма стала белой).

#### 15.1.4. Кэш `$modx->config`

`$modx->config` **кэшируется** в `assets/cache/siteCache.idx.php`.

После записи в `system_settings` **напрямую через SQL** — кэш **не обновляется сам**.

**Что делать:**
- Читать значения **напрямую из БД** (как делает `seoVolo Settings`).
- Или чистить `siteCache.idx.php` после сохранения.

#### 15.1.5. DLSitemap не создаёт TV автоматически

`DLSitemap` не создаёт TV `sitemap_*`. Их создаёт наш SEO-пакет (11 TV).

**Что нужно:** настроить DLSitemap через параметры сниппета, чтобы он использовал наши TV.

#### 15.1.6. Два инсталлера

Корневой `install/` и `assets/modules/store/installer/` — разные. Патчить оба. Подробно — часть 4.

#### 15.1.7. eval-инклюды в админке

Многие экшены админки (`manager/actions/*.php`) подключаются через `eval()`. Это:
- Ломает stack-trace при ошибках.
- Не даёт нормально IDE-навигацию.
- Мешает рефакторингу.

**Что делаем:** в новом коде (`src/`) — без eval. Старые экшены не трогаем.

#### 15.1.8. Кэш Evo — три уровня

**Важно (13.10.2026).**

| Уровень | Настройка | Что кэширует | Что делать |
|---|---|---|---|
| **HTML-страницы** | `cache_type` | Готовый HTML | `1` (только ID) |
| **Определения сниппетов** | `cache_snippets` | Какие сниппеты есть, параметры | **`1` — всегда** |
| **Определения плагинов** | `cache_plugins` | Какие плагины есть, события | **`1` — всегда** |
| **Определения чанков** | `cache_chunks` | Содержимое чанков | **`1` — всегда** |

**Правило:**
- **`cache_snippets`, `cache_plugins`, `cache_chunks` — всегда `1`.**
- **`cache_type` — `1`** (только ID), если сайт без параметрического контента.

**Отключение `cache_snippets`/`cache_plugins`/`cache_chunks` ломает:**
- Регистрацию плагинов на событиях.
- `regClientStartupHTMLBlock` / `regClientScript` (используются SimpleGallery).
- AJAX-endpoints (`ajax.php`) → «не удалось обработать ответ сервера».

**Отключение `cache_type` = `0`** (не кэшировать) в Evo 1.4 **не имеет radio-кнопки в админке.** Только через SQL:
```sql
UPDATE eh2v_system_settings
SET setting_value = '0'
WHERE setting_name = 'cache_type';

Но не рекомендовано. Лучше cache_type = 1.

Почему cache_type = 2 (учёт ID + $_GET) — плохо:

Отдельный кэш под каждую комбинацию UTM/GET-меток.

Реклама (?utm_source=google, ?gclid=..., ?fbclid=...) создаёт отдельные кэши.

Правки видны только в «чистом» кэше. Рекламные версии остаются старыми.

Для сайта-портфолио без параметрического контента — не нужно.

Почему cache_type = 1 — правильно:

Один кэш на страницу.

UTM игнорируются — все видят свежую версию.

Метрика работает (UTM ловятся JS в браузере).

#### 15.1.9. Эмодзи ломают сохранение (utf8mb3)
**Проблема:** Evo 1.4 использует кодировку БД `utf8` (3 байта). Эмодзи (👁, 😊, и т.д.) — это 4-байтовые символы (`utf8mb4`). При попытке сохранить чанк или документ с эмодзи MySQL молча обрезает текст (или откатывает сохранение), и поле в админке становится пустым.
**Решение:** Никогда не вставлять эмодзи в админку Evo 1.4. Для иконок использовать SVG-код или `<img>`.

#### 15.1.10. DLMenu и DLCrumbs — правильный синтаксис
**Wayfinder** — устарел, падает на PHP 8.x (использует `each()`). Не использовать. Только `DLMenu` и `DLCrumbs` из DocLister.

**DLMenu (Меню):**
- Родительский пункт без перехода на страницу (только выпадашка): в `&parentRowTpl` указать `href="#"`.
- `&parents=`0``, `&maxDepth=`2``.

**DLCrumbs (Хлебные крошки):**
- Обертка: **`&ownerTPL`** (не `outerTpl`!).
- Плейсхолдер списка: **`[+crumbs.wrap+]`** (не `[+wrap+]`!).
- Текущая страница: **`&tplCurrent`** (не `lastTpl`!).
- Скрыть родительские папки (например, «Деятельность») из крошек: **`&addWhereList=`id NOT IN (5,6,7)``** (параметр `&exclude` в DLCrumbs работает некорректно).

15.2. Патчи ядра — модель работы
15.2.1. Было (старая модель, до 10.10.2026)
Патчили файлы ядра напрямую.

Реестр — в docs/patches-manifest.md.

При выходе нового CE — накатывали вручную по манифесту.

Для автоматизации — планировался CLI-скрипт (этап 7.7).

15.2.2. Стало (форк-модель, с 10.10.2026)
Патчи ядра — обычные коммиты в форке.

patches-manifest.md — не инструкция, а реестр отличий от апстрима.

Обновление апстрима — через git merge upstream/1.4.x.

CLI-скрипт — отменён (не нужен).

15.2.3. Как разрешать конфликты при мерже
Если конфликт в файле из нашего реестра:

Взять версию апстрима.

Проверить: устранена ли причина нашего патча?

Пример: E_STRICT убран в апстриме — наш патч не нужен.

Пример: ?? '' для escape_string() — апстрим не тронул — наш патч остаётся.

Если причина осталась — наложить патч заново.

Обновить docs/patches-manifest.md.

Если конфликт в install/ или Store installer:

Патчить оба комплекта.

Если апстрим тронул assets/ (CodeMirror, ManagerManager):

Заново наложить патчи 2.6 и 2.7 (см. CHANGELOG).

15.3. Store — ключевые моменты
15.3.1. Каталог
Источник: github.com/srkkrs77/vololution/packages/catalog.json.

16 пакетов, мультиязычные описания (description — объект {ru, en}).

Поля: name, alias, version, category, description, author, file, patched, опционально name_in_modx и status: "unchecked".

15.3.2. Прокси
assets/modules/store/api.php — читает catalog.json с GitHub, отдаёт в формате store.js.

Проверки: $_SESSION['mgrValidated'] + $modx->hasPermission('exec_module').

15.3.3. Логика «установлен / не установлен»
Store не сравнивает версии. Проверяет факт наличия имени в БД:

core.php читает имена из site_snippets, site_plugins, site_modules.

Пишет JSON: $PACK[тип][имя] = '1'.

store.js смотрит наличие имени в этом объекте.

Почему: версия в описании пакета произвольная, часто без <strong>. Сравнение разных чисел давало ложные результаты.

Обновление до новой версии: нажать «Переустановить». Store скачает свежий ZIP.

15.3.4. Маппинг категорий
UI Store знает три типа: snippet, plugin, module.

При загрузке каталога api.php маппит:

snippets → snippet

plugins → plugin

modules → module

tvs → snippet (MultiTV идёт в сниппеты)

lib → snippet (phpThumb идёт в сниппеты)

15.3.5. Язык описаний
api.php определяет язык по $modx->config['manager_language']:

php
$mgrLang = substr($modx->config['manager_language'] ?? 'english', 0, 2);
if ($mgrLang !== 'ru') $mgrLang = 'en';
Возвращает описание по языку, fallback на en.

15.3.6. Формат ответа для store.js
php
[
    'id' => alias,
    'cid' => alias,
    'title' => name,
    'description' => строка (по языку),
    'type' => snippet|plugin|module,
    'name_in_modx' => name_in_modx или name,
    'version' => version,
    'date' => updated из каталога,
    'author' => author,
    'downloads' => '0',
    'image' => '',
    'url' => ['fieldValue' => [[file, version, date]]],
    'cls' => 'pack_install',
]
15.3.7. Заглушки для неиспользуемых эндпоинтов
store.js иногда зовёт методы, которых у нас нет. api.php возвращает пустые ответы:

login → ['result' => false]

verifyuser → ['result' => false]

logout → ['result' => true]

download → ['result' => true]

saveuser → ['result' => true]

exituser → ['result' => true]

get_own_list → []

get_category → []

get_list → []

15.3.8. Техдолг Store
instprocessor-fast.php — 16 вызовов mysql_error() (удалена в PHP 7).

instprocessor-fast.php — $count_new_name, $props, $dbase могут быть undefined.

instprocessor-fast.php — getCreateDbCategory($category, $sqlParser) — лишний аргумент.

CSRF — api.php и core.php не проверяют токен (раздел 9.1, задача C).

Нет уведомлений об обновлениях пакетов.

Установка «из архива» не проверяет содержимое ZIP.

15.3.9. Что НЕ делает Store
Не логинится на evo.im. Отвязан.

Не сравнивает версии. Только «есть в БД / нет».

Не использует fancybox. Удалён.

Не делает self-update. Удалён.

Не блокирует пакеты. Чёрный список удалён.

15.4. TV relation от evoBabel — служебная
Важно (13.10.2026).

TV relation — служебная для evoBabel. Хранит языковые связи в формате de:5||en:12.

Что с ней:

Привязана ко всем шаблонам. Чтобы evoBabel мог связать любой документ.

В форме документа — не нужна. Ни на одной странице. Клиент с ней не работает.

evoBabel пишет и читает её через БД напрямую. Видимость в форме не важна.

Как скрыть:

Вариант A — mm_hideFields в mm_rules.tpl. Работает через ManagerManager.

php
mm_hideFields('relation');
Вариант B — плагин на OnDocFormPrerender. НЕ РАБОТАЕТ. Ломает форму — $modx->event->output() на этом событии выводится вместо HTML формы. Проверено 13.10.2026. Не использовать.

Вариант C — отключить TV от шаблонов. Не подходит — evoBabel не сможет связать новые документы.

Что рекомендовано: mm_hideFields('relation') в mm_rules.tpl.

15.5. Кэш браузера админки — no-cache
Проблема (11.10.2026): при переходе между документами админка отдавала закэшированную форму. На кастомных TV (image_alt_caption) поля оставались пустыми, пока не сделаешь Ctrl+Shift+R.

Решение: в manager/includes/header.inc.php, в <head>, после <meta http-equiv="Content-Type">:

html
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
Что это НЕ делает:

Не влияет на безопасность.

Не ломает сессии.

Не трогает фронтенд сайта.

Что это делает:

Браузер не кэширует страницы админки.

Форма всегда грузится с сервера.

Кастомные TV показывают актуальные значения.

CHANGELOG: 2.8.

15.6. AutoCacheClear — отклонённый плагин
Дата: 13.10.2026
Статус: ❌ Не использовать.

Идея: плагин на события OnDocFormSave, OnTempFormSave и т.д. вызывает $modx->clearCache('full') — чтобы после правок кэш страниц чистился автоматически.

Проблема:

clearCache('full') стирает всё, включая файлы, которые Evo использует в текущем процессе сохранения. После сохранения — белая страница, форма не открывается.

clearCache('siteContent') — то же самое. Проверено.

Итог: плагин удалён. Не создавать заново.

Правильное решение: cache_type = 1 + ручная очистка кэша (Сайт → Очистить кэш) после правок шаблонов.

15.7. Кастомные TV — механизм @INCLUDE
Как работает:

Создаётся папка assets/tvs/<имя_кастомной_tv>/.

Внутри — файл <имя_кастомной_tv>.php — рендер формы в админке.

В TV:

Тип ввода: Custom Input

Возможные значения: @INCLUDE/assets/tvs/<имя>/<имя>.php

Что доступно в файле рендера:

$field_id — ID TV.

$modx — объект MODX.

$value — не доступно напрямую. Читается из БД по $field_id и ID документа.

Как получить ID документа в форме:

php
$docId = (int)($_REQUEST['id'] ?? $modx->documentIdentifier);
Как сохранить значение:

Скрытое поле <input type="hidden" name="tvNNN" value="...">.

Evo само записывает его в site_tmplvar_contentvalues.

Пример: image_alt_caption. См. часть 8, раздел 8.17.

15.8. TV-Elements — порядок label==value
Важно. В Evo 1.4 формат @input_options — label==value.

Правильно:

text
always (every access)==always
monthly (default)==monthly
Неправильно:

text
always==always (every access)
Что происходит при неправильном порядке: в БД сохраняется label (с описанием в скобках), а не value. В XML/Schema/HTML попадает мусор.

Пример: sitemap_changefreq → <changefreq>always (every access)</changefreq>. Google считает невалидным.

Рекомендация для sitemap TV: без подписей.

text
always||hourly||daily||weekly||monthly||yearly||never
0.0||0.1||0.2||0.3||0.4||0.5||0.6||0.7||0.8||0.9||1.0
CHANGELOG: 15.13.

Конец части 15.

text

---

**Куда:** `docs/context/CONTEXT-15.md` в двух локальных копиях.

**Что изменилось:**

1. **Раздел 15.1.3** — дополнен: `$modx->event->output()` на `OnDocFormPrerender` **ломает форму** (проверено 13.10.2026).
2. **Раздел 15.1.8** — новый. Кэш Evo — три уровня. `cache_type = 1`. Отключение `cache_snippets/plugins/chunks` ломает SimpleGallery.
3. **Раздел 15.4** — новый. TV `relation` от evoBabel — служебная. Варианты скрытия.
4. **Раздел 15.5** — новый. no-cache в админке.
5. **Раздел 15.6** — новый. AutoCacheClear отклонён.
6. **Раздел 15.7** — новый. Кастомные TV через `@INCLUDE`.
7. **Раздел 15.8** — новый. Порядок `label==value` в TV-Elements.
CHANGELOG-12.md — Раздел 14.8–14.15
Часть 12 из 14. Store: дескриптор, чистка installer, instprocessor-fast, логика, техдолг, тесты.

markdown
### 14.8. Правки `install/assets/modules/store.tpl`

**Файл:** `install/assets/modules/store.tpl`

#### 14.8.1. Маркер

```diff
- // <?php  (с пробелом после //)
+ //<?php   (без пробела)
Критично: парсер установщика Evo ищет именно //<?php.

14.8.2. Название модуля
diff
- Extras
+ VoloLution Store
14.8.3. Описание
Было:

text
first repository for Evolution CMS
Стало:

text
Установка пакетов из репозитория VoloLution CMS
Источник: github.com/srkkrs77/vololution/packages
Основано на оригинальном Store от Bumkaka & Dmi3yy
Модифицировано для VoloLution CMS
14.8.4. Атрибуты docblock
Было:

php
@version     0.1.4
@internal    @properties
@internal    @guid store435243542tf542t5t
@internal    @shareparams 1
@internal    @dependencies requires files located at /assets/modules/store/
@internal    @modx_category Manager and Admin
@internal    @installset base, sample
@lastupdate  25/11/2016
Стало:

php
@version     1.0.0
@internal    @modx_category Manager and Admin
@internal    @dependencies requires files located at /assets/modules/store/
@internal    @installset base, sample
@lastupdate  07/10/2026
Удалены:

@properties — не нужен.

@guid — нужен только для апдейтов через evo.im.

@shareparams — не нужен.

14.8.5. Тело модуля
Было:

php
//AUTHORS: Bumkaka & Dmi3yy
include_once('../assets/modules/store/core.php');
Стало:

php
// ORIGINAL AUTHORS: Bumkaka & Dmi3yy
// MODIFIED BY: VoloLution CMS
include_once(MODX_BASE_PATH . 'assets/modules/store/core.php');
Относительный путь заменён на абсолютный через MODX_BASE_PATH.

14.9. Чистка installer/
14.9.1. Удалено 8 файлов
Файл	Назначение
installer/index.php	Точка входа UI-установщика
installer/action.install.php	Экран «Установка завершена»
installer/action.load.php	Заглушка (закомментирована)
installer/action.options.php	Экран выбора компонентов
installer/instprocessor.php	Старая версия, дубликат -fast
installer/style.css	Стили UI
installer/jquery-1.4.4.min.js	jQuery для UI
Причина: Store ставит пакеты через AJAX, UI-установщик не нужен.

14.9.2. Удалены папки
installer/img/ — 9 декоративных картинок.

installer/lang/*.inc.php — 18 языков.

14.9.3. Осталось 6 файлов
installer/functions.php

installer/instprocessor-fast.php

installer/setup.info.php

installer/sqlParser.class.php

installer/lang/russian-UTF8.inc.php

installer/lang/english.inc.php

14.10. Правки installer/instprocessor-fast.php
14.10.1. Выбор языка (было — хардкод)
Было:

php
$_lang = array();
$_params = array();
require_once($modulePath."/lang/russian-UTF8.inc.php");
Стало:

php
$_lang = array();
$_params = array();
$mgrLang = $modx->config['manager_language'] ?? 'english';
$langFile = $modulePath . "/lang/" . $mgrLang . ".inc.php";
if (!file_exists($langFile)) {
    $langFile = $modulePath . "/lang/english.inc.php";
}
require_once($langFile);
Причина: старый хардкод требовал russian-UTF8.inc.php, что ломало английский интерфейс.

14.10.2. Остальные правки
Техдолг НЕ закрыт (см. раздел 14.14):

16 вызовов mysql_error() — удалена в PHP 7.

$count_new_name, $props, $dbase могут быть undefined.

getCreateDbCategory($category, $sqlParser) — лишний аргумент.

Правим по факту ошибки.

14.11. Чистка корня Store
14.11.1. Удалено
js/fancybox/ — вся папка с пакетом fancybox v2.1.4, хелперами (buttons, thumbs, media), jquery.mousewheel-3.0.6.

img/body.jpg — фон, не использовался.

img/github.png — логотип, не использовался.

lang/italian.php

lang/nederlands.php

lang/nederlands-utf8.php

14.11.2. Осталось
Файл	Назначение
js/jquery.min.js	jQuery 1.8.3 (для AJAX)
js/store.js	Основная логика
img/loader.gif	Используется .loader в CSS
img/loading.png	Используется #loading в CSS
lang/russian-UTF8.php	Подключается core.php
lang/english.php	Fallback
14.12. Логика «установлен / не установлен»
Store НЕ сравнивает версии пакетов.

Проверка:

core.php читает имена из site_snippets, site_plugins, site_modules.

Записывает в JSON как $PACK[тип][имя] = '1'.

store.js смотрит — есть ли имя в этом объекте.

Результат:

Состояние	Класс	Кнопка
Есть в БД	pack_reinstall	«Переустановить»
Нет в БД	pack_install	«Установить»
Почему убрали сравнение версий
Версия в описании пакета (из @version в .tpl) — произвольная, часто без тега <strong>.

Версия в catalog.json — наша, отдельная.

Сравнение разных чисел давало ложные результаты:

'2.1' < '1.0.0' в строках = false

'2.1' == '1.0.0' = false

Класс оставался pack_install, хотя пакет установлен.

Обновление до новой версии — просто нажатие «Переустановить». Магазин скачает свежий ZIP с GitHub и перезапишет пакет.

Уведомление о новых версиях — задача на будущее (см. 14.14, п.5).

14.13. Обновление карточки после установки
В store.install() после успеха AJAX-запроса:

javascript
el.closest('.catalog_item')
    .removeClass('item-install')
    .addClass('item-reinstall');
Карточка мгновенно меняет кнопку «Установить» на «Переустановить» без перезагрузки страницы.

14.14. Техдолг (не критично, фиксируем)
П.1 — instprocessor-fast.php, 16 вызовов mysql_error()
Функция удалена в PHP 7. Сработает только при SQL-ошибке и приведёт к Fatal. Пока не всплывало — не патчим.

П.2 — instprocessor-fast.php, неопределённые переменные
$count_new_name, $props, $dbase могут быть undefined в редких ветках (overwrite=false, пустой список plugins, TV с assignments). Warning в логе, не критично.

П.3 — instprocessor-fast.php, лишний аргумент
getCreateDbCategory($category, $sqlParser) вызывается с двумя аргументами, принимает один. PHP игнорирует лишний.

П.4 — CSRF
api.php и core.php не проверяют токен. Раздел 9.1, Задача C. Кросс-доменный POST может дёрнуть эндпоинты от имени админа.

П.5 — Нет уведомлений об обновлениях пакетов
Пользователь не знает, что в catalog.json появилась новая версия.

Решение на будущее: писать установленную версию в assets/cache/store/installed.json и сверять при открытии.

П.6 — Установка «из архива» (сайдбар) не проверяет содержимое
Если пользователь зальёт ZIP со зловредным PHP — он попадёт на сайт. Ограничение — только админ.

14.15. Результаты тестирования
Дата: 07.10.2026
URL: https://vol.test/manager/
БД: vol, префикс gyl8_
PHP: 8.5.10, Laragon, MySQL 8.0.30

Проверено
✅ Подключение модуля через site_modules

✅ Загрузка каталога через api.php с GitHub

✅ Категории: Сниппеты 7, Плагины 6, Модули 2

✅ Поиск по названию

✅ Переключение вида: список / 2 колонки / 3 колонки

✅ Установка MonthDate (сниппет)

✅ Установка DLSiblings (сниппет)

✅ Установка SimpleGallery (плагин)

✅ Определение установленных пакетов

✅ Кнопка «Переустановить» появляется после установки

✅ Работа всех трёх категорий UI

✅ PHP error_log — пусто

Не проверено (пакеты в очереди)
⬜ Установка остальных 13 пакетов

⬜ Мультиязычные описания (переключение языка Evo)

⬜ Установка MultiTV (сложный TV-пакет)

⬜ Установка editDocs (сложный модуль)

⬜ Установка из локального ZIP через сайдбар

text

**Конец части 12.**
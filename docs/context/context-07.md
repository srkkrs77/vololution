CONTEXT-07.md — Свой Extras-Store
Часть 7 из 19. Читать после CONTEXT.md, CONTEXT-01.md — CONTEXT-06.md.

7. ЧТО СДЕЛАНО: СВОЙ EXTRAS-STORE
Статус: ✅ выполнено (07.10.2026).
CHANGELOG: раздел 14 (полные исходники и diff'ы — там).
patches-manifest.md: раздел «СВОЙ STORE».

7.1. Цель
Заменить источник каталога пакетов и убрать наследие evo.im.

Было	Стало
extras.evo.im / extras.evocms.ru	github.com/srkkrs77/vololution/packages
Fancybox, логин, self-update, чёрный список, сравнение версий	Чистый Store: каталог, поиск, установка
UI и AJAX-установка оригинального Store — сохранены.

7.2. Репозиторий пакетов
GitHub: github.com/srkkrs77/vololution

Папка: /packages/

Файл каталога: /packages/catalog.json

16 ZIP с именами <alias>.zip: DLSiblings, DocLister, FirstChildRedirect, FormLister, FormResults, MonthDate, MultiCategories, SimpleGallery, SimpleTube, editDocs, evoBabel, evoFileManagerDialog, evoSearch, multiTV, phpThumb, webP

Категории:

Сниппеты: DocLister, FormLister, DLSiblings, MonthDate, FirstChildRedirect

Плагины: SimpleGallery, SimpleTube, evoSearch, EvoBabel, evoFileManagerDialog, WebP Converter, MultiCategories

TV: MultiTV

Модули: FormResults, editDocs

Библиотеки: phpThumb

Поле name_in_modx: задано там, где имя в БД отличается от name (multiTV → multiTV, phpthumb → phpthumb, evoBabel → evoBabel).

7.3. Файлы модуля Store (в assets/modules/store/)
Файл	Что
core.php	Основная логика, ~версия 1.0.0
api.php	Новый. Прокси каталога с GitHub
template/main.html	Переписан, без fancybox/логина/self-update
js/store.js	Очищен от blockedPackages, packageDescriptions, логина
js/jquery.min.js	jQuery 1.8.3 (для AJAX, оставлен)
css/style.css	Иконки типов через ::before, .extra-meta
lang/russian-UTF8.php, lang/english.php	Только эти два
img/loader.gif, img/loading.png	Используются CSS
installer/	6 файлов (см. часть 4.5)
Удалено: js/fancybox/, img/body.jpg, img/github.png, lang/italian.php, lang/nederlands*.php.

7.4. Ключевые правки core.php
$version = "1.0.0" (было 0.1.3).

Удалён fallback на extras.evo.im — при отсутствии URL отдаёт ошибку.

Добавлено public $errors = []; (PHP 8.2+ запрещает динамические свойства).

downloadFile() переписан: @fopen, проверка $newf, curl_close, catch (\Exception $e).

Обнаружение установленных пакетов: $PACK[$value][$row['name']] = '1'; — без сравнения версий.

7.5. api.php — прокси каталога
Путь: assets/modules/store/api.php. ~140 строк.

Инициализация MODX:

php
define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);
include_once(__DIR__ . '/../../../index.php');
$modx->db->connect();
if (empty($modx->config)) {
    $modx->getSettings();
}
Проверки: $_SESSION['mgrValidated'] + $modx->hasPermission('exec_module').

Заглушки для неиспользуемых эндпоинтов (login, verifyuser, logout, download, saveuser, exituser, get_own_list, get_category, get_list) — возвращают пустые ответы.

Маппинг категорий для UI (snippets → snippet, plugins → plugin, modules → module, tvs/lib → snippet).

Язык: substr($modx->config['manager_language'] ?? 'english', 0, 2), fallback на en.

Формат ответа для store.js: id, cid, title, description, type, name_in_modx, version, date, author, downloads, image, url, cls.

7.6. Логика «установлен / не установлен»
Store НЕ сравнивает версии.

core.php читает имена из site_snippets, site_plugins, site_modules.

Пишет JSON: $PACK[тип][имя] = '1'.

store.js смотрит — есть ли имя в этом объекте.

Состояние	Класс	Кнопка
Имя есть в БД	pack_reinstall	«Переустановить»
Имени нет	pack_install	«Установить»
Почему убрали сравнение версий: версия в описании пакета произвольная, часто без <strong>. Сравнение разных чисел давало ложные результаты. Обновление до новой версии — просто нажатие «Переустановить» (Store скачает свежий ZIP и перезапишет).

7.7. Дескриптор модуля (install/assets/modules/store.tpl)
Маркер //<?php (без пробела) — критично для парсера Evo.

Название: VoloLution Store.

Удалены атрибуты @properties, @guid, @shareparams.

Тело: include_once(MODX_BASE_PATH . 'assets/modules/store/core.php');.

7.8. Техдолг Store
instprocessor-fast.php — 16 вызовов mysql_error() (удалена в PHP 7).

instprocessor-fast.php — $count_new_name, $props, $dbase могут быть undefined.

instprocessor-fast.php — getCreateDbCategory($category, $sqlParser) — лишний аргумент.

CSRF — api.php и core.php не проверяют токен (раздел 9.1, задача C).

Нет уведомлений об обновлениях пакетов (установленная версия не пишется).

Установка «из архива» не проверяет содержимое ZIP (ограничение — только админ).

7.9. Результаты тестирования Store (07.10.2026)
✅ Подключение модуля, загрузка каталога через api.php, категории (Сниппеты 7, Плагины 6, Модули 2), поиск, переключение вида, установка MonthDate / DLSiblings / SimpleGallery, определение установленных, кнопка «Переустановить», PHP error_log пусто.

⬜ Установка остальных 13 пакетов, мультиязычные описания, установка MultiTV / editDocs, установка из локального ZIP.

Конец части 7.
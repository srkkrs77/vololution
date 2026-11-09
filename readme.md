КОНТЕКСТ РАБОТЫ

Работаем над оживлением Evolution CMS 1.4.37 для PHP 8.5.10.
Цель: собрать эталонный дистрибутив (ядро + патчи + пропатченные пакеты),
почистить Extras от legacy-мусора, написать свои дополнения.

Среда: Laragon, Windows, PHP 8.5.10, MySQL 8.4.3, nginx.
Сайт: https://site1437.test/, БД site1437, префикс ht4q_.


============================================================
ПАТЧИ ЯДРА (4 файла)
============================================================

1. manager/includes/protect.inc.php
   Было:  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
   Стало: error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

2. manager/includes/extenders/dbapi.mysqli.class.inc.php
   Правка A (метод connect, после установки кодировки):
     Добавлено: $this->conn->query("SET SESSION sql_mode='';");
   Правка B (метод escape, ~строка 129):
     Было:  return $this->conn->escape_string($s);
     Стало: return $this->conn->escape_string($s ?? '');

3. assets/lib/class.modxRTEbridge.php
   Метод initLang(), ~строка 807:
   Было:  $modx->config['manager_language']
   Стало: $modx->config['manager_language'] ?? 'english'

4. manager/includes/header.inc.php
   После проверки IN_MANAGER_MODE (строки 6-7):
     Добавлено:
       $modx_manager_charset = $modx_manager_charset ?? 'UTF-8';
       $mxla = $modx_lang_attribute ?? 'en';


============================================================
ПРАВКИ МОДУЛЯ EXTRAS (4 файла)
============================================================

5. assets/modules/store/js/store.js
   - Массив blockedPackages (~150 имён legacy-пакетов)
   - Функция filterBlocked(data) — фильтр по item.title
   - В init() фильтрация data.allcategory
   - Словарь packageDescriptions (RU/EN) для оставшихся 27 пакетов
   - Функция getLocalizedDescription() + вызов в parse_list_item

6. assets/modules/store/template/main.html
   - Убраны кнопки 🚫 из #tpl_list и #tpl_cart
   - Убран блок «Скрытые пакеты» из сайдбара

7. assets/modules/store/installer/setup.info.php
   6 мест: $params['modx_category'] → $params['modx_category'] ?? ''
   Плюс 1 место: $params['guid'] → $params['guid'] ?? ''

8. assets/modules/store/installer/instprocessor-fast.php
   5 мест: $_POST['template'], $_POST['tv'], $_POST['chunk'],
           $_POST['module'], $_POST['snippet'] → ... ?? []


============================================================
ПРАВКИ ПАКЕТОВ-ДОПОЛНЕНИЙ
============================================================

9. assets/tvs/multitv/includes/multitv.class.php (7 правок)
   - manager_language ?? 'english'
   - which_editor ?? 'none'
   - thumbsDir — !empty() в 2 местах (BolmerCMS, Evolution/default)
   - compareSort(): strtotime((string)(... ?? '')) в 2 местах
   - getMultiValue(): $tvOutput[$this->tvName] ?? ''

10. assets/plugins/evoSearch/plugin.class.php
    - emptyExcluded(): !empty() для excludeTmpls/excludeIDs
    - makeSQLForSelectWords(): ?? '1' для unpublished/deleted
    - __construct(): ?? '' для dicts/id
    - getDicts(): защита от пустой строки
    - Words2BaseForm(): защита от пустого массива dicts

11. assets/modules/formresults/core/src/FormResults.php
    Строки 8-10 (после use-блоков, до class FormResults):
      $autoload = MODX_BASE_PATH . 'vendor/autoload.php';
      if (is_file($autoload)) { require_once $autoload; }

12. assets/modules/formresults/core/templates/forms_list.tpl
    Строка 23: ?? '' для alias/caption + закрыт </a>
    Строка 25: $form['results_total'] ?? 0
    Строка 26: $form['last_result'] ?? ''

13. vendor/ (папка в корне)
    PhpSpreadsheet + зависимости, composer.json, composer.lock.
    Копируется в эталон для экспорта FormResults в Excel без Composer.


============================================================
bLANG — ПРАВКИ CSRF (8 файлов)
============================================================

Путь: assets/modules/blang/

ФАЙЛ 1: module/module.blang.php — УЖЕ ПРОПАТЧЕН
  ✓ $csrf = csrf_token();
  ✓ new bLangModule($modx,$modulePath,$moduleurl,$csrf)
  ✓ 'csrf' => $csrf в $data
  ✓ &_token= в sendRedirect

ФАЙЛ 2: module/actions/params.php — ПРАВИМ
  ✓ 'csrf' => csrf_token() в конструкторе
  ✓ &_token= в getAndProcessParamForm (3 редиректа)
  ✓ &_token= в deleteParam
  ❌ ОСТАЛОСЬ: метод getParamsList(), строка с parseChunk('@FILE:params/param'):
     Было:  array_merge($tmplvar,['moduleurl'=>$this->bLangModuleObj->moduleurl])
     Стало: array_merge($tmplvar,['moduleurl'=>$this->bLangModuleObj->moduleurl,'csrf'=>$this->data['csrf']])
  ❌ ОСТАЛОСЬ: renderPossibilityAlert — в ссылку «Исправить» добавить &_token=[+csrf+]
     и в массив данных 'csrf'=>$this->data['csrf']
  ❌ ОСТАЛОСЬ: fixParams — в sendRedirect добавить &_token=.$this->data['csrf']
  ❌ ОСТАЛОСЬ: duplicateParam — в sendRedirect добавить &_token=.$this->data['csrf']
  ❌ ОСТАЛОСЬ: checkUpdatePossibilityInAction — в parseChunk для params/error
     добавить 'csrf'=>$this->data['csrf']

ФАЙЛ 3: module/templates/settings.tpl — НЕ ПРОВЕРЕН
  Искать: <input type="hidden" name="_token" value="[+csrf+]">

ФАЙЛ 4: module/templates/home.tpl — НЕ ПРОВЕРЕН
  Искать: const token = '[+csrf+]'; + &_token=[+csrf+] в AJAX

ФАЙЛ 5: module/templates/params/error.tpl — УЖЕ ПРОПАТЧЕН
  ✓ <a href="[+moduleurl+]action=params&_token=[+csrf+]">Назад</a>

ФАЙЛ 6: module/templates/params/param.tpl — УЖЕ ПРОПАТЧЕН
  ✓ &_token=[+csrf+] в ссылке

ФАЙЛ 7: module/templates/params/paramForm.tpl — НЕ ПРОВЕРЕН
  Искать: const token = '[+csrf+]'; + &_token=[+csrf+] в action формы

ФАЙЛ 8: module/templates/params/params.tpl — НЕ ПРОВЕРЕН
  Искать: const token = '[+csrf+]'; + &_token= в AJAX


============================================================
СТАТУС ПРОВЕРКИ ПАКЕТОВ
============================================================

ПРОШЛИ ПРОВЕРКУ (11):
  DocLister, FormLister, MultiTV, TinyMCE4, ElementsInTree,
  DLSiblings, evoSearch, Form Results, WebP Converter,
  Evo File Manager Dialog, templatesEdit3

ОСТАЛОСЬ ПРОВЕРИТЬ (10):
  ClientSettings, Doc Manager for Evo 1.4.x, EditDocs,
  evoBabel, PageBuilder, SimpleGallery, SimpleTube, SimplePolls,
  eFilter + DocInfo, bLang (правим CSRF)

УДАЛЯЕМ (в blockedPackages):
  ~150 legacy-пакетов + AutoEvoBackup + EvoBackup (PclZip не работает
  на PHP 8.5 — fread() с length=0 даёт ValueError).


============================================================
КАСТОМНЫЕ РАЗРАБОТКИ (список на будущее)
============================================================

1. SEO-пакет (аналог sSeo для 1.4.37)
2. Теги
3. Хэштеги
4. Лайки
5. Оценочный рейтинг
6. Система комментариев (замена JotX)
7. Мультикатегории
8. PageBuilder-замена
9. Счётчик просмотров
10. Редиректы
11. SimpleGallery (замена) — если не заработает
12. StaticElements (замена)
13. TOC (замена)
14. TvTable (замена)
15. Облако тегов (замена TvTagCloud)
16. Yandex.Metrika Widget (замена)
17. YandexMapTV (замена)
18. yearCopyright (замена) — 5 минут работы
19. Живой поиск на базе evoSearch (AJAX, универсальный)
20. Модуль бэкапа:
    - Режимы: полный сайт, только БД, только файлы, выборочный
    - Визуальный интерфейс с деревом папок
    - Список бэкапов: скачать, удалить, размер, дата
    - Восстановление из архива
    - Исключения: _backup_archives/, assets/cache/
    - ZipArchive + mysqldump, без PclZip
    - Файл исключений .gitignore-типа
    - Автобэкап по расписанию
    - Прогресс, проверка свободного места
21. Плагин авто-восстановления патчей ядра (отложено до 10+ патчей)


============================================================
ТЕКУЩИЙ МОМЕНТ
============================================================

Правим bLang. Нужно:
1. Внести оставшиеся правки в params.php (см. выше)
2. Проверить 4 файла: settings.tpl, home.tpl, paramForm.tpl, params.tpl
3. Продолжить проверку пакетов: ClientSettings и далее


============================================================
ЛОКАЛЬНЫЙ СКЛАД
============================================================

_my_extras/
├── core/        (MultiTV, DocLister)
├── optional/
├── custom/
└── legacy/

_my_patches/
└── README.md    (описание всех правок — собрать в конце)
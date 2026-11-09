CONTEXT-11.md — Философия простоты: что такое простота Evo
Часть 11 из 19. Читать после CONTEXT.md, CONTEXT-01.md — CONTEXT-10.md.

11. ФИЛОСОФИЯ ПРОСТОТЫ — ЧТО ТАКОЕ ПРОСТОТА EVO
Это главный раздел документации. Он объясняет, что нельзя ломать в Evo. Всё остальное — производное от него.

11.1. Суть
Evo славится тем, что разработчик приходит и сразу понимает, как всё работает. Это то, что мы не ломаем.

Ключевая идея: простой PHP-файл + прямой доступ к $modx. Никаких абстракций, контейнеров, DTO, репозиториев. Всё лежит «на поверхности».

11.2. Пять базовых элементов Evo
11.2.1. Сниппет
Что это: PHP-файл.

Где лежит: assets/snippets/<name>/.

Как вызывается: [[snippetName? &param=value]].

Доступ: прямой к $modx, $params, $modx->db, $modx->config.

Пример:

php
<?php
// assets/snippets/mySnippet/snippet.mySnippet.php
$limit = (int)($params['limit'] ?? 10);
$rows = $modx->db->select('id, pagetitle', 'site_content', '', 'id DESC', $limit);
$output = '';
while ($row = $modx->db->getRow($rows)) {
    $output .= '<a href="' . $modx->makeUrl($row['id']) . '">' . $row['pagetitle'] . '</a>';
}
return $output;
Вызов в шаблоне: [[mySnippet? &limit=5]].

Так пишется в Evo. Так остаётся.

11.2.2. Чанк
Что это: HTML-фрагмент.

Где лежит: в БД (site_htmlsnippets), правится через админку.

Как используется: [+placeholder+], [*tv*], [[snippet]].

Пример:

html
<div class="news-item">
    <h3>[+pagetitle+]</h3>
    <p>[*introtext*]</p>
    <a href="[~[+id+]~]">Читать</a>
</div>
Так пишется в Evo. Так остаётся. Никаких Twig, Blade, Smarty.

11.2.3. TV (Template Variable)
Что это: дополнительное поле документа.

Где настраивается: админка → Элементы → TV.

Как выводится: [*tv_name*] в чанке, или $modx->getTemplateVar(...) в коде.

Особенность: настраивается без кода.

11.2.4. Плагин
Что это: PHP-файл, подписанный на события Evo.

Где лежит: assets/plugins/<name>/.

События: OnDocFormSave, OnWebPageInit, OnManagerPageInit, OnEmptyTrash и т.д.

Доступ: $modx, $modx->event.

Пример:

php
<?php
// assets/plugins/myPlugin/plugin.myPlugin.php
switch ($modx->event->name) {
    case 'OnDocFormSave':
        $id = $modx->event->params['id'];
        // ...
        break;
}
11.2.5. Модуль
Что это: PHP-файл + вкладка в админке.

Где лежит: assets/modules/<name>/.

Дескриптор: install/assets/modules/<name>.tpl.

Вкладка: появляется в меню «Модули».

11.3. Ключевой API Evo
Всё это остаётся. Не оборачиваем в «красивые классы». Не переводим на DI. Не вводим конфиг-файлы вместо настроек в БД.

API	Что делает
$modx->db->select(...)	SELECT-запрос
$modx->db->getRow(...)	Одна строка
$modx->db->update(...)	UPDATE
$modx->db->insert(...)	INSERT
$modx->db->delete(...)	DELETE
$modx->db->query(...)	Произвольный SQL
$modx->invokeEvent('EventName', $params)	Запуск события
$modx->getChunk('name', $params)	Получить чанк
$modx->parseChunk('name', $params)	Распарсить чанк
$modx->runSnippet('name', $params)	Запустить сниппет
$modx->logEvent(...)	Запись в Event Log
$modx->makeUrl($id)	URL документа
$modx->getConfig('key')	Системная настройка
11.4. Что это значит для разработчика
Пишет сниппет — PHP-файл, $modx->db->query(...). Как в Evo 2010-х.

Пишет модуль — PHP-файл в assets/modules/. Как в Evo.

Пишет плагин — подписывается на события. Как в Evo.

Шаблоны — чанки с [+placeholder+]. Как в Evo.

Никаких «прослоек» между разработчиком и движком. Если нужен доступ к БД — берёт $modx->db. Если нужен шаблон — берёт чанк. Просто.

11.5. Что это значит для системы
Новый код (src/) — типизирован, PSR-4, PDO, PSR-3.

Старый API Evo — работает. Обратная совместимость критична.

Постепенно: старые модули переписываются на новый слой. Но не через «фреймворк», а через нормальный PHP.

11.6. Что это значит для фронта
Формы — обычный POST. action="?a=5", обработчик на бэкенде, redirect на «спасибо».

Магазин: корзина в сессии, оформление — POST + redirect.

Комментарии: POST + redirect.

Авторизация: POST + redirect.

Поиск: страница результатов — обычный GET. Автокомплит — AJAX (нельзя иначе).

11.7. Продолжение
Следующая часть — что именно запрещено, что разрешено, и как модернизировать extras.

Конец части 11.
CONTEXT-04.md — Два инсталлера
Часть 4 из 19. Читать после CONTEXT.md, CONTEXT-01.md — CONTEXT-03.md.

4. ДВА ИНСТАЛЛЕРА — НЕ ПУТАТЬ
В проекте два разных инсталлера, происходящих от одного предка (MODX). У них схожие баги, патчить нужно оба комплекта.

4.1. Таблица
#	Где	Что ставит	Файлы патчей
1	install/ (корневой установщик)	Саму CMS	setup.info.php, instprocessor.php
2	assets/modules/store/installer/	Пакеты через Store	setup.info.php, instprocessor-fast.php
4.2. Корневой install/
Назначение: установка самой CMS на чистую БД.

Файлы с нашими патчами:

install/setup.info.php — 2 правки ?? 0 для shareparams

install/instprocessor.php — 4 правки ?? '' для moduleChunk

CHANGELOG: раздел 16.

4.3. Store installer assets/modules/store/installer/
Назначение: установка отдельных пакетов через модуль Store.

Файлы с нашими патчами:

assets/modules/store/installer/setup.info.php — $params['input_options'] ?? '' (строка ~66)

assets/modules/store/installer/instprocessor-fast.php — удалён $dbase . '.' из DELETE-запроса (строка ~209), выбор языка через manager_language

CHANGELOG: раздел 14.10.

4.4. Почему оба
Оба инсталлера — от одного предка MODX:

Одинаковый setup.info.php — парсит дескрипторы пакетов.

Одинаковый instprocessor*.php — устанавливает элементы (TV, сниппеты, чанки, модули).

Если править только один — второй при следующем использовании выдаст ту же ошибку. Патчить всегда оба.

4.5. Что осталось в Store installer после чистки
Осталось 6 файлов (см. CHANGELOG 14.9):

functions.php

instprocessor-fast.php

setup.info.php

sqlParser.class.php

lang/russian-UTF8.inc.php

lang/english.inc.php

Удалено 8 файлов + 2 папки (UI-установщик не нужен — Store ставит пакеты через AJAX без UI).

4.6. Техдолг Store installer
Не закрыт (см. CHANGELOG 14.14):

16 вызовов mysql_error() — удалена в PHP 7. Сработает при SQL-ошибке → Fatal.

$count_new_name, $props, $dbase могут быть undefined в редких ветках.

getCreateDbCategory($category, $sqlParser) — лишний аргумент.

CSRF — api.php и core.php не проверяют токен (раздел 9.1, задача C).

Нет уведомлений об обновлениях пакетов.

Установка «из архива» не проверяет содержимое.

Конец части 4.
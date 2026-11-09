CONTEXT-09.md — Патчи корневого install/
Часть 9 из 19. Читать после CONTEXT.md, CONTEXT-01.md — CONTEXT-08.md.

9. ЧТО СДЕЛАНО: ПАТЧИ КОРНЕВОГО INSTALL/
CHANGELOG: раздел 16.
patches-manifest.md: раздел «КОРНЕВОЙ INSTALL/ — 2 ФАЙЛА».

Отдельный слой от патчей ядра. Правки в установщике самой CMS. Обнаружены при установке дистрибутива на чистую БД (09.10.2026).

9.1. install/setup.info.php
Правка A — строка ~168 (блок модулей):

diff
- (int)$params['shareparams'],
+ (int)($params['shareparams'] ?? 0),
Правка B — строка ~173 (проверка зависимостей):

diff
- if ((int)$params['shareparams'] || !empty($params['dependencies'])) {
+ if ((int)($params['shareparams'] ?? 0) || !empty($params['dependencies'])) {
Причина: warning «Undefined array key "shareparams"» при установке модулей без параметра @shareparams.

9.2. install/instprocessor.php
Строка ~471 (блок Install Chunks):

diff
- $name = mysqli_real_escape_string($conn, $moduleChunk[0]);
- $desc = mysqli_real_escape_string($conn, $moduleChunk[1]);
- $category = mysqli_real_escape_string($conn, $moduleChunk[3]);
- $overwrite = mysqli_real_escape_string($conn, $moduleChunk[4]);
+ $name = mysqli_real_escape_string($conn, $moduleChunk[0] ?? '');
+ $desc = mysqli_real_escape_string($conn, $moduleChunk[1] ?? '');
+ $category = mysqli_real_escape_string($conn, $moduleChunk[3] ?? '');
+ $overwrite = mysqli_real_escape_string($conn, $moduleChunk[4] ?? '');
Причина: deprecated warning «Passing null to parameter #2 ($string)» в PHP 8.5.

9.3. Отличие от патчей Store installer
Слой	Папка	Назначение
Корневой install/	install/	Установка самой CMS
Store installer	assets/modules/store/installer/	Установка пакетов через Store
Оба инсталлера происходят от одного предка (MODX), отсюда схожие баги. Патчить надо оба комплекта.

См. также: часть 4 (Два инсталлера — не путать).

9.4. Где применено
Локальная копия C:\laragon\www\vol.test\install\

Дистрибутив D:\VoloLution-CMS-1.4.37\install\

Форк github.com/srkkrs77/vololution — после создания

9.5. Как проверить, что патчи на месте
Корневой install/setup.info.php:

Открыть файл, найти строки с shareparams. Должно быть (int)($params['shareparams'] ?? 0) в двух местах.

Корневой install/instprocessor.php:

Найти блок Install Chunks (около строки 471). Должно быть $moduleChunk[0] ?? '', [1] ?? '', [3] ?? '', [4] ?? ''.

Store installer assets/modules/store/installer/setup.info.php:

Найти строку 66. Должно быть $params['input_options'] ?? ''.

Store installer assets/modules/store/installer/instprocessor-fast.php:

Найти DELETE-запрос около строки 209. Не должно быть $dbase . '.' в префиксе таблицы.

9.6. Что НЕ трогаем в install/
install/setup.sql — SQL ядра, не патчим.

install/config.inc.tpl — там наши CSRF-функции (см. часть 5). Но сам tpl-файл — часть установщика, при обновлении апстрима конфликты решаем вручную.

Остальные файлы install/assets/ — наши дескрипторы (seoVolo, store). Не трогаются апстримом.

9.7. При обновлении апстрима
ФАЗА 1, шаг 15b (старый чек-лист из patches-manifest):

Проверить корневой install/ — если CE обновил setup.info.php или instprocessor.php, заново наложить патчи из блока «Корневой install/».

В форк-модели: при git merge upstream/1.4.x — конфликты в этих файлах разрешаем вручную, сверяясь с этим разделом.

Конец части 9.
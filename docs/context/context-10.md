CONTEXT-10.md — Что в работе
Часть 10 из 19. Читать после CONTEXT.md, CONTEXT-01.md — CONTEXT-09.md.

10. ЧТО В РАБОТЕ

10.1. Форк на GitHub — ✅ ВЫПОЛНЕНО (09.11.2026)
Дата решения: 10.10.2026 (см. CHANGELOG.md, раздел 18).

Статус: Проект успешно залит на GitHub.
- Создан и настроен `.gitignore` (исключены vendor, кэш, config.inc.php).
- Настроена авторизация через Personal Access Token (PAT).
- Ветка `main` синхронизирована с `github.com/srkkrs77/vololution`.

10.2. Проблема на проде studio-milstein.de
10.2.1. SimpleGallery — не создалась таблица sg_images
Дата: 10.10.2026.

Симптом: при удалении ресурса в админке:
Table 'dbs16208630.eh2v_sg_images' doesn't exist
SQL > DELETE FROM `dbs16208630`.`eh2v_sg_images` WHERE `sg_rid` IN (1)

Причина: при установке SimpleGallery на хостинге не создалась таблица sg_images. Установщик пакета не смог выполнить SQL.

Временное решение (применено): таблица создана вручную SQL-запросом.

Стратегическое решение: SchemaGuard — плагин на OnManagerPageInit, проверяет и создаёт отсутствующие таблицы сторонних пакетов. Пакеты сторонних авторов (Pathologic, Grinyaha и т.д.) не патчим — сторож работает снаружи.

10.2.2. SimpleGallery — InvalidStateError в CSRF-JS
Дата: 10.10.2026 — 11.10.2026.

Симптом: при открытии вкладки «Галерея» в форме документа:
Server-Fehler: 0
InvalidStateError: Failed to execute 'setRequestHeader' on 'XMLHttpRequest':
The object's state must be OPENED.

Причина: в header.inc.php было два CSRF-JS-блока. Второй перехватывал XMLHttpRequest.prototype.open и вызывал setRequestHeader до origOpen.apply(). Это ломало AJAX-запросы SimpleGallery.

Решение (11.10.2026): два блока объединены в один, setRequestHeader вызывается в send, добавлены isSameOrigin / isStateChanging, try/catch, HTMLFormElement.prototype.submit, MutationObserver.

CHANGELOG: раздел 19. Файл manager/includes/header.inc.php перезаписан целиком.
Задача D техдолга CSRF — ✅ закрыта.

10.3. Отложено (из старых задач)
- sitemap_* TV — созданы в SEO-пакете, но DLSitemap нужно дополнительно настроить через параметры сниппета, чтобы он их использовал.
- Schema.org Product — без seo_sku, seo_price. Соответствующие TV не создаются.
- Hreflang — требует активного EvoBabel. На мультиязычном сайте не проверено.
- Переписать SimpleGallery под классические формы — не делать сейчас, отложить до новой архитектуры (src/, PSR-4, PDO). Ориентировочный объём — 2–3 дня работы (~1500–2000 строк). Логичное место — Этап 3 или 5 roadmap.

10.4. Техдолг CSRF (остался)
Из раздела 9.1 CHANGELOG.md:
A. Ротация токенов на login/logout (4 точки) — ⬜
B. Origin / Referer check в checkCsrfToken() — ⬜
C. Защита connector'ов модулей (Store, MultiTV, evoSearch, editDocs) — ⬜
D. Убрать дублирование JS в header.inc.php — ✅ закрыта 11.10.2026

10.5. Техдолг пакетов
editdocs.class.php — остались места без ??, не падают.
save_content.processor.php, строка 614 — $use_udperms без ??, warning был один раз.

10.6. Техдолг Store
instprocessor-fast.php — 16 вызовов mysql_error() (удалена в PHP 7).
instprocessor-fast.php — $count_new_name, $props, $dbase могут быть undefined.
instprocessor-fast.php — getCreateDbCategory($category, $sqlParser) — лишний аргумент.
CSRF в api.php и core.php — раздел 9.1, задача C.
Нет уведомлений об обновлениях пакетов.
Установка «из архива» не проверяет содержимое ZIP.

10.7. Техдолг SEO-пакета (остатки)
- sitemap_* TV — через параметры DLSitemap.
- Schema.org Product — без seo_sku, seo_price.
- Hreflang — требует активного EvoBabel.

10.8. Выполнено 09.11.2026
- **Индикатор длины description:** Реализован через `mm_rules.tpl` (v2.1.0) и JS. Автоматически определяет ID TV и работает на RU, EN, DE.
- **Фиксы PHP 8.4:** Исправлен установщик (`install/actions/action_options.php`, `instprocessor.php`, `setup.info.php`). Ошибок `in_array` больше нет.
- **Фиксы seoVolo:** Исправлены HTML-сущности (`&auml;`) в мета-тегах (Разделы 8, 10, 11, 15). Поменян приоритет OG-картинки (`og_default_image` теперь важнее логотипа).
- **GDPR:** Создан сниппет `cookieConsent` (v1.1.0, 6 языков) и добавлен патч отложенной сессии в `index.php`. В `install/` добавлен дескриптор.

10.9. Следующие шаги (Приоритеты на после 09.11.2026)
1. **SchemaGuard** — плагин-сторож для таблиц пакетов.
2. **Настройка upstream** — привязка локального репозитория к оригинальной Evo (`evocms-community/evolution`) для безопасного мержа security-фиксов.
3. **Инфраструктура форка** — создание `composer.json`, папки `src/` и PSR-4 автозагрузки.

Конец части 10.
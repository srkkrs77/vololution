# Studio Milstein — кэш, URL, языки

**Проект:** studio-milstein.de  
**Дата:** 15.10.2026  
**Проблемы:** UTM-ссылки дают 404, HTML кэшируется браузером (правки не видны), DE/EN-переключатель показывает неверный язык.

---

## 1. Итоговый `.htaccess`

Файл лежит в корне сайта: `/.htaccess`  
Ниже — **полная рабочая версия**, включая стандартные правила Evo и все наши правки.

```apache
# For full documentation and other suggested options, please see
# http://rtfm.modx.com/evolution/1.0/administration/friendly-url-solutions
# including for unexpected logouts in multi-server/cloud environments
# and especially for the first three commented out rules

#php_flag register_globals Off
AddDefaultCharset utf-8
#php_value date.timezone Europe/Moscow

#Options +FollowSymlinks
RewriteEngine On
RewriteBase /

# --- НАЧАЛО: Правила перенаправления (совместимо с Evo) ---

# 1. Принудительный HTTPS для всех доменов
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]

# 2. Убираем www для studio-milstein.de
RewriteCond %{HTTP_HOST} ^www\.studio-milstein\.de$ [NC]
RewriteRule ^(.*)$ https://studio-milstein.de/$1 [R=301,L]

# 3. Перенаправляем studio-milstein.com (и www) на .de
RewriteCond %{HTTP_HOST} ^(www\.)?studio-milstein\.com$ [NC]
RewriteRule ^(.*)$ https://studio-milstein.de/en/ [R=301,L]

# --- КОНЕЦ: Правила перенаправления ---

# Fix Apache internal dummy connections from breaking [(site_url)] cache
RewriteCond %{HTTP_USER_AGENT} ^.*internal\ dummy\ connection.*$ [NC]
RewriteRule .* - [F,L]

#redirect http to https
#RewriteCond %{HTTP:SSL} !=1 [NC]
#RewriteRule ^(.*) https://%{SERVER_NAME}/$1 [L,R=301]

# Rewrite domain.com -> www.domain.com -- used with SEO Strict URLs plugin
#RewriteCond %{HTTP_HOST} !^$
#RewriteCond %{HTTP_HOST} !^www\. [NC]
#RewriteCond %{HTTP_HOST} (.+)$
#RewriteRule ^(.*)$ http://www.%1/$1 [R=permanent,L] . 

# without www
#RewriteCond %{HTTP_HOST} .
#RewriteCond %{HTTP_HOST} !^example\.com [NC]
#RewriteRule (.*) http://example.com/$1 [R=301,L]

# without www all domains
#RewriteCond %{HTTP_HOST} .
#RewriteCond %{HTTP_HOST} ^www.(.*)$ [NC]
#RewriteRule ^(.*)$ http://%1/$1 [R=301,L]

# 301 that removes index.php?q= from all urls
#RewriteCond %{THE_REQUEST} \s/+index\.php\?&q=([^\s&]+) [NC]
#RewriteRule ^ /%1? [R=301,L]
#RewriteCond %{THE_REQUEST} \s/+index\.php\?q=([^\s&]+) [NC]
#RewriteRule ^ /%1? [R=301,L]

# Exclude /assets and /manager directories and images from rewrite rules
RewriteRule ^(manager|assets|js|css|images|img)/.*$ - [L]
RewriteRule \.(jpg|jpeg|png|gif|ico)$ - [L]

# --- Убираем UTM-метки до Evo (иначе 404) ---
RewriteCond %{QUERY_STRING} ^(.+)$ [NC]
RewriteCond %{QUERY_STRING} (^|&)(utm_[^&]*|gclid|fbclid|yclid|_openstat|from|referrer)=[^&]*&?(.*)$ [NC]
RewriteRule ^(.*)$ /$1?%1%3 [L,R=301]

# For Friendly URLs
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]

# Reduce server overhead by enabling output compression if supported.
#php_flag zlib.output_compression On
#php_value zlib.output_compression_level 5

# Управление кэшем браузера для HTML, CSS и JS
<IfModule mod_headers.c>
    # Статика — кэшируем нормально
    SetEnvIf Request_URI "\.(css|js|jpg|jpeg|png|gif|webp|svg|woff|woff2|ttf|otf|eot|ico|pdf)$" IS_STATIC
    # HTML и всё остальное — не кэшируем
    Header set Cache-Control "no-cache, no-store, must-revalidate, max-age=0" env=!IS_STATIC
    Header set Pragma "no-cache" env=!IS_STATIC
    Header set Expires "0" env=!IS_STATIC
</IfModule>
# --- КОНЕЦ: Кэш браузера ---

2. Разбор каждого правила
2.1. HTTPS-редирект
apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
Условие: запрос пришёл по http://.

Действие: редирект 301 на тот же адрес с https://.

%{HTTP_HOST} — берёт текущий домен (не хардкодим studio-milstein.de, работает и на тестовых поддоменах).

[R=301,L] — постоянный редирект, остановить обработку.

2.2. Убираем www
apache
RewriteCond %{HTTP_HOST} ^www\.studio-milstein\.de$ [NC]
RewriteRule ^(.*)$ https://studio-milstein.de/$1 [R=301,L]
Условие: домен www.studio-milstein.de (регистронезависимо).

Действие: редирект на studio-milstein.de без www.

[NC] — case-insensitive (WWW и www — одно и то же).

2.3. Домен .com → .de
apache
RewriteCond %{HTTP_HOST} ^(www\.)?studio-milstein\.com$ [NC]
RewriteRule ^(.*)$ https://studio-milstein.de/$1 [R=301,L]
Условие: домен studio-milstein.com с www или без.

Действие: редирект на studio-milstein.de.

(www\.)? — необязательная группа: ловит и www.studio-milstein.com, и studio-milstein.com.

⚠️ Важно: для этого правила у .com должен быть SSL. Иначе браузер покажет «небезопасное соединение» до редиректа. Оптимально — настроить редирект .com → .de в панели STRATO, а не в .htaccess.

2.4. UTM-метки → 301 на чистый URL
apache
RewriteCond %{QUERY_STRING} ^(.+)$ [NC]
RewriteCond %{QUERY_STRING} (^|&)(utm_[^&]*|gclid|fbclid|yclid|_openstat|from|referrer)=[^&]*&?(.*)$ [NC]
RewriteRule ^(.*)$ /$1?%1%3 [L,R=301]
Зачем: без этого правила Evo получает q=studio.html&utm_source=test и ищет документ с алиасом studio.html&utm_source=test. Такого нет → 404. Все рекламные ссылки (?utm_source=..., ?gclid=..., ?fbclid=...) ломались.

Как работает:

Условие 1: в строке запроса есть хоть что-то (^(.+)$).

Условие 2: regex ловит UTM-параметр в строке запроса:

(^|&) — начало строки или &

(utm_[^&]*|gclid|fbclid|yclid|_openstat|from|referrer) — имя параметра

= — знак равенства

[^&]* — значение (до следующего &)

&?(.*)$ — опциональный & и остаток строки

Действие: редирект 301 на /$1?%1%3

$1 — путь без строки запроса

%1 — первая группа из условия 1 (всё до UTM)

%3 — третья группа (всё после UTM)

Что это даёт:

?utm_source=test → 301 → / (чистый URL)

?foo=1&utm_source=test&bar=2 → 301 → /?foo=1&bar=2 (другие параметры сохраняются)

Метрика: Google Analytics и Яндекс.Метрика ловят UTM из JS (window.location.search) до редиректа. Данные не теряются.

⚠️ Внимание: from и referrer в списке — это распространённые имена. Если на сайте есть свои параметры с такими именами — они тоже будут резаться. Если не нужны — убери их из regex, оставь только utm_*|gclid|fbclid|yclid|_openstat.

2.5. Cache-Control для HTML
apache
<IfModule mod_headers.c>
    SetEnvIf Request_URI "\.(css|js|jpg|jpeg|png|gif|webp|svg|woff|woff2|ttf|otf|eot|ico|pdf)$" IS_STATIC
    Header set Cache-Control "no-cache, no-store, must-revalidate, max-age=0" env=!IS_STATIC
    Header set Pragma "no-cache" env=!IS_STATIC
    Header set Expires "0" env=!IS_STATIC
</IfModule>


Зачем: стандартный <FilesMatch "\.(html|css|js)$"> не работает в Evo. URL страниц вида https://studio-milstein.de/en/studio.html — с .html, но Friendly URLs без расширения тоже существуют (https://studio-milstein.de/ — главная). А браузеры кэшируют HTML надолго, если сервер не даёт Cache-Control.

Как работает:

SetEnvIf Request_URI ... — если URL заканчивается на .css, .js, .jpg и т.д. — ставим переменную окружения IS_STATIC.

Header set Cache-Control ... env=!IS_STATIC — заголовок добавляется для всего, кроме статики (env=!IS_STATIC означает «если НЕ IS_STATIC»).

Header set Pragma / Expires — дополнительные заголовки для старых браузеров, дублируют Cache-Control.

Итого:

HTML-страницы Evo → no-cache, no-store, must-revalidate, max-age=0 → браузер каждый раз проверяет свежесть.

CSS, JS, картинки → кэшируются нормально → быстрее для посетителя.

Проверка:

F12 → Network → клик по HTML-запросу → вкладка Headers → Response Headers.

Должны быть две строки Cache-Control:

private, must-revalidate (от Evo)

no-cache, no-store, must-revalidate, max-age=0 (от .htaccess)

Браузер берёт самую строгую → no-cache.

⚠️ Если mod_headers не подключён — блок молча игнорируется, HTML кэшируется. Проверить можно через phpinfo() → apache2handler → Loaded Modules → должен быть mod_headers.

3. Что делали — хронология фиксов
3.1. UTM-метки дают 404
Симптом: https://studio-milstein.de/en/studio.html?utm_source=test → 404.
Причина: QSA в правиле Friendly URLs приклеивал utm_source=test к q, Evo искал несуществующий алиас.
Решение: правило редиректа, срезающее UTM до Evo.

3.2. HTML кэшируется браузером — правки не видны
Симптом: после правки в админке посетители видят старую версию. Помогает только Ctrl+F5.
Причина: сервер не отдавал Cache-Control: no-cache для HTML-страниц. Стандартный <FilesMatch "\.html$"> в Evo не срабатывает (URL без расширения).
Решение: SetEnvIf + Header с env=!IS_STATIC.

3.3. DE/EN-переключатель показывает неверный язык
Симптом: на /en/studio.html активна DE, а должна быть EN.
Причина: конфликт EvoBabel + серверного кэша Evo (docid_N.pageCache.php). Кэш хранит HTML с языком из сессии того, кто его сгенерировал.
Решение: на языковых корневых документах (/ и /en/) снять галку «Cache aktiv». После этого кэш Cache-Control: no-cache для HTML работает правильно.

4. Проверка после правок
UTM-ссылка:
https://studio-milstein.de/en/studio.html?utm_source=test
→ 301 → https://studio-milstein.de/en/studio.html → 200 OK.

Cache-Control:
F12 → Network → HTML-запрос → Response Headers → есть no-cache, no-store....

Правки видны сразу:
Изменить pagetitle в админке → сохранить → открыть на фронте без Ctrl+F5. Должно быть видно.

Язык:
/ → DE активна. /en/studio.html → EN активна.

Статика кэшируется:
F12 → Network → CSS-запрос → Response Headers → нет no-cache (кэшируется по дефолту STRATO).

iPhone клиентки:
Попросить: Safari → Настройки → Очистить историю и данные сайтов.
После этого открыть сайт заново.

5. Грабли
5.1. FilesMatch "\.html$" не покрывает Evo-страницы
Evo через Friendly URLs отдаёт страницы без расширения: https://studio-milstein.de/, https://studio-milstein.de/en/studio.html (с .html), https://studio-milstein.de/studio/ (без).
FilesMatch работает только с расширениями в имени файла — а сервер отдаёт index.php, не HTML-файл.

Решение: использовать SetEnvIf Request_URI — он работает с URL, а не с именем файла.

5.2. Два Cache-Control — это нормально
Evo добавляет свой Cache-Control: private, must-revalidate. Наш .htaccess добавляет второй. Браузер объединяет и берёт самую строгую директиву. Ничего страшного — работает.

5.3. UTM-редирект — только для HTTP-метода GET
Правило работает с URL. POST-запросы (формы) не задевает. Если понадобится резать UTM и в POST — отдельная задача.

5.4. .com без SSL
Редирект .com → .de в .htaccess не сработает корректно без SSL на .com. Браузер покажет предупреждение до того, как отправит запрос.
Решение: настроить редирект в панели STRATO (Weiterleitung), а не в .htaccess.

5.5. STRATO и mod_headers
STRATO обычно имеет mod_headers включённым. Если блок <IfModule mod_headers.c> не срабатывает — проверить через phpinfo(): раздел apache2handler → Loaded Modules → mod_headers.

Если не включён — обратиться в поддержку STRATO.

6. Связанные документы
FormLister — форма обратной связи: docs/formlister-project-form.md

Кэш Evo — настройки: системные настройки → Сайт → cache_type = 1

Дата создания: 15.10.2026
Автор: по итогам сессии диагностики
Проект: studio-milstein.de
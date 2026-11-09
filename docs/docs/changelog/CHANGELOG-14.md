CHANGELOG-14.md — Раздел 18–19 (последняя часть)
Часть 14 из 14. Форк и новая архитектура + исправление CSRF-JS.

markdown
## РАЗДЕЛ 18. ФОРК И НОВАЯ АРХИТЕКТУРА

**Дата решения:** 10.10.2026

### 18.1. Смена концепции

**Было:** патч-сборка поверх CE 1.4.37. Патчи ядра — отдельный реестр, накат вручную при обновлении CE. CLI-скрипт для автоматизации (этап 7.7).

**Стало:** **форк** `evocms-community/evolution` → `srkkrs77/vololution`. Своя ветка разработки. Патчи ядра — обычные коммиты. Апстрим — источник security-фиксов, мержим выборочно.

**Причина:** косметические патчи поверх чужого проекта — тупик. Цель — постепенная модернизация Evo под современные стандарты. Для этого нужен собственный репозиторий.

### 18.2. Что осталось в силе

- 13 патчей ядра (раздел 1).
- CSRF-защита (раздел 3).
- 6 патченных пакетов (раздел 4).
- 10 пакетов без правок (раздел 5).
- Свой Store (раздел 14).
- SEO-пакет seoVolo (раздел 15).
- Патчи корневого `install/` (раздел 16).

### 18.3. Что меняется

| Область | Было | Стало |
|---|---|---|
| **Репозиторий** | Локальные копии + `github.com/srkkrs77/vololution` для packages | Форк `evocms-community/evolution` |
| **Обновление апстрима** | Ручной накат по манифесту | `git merge upstream/1.4.x` |
| **Патчи ядра** | Отдельный реестр + CLI-скрипт | Обычные коммиты |
| **`patches-manifest.md`** | Инструкция для ручного наката | Реестр отличий (для разрешения конфликтов) |
| **CLI-скрипт для патчей (7.7)** | ⬜ Планировался | ❌ Отменён |
| **`CONTEXT.md`, `roadmap.md`, `patches-manifest.md`** | Описывают патч-модель | Переписаны под форк |

### 18.4. Философия простоты

**Сохраняем простоту Evo для разработчика:**

- Сниппеты `[[snippet? &param=value]]`.
- Чанки `[+placeholder+]`, `[*tv*]`, `[[snippet]]`.
- TV, плагины, модули — как есть.
- Прямой доступ к `$modx` в коде.

**Запрещено окончательно:**

- Laravel, Symfony, CodeIgniter, Yii.
- Twig, Blade, Smarty, Mustache, Latte.
- Doctrine, Eloquent, Propel.
- SPA (Vue, React, Angular, Svelte) для управления.
- Тяжёлые DI-контейнеры.
- AJAX-first архитектура.

**Разрешено:**

- Composer — только для библиотек.
- PSR-4 автозагрузка.
- Типизация PHP 8.4+.
- PDO для нового кода.
- Минимальный AJAX — только автокомплит, предпросмотр, drag&drop.
- PSR-3, PSR-14, PSR-7, PSR-15 — точечно.

### 18.5. Архитектурные принципы

- **Composer** + PSR-4 autoload.
- **PSR-12** + `declare(strict_types=1)` + типизация.
- **PSR-3, PSR-11, PSR-14, PSR-7, PSR-15** — точечно.
- **PDO** для нового слоя БД; старый DBAPI остаётся для extras.
- **`src/`** с namespace `Vololution\...`.
- **Обратная совместимость с extras — критична.**

### 18.6. Политика апстрима

- `upstream` = `evocms-community/evolution`.
- Мержим **не каждый релиз**, а security-фиксы (обязательно), крупные багфиксы (по ситуации), новые фичи (только нужные).
- Мерж — в отдельной ветке `merge/upstream-YYYY-MM`, тест на `vol.test`, потом в `main`.
- После мержа — синхронизация `docs/patches-manifest.md`.

### 18.7. Первые шаги

1. Установка GitHub Desktop.
2. Форк репозитория `evocms-community/evolution`.
3. Клонирование форка локально.
4. Перенос наработок (13 патчей ядра, Store, SEO-пакет).
5. Инфраструктура: `composer.json`, `src/`, `.gitignore`.
6. SchemaGuard — плагин-сторож для таблиц пакетов.

### 18.8. Проблема на проде (SimpleGallery)

**Дата:** 10.10.2026  
**Сайт:** `studio-milstein.de`

При удалении ресурса:
Table 'dbs16208630.eh2v_sg_images' doesn't exist
SQL > DELETE FROM dbs16208630.eh2v_sg_images WHERE sg_rid IN (1)

text

**Причина:** SimpleGallery не создал таблицу `sg_images` при установке на хостинге.

**Временное решение:** таблица создана вручную SQL-запросом.

**Стратегическое решение:** SchemaGuard (этап 0.3 roadmap) — плагин на `OnManagerPageInit`, проверяет и создаёт отсутствующие таблицы сторонних пакетов.

---

## РАЗДЕЛ 19. ИСПРАВЛЕНИЕ CSRF-JS В HEADER.INC.PHP

**Дата:** 11.10.2026  
**Связано:** раздел 9.1, задача D (техдолг CSRF)  
**Файл:** `manager/includes/header.inc.php`

### 19.1. Проблема

На проде `studio-milstein.de` при открытии вкладки SimpleGallery в форме документа:
Server-Fehler: 0
InvalidStateError: Failed to execute 'setRequestHeader' on 'XMLHttpRequest':
The object's state must be OPENED.

text

Вкладка не открывалась, галерея не отображалась.

### 19.2. Причина

В `header.inc.php` было **два** CSRF-JS-блока:

1. **Первый** (в начале `<head>`) — корректный. Перехватывал `XMLHttpRequest.prototype.open` и `XMLHttpRequest.prototype.send` раздельно, `setRequestHeader` вызывался в `send` — после `open`.

2. **Второй** (после блока с `evo.lang`) — **сломанный**. Перехватывал только `open`, и внутри него сразу вызывал `this.setRequestHeader(...)` **до** вызова оригинального `origOpen.apply()`. По спецификации XHR `setRequestHeader` нельзя вызывать до `open()` — отсюда `InvalidStateError`.

Второй блок дополнительно дублировал логику первого: перехват `submit`, `fetch`, XHR. Это и есть техдолг из раздела 9.1, задача D.

Первым блоком SimpleGallery не ломался (там всё правильно). Ломался именно вторым — он выполнялся позже и перезаписывал `XMLHttpRequest.prototype.open` сломанной версией.

### 19.3. Решение

**Два блока объединены в один.** Итоговый блок:

- `setRequestHeader` вызывается в `send`, а не в `open` — `InvalidStateError` исчезает.
- Из второго блока перенесено полезное:
  - перехват `HTMLFormElement.prototype.submit` (лечит прямые вызовы `form.submit()` — Системная конфигурация),
  - `MutationObserver` для динамически созданных форм.
- Дубли убраны.
- Сохранены проверки `isSameOrigin` (токен не летит на чужие домены) и `isStateChanging` (не добавляется к GET/HEAD/OPTIONS).
- Правка обёрнута в `try/catch` — если `setRequestHeader` по какой-то причине упадёт, это не сломает AJAX-запрос.

**Файл перезаписан целиком.**

### 19.4. Что проверено

- ✅ Вкладка SimpleGallery в форме документа — открывается, галерея видна.
- ✅ Загрузка изображения через SimpleGallery — работает.
- ✅ Системная конфигурация — сохраняется.
- ✅ Сохранение документа — работает.
- ✅ Console браузера (F12) — ошибок `InvalidStateError` нет.

### 19.5. Новая версия `manager/includes/header.inc.php`

Ключевое изменение — **единственный CSRF-JS-блок** между `OnManagerMainFrameHeaderHTMLBlock` и блоком `evo.lang`:

```javascript
(function() {
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!tokenMeta) return;
    var csrfToken = tokenMeta.getAttribute('content');
    var managerOrigin = window.location.origin;

    function isSameOrigin(url) {
        try {
            return new URL(url, window.location.href).origin === managerOrigin;
        } catch (e) {
            return false;
        }
    }

    function isStateChanging(method) {
        method = String(method || 'GET').toUpperCase();
        return method !== 'GET' && method !== 'HEAD' && method !== 'OPTIONS';
    }

    function injectToken(form) {
        if (!form || form.tagName.toLowerCase() !== 'form') return;
        if ((form.method || '').toLowerCase() !== 'post') return;
        if (form.querySelector('input[name="csrf_token"], input[name="_token"]')) return;
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = csrfToken;
        form.appendChild(input);
    }

    // 1. Перехват события submit
    document.addEventListener('submit', function(e) {
        injectToken(e.target);
    }, true);

    // 2. Перехват прямых вызовов form.submit() — Системная конфигурация
    var origFormSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function() {
        injectToken(this);
        return origFormSubmit.apply(this, arguments);
    };

    // 3. MutationObserver — динамически созданные формы
    if (window.MutationObserver) {
        new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                m.addedNodes.forEach(function(node) {
                    if (node.nodeType !== 1) return;
                    if (node.tagName === 'FORM') injectToken(node);
                    if (node.querySelectorAll) {
                        node.querySelectorAll('form').forEach(injectToken);
                    }
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    }

    // 4. Перехват fetch
    var origFetch = window.fetch;
    if (origFetch) {
        window.fetch = function(url, opts) {
            opts = opts || {};
            var method = opts.method || 'GET';
            if (isStateChanging(method) && isSameOrigin(typeof url === 'string' ? url : url.url)) {
                if (opts.headers instanceof Headers) {
                    opts.headers.set('X-CSRF-TOKEN', csrfToken);
                } else {
                    opts.headers = opts.headers || {};
                    opts.headers['X-CSRF-TOKEN'] = csrfToken;
                }
            }
            return origFetch.call(this, url, opts);
        };
    }

    // 5. Перехват XMLHttpRequest — БЕЗ ошибки InvalidStateError
    var origOpen = XMLHttpRequest.prototype.open;
    var origSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function(method, url) {
        this._evoCsrfMethod = method;
        this._evoCsrfUrl = url;
        return origOpen.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function() {
        if (isStateChanging(this._evoCsrfMethod) && isSameOrigin(this._evoCsrfUrl)) {
            try {
                this.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            } catch (e) {}
        }
        return origSend.apply(this, arguments);
    };
})();
Удалён второй <script>-блок, который начинался сразу после evo.locale = '...'.

19.6. Где применено
Прод studio-milstein.de — заменён файл.

Локальная копия C:\laragon\www\vol.test\manager\includes\header.inc.php — заменить.

Дистрибутив D:\VoloLution-CMS-1.4.37\manager\includes\header.inc.php — заменить.

19.7. Уроки
Дублирование кода — источник багов. Два блока делали одно и то же, но по-разному. Один работал, второй ломал.

CSRF-перехват XHR должен вызывать setRequestHeader в send, не в open. Это неочевидно и легко ошибиться.

Проверки isSameOrigin / isStateChanging обязательны — без них токен улетит на сторонние домены и на GET-запросы.

Тестировать правки ядра на сторонних пакетах (SimpleGallery, MultiTV и т.д.), а не только на стандартных формах админки. Наш CSRF-патч 3.4 обкатывался только на ядре — отсюда пропустили.

Конец CHANGELOG.
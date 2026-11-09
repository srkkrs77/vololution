CHANGELOG-04.md — Раздел 3.3–3.5
Часть 4 из 14. CSRF: accesscontrol + header + config.inc.php.

markdown
### 3.3 `manager/includes/accesscontrol.inc.php`

**Где:** внутри `else { ... }` после `$modx->updateValidatedUserSession();`

**Что вставлено:**

```php
// === CSRF check for POST ===
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfAction = isset($_REQUEST['a']) ? (int)$_REQUEST['a'] : 0;
    $csrfExcept = array(8, 67, 112, 118);
    $isCoreAjax = isset($_REQUEST['updateMsgCount']) || isset($_REQUEST['ajaxa']);
    if (!$isCoreAjax && !in_array($csrfAction, $csrfExcept, true)) {
        checkCsrfToken();
    }
}
// === /CSRF check ===
Whitelist (действия, не требующие токена):

a	Действие
8	logout
67	remove locks
112	execute module
118	settings ajax
Исключения по параметрам: updateMsgCount, ajaxa — core AJAX (не требуют токена).

Что это даёт: все POST-запросы в админке (кроме whitelist и core AJAX) проверяются на CSRF-токен. Провал → checkCsrfToken() → 403 + запись в Event Log.

3.4 manager/includes/header.inc.php — инъекция токена
Meta-тег — после <title>Evolution CMS</title>:

php
<?= csrfTokenMeta() ?>
JS-блок — перед <script src="media/script/main.js">.

⚠️ УСТАРЕЛ 11.10.2026. Актуальная версия — в разделе 19. Старая содержала сломанный XHR-перехват (setRequestHeader в open до origOpen.apply()), вызывавший InvalidStateError в SimpleGallery.

Что было (для истории):

<details> <summary>Старый сломанный JS-блок</summary>
html
<script>
(function() {
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;
    var csrfToken = token.getAttribute('content');

    function injectToken(form) {
        if (!form || form.tagName.toLowerCase() !== 'form') return;
        if ((form.method || '').toLowerCase() !== 'post') return;
        if (form.querySelector('input[name="csrf_token"]') || form.querySelector('input[name="_token"]')) return;
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = csrfToken;
        form.appendChild(input);
    }

    document.addEventListener('submit', function(e) {
        injectToken(e.target);
    }, true);

    var origSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function() {
        injectToken(this);
        return origSubmit.apply(this, arguments);
    };

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

    var origFetch = window.fetch;
    window.fetch = function(url, opts) {
        opts = opts || {};
        opts.headers = opts.headers || {};
        if (opts.headers instanceof Headers) {
            opts.headers.set('X-CSRF-TOKEN', csrfToken);
        } else {
            opts.headers['X-CSRF-TOKEN'] = csrfToken;
        }
        return origFetch.call(this, url, opts);
    };

    var origOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function() {
        this.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        return origOpen.apply(this, arguments);
    };
})();
</script>
Проблема: setRequestHeader вызывался внутри перехвата open до вызова origOpen.apply(). По спецификации XHR — ошибка InvalidStateError: The object's state must be OPENED. Ломало все AJAX-запросы сторонних пакетов (SimpleGallery).

</details>
3.5 manager/includes/config.inc.php и install/config.inc.tpl
В checkCsrfToken():

diff
- if (isset($GLOBALS['modx']) && $GLOBALS['modx']->config('debug', 0)) {
+ if (!empty($GLOBALS['modx']->config['debug'])) {
Причина: старый вызов $GLOBALS['modx']->config('debug', 0) — некорректный. В Evo 1.4 config — это свойство-массив, не метод. Правка делает проверку debug-режима корректной.

Где именно: в checkCsrfToken() — блок, который добавляет отладочную информацию к сообщению об ошибке (когда debug включён).

Файлов два, правка одинаковая:

manager/includes/config.inc.php — рабочий файл (в .gitignore, не коммитится).

install/config.inc.tpl — шаблон, из которого создаётся config.inc.php при установке. Коммитится.

text

**Конец части 4.**
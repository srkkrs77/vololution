CHANGELOG-03.md — Раздел 3.1–3.2
Часть 3 из 14. CSRF-защита: preload.functions.inc.php + install/config.inc.tpl.

markdown
## РАЗДЕЛ 3. CSRF-ЗАЩИТА

### 3.1 `manager/includes/preload.functions.inc.php`

**Метод `startCMSSession()` — было:**
```php
session_set_cookie_params($cookieExpiration, $cookiePath, $cookieDomain, $secure, true);
session_start();
$key = "modx.mgr.session.cookie.lifetime";
Стало:

php
session_set_cookie_params([
    'lifetime' => $cookieExpiration,
    'path'     => $cookiePath,
    'domain'   => $cookieDomain,
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
if (!isset($_SESSION['evo_sid_hash']) || $_SESSION['evo_sid_hash'] !== md5(session_id())) {
    session_regenerate_id(true);
    $_SESSION['evo_sid_hash'] = md5(session_id());
}
$key = "modx.mgr.session.cookie.lifetime";
Что сделано:

Замена старого формата session_set_cookie_params() (5 аргументов) на массив с httponly=true и samesite=Lax.

session_regenerate_id(true) при первом входе — защита от session fixation.

Хэш md5(session_id()) в сессии — чтобы не регенерировать ID повторно на каждом запросе.

csrf_token() и csrf_field() — было:

php
function csrf_token()
{
    if (isset($_SESSION)) {
        if (empty($_SESSION['_token'])) {
            $string = '';
            while (($len = strlen($string)) < 40) {
                $size = 40 - $len;
                $bytes = random_bytes($size);
                $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
            }
            $_SESSION['_token'] = $string;
        }
        return $_SESSION['_token'];
    }
    throw new RuntimeException('Application session store not set.');
}

function csrf_field()
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}
Стало:

php
function csrf_token()
{
    if (function_exists('getCurrentCsrfToken')) {
        return getCurrentCsrfToken();
    }
    if (isset($_SESSION)) {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
    throw new RuntimeException('Application session store not set.');
}

function csrf_field()
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
Что сделано:

csrf_token() теперь делегирует к getCurrentCsrfToken() (если определена). Единая система токенов из install/config.inc.tpl.

csrf_field() обёрнут в htmlspecialchars() — защита от XSS при выводе токена.

3.2 install/config.inc.tpl — CSRF-функции
Вставить перед include_once(MODX_MANAGER_PATH . 'includes/preload.functions.inc.php');

php
// === CSRF TOKEN MANAGEMENT ===
if (!function_exists('generateCsrfToken')) {

    define('CSRF_TOKEN_MAX', 10);
    define('CSRF_TOKEN_KEEP', 5);

    function generateCsrfToken()
    {
        $tokens = $_SESSION['csrf_tokens'] ?? [];
        if (count($tokens) >= CSRF_TOKEN_MAX) {
            $tokens = array_slice($tokens, -CSRF_TOKEN_KEEP, null, true);
        }
        $token = bin2hex(random_bytes(32));
        $tokens[$token] = true;
        $_SESSION['csrf_tokens'] = $tokens;
        return $token;
    }

    function getCurrentCsrfToken()
    {
        $tokens = $_SESSION['csrf_tokens'] ?? [];
        if (empty($tokens)) {
            return generateCsrfToken();
        }
        $keys = array_keys($tokens);
        return end($keys);
    }

    function getRequestCsrfToken()
    {
        return $_POST['csrf_token'] ?? $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    function validateCsrfToken($token = null)
    {
        if ($token === null) {
            $token = getRequestCsrfToken();
        }
        $tokens = $_SESSION['csrf_tokens'] ?? [];
        return !empty($token) && isset($tokens[$token]);
    }

    function checkCsrfToken($token = null)
    {
        if (validateCsrfToken($token)) {
            return;
        }

        $action = $_REQUEST['a'] ?? 'none';
        $userId = isset($GLOBALS['modx']) && method_exists($GLOBALS['modx'], 'getLoginUserID')
            ? $GLOBALS['modx']->getLoginUserID()
            : 'not logged in';

        $logMsg = 'CSRF token validation failed'
            . ' | action: ' . $action
            . ' | method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown')
            . ' | ip: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
            . ' | user_id: ' . $userId;

        if (isset($GLOBALS['modx']) && is_numeric($userId)) {
            $GLOBALS['modx']->logEvent(0, 3, $logMsg, 'CSRF Token Validation Failed');
        }

        $errorMsg = 'Invalid CSRF token. Please refresh the page and try again.';
        if (!empty($GLOBALS['modx']->config['debug'])) {
            $errorMsg .= "\n\nDebug: action=" . $action
                . ' | method=' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown')
                . ' | valid_tokens=' . count($_SESSION['csrf_tokens'] ?? []);
        }

        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain; charset=UTF-8');
        exit($errorMsg);
    }

    function clearCsrfTokens()
    {
        $_SESSION['csrf_tokens'] = null;
    }

    function csrfTokenField($token = null)
    {
        if ($token === null) {
            $token = getCurrentCsrfToken();
        }
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    function csrfTokenMeta($token = null)
    {
        if ($token === null) {
            $token = getCurrentCsrfToken();
        }
        return '<meta name="csrf-token" content="'
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
Что делает система:

Функция	Назначение
generateCsrfToken()	Новый токен. Хранит до 10, оставляет 5 последних
getCurrentCsrfToken()	Последний активный токен (или создаёт новый)
getRequestCsrfToken()	Токен из POST, _token, или HTTP-заголовка
validateCsrfToken()	Проверка токена против сессии
checkCsrfToken()	Валидация с логированием и 403 при провале
clearCsrfTokens()	Обнуление сессии (для logout, задача A)
csrfTokenField()	HTML input hidden
csrfTokenMeta()	Meta-тег для JS
text

**Конец части 3.**
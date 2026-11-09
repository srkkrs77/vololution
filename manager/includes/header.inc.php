<?php
if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />
    Please use the EVO Content Manager instead of accessing this file directly.");
}
$modx_manager_charset = $modx_manager_charset ?? 'UTF-8';
$mxla = $modx_lang_attribute ?? 'en';

// invoke OnManagerRegClientStartupHTMLBlock event
$evtOut = $modx->invokeEvent('OnManagerMainFrameHeaderHTMLBlock');
$modx_textdir = isset($modx_textdir) ? $modx_textdir : null;
$onManagerMainFrameHeaderHTMLBlock = is_array($evtOut) ? implode("\n", $evtOut) : '';
$textdir = $modx_textdir === 'rtl' ? 'rtl' : 'ltr';
if (!isset($modx->config['mgr_jquery_path'])) {
    $modx->config['mgr_jquery_path'] = 'media/script/jquery/jquery.min.js';
}
if (!isset($modx->config['mgr_date_picker_path'])) {
    $modx->config['mgr_date_picker_path'] = 'media/script/air-datepicker/datepicker.inc.php';
}

$body_class = '';
$theme_modes = array('', 'lightness', 'light', 'dark', 'darkness');
$theme_mode = isset($_COOKIE['MODX_themeMode']) ? $_COOKIE['MODX_themeMode'] : '';
if (!empty($theme_modes[$theme_mode])) {
    $body_class .= ' ' . $theme_modes[$theme_mode];
} elseif (!empty($theme_modes[$modx->config['manager_theme_mode'] ?? 3])) {
    $body_class .= ' ' . $theme_modes[$modx->config['manager_theme_mode'] ?? 3];
}

$css = 'media/style/' . $modx->config['manager_theme'] . '/style.css?v=' . $lastInstallTime;

if ($modx->config['manager_theme'] == 'default') {
    if (!file_exists(MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/styles.min.css')
        && is_writable(MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css')) {
        $files = array(
            'bootstrap' => MODX_MANAGER_PATH . 'media/style/common/bootstrap/css/bootstrap.min.css',
            'font-awesome' => MODX_MANAGER_PATH . 'media/style/common/font-awesome/css/font-awesome.min.css',
            'fonts' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/fonts.css',
            'forms' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/forms.css',
            'mainmenu' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/mainmenu.css',
            'tree' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/tree.css',
            'custom' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/custom.css',
            'tabpane' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/tabpane.css',
            'contextmenu' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/contextmenu.css',
            'index' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/index.css',
            'main' => MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/main.css'
        );
        $evtOut = $modx->invokeEvent('OnBeforeMinifyCss', array(
            'files' => $files,
            'source' => 'manager',
            'theme' => $modx->config['manager_theme']
        ));
        switch (true) {
            case empty($evtOut):
            case is_array($evtOut) && count($evtOut) === 0:
                break;
            case is_array($evtOut) && count($evtOut) === 1:
                $files = $evtOut[0];
                break;
            default:
                $modx->webAlertAndQuit(sprintf($_lang['invalid_event_response'], 'OnBeforeMinifyManagerCss'));
        }
        require_once MODX_BASE_PATH . 'assets/lib/Formatter/CSSMinify.php';
        $minifier = new Formatter\CSSMinify($files);
        $css = $minifier->minify();
        file_put_contents(
            MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/styles.min.css',
            $css
        );
    }
    if (file_exists(MODX_MANAGER_PATH . 'media/style/' . $modx->config['manager_theme'] . '/css/styles.min.css')) {
        $css = 'media/style/' . $modx->config['manager_theme'] . '/css/styles.min.css?v=' . $lastInstallTime;
    }
}

?>
<!DOCTYPE html>
<html lang="<?= $mxla ?>" dir="<?= $textdir ?>">
<head>
    <title>Evolution CMS</title>
    <?= csrfTokenMeta() ?>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= $modx_manager_charset ?>"/>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Expires" content="0"/>
    <meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width"/>
    <meta name="theme-color" content="#1d2023"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="stylesheet" type="text/css" href="<?= $css ?>"/>
    <script type="text/javascript" src="media/script/tabpane.js"></script>
    <?= sprintf('<script type="text/javascript" src="%s"></script>' . "\n", $modx->config['mgr_jquery_path']) ?>
    <?php if ($modx->config['show_picker'] != "0") { ?>
        <script src="media/style/<?= $modx->config['manager_theme'] ?>/js/color.switcher.js"
                type="text/javascript"></script>
    <?php } ?>
    <!-- OnManagerMainFrameHeaderHTMLBlock -->
    <?= $onManagerMainFrameHeaderHTMLBlock . "\n" ?>

    <?php /* CSRF: единый блок перехвата форм, fetch и XHR */ ?>
    <script>
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

        // 2. Перехват прямых вызовов form.submit() — лечит Системную конфигурацию
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
    </script>

    <script type="text/javascript">
      if (!evo) {
        var evo = {};
      }
      var actions;
      var actionStay = [];
      var dontShowWorker = false;
      var documentDirty = false;
      var timerForUnload;
      var managerPath = '';

      evo.lang = {
        saving: '<?= $_lang['saving'] ?>',
        error_internet_connection: '<?= addslashes($_lang['error_internet_connection']) ?>',
        warning_not_saved: '<?= addslashes($_lang['warning_not_saved']) ?>'
      };
      evo.style = {
        actions_file: '<?= $_style['actions_file'] ?>',
        actions_pencil: '<?= $_style['actions_pencil'] ?>',
        actions_reply: '<?= $_style['actions_reply'] ?>',
        actions_plus: '<?= $_style['actions_plus'] ?>'
      };
      evo.urlCheckConnectionToServer = '<?= MODX_MANAGER_URL ?>includes/version.inc.php';
      evo.locale = '<?= evo()->getLocale() ?>';
    </script>

    <script src="media/script/main.js"></script>
    <script>
        <?php
        if (isset($_REQUEST['r']) && preg_match('@^[0-9]+$@', $_REQUEST['r'])) {
            echo 'doRefresh(' . $_REQUEST['r'] . ");\n";
        }
        ?>
    </script>
</head>
<body <?= ($modx_textdir ? ' class="rtl"' : '') ?> class="<?= $body_class ?>" data-evocp="color">
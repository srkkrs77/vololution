<?php
/**
 * cookieConsent — GDPR/TTDSG Cookie Banner
 * 
 * @package    VoloLution CMS
 * @version    1.1.0
 */

if (!defined('MODX_BASE_PATH')) { die('HACK?'); }

// Определяем язык (по URL, либо по языку менеджера)
$langPrefix = 'en';
$uri = $_SERVER['REQUEST_URI'] ?? '';

if (strpos($uri, '/de/') === 0 || $uri === '/de' || strpos($uri, '/de') === 0) { $langPrefix = 'de'; }
elseif (strpos($uri, '/ru/') === 0 || $uri === '/ru' || strpos($uri, '/ru') === 0) { $langPrefix = 'ru'; }
elseif (strpos($uri, '/fr/') === 0 || $uri === '/fr' || strpos($uri, '/fr') === 0) { $langPrefix = 'fr'; }
elseif (strpos($uri, '/es/') === 0 || $uri === '/es' || strpos($uri, '/es') === 0) { $langPrefix = 'es'; }
elseif (strpos($uri, '/it/') === 0 || $uri === '/it' || strpos($uri, '/it') === 0) { $langPrefix = 'it'; }
elseif (strpos($uri, '/en/') === 0 || $uri === '/en' || strpos($uri, '/en') === 0) { $langPrefix = 'en'; }
else {
    // Если это главная страница без префикса, берем язык менеджера
    $langPrefix = substr($modx->config['manager_language'] ?? 'english', 0, 2);
}

$texts = [
    'ru' => [
        'title' => 'Мы используем файлы cookie',
        'desc'  => 'Мы используем файлы cookie для улучшения работы сайта. Выберите, какие категории вы разрешаете.',
        'btn_all' => 'Принять все',
        'btn_ess' => 'Только необходимые',
        'btn_set' => 'Настройки',
        'btn_save'=> 'Сохранить',
        'stats'   => 'Статистика',
        'mkt'     => 'Маркетинг'
    ],
    'en' => [
        'title' => 'We use cookies',
        'desc'  => 'We use cookies to improve your experience. Choose which categories you allow.',
        'btn_all' => 'Accept all',
        'btn_ess' => 'Essential only',
        'btn_set' => 'Settings',
        'btn_save'=> 'Save',
        'stats'   => 'Statistics',
        'mkt'     => 'Marketing'
    ],
    'de' => [
        'title' => 'Wir verwenden Cookies',
        'desc'  => 'Wir verwenden Cookies, um unsere Website zu verbessern. Bitte wählen Sie aus, welche Kategorien Sie zulassen.',
        'btn_all' => 'Alle akzeptieren',
        'btn_ess' => 'Nur notwendige',
        'btn_set' => 'Einstellungen',
        'btn_save'=> 'Speichern',
        'stats'   => 'Statistik',
        'mkt'     => 'Marketing'
    ],
    'fr' => [
        'title' => 'Nous utilisons des cookies',
        'desc'  => 'Nous utilisons des cookies pour améliorer votre expérience. Choisissez les catégories que vous autorisez.',
        'btn_all' => 'Tout accepter',
        'btn_ess' => 'Nécessaires uniquement',
        'btn_set' => 'Paramètres',
        'btn_save'=> 'Enregistrer',
        'stats'   => 'Statistiques',
        'mkt'     => 'Marketing'
    ],
    'es' => [
        'title' => 'Utilizamos cookies',
        'desc'  => 'Utilizamos cookies para mejorar su experiencia. Elija qué categorías permite.',
        'btn_all' => 'Aceptar todo',
        'btn_ess' => 'Solo esenciales',
        'btn_set' => 'Ajustes',
        'btn_save'=> 'Guardar',
        'stats'   => 'Estadísticas',
        'mkt'     => 'Marketing'
    ],
    'it' => [
        'title' => 'Utilizziamo i cookie',
        'desc'  => 'Utilizziamo i cookie per migliorare la tua esperienza. Scegli quali categorie consentire.',
        'btn_all' => 'Accetta tutto',
        'btn_ess' => 'Solo essenziali',
        'btn_set' => 'Impostazioni',
        'btn_save'=> 'Salva',
        'stats'   => 'Statistiche',
        'mkt'     => 'Marketing'
    ]
];

$t = $texts[$langPrefix] ?? $texts['en'];

$html = <<<HTML
<style>
.cookie-banner { position: fixed; bottom: 20px; left: 20px; right: 20px; max-width: 600px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; padding: 20px; z-index: 99999; font-family: sans-serif; transition: opacity 0.3s, transform 0.3s; transform: translateY(0); opacity: 1; }
.cookie-banner.hidden { opacity: 0; transform: translateY(20px); pointer-events: none; }
.cookie-banner h3 { margin: 0 0 10px; font-size: 18px; color: #333; }
.cookie-banner p { margin: 0 0 15px; font-size: 14px; color: #666; line-height: 1.5; }
.cookie-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; }
.cookie-actions button { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; transition: background 0.2s; }
.btn-all { background: #222; color: #fff; }
.btn-all:hover { background: #444; }
.btn-ess { background: #eee; color: #333; }
.btn-ess:hover { background: #ddd; }
.btn-set { background: transparent; color: #666; text-decoration: underline; }
.cookie-details { display: none; margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px; }
.cookie-details.open { display: block; }
.cookie-details label { display: block; margin-bottom: 8px; font-size: 14px; cursor: pointer; }
.cookie-details .disabled { opacity: 0.5; cursor: not-allowed; }
.btn-save { background: #222; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
.btn-save:hover { background: #444; }
</style>

<div id="cookie-banner" class="cookie-banner hidden" aria-hidden="true">
    <h3>{$t['title']}</h3>
    <p>{$t['desc']}</p>
    
    <div id="cookie-details" class="cookie-details">
        <label class="disabled"><input type="checkbox" id="cookie-essential" checked disabled> {$t['btn_ess']}</label>
        <label><input type="checkbox" id="cookie-stats"> {$t['stats']}</label>
        <label><input type="checkbox" id="cookie-marketing"> {$t['mkt']}</label>
        <button id="cookie-save" class="btn-save">{$t['btn_save']}</button>
    </div>

    <div class="cookie-actions">
        <button id="cookie-all" class="btn-all">{$t['btn_all']}</button>
        <button id="cookie-essential" class="btn-ess">{$t['btn_ess']}</button>
        <button id="cookie-toggle" class="btn-set">{$t['btn_set']}</button>
    </div>
</div>

<script>
(function() {
    var banner = document.getElementById('cookie-banner');
    if (!banner) return;

    var qs = function (s) { return document.querySelector(s); };
    var details   = qs('#cookie-details');
    var toggleBtn = qs('#cookie-toggle');
    var btnAll    = qs('#cookie-all');
    var btnEss    = qs('#cookie-essential');
    var btnSave   = qs('#cookie-save');
    var chkStats  = qs('#cookie-stats');
    var chkMkt    = qs('#cookie-marketing');
    var KEY       = 'sm_cookie_consent';
    var TTL       = 180 * 24 * 60 * 60 * 1000;

    function readConsent() {
        try {
            var raw = localStorage.getItem(KEY);
            if (!raw) return null;
            var c = JSON.parse(raw);
            if (!c || !c.ts || (Date.now() - c.ts > TTL)) {
                localStorage.removeItem(KEY);
                return null;
            }
            return c;
        } catch (e) { return null; }
    }

    function save(choice) {
        var prev = readConsent();
        var next = {
            necessary: true,
            stats: !!choice.stats,
            marketing: !!choice.marketing,
            ts: Date.now()
        };
        try { localStorage.setItem(KEY, JSON.stringify(next)); } catch (e) {}
        
        banner.classList.add('hidden');
        banner.setAttribute('aria-hidden', 'true');
        
        var gained = ((!prev || !prev.stats) && next.stats) || ((!prev || !prev.marketing) && next.marketing);
        if (gained) location.reload();
    }

    function open() {
        var saved = readConsent();
        if (saved) {
            if (chkStats) chkStats.checked = !!saved.stats;
            if (chkMkt)   chkMkt.checked   = !!saved.marketing;
        }
        banner.classList.remove('hidden');
        banner.setAttribute('aria-hidden', 'false');
    }

    var saved = readConsent();
    if (!saved) setTimeout(open, 800);
    else banner.classList.add('hidden');

    if (toggleBtn) toggleBtn.addEventListener('click', function () {
        if (details) details.classList.toggle('open');
    });
    if (btnAll) btnAll.addEventListener('click', function () {
        save({ stats: true, marketing: true });
    });
    if (btnEss) btnEss.addEventListener('click', function () {
        save({ stats: false, marketing: false });
    });
    if (btnSave) btnSave.addEventListener('click', function () {
        save({
            stats: chkStats ? chkStats.checked : false,
            marketing: chkMkt ? chkMkt.checked : false
        });
    });
})();
</script>
HTML;

return $html;
(function() {
    var banner = document.getElementById('cc-banner');
    if (!banner) return;

    // RU: Получаем элементы DOM | EN: Get DOM elements
    var details   = document.getElementById('cc-details');
    var toggleBtn = document.getElementById('cc-toggle');
    var btnAll    = document.getElementById('cc-all');
    var btnEss    = document.getElementById('cc-essential-btn');
    var btnSave   = document.getElementById('cc-save');
    var chkStats  = document.getElementById('cc-stats');
    var chkMkt    = document.getElementById('cc-marketing');
    
    var KEY       = 'sm_cookie_consent'; // RU: Ключ в localStorage | EN: localStorage key
    var TTL       = 180 * 24 * 60 * 60 * 1000; // RU: Время жизни 180 дней | EN: TTL 180 days

    // RU: Чтение сохраненного согласия | EN: Read saved consent
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

    // RU: Сохранение выбора пользователя | EN: Save user choice
    function save(choice) {
        var prev = readConsent();
        var next = { necessary: true, stats: !!choice.stats, marketing: !!choice.marketing, ts: Date.now() };
        try { localStorage.setItem(KEY, JSON.stringify(next)); } catch (e) {}
        
        banner.classList.add('hidden');
        banner.setAttribute('aria-hidden', 'true');
        
        // RU: Перезагрузка, если были разблокированы новые скрипты | EN: Reload if new scripts were unblocked
        var gained = ((!prev || !prev.stats) && next.stats) || ((!prev || !prev.marketing) && next.marketing);
        if (gained) location.reload();
    }

    // RU: Открытие баннера | EN: Open banner
    function open() {
        var saved = readConsent();
        if (saved) {
            if (chkStats) chkStats.checked = !!saved.stats;
            if (chkMkt)   chkMkt.checked   = !!saved.marketing;
        }
        banner.classList.remove('hidden');
        banner.setAttribute('aria-hidden', 'false');
    }

    // RU: Инициализация | EN: Initialization
    var saved = readConsent();
    if (!saved) setTimeout(open, 800);
    else banner.classList.add('hidden');

    // RU: Обработчики событий | EN: Event listeners
    if (toggleBtn) toggleBtn.addEventListener('click', function () { details.classList.toggle('open'); });
    if (btnAll) btnAll.addEventListener('click', function () { save({ stats: true, marketing: true }); });
    if (btnEss) btnEss.addEventListener('click', function () { save({ stats: false, marketing: false }); });
    if (btnSave) btnSave.addEventListener('click', function () {
        save({ stats: chkStats ? chkStats.checked : false, marketing: chkMkt ? chkMkt.checked : false });
    });
})();
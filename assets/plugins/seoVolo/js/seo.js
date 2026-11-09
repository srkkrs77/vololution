/* ============================================
   seoVolo — admin script
   ============================================ */

(function () {
    'use strict';

    var CONFIG = window.seoVoloConfig || {
        short: 'Short',
        good: 'Good',
        great: 'Great',
        long: 'Too long',
        label: 'Characters'
    };

    var MIN_GOOD  = 70;
    var MIN_GREAT = 120;
    var MAX_GREAT = 160;

    function getState(len) {
        if (len === 0)       return 'short';
        if (len < MIN_GOOD)  return 'short';
        if (len < MIN_GREAT) return 'good';
        if (len <= MAX_GREAT) return 'great';
        return 'long';
    }

    function getLabel(state) {
        switch (state) {
            case 'short': return CONFIG.short;
            case 'good':  return CONFIG.good;
            case 'great': return CONFIG.great;
            case 'long':  return CONFIG.long;
            default:      return '';
        }
    }

    function getPercent(len) {
        var p = Math.round((len / MAX_GREAT) * 100);
        return p > 100 ? 100 : p;
    }

    function updateCounter(field, counter) {
        var len   = field.value.length;
        var state = getState(len);

        counter.className = 'seovolo-counter seovolo-state-' + state;

        var countEl  = counter.querySelector('.seovolo-count');
        var statusEl = counter.querySelector('.seovolo-status');
        var fillEl   = counter.querySelector('.seovolo-bar-fill');

        if (countEl) {
            countEl.textContent = len + ' / ' + MAX_GREAT;
        }
        if (statusEl) {
            statusEl.textContent = getLabel(state);
        }
        if (fillEl) {
            fillEl.style.width = getPercent(len) + '%';
        }
    }

    function createCounter() {
        var wrap = document.createElement('div');
        wrap.className = 'seovolo-counter';
        wrap.innerHTML =
            '<div>' +
                '<span class="seovolo-count">0 / ' + MAX_GREAT + '</span>' +
                ' <small>' + CONFIG.label + '</small>' +
            '</div>' +
            '<span class="seovolo-status"></span>' +
            '<div class="seovolo-bar"><div class="seovolo-bar-fill"></div></div>';
        return wrap;
    }

    function attach(field) {
        if (!field) return;
        if (field.dataset.seovoloAttached === '1') return;

        var counter = createCounter();

        if (field.parentNode) {
            field.parentNode.insertBefore(counter, field.nextSibling);
        } else {
            return;
        }

        field.dataset.seovoloAttached = '1';

        field.addEventListener('input', function () {
            updateCounter(field, counter);
        });
        field.addEventListener('change', function () {
            updateCounter(field, counter);
        });

        updateCounter(field, counter);
    }

    function findField() {
        // Ищем по правильному ID, который передал mm_rules.tpl
        var tvId = window.seoVoloTVId;
        if (!tvId) return null;
        
        return document.getElementById('tv' + tvId)
            || document.querySelector('textarea[name="tv' + tvId + '"]')
            || document.querySelector('input[name="tv' + tvId + '"]');
    }

    function init() {
        var field = findField();
        if (field) {
            attach(field);
        }
    }

    if (window.MutationObserver) {
        var observer = new MutationObserver(function () {
            init();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
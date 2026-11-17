/**
 * VoloLution Store v2 - Frontend Logic
 * Чистый JS (Vanilla), без jQuery.
 */
(function() {
    'use strict';

    const config = window.StoreV2 || {};
    const catalogContainer = document.getElementById('store-catalog');
    const statusContainer = document.getElementById('store-status');

    if (!config.apiUrl) {
        console.error('StoreV2: apiUrl не найден в window.StoreV2');
        return;
    }

    function setStatus(message, type = 'info') {
        if (statusContainer) {
            statusContainer.textContent = message;
            statusContainer.style.borderLeftColor = type === 'error' ? '#d93025' : '#3178c6';
            statusContainer.style.color = type === 'error' ? '#d93025' : '#475467';
        }
    }

    async function loadCatalog() {
        setStatus(config.isRu ? 'Загрузка каталога...' : 'Loading catalog...');

        try {
            const response = await fetch(config.apiUrl + '?action=catalog', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken || '',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (result.success && result.packages) {
                renderCatalog(result.packages);
                setStatus(config.isRu ? `Каталог успешно загружен (${result.total} пакетов).` : `Catalog loaded successfully (${result.total} packages).`, 'info');
            } else {
                throw new Error(result.error || 'Unknown error');
            }
        } catch (error) {
            console.error('StoreV2 Error:', error);
            setStatus(config.isRu ? 'Ошибка загрузки: ' + error.message : 'Load error: ' + error.message, 'error');
        }
    }

    function renderCatalog(items) {
        if (!items || items.length === 0) {
            catalogContainer.innerHTML = `<p style="padding:20px; color:#667085;">${config.isRu ? 'Каталог пуст.' : 'Catalog is empty.'}</p>`;
            return;
        }

        const html = items.map(item => {
            const typeLabel = config.isRu ? getTypeLabelRu(item.type) : item.type;
            const btnClass = item.installed ? 'sv2-btn-secondary' : 'sv2-btn-primary';
            const btnText = item.installed
                ? (config.isRu ? 'Переустановить' : 'Reinstall')
                : (config.isRu ? 'Установить' : 'Install');
            const patchedBadge = item.patched
                ? `<span class="sv2-patched-badge" title="${config.isRu ? 'Патченый пакет' : 'Patched package'}">⚙</span>`
                : '';

            return `
                <div class="sv2-card">
                    <div class="sv2-card-header">
                        <span class="sv2-type-badge sv2-type-${item.type}">${typeLabel}</span>
                        <h3 class="sv2-card-title">${escapeHtml(item.name)}${patchedBadge}</h3>
                    </div>
                    <p class="sv2-card-desc">${escapeHtml(item.description)}</p>
                    <div class="sv2-card-meta">
                        <span>v${escapeHtml(item.version)}</span>
                        <span>•</span>
                        <span>${escapeHtml(item.author)}</span>
                    </div>
                    <div class="sv2-card-actions">
                        <button class="sv2-btn ${btnClass}" data-alias="${escapeHtml(item.alias)}" data-name="${escapeHtml(item.name)}" data-installed="${item.installed}">
                            ${btnText}
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        catalogContainer.innerHTML = `<div class="sv2-grid">${html}</div>`;
    }

    function getTypeLabelRu(type) {
        const map = { 'snippet': 'Сниппет', 'plugin': 'Плагин', 'module': 'Модуль' };
        return map[type] || type;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadCatalog);
    } else {
        loadCatalog();
    }
})();
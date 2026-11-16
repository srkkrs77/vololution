/**
 * cookieConsent.tpl
 *
 * HTML markup for the cookie banner.
 * RU: HTML-разметка для баннера куки.
 *
 * @category    chunk
 * @internal    @overwrite true
 * @internal    @installset base, sample
 */

<div id="cc-banner" class="cc-banner [+cc.position_class+] hidden" aria-hidden="true">
    <!-- RU: Заголовок | EN: Title -->
    <h3>[+cc.title+]</h3>
    
    <!-- RU: Описание | EN: Description -->
    <p>[+cc.desc+]</p>
    
    <!-- RU: Дополнительные настройки | EN: Extra settings -->
    <div id="cc-details" class="cc-details">
        <label class="disabled"><input type="checkbox" id="cc-essential" checked disabled> [+cc.btn_ess+]</label>
        <label><input type="checkbox" id="cc-stats"> [+cc.stats+]</label>
        <label><input type="checkbox" id="cc-marketing"> [+cc.mkt+]</label>
        <button id="cc-save" class="cc-btn-save">[+cc.btn_save+]</button>
    </div>

    <!-- RU: Кнопки | EN: Buttons -->
    <div class="cc-actions">
        <button id="cc-all" class="cc-btn-all">[+cc.btn_all+]</button>
        <button id="cc-essential-btn" class="cc-btn-ess">[+cc.btn_ess+]</button>
        <button id="cc-toggle" class="cc-btn-set">[+cc.btn_set+]</button>
    </div>
</div>
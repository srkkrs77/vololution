//<?php
/**
 * seo_canonical
 * 
 * Canonical URL — the "main" address of the page.
 * Used when the same page is available via multiple URLs
 * (with ?page=2, ?sort=date, with and without www, with GET params).
 * 
 * Leave empty in most cases — will be auto-generated
 * from the current document URL without sort params and UTM tags.
 * 
 * Fill in only if:
 *   - the page is a copy of another (duplicate content)
 *   - you need to point canonical to a different URL
 * 
 * Example: two pages "About" and "About-Us" with the same text —
 * on the second one, set canonical to the first.
 * 
 * @category	tv
 * @version 	1.0.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@caption Canonical URL (empty = auto)
 * @internal	@input_type text
 * @internal	@input_default
 * @internal	@output_widget
 * @internal	@output_widget_params
 * @internal	@template_assignments *
 * @internal	@modx_category SEO
 * @internal	@lock_tv 0
 * @internal    @installset base
 */
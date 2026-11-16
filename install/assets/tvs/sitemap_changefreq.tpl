//<?php
/**
 * sitemap_changefreq
 * 
 * Sitemap Change Frequency — how often the page is updated.
 * Used by DLSitemap snippet.
 * 
 * Values:
 *   always  — changes on every access (rarely used)
 *   hourly  — changes every hour
 *   daily   — changes every day (news, blogs)
 *   weekly  — changes every week (regular sections)
 *   monthly — changes every month (default)
 *   yearly  — changes every year (static pages)
 *   never   — archived content
 * 
 * Leave empty — DLSitemap uses "monthly" by default.
 * 
 * @category	tv
 * @version 	1.0.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@caption Sitemap change frequency
 * @internal	@input_type dropdown
 * @internal	@input_default monthly
 * @internal	@input_options always||hourly||daily||weekly||monthly||yearly||never
 * @internal	@output_widget
 * @internal	@output_widget_params
 * @internal	@template_assignments *
 * @internal	@modx_category SEO
 * @internal	@lock_tv 0
 * @internal    @installset base
 */
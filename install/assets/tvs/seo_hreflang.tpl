//<?php
/**
 * seo_hreflang
 * 
 * Hreflang — alternative language versions of the page.
 * Adds <link rel="alternate" hreflang="..."> tags for multilingual sites.
 * Requires EvoBabel plugin to be installed and configured.
 * 
 * Leave empty by default — works automatically based on EvoBabel data.
 * 
 * Fill in only to disable hreflang for a specific page (e.g. unique content
 * that has no translations). Value "exclude" removes the page from hreflang.
 * 
 * Global switch: seo_hreflang_enabled in System Settings.
 * If disabled globally — this TV is ignored.
 * 
 * @category	tv
 * @version 	1.0.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@caption Hreflang (empty = auto, "exclude" = off)
 * @internal	@input_type text
 * @internal	@input_default
 * @internal	@output_widget
 * @internal	@output_widget_params
 * @internal	@template_assignments *
 * @internal	@modx_category SEO
 * @internal	@lock_tv 0
 * @internal    @installset base
 */
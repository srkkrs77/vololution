//<?php
/**
 * seo_noindex
 * 
 * Robots meta tag — controls page indexing by search engines.
 * 
 * Options:
 *   index, follow      — default, index and follow links
 *   noindex, follow    — don't index, but follow links
 *   noindex, nofollow  — don't index, don't follow (full block)
 *   noindex, noarchive — don't index, no cached copy
 * 
 * @category	tv
 * @version 	1.0.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@caption Robots (indexing)
 * @internal	@input_type dropdown
 * @internal	@input_default index, follow
 * @internal	@input_options Index + Follow==index, follow||Noindex + Follow==noindex, follow||Noindex + Nofollow==noindex, nofollow||Noindex + Noarchive==noindex, noarchive
 * @internal	@output_widget
 * @internal	@output_widget_params
 * @internal	@template_assignments *
 * @internal	@modx_category SEO
 * @internal	@lock_tv 0
 * @internal    @installset base
 */
//<?php
/**
 * seo_og_image
 * 
 * OG Image — preview image for social networks (Open Graph, Twitter Cards).
 * Recommended size: 1200×630 px.
 * 
 * Leave empty in most cases — the global image from system settings 
 * will be used: seo_org_logo or og_default_image.
 * 
 * Fill in only for pages with a unique image 
 * (products, articles, cases, landing pages).
 * 
 * Fallback (in order):
 *   1. This TV
 *   2. seo_org_logo (System Settings)
 *   3. og_default_image (System Settings)
 *   4. First image from page content
 * 
 * @category	tv
 * @version 	1.0.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@caption OG Image (empty = global)
 * @internal	@input_type image
 * @internal	@input_default
 * @internal	@output_widget
 * @internal	@output_widget_params
 * @internal	@template_assignments *
 * @internal	@modx_category SEO
 * @internal	@lock_tv 0
 * @internal    @installset base
 */
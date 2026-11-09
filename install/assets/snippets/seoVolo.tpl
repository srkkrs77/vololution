//<?php
/**
 * seoVolo
 * 
 * SEO meta tags generator for VoloLution CMS.
 * Outputs title, description, canonical, Open Graph, Twitter Cards,
 * Schema.org JSON-LD, hreflang and robots meta.
 * 
 * Usage:
 *   [[seoVolo]]
 * 
 * Parameters:
 *   &type      — override Schema.org type (WebPage|Article|Product|Person|Event|Recipe|... — 40 types)
 *   &tpl       — custom chunk template for output
 *   &debug     — 1 to output debug comments in HTML
 * 
 * @category	snippet
 * @version 	1.0.7
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &type=Schema.org type;text; &tpl=Template chunk;text; &debug=Debug mode;list;0,1;0
 * @internal	@modx_category SEO
 * @internal    @installset base
 */

return require MODX_BASE_PATH . 'assets/snippets/seoVolo/snippet.seoVolo.php';
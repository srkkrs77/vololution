/**
 * mm_rules
 *
 * Default ManagerManager rules for VoloLution CMS.
 *
 * @category	chunk
 * @version 	2.0.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal 	@modx_category Js
 * @internal    @overwrite true
 * @internal    @installset base, sample
 */

mm_widget_showimagetvs();

mm_createTab('SEO', 'seo', '', '', '', '');

mm_moveFieldsToTab(
    'seo_title,seo_description,seo_keywords,seo_og_image,seo_canonical,seo_noindex,seo_schema_type,seo_hreflang,sitemap_priority,sitemap_changefreq,sitemap_exclude',
    'seo',
    '',
    ''
);

mm_widget_tags('seo_keywords', ',');
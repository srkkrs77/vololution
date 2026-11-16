/**
 * mm_rules
 *
 * Default ManagerManager rules for VoloLution CMS.
 *
 * @category	chunk
 * @version 	2.1.0
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

// --- Индикатор длины description ---
$lang = $modx->config['manager_language'] ?? 'english';
$langPrefix = substr($lang, 0, 2);
if (!in_array($langPrefix, ['ru', 'en', 'de'])) {
    $langPrefix = 'en';
}

$langFile = MODX_BASE_PATH . 'assets/plugins/seoVolo/lang/' . $langPrefix . '.php';
$seoVoloLang = [];
if (is_file($langFile)) {
    include $langFile;
}

$jsConfig = [
    'short' => $seoVoloLang['length_short'] ?? 'Short',
    'good'  => $seoVoloLang['length_good']  ?? 'Good',
    'great' => $seoVoloLang['length_great'] ?? 'Great',
    'long'  => $seoVoloLang['length_long']  ?? 'Too long',
    'label' => $seoVoloLang['length_label'] ?? 'Characters'
];

// Получаем ID TV seo_description
$tvTable = $modx->getFullTableName('site_tmplvars');
$res = $modx->db->select('id', $tvTable, "name = 'seo_description'");
$tvId = 0;
if ($res && $modx->db->getRecordCount($res) > 0) {
    $row = $modx->db->getRow($res);
    $tvId = (int)$row['id'];
}

if ($tvId > 0) {
    $baseUrl = rtrim($modx->config['site_url'] ?? '/', '/') . '/';
    
    echo '<link rel="stylesheet" href="' . $baseUrl . 'assets/plugins/seoVolo/css/seo.css">' . "\n";
    echo '<script>window.seoVoloConfig = ' . json_encode($jsConfig) . '; window.seoVoloTVId = ' . $tvId . ';</script>' . "\n";
    echo '<script src="' . $baseUrl . 'assets/plugins/seoVolo/js/seo.js"></script>' . "\n";
}
<?php
/**
 * seoVolo — SEO meta tags generator
 *
 * @package    seoVolo
 * @version    1.0.7
 * @license    GNU GPL
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK?');
}

// === 1. Параметры сниппета ===
$type   = isset($type)   ? trim($type)   : '';
$tpl    = isset($tpl)    ? trim($tpl)    : '';
$debug  = isset($debug)  ? (int)$debug   : 0;

// === 2. Текущий документ ===
$docId = $modx->documentIdentifier ?? $modx->documentObject['id'] ?? 0;

if (!$docId) {
    return '<!-- seoVolo: no document id -->';
}

// === 3. Язык страницы (по alias корневой папки языка) ===
// Page language (from alias of the root language folder)
$langPrefix = seoVolo_getPageLang($modx, $docId);

// Fallback to manager language if alias is empty
// Fallback на язык менеджера, если alias пуст
if ($langPrefix === '') {
    $langPrefix = substr($modx->config['manager_language'] ?? 'en', 0, 2);
}
if ($langPrefix === '') {
    $langPrefix = 'en';
}

// Build locale and inLanguage from alias dynamically
// Строим locale и inLanguage из alias динамически
list($ogLocale, $inLanguage) = seoVolo_getLocale($langPrefix);

// === 4. Системные настройки ===
$siteName    = $modx->config['site_name'] ?? '';
$siteUrl     = $modx->config['site_url'] ?? '';
$seoOrgName  = $modx->config['seo_org_name'] ?? $siteName;
$seoOrgLogo  = $modx->config['seo_org_logo'] ?? '';
$seoOrgPhone = $modx->config['seo_org_phone'] ?? '';
$seoOrgEmail = $modx->config['seo_org_email'] ?? '';
$seoOrgAddr  = $modx->config['seo_org_address'] ?? '';
$ogDefault   = $modx->config['og_default_image'] ?? '';
$hreflangOn  = (int)($modx->config['seo_hreflang_enabled'] ?? 0);
$keywordsOn  = (int)($modx->config['seo_keywords_enabled'] ?? 0);

// Title separator — ensure it has spaces around if not set
// Разделитель title — если не задан или пуст, используем с пробелами
$titleSeparator = trim($modx->config['seo_title_separator'] ?? '');
if ($titleSeparator === '') {
    $titleSeparator = '—';
}
$titleSeparator = ' ' . $titleSeparator . ' ';

// === 5. Хранилище ===
$seo = [
    'title'       => '',
    'description' => '',
    'keywords'    => '',
    'canonical'   => '',
    'robots'      => '',
    'og_image'    => '',
    'schema_type' => 'WebPage',
    'hreflang'    => [],
];

// Инициализация заранее — чтобы не было undefined
$schemaJson  = null;

// === 6. Отладка ===
if ($debug) {
    $seo['debug'] = [
        'docId'      => $docId,
        'lang'       => $langPrefix,
        'og_locale'  => $ogLocale,
        'inLanguage' => $inLanguage,
        'type_param' => $type,
    ];
}

// === 7. Хелперы (объявлены до использования) ===

function seoVolo_getTV($modx, $name, $docId, $default = '')
{
    $tv = $modx->getTemplateVar($name, '*', $docId);
    if (is_array($tv) && isset($tv['value']) && $tv['value'] !== '') {
        return $tv['value'];
    }
    return $default;
}

function seoVolo_getPageLang($modx, $docId)
{
    // Find root parent (language folder) and get its alias
    // Ищем корневого родителя (папку языка) и берём её alias
    $parents = $modx->getParentIds($docId, 10, ['root' => '1']);
    if (empty($parents)) {
        $doc = $modx->getDocument($docId, 'alias');
        return $doc['alias'] ?? '';
    }
    $parents = array_values($parents);
    $rootId  = end($parents);
    $doc     = $modx->getDocument($rootId, 'alias');
    return $doc['alias'] ?? '';
}

function seoVolo_getLocale($langAlias)
{
    // Build og:locale and inLanguage from language alias
    // Строим og:locale и inLanguage из алиаса языка
    //
    // Rules / Правила:
    //   de  → de_DE / de-DE
    //   ru  → ru_RU / ru-RU
    //   fr  → fr_FR / fr-FR
    //   en  → en_US / en-US  (special case / особый случай)
    //   pt-br → pt_BR / pt-BR (region-specific / с регионом)

    $langAlias = strtolower(trim($langAlias));
    if ($langAlias === '') {
        return ['en_US', 'en-US'];
    }

    // Special cases where region != language code
    // Особые случаи, когда регион != код языка
    $specialRegions = [
        'en' => 'US',
        'ja' => 'JP',
        'zh' => 'CN',
        'ar' => 'SA',
        'he' => 'IL',
        'ko' => 'KR',
    ];

    // Handle region-specific aliases like "pt-br" or "pt_br"
    // Обработка региональных алиасов вроде "pt-br" или "pt_br"
    if (strpos($langAlias, '-') !== false || strpos($langAlias, '_') !== false) {
        $parts  = preg_split('/[-_]/', $langAlias);
        $lang   = strtolower($parts[0]);
        $region = strtoupper($parts[1] ?? ($specialRegions[$lang] ?? strtoupper($lang)));
    } else {
        $lang   = $langAlias;
        $region = $specialRegions[$lang] ?? strtoupper($lang);
    }

    return [$lang . '_' . $region, $lang . '-' . $region];
}

function seoVolo_truncate($text, $limit = 160)
{
    // Декодируем HTML-сущности перед обрезкой
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    $cut = mb_substr($text, 0, $limit, 'UTF-8');
    $lastSpace = mb_strrpos($cut, ' ', 0, 'UTF-8');
    if ($lastSpace !== false && $lastSpace > $limit * 0.6) {
        $cut = mb_substr($cut, 0, $lastSpace, 'UTF-8');
    }
    return rtrim($cut, '.,;:!?') . '…';
}

function seoVolo_absoluteUrl($url, $siteUrl)
{
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }
    return rtrim($siteUrl, '/') . '/' . ltrim($url, '/');
}

function seoVolo_findFirstImage($content)
{
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
        return $m[1];
    }
    return '';
}

function seoVolo_getHreflang($modx, $docId)
{
    $out = [];

    // TV with language relations (evoBabel stores them in TV "relation")
    // TV с языковыми связями (evoBabel хранит их в TV "relation")
    $relTvName = 'relation';

    $tv = $modx->getTemplateVar($relTvName, '*', $docId);
    $relations = $tv['value'] ?? '';

    // No relations — try parent (evoBabel fallback logic)
    // Нет связей — пробуем родителя (логика fallback в evoBabel)
    if (empty($relations)) {
        $parentId = (int)($modx->documentObject['parent'] ?? 0);
        if ($parentId > 0) {
            $tv = $modx->getTemplateVar($relTvName, '*', $parentId);
            $relations = $tv['value'] ?? '';
        }
    }

    if (empty($relations)) {
        return $out;
    }

    // Format: "de:5||en:12"
    // Формат: "de:5||en:12"
    $items = explode('||', $relations);
    foreach ($items as $rel) {
        $rel = trim($rel);
        if ($rel === '') continue;

        $parts = explode(':', $rel);
        if (count($parts) !== 2) continue;

        $lang = trim($parts[0]);
        $id   = (int)$parts[1];

        if ($lang === '' || $id === 0) continue;

        $url = $modx->makeUrl($id, '', '', 'full');
        if ($url) {
            $out[] = ['lang' => $lang, 'url' => $url];
        }
    }

    return $out;
}

function seoVolo_ogType($schemaType)
{
    // Full Schema.org → Open Graph type mapping
    // Полный маппинг Schema.org → Open Graph
    $map = [
        // Articles / Статьи
        'Article'                => 'article',
        'NewsArticle'            => 'article',
        'BlogPosting'            => 'article',
        'ScholarlyArticle'       => 'article',
        'TechArticle'            => 'article',
        'Report'                 => 'article',
        'Recipe'                 => 'article',
        'Course'                 => 'article',
        'HowTo'                  => 'article',
        'JobPosting'             => 'article',
        'Event'                  => 'article',

        // Products / Товары
        'Product'                => 'product',
        'Offer'                  => 'product',
        'AggregateOffer'         => 'product',

        // Profiles / Профили
        'Person'                 => 'profile',
        'ProfilePage'            => 'profile',

        // Books / Книги
        'Book'                   => 'book',

        // Video / Видео
        'VideoObject'            => 'video.other',
        'Movie'                  => 'video.movie',
        'TVSeries'               => 'video.tv_show',
        'TVEpisode'              => 'video.episode',

        // Music / Музыка
        'MusicRecording'         => 'music.song',
        'MusicAlbum'             => 'music.album',
        'MusicPlaylist'          => 'music.playlist',
        'MusicGroup'             => 'music.radio_station',

        // Everything else → website
        // Всё остальное → website
        'WebPage'                => 'website',
        'AboutPage'              => 'website',
        'ContactPage'            => 'website',
        'FAQPage'                => 'website',
        'CollectionPage'         => 'website',
        'ItemPage'               => 'website',
        'SearchResultsPage'      => 'website',
        'QAPage'                 => 'website',
        'Service'                => 'website',
        'Organization'           => 'website',
        'LocalBusiness'          => 'website',
        'SoftwareApplication'    => 'website',
        'WebApplication'         => 'website',
        'MobileApplication'      => 'website',
        'Dataset'                => 'website',
    ];

    return $map[$schemaType] ?? 'website';
}

function seoVolo_getBreadcrumbs($modx, $docId, $seo, $siteUrl)
{
    $crumbs = [];
    $parents = $modx->getParentIds($docId, 10, ['root' => '1']);

    if (!empty($parents)) {
        $parents = array_reverse($parents);

        foreach ($parents as $pid) {
            $doc = $modx->getDocument($pid, 'pagetitle,menutitle,parent,alias');
            if (!$doc) continue;

            // Skip language containers (root folders with 2-3 letter alias)
            // Пропускаем языковые контейнеры (корневые папки с 2-3-буквенным alias)
            if ((int)$doc['parent'] === 0 && preg_match('/^[a-z]{2,3}$/', (string)$doc['alias'])) {
                continue;
            }

            $title = $doc['menutitle'] ?? '';
            if ($title === '') {
                $title = $doc['pagetitle'] ?? '';
            }
            if ($title === '') continue;

            $crumbs[] = [
                'title' => $title,
                'url'   => $modx->makeUrl($pid, '', '', 'full'),
            ];
        }
    }

    $crumbs[] = [
        'title' => $seo['title'] ?: ($modx->documentObject['pagetitle'] ?? ''),
        'url'   => $seo['canonical'],
    ];

    return $crumbs;
}

// === 8. Системные поля документа ===
$pagetitle  = html_entity_decode($modx->documentObject['pagetitle'] ?? '', ENT_QUOTES, 'UTF-8');
$descrField = html_entity_decode($modx->documentObject['description'] ?? '', ENT_QUOTES, 'UTF-8');
$introtext  = html_entity_decode($modx->documentObject['introtext'] ?? '', ENT_QUOTES, 'UTF-8');
$content    = html_entity_decode($modx->documentObject['content'] ?? '', ENT_QUOTES, 'UTF-8');

// === 9. TV-значения ===
$tvTitle       = seoVolo_getTV($modx, 'seo_title', $docId);
$tvDescription = seoVolo_getTV($modx, 'seo_description', $docId);
$tvKeywords    = seoVolo_getTV($modx, 'seo_keywords', $docId);
$tvOgImage     = seoVolo_getTV($modx, 'seo_og_image', $docId);
$tvCanonical   = seoVolo_getTV($modx, 'seo_canonical', $docId);
$tvNoindex     = seoVolo_getTV($modx, 'seo_noindex', $docId, 'index, follow');
$tvSchemaType  = seoVolo_getTV($modx, 'seo_schema_type', $docId, 'WebPage');
$tvHreflang    = seoVolo_getTV($modx, 'seo_hreflang', $docId);

// === 10. Title ===
if ($tvTitle !== '') {
    $seo['title'] = html_entity_decode($tvTitle, ENT_QUOTES, 'UTF-8');
} else {
    $seo['title'] = $siteName
        ? $pagetitle . $titleSeparator . $siteName
        : $pagetitle;
}

// === 11. Description ===
if ($tvDescription !== '') {
    $seo['description'] = html_entity_decode($tvDescription, ENT_QUOTES, 'UTF-8');
} elseif ($descrField !== '') {
    $seo['description'] = $descrField;
} elseif ($introtext !== '') {
    $seo['description'] = seoVolo_truncate(strip_tags($introtext), 160);
} elseif ($content !== '') {
    $seo['description'] = seoVolo_truncate(strip_tags($content), 160);
}

// === 12. Keywords ===
if ($keywordsOn && $tvKeywords !== '') {
    $seo['keywords'] = $tvKeywords;
}

// === 13. Canonical ===
if ($tvCanonical !== '') {
    $seo['canonical'] = $tvCanonical;
} else {
    $seo['canonical'] = $modx->makeUrl($docId, '', '', 'full');
}

// === 14. Robots ===
if ($tvNoindex && $tvNoindex !== 'index, follow') {
    $seo['robots'] = $tvNoindex;
}

// === 15. OG Image ===
if ($tvOgImage !== '') {
    $seo['og_image'] = seoVolo_absoluteUrl($tvOgImage, $siteUrl);
} elseif ($ogDefault !== '') {
    $seo['og_image'] = seoVolo_absoluteUrl($ogDefault, $siteUrl);
} elseif ($seoOrgLogo !== '') {
    $seo['og_image'] = seoVolo_absoluteUrl($seoOrgLogo, $siteUrl);
} else {
    $firstImg = seoVolo_findFirstImage($content);
    if ($firstImg) {
        $seo['og_image'] = seoVolo_absoluteUrl($firstImg, $siteUrl);
    }
}

// === 16. Schema.org type ===
$seo['schema_type'] = $type ?: $tvSchemaType;

// === 17. Hreflang ===
if ($hreflangOn && $tvHreflang !== 'exclude') {
    $seo['hreflang'] = seoVolo_getHreflang($modx, $docId);
}

// === 18. Буфер вывода ===
$out = [];

// === 19. Debug ===
if ($debug && !empty($seo['debug'])) {
    $out[] = '<!-- seoVolo debug: ' . json_encode($seo['debug'], JSON_UNESCAPED_UNICODE) . ' -->';
}

// === 20. Title ===
if ($seo['title'] !== '') {
    $out[] = '<title>' . htmlspecialchars($seo['title'], ENT_QUOTES, 'UTF-8') . '</title>';
}

// === 21. Description ===
if ($seo['description'] !== '') {
    $out[] = '<meta name="description" content="'
        . htmlspecialchars($seo['description'], ENT_QUOTES, 'UTF-8') . '" />';
}

// === 22. Keywords ===
if ($seo['keywords'] !== '') {
    $out[] = '<meta name="keywords" content="'
        . htmlspecialchars($seo['keywords'], ENT_QUOTES, 'UTF-8') . '" />';
}

// === 23. Robots ===
if ($seo['robots'] !== '') {
    $out[] = '<meta name="robots" content="' . htmlspecialchars($seo['robots'], ENT_QUOTES, 'UTF-8') . '" />';
}

// === 24. Canonical ===
if ($seo['canonical'] !== '') {
    $out[] = '<link rel="canonical" href="'
        . htmlspecialchars($seo['canonical'], ENT_QUOTES, 'UTF-8') . '" />';
}

// === 25. Hreflang ===
if (!empty($seo['hreflang'])) {
    foreach ($seo['hreflang'] as $item) {
        $out[] = '<link rel="alternate" hreflang="'
            . htmlspecialchars($item['lang'], ENT_QUOTES, 'UTF-8')
            . '" href="' . htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') . '" />';
    }
    if ($seo['canonical']) {
        $out[] = '<link rel="alternate" hreflang="x-default" href="'
            . htmlspecialchars($seo['canonical'], ENT_QUOTES, 'UTF-8') . '" />';
    }
}

// === 26. Open Graph ===
$ogType = seoVolo_ogType($seo['schema_type']);
$out[] = '<meta property="og:type" content="' . $ogType . '" />';
$out[] = '<meta property="og:site_name" content="'
    . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '" />';
$out[] = '<meta property="og:title" content="'
    . htmlspecialchars($seo['title'], ENT_QUOTES, 'UTF-8') . '" />';

if ($seo['description'] !== '') {
    $out[] = '<meta property="og:description" content="'
        . htmlspecialchars($seo['description'], ENT_QUOTES, 'UTF-8') . '" />';
}

$out[] = '<meta property="og:url" content="'
    . htmlspecialchars($seo['canonical'], ENT_QUOTES, 'UTF-8') . '" />';

if ($seo['og_image'] !== '') {
    $out[] = '<meta property="og:image" content="'
        . htmlspecialchars($seo['og_image'], ENT_QUOTES, 'UTF-8') . '" />';
}

$out[] = '<meta property="og:locale" content="' . $ogLocale . '" />';

// === 27. Twitter Cards ===
$out[] = '<meta name="twitter:card" content="summary_large_image" />';
$out[] = '<meta name="twitter:title" content="'
    . htmlspecialchars($seo['title'], ENT_QUOTES, 'UTF-8') . '" />';

if ($seo['description'] !== '') {
    $out[] = '<meta name="twitter:description" content="'
        . htmlspecialchars($seo['description'], ENT_QUOTES, 'UTF-8') . '" />';
}

if ($seo['og_image'] !== '') {
    $out[] = '<meta name="twitter:image" content="'
        . htmlspecialchars($seo['og_image'], ENT_QUOTES, 'UTF-8') . '" />';
}

$twSite = $modx->config['seo_twitter_site'] ?? '';
if ($twSite) {
    $out[] = '<meta name="twitter:site" content="'
        . htmlspecialchars($twSite, ENT_QUOTES, 'UTF-8') . '" />';
}
$twCreator = $modx->config['seo_twitter_creator'] ?? '';
if ($twCreator) {
    $out[] = '<meta name="twitter:creator" content="'
        . htmlspecialchars($twCreator, ENT_QUOTES, 'UTF-8') . '" />';
}

// === 28. Schema.org JSON-LD ===
$schemaGraph = [];

// 28.1. Organization
$orgNode = [
    '@type' => 'Organization',
    '@id'   => rtrim($siteUrl, '/') . '/#organization',
    'name'  => $seoOrgName ?: $siteName,
    'url'   => $siteUrl,
];
if ($seoOrgLogo) {
    $orgNode['logo'] = [
        '@type' => 'ImageObject',
        'url'   => seoVolo_absoluteUrl($seoOrgLogo, $siteUrl),
    ];
}
if ($seoOrgPhone) $orgNode['telephone'] = $seoOrgPhone;
if ($seoOrgEmail) $orgNode['email']     = $seoOrgEmail;
if ($seoOrgAddr) {
    $orgNode['address'] = [
        '@type'         => 'PostalAddress',
        'streetAddress' => $seoOrgAddr,
    ];
}
$schemaGraph[] = $orgNode;

// 28.2. WebSite
$schemaGraph[] = [
    '@type'      => 'WebSite',
    '@id'        => rtrim($siteUrl, '/') . '/#website',
    'url'        => $siteUrl,
    'name'       => $siteName,
    'publisher'  => ['@id' => rtrim($siteUrl, '/') . '/#organization'],
    'inLanguage' => $inLanguage,
];

// 28.3. Основной узел
$pageNode = [
    '@type'      => $seo['schema_type'],
    '@id'        => $seo['canonical'] . '#page',
    'url'        => $seo['canonical'],
    'name'       => $seo['title'],
    'inLanguage' => $inLanguage,
];
if ($seo['description'] !== '') {
    $pageNode['description'] = $seo['description'];
}
if ($seo['og_image'] !== '') {
    $pageNode['image'] = $seo['og_image'];
}

// 28.4. Article
if (in_array($seo['schema_type'], ['Article', 'NewsArticle', 'BlogPosting'], true)) {
    $published = $modx->documentObject['publishedon'] ?? 0;
    $edited    = $modx->documentObject['editedon'] ?? 0;
    if ($published) $pageNode['datePublished'] = date('c', $published);
    if ($edited)    $pageNode['dateModified']  = date('c', $edited);
    $pageNode['publisher']        = ['@id' => rtrim($siteUrl, '/') . '/#organization'];
    $pageNode['mainEntityOfPage'] = ['@id' => $seo['canonical'] . '#page'];
}

// 28.5. Product
if ($seo['schema_type'] === 'Product') {
    $pageNode['brand'] = [
        '@type' => 'Brand',
        'name'  => $seoOrgName ?: $siteName,
    ];
}

$schemaGraph[] = $pageNode;

// 28.6. BreadcrumbList
$breadcrumbs = seoVolo_getBreadcrumbs($modx, $docId, $seo, $siteUrl);
if (!empty($breadcrumbs)) {
    $items = [];
    $position = 1;
    foreach ($breadcrumbs as $crumb) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => $crumb['title'],
            'item'     => $crumb['url'],
        ];
    }
    $schemaGraph[] = [
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

// 28.7. Собираем
if (!empty($schemaGraph)) {
    $schemaJson = [
        '@context' => 'https://schema.org',
        '@graph'   => $schemaGraph,
    ];
    $out[] = '<script type="application/ld+json">'
        . json_encode($schemaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>';
}

// === 29. Пустой результат ===
if (empty($out)) {
    return '';
}

// === 30. Свой шаблон ===
if ($tpl !== '') {
    $chunk = $modx->getChunk($tpl);
    if ($chunk) {
        $ph = [
            'title'       => htmlspecialchars($seo['title'], ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars($seo['description'], ENT_QUOTES, 'UTF-8'),
            'keywords'    => htmlspecialchars($seo['keywords'], ENT_QUOTES, 'UTF-8'),
            'canonical'   => htmlspecialchars($seo['canonical'], ENT_QUOTES, 'UTF-8'),
            'robots'      => htmlspecialchars($seo['robots'], ENT_QUOTES, 'UTF-8'),
            'og_image'    => htmlspecialchars($seo['og_image'], ENT_QUOTES, 'UTF-8'),
            'schema_type' => htmlspecialchars($seo['schema_type'], ENT_QUOTES, 'UTF-8'),
            'site_name'   => htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'),
            'site_url'    => htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'),
            'locale'      => $ogLocale,
            'lang'        => $langPrefix,
        ];

        $hreflangStr = '';
        foreach ($seo['hreflang'] as $item) {
            $hreflangStr .= '<link rel="alternate" hreflang="'
                . htmlspecialchars($item['lang'], ENT_QUOTES, 'UTF-8')
                . '" href="' . htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') . '" />' . "\n";
        }
        $ph['hreflang'] = $hreflangStr;

        if (!empty($schemaJson)) {
            $ph['schema_json'] = '<script type="application/ld+json">'
                . json_encode($schemaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                . '</script>';
        } else {
            $ph['schema_json'] = '';
        }

        return $modx->parseText($chunk, $ph);
    }
}

// === 31. Стандартный вывод ===
return implode("\n", $out);
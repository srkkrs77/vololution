<?php
/**
 * seoVolo — plugin backend
 *
 * @package    seoVolo
 * @version    1.0.3
 * @license    GNU GPL
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK?');
}

$eventName = $modx->event->name;

// === 1. Автосоздание системных настроек ===
if ($eventName === 'OnManagerPageInit') {
    seoVolo_ensureSettings($modx);
}

// === 2. Авто-привязка TV к новым шаблонам ===
if ($eventName === 'OnTempFormSave') {
    seoVolo_attachTVsToTemplate($modx, $id ?? 0);
}

// ============================================================
// ФУНКЦИИ
// ============================================================

function seoVolo_ensureSettings($modx)
{
    $table = $modx->getFullTableName('system_settings');

    $defaults = [
        'seo_org_name'         => '',
        'seo_org_logo'         => '',
        'seo_org_phone'        => '',
        'seo_org_email'        => '',
        'seo_org_address'      => '',
        'og_default_image'     => '',
        'seo_title_separator'  => ' — ',
        'seo_keywords_enabled' => '0',
        'seo_hreflang_enabled' => '0',
        'seo_twitter_site'     => '',
        'seo_twitter_creator'  => '',
    ];

    $names = [];
    foreach (array_keys($defaults) as $n) {
        $names[] = "'" . $modx->db->escape($n) . "'";
    }

    $rs = $modx->db->select(
        'setting_name',
        $table,
        'setting_name IN (' . implode(',', $names) . ')'
    );

    $existed = [];
    while ($row = $modx->db->getRow($rs, 'assoc')) {
        $existed[$row['setting_name']] = true;
    }

    foreach ($defaults as $name => $value) {
        if (isset($existed[$name])) continue;
        $modx->db->insert([
            'setting_name'  => $name,
            'setting_value' => $value,
        ], $table);
    }
}

function seoVolo_attachTVsToTemplate($modx, $templateId)
{
    $templateId = (int)$templateId;
    if (!$templateId) return;

    $seoTvs = [
        'seo_title', 'seo_description', 'seo_keywords', 'seo_og_image',
        'seo_canonical', 'seo_noindex', 'seo_schema_type', 'seo_hreflang',
        'sitemap_priority', 'sitemap_changefreq', 'sitemap_exclude',
    ];

    $tableTmplvar     = $modx->getFullTableName('site_tmplvars');
    $tableTmplvarTmpl = $modx->getFullTableName('site_tmplvar_templates');

    foreach ($seoTvs as $tvName) {
        $tvId = $modx->db->getValue(
            $modx->db->select('id', $tableTmplvar, "name = '" . $modx->db->escape($tvName) . "'")
        );
        if (!$tvId) continue;

        $exists = $modx->db->getValue(
            $modx->db->select('COUNT(*)', $tableTmplvarTmpl,
                "tmplvarid = " . (int)$tvId . " AND templateid = " . (int)$templateId)
        );
        if ($exists) continue;

        $modx->db->insert(
            ['tmplvarid' => (int)$tvId, 'templateid' => (int)$templateId],
            $tableTmplvarTmpl
        );
    }
}
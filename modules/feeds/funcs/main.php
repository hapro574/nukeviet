<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Apr 20, 2010 10:47:41 AM
 */

if (! defined('NV_IS_MOD_RSS')) {
    die('Stop!!!');
}

/**
 * nv_get_rss_link()
 *
 * @return
 */
function nv_get_rss_link()
{
    global $db, $nv_Cache, $module_data, $global_config, $site_mods;
    $contentrss = '';

    foreach ($site_mods as $mod_name => $mod_info) {
        if ($mod_info['rss'] == 1 and isset($mod_info['alias']['rss']) and file_exists(NV_ROOTDIR . '/modules/' . $mod_info['module_file'] . '/funcs/rss.php')) {
            $mod_data = $mod_info['module_data'];
            $mod_file = $mod_info['module_file'];

            $contentrss .= "<li><span><i class=\"fa fa-rss text-warning\"></i> <a rel=\"nofollow\" title=\"" . $mod_info['custom_title'] . "\" href=\"" . NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $mod_name . "&amp;" . NV_OP_VARIABLE . "=" . $mod_info['alias']['rss'] . "\"><strong> " . $mod_info['custom_title'] . "</strong></span></a>";
            if (file_exists(NV_ROOTDIR . '/modules/' . $mod_file . '/rssdata.php')) {
                $rssarray = array();
                include NV_ROOTDIR . '/modules/' . $mod_file . '/rssdata.php' ;

                $contentrss .= "<ul>";
                foreach ($rssarray as $key => $value) {
                    $parentid = (isset($value['parentid'])) ? $value['parentid'] : 0;
                    if ($parentid == 0) {
                        $contentrss .= "<li><span><i class=\"fa fa-rss text-warning\"></i> <a rel=\"nofollow\" title=\"" . $value['title'] . "\" href=\"" . $value['link'] . "\">" . $value['title'] . "</a></span>";
                        $catid = (isset($value['catid'])) ? $value['catid'] : 0;
                        if ($catid > 0) {
                            $contentrss .= nv_get_sub_rss_link($rssarray, $catid);
                        }
                        $contentrss .= "</li>";
                    }
                }
                $contentrss .= "</ul>";
                $contentrss .= "</li>";
            }
        }
    }
    return $contentrss;
}

/**
 * nv_get_sub_rss_link()
 *
 * @param mixed $rssarray
 * @param mixed $id
 * @param mixed $image
 * @return
 */
function nv_get_sub_rss_link($rssarray, $id)
{
    $content = '';
    $content .= '<ul>';
    foreach ($rssarray as $value) {
        if (isset($value['parentid']) and $value['parentid'] == $id) {
            $content .= "<li><span><i class=\"fa fa-rss text-warning\"></i> <a rel=\"nofollow\" title=\"" . $value['title'] . "\" href=\"" . $value['link'] . "\">" . $value['title'] . "</a></span>";
            $catid = (isset($value['catid'])) ? $value['catid'] : 0;
            if ($catid > 0) {
                $content .= nv_get_sub_rss_link($rssarray, $catid);
            }
            $content .= "</li>";
        }
    }
    $content .= '</ul>';
    return $content;
}

$page_title = $module_info['site_title'];
$base_url_rewrite = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name, true);
$base_url_check = str_replace('&amp;', '&', $base_url_rewrite);
$request_uri = rawurldecode($_SERVER['REQUEST_URI']);
if (str_starts_with($request_uri, $base_url_check)) {
    $canonicalUrl = NV_MAIN_DOMAIN . $base_url_rewrite;
} elseif (str_starts_with(NV_MY_DOMAIN . $request_uri, $base_url_check)) {
    $canonicalUrl = $base_url_rewrite;
} else {
    nv_redirect_location($base_url_check);
}

$array = '';
$content_file = NV_ROOTDIR . '/' . NV_DATADIR . '/' . NV_LANG_DATA . '_' . $module_data . 'Content.txt';
if (file_exists($content_file)) {
    $array = file_get_contents($content_file);
}

$contents = nv_rss_main_theme($array);

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';

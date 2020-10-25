<?php

// $Id: index.php,v 1.2 2006/03/27 08:20:23 mikhail Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       < http://xoops.eti.br >                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

$xoopsOption['pagetype'] = 'search';

require dirname(__DIR__, 2) . '/mainfile.php';

if (!defined('XOOPS_ROOT_PATH')) {
    exit;
}
$mydirname = basename(__DIR__);

$configHandler = xoops_getHandler('config');
$xoopsConfigSearch = $configHandler->getConfigsByCat(XOOPS_CONF_SEARCH);

if (1 != $xoopsConfigSearch['enable_search']) {
    header('Location: ' . XOOPS_URL . '/index.php');

    exit();
}
$myts = MyTextSanitizer::getInstance();
$action = isset($_REQUEST['action']) ? $myts->stripSlashesGPC($_REQUEST['action']) : 'search';
$query = isset($_REQUEST['query']) ? $myts->stripSlashesGPC($_REQUEST['query']) : '';
$andor = isset($_REQUEST['andor']) ? $myts->stripSlashesGPC($_REQUEST['andor']) : 'AND';
$mid = isset($_REQUEST['mid']) ? (int)$_REQUEST['mid'] : 0;
$uid = isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$sug = isset($_REQUEST['sug']) ? (int)$_REQUEST['sug'] : 0;
$showcontext = isset($_REQUEST['showcontext']) ? (int)$_REQUEST['showcontext'] : 1;
$mids_p = $_REQUEST['mids'] ?? '';
$mids = [];
if (is_array($mids_p)) {
    foreach ($mids_p as $e) {
        $mids[] = (int)$e;
    }
}
$query = str_replace(_MD_NBSP, ' ', $query);
$queries = [];
$mb_suggest = [];
$mb_suggest_w = [];
if ('results' == $action && '' == $query) {
    redirect_header('index.php', 1, _MD_PLZENTER);

    exit();
}

if ('showall' == $action && ('' == $query || empty($mid))) {
    redirect_header('index.php', 1, _MD_PLZENTER);

    exit();
}

if ('showallbyuser' == $action && (empty($mid) || empty($uid))) {
    redirect_header('index.php', 1, _MD_PLZENTER);

    exit();
}

$groups = ($xoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
$gpermHandler = xoops_getHandler('groupperm');
$available_modules = $gpermHandler->getItemIds('module_read', $groups);
require XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/include/function.php';

if ('search' == $action) {
    require XOOPS_ROOT_PATH . '/header.php';

    $GLOBALS['xoopsOption']['template_main'] = 'search_index.html';

    require XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/include/searchform.php';

    $search_form = $search_form->render();

    //Do not remove follows

    $search_form .= '<p><a href="http://www.suin.jp" target="_blank">search</a>(<a href="http://jp.xoops.org/" target="_blank">original</a>)</p>';

    $xoopsTpl->assign('search_form', $search_form);

    require XOOPS_ROOT_PATH . '/footer.php';

    exit();
}

if ('OR' != $andor && 'exact' != $andor && 'AND' != $andor) {
    $andor = 'AND';
}

if ('showallbyuser' != $action) {
    if ('exact' != $andor) {
        $ignored_queries = []; // holds kewords that are shorter than allowed minmum length

        $temp_queries = preg_preg_split('/[\s,]+/', $query);

        foreach ($temp_queries as $q) {
            $q = trim($q);

            if (mb_strlen($q) >= $xoopsConfigSearch['keyword_min']) {
                $queries[] = $myts->addSlashes($q);

                //for EUC-JP

                if (function_exists('mb_convert_kana') && function_exists('mb_detect_encoding')) {
                    if (preg_match(_MD_PREG_ZESU, $q) && 'EUC-JP' == mb_detect_encoding($q, mb_detect_order(), true)) { //Zenkaku Eisu
                        $mb_suggest[] = mb_convert_kana($myts->addSlashes($q), 'a') . _MD_HANKAKU_EISU;

                        $mb_suggest_w[] = mb_convert_kana($myts->addSlashes($q), 'a');
                    } elseif (preg_match(_MD_PREG_HESU, $q)) { //Hankaku Eisu
                        $mb_suggest[] = mb_convert_kana($myts->addSlashes($q), 'A') . _MD_ZENKAKU_EISU;

                        $mb_suggest_w[] = mb_convert_kana($myts->addSlashes($q), 'A');
                    } elseif (preg_match(_MD_PREG_ZKANA, $q) && 'EUC-JP' == mb_detect_encoding($q, mb_detect_order(), true)) { //Zenkaku Katakana
                        $mb_suggest[] = mb_convert_kana($myts->addSlashes($q), 'k') . _MD_HANKAKU_EISU;

                        $mb_suggest_w[] = mb_convert_kana($myts->addSlashes($q), 'k');
                    } elseif (preg_match(_MD_PREG_HKANA, $q) && 'EUC-JP' == mb_detect_encoding($q, mb_detect_order(), true)) { //Hankaku Katakana
                        $mb_suggest[] = mb_convert_kana($myts->addSlashes($q), 'KV') . _MD_ZENKAKU_EISU;

                        $mb_suggest_w[] = mb_convert_kana($myts->addSlashes($q), 'KV');
                    }

                    //	$mb_suggest_w[] = $myts->addSlashes($q);
                }
            }
        }

        if (0 == count($queries)) {
            redirect_header('index.php', 2, sprintf(_MD_KEYTOOSHORT, $xoopsConfigSearch['keyword_min'], ceil($xoopsConfigSearch['keyword_min'] / 2)));

            exit();
        }
    } else {
        $query = trim($query);

        if (mb_strlen($query) < $xoopsConfigSearch['keyword_min']) {
            redirect_header('index.php', 2, sprintf(_MD_KEYTOOSHORT, $xoopsConfigSearch['keyword_min'], ceil($xoopsConfigSearch['keyword_min'] / 2)));

            exit();
        }

        $queries = [$myts->addSlashes($query)];
    }
}
switch ($action) {
    case 'results':
        $moduleHandler = xoops_getHandler('module');
        $criteria = new CriteriaCompo(new Criteria('hassearch', 1));
        $criteria->add(new Criteria('isactive', 1));
        $criteria->add(new Criteria('mid', '(' . implode(',', $available_modules) . ')', 'IN'));
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $result = $db->query('SELECT mid FROM ' . $db->prefix('search') . ' WHERE notshow!=0');
        while (list($badmid) = $db->fetchRow($result)) {
            $criteria->add(new Criteria('mid', $badmid, '!='));
        }
        $modules = $moduleHandler->getObjects($criteria, true);
        if (0 == count($modules)) {
            redirect_header('index.php', 3, _MD_UNABLE_TO_SEARCH);

            exit();
        }
        if (empty($mids) || !is_array($mids)) {
            unset($mids);

            $mids = array_keys($modules);
        }
        require XOOPS_ROOT_PATH . '/header.php';
        $xoopsTpl->assign('xoops_module_header', '<link rel="stylesheet" type="text/css" media="screen" href="' . XOOPS_URL . '/modules/' . $mydirname . '/include/search.css">');
        $GLOBALS['xoopsOption']['template_main'] = 'search_result.html';
        $xoopsTpl->assign('lang_search_results', _MD_SEARCHRESULTS);
        $xoopsTpl->assign('lang_keyword', _MD_KEYWORDS);
        if ('exact' != $andor) {
            foreach ($queries as $q) {
                $keywords = [];

                $keywords['key'] = htmlspecialchars(stripslashes($q), ENT_QUOTES | ENT_HTML5);

                $xoopsTpl->append('keywords', $keywords);
            }

            if (!empty($ignored_queries)) {
                $xoopsTpl->assign('lang_ignoredwors', sprintf(_MD_IGNOREDWORDS, $xoopsConfigSearch['keyword_min']));

                foreach ($ignored_queries as $q) {
                    $badkeywords = [];

                    $badkeywords['key'] = htmlspecialchars(stripslashes($q), ENT_QUOTES | ENT_HTML5);

                    $xoopsTpl->append('badkeywords', $badkeywords);
                }
            }
        } else {
            $keywords = [];

            $keywords['key'] = '"' . htmlspecialchars(stripslashes($queries[0]), ENT_QUOTES | ENT_HTML5) . '"';

            $xoopsTpl->append('keywords', $keywords);
        }
        if (count($mb_suggest) > 0 && 1 != $sug) {
            $xoopsTpl->assign('lang_sugwords', _MD_KEY_WORD_SUG);

            $sug_url = XOOPS_URL . '/modules/' . $mydirname . '/index.php';

            $sug_url .= '?andor=' . $andor;

            foreach ($mids as $m) {
                $sug_url .= '&mids%5B%5D=' . $m;
            }

            $sug_url .= '&action=' . $action;

            $sug_url .= '&sug=1';

            $xoopsTpl->assign('sug_url', $sug_url);

            foreach ($mb_suggest as $k => $m) {
                $sug_keys = [];

                $sug_keys['key'] = htmlspecialchars(stripslashes($m), ENT_QUOTES | ENT_HTML5);

                $sug_keys['url'] = $sug_url . '&query=' . urlencode(stripslashes($mb_suggest_w[$k]));

                $xoopsTpl->append('sug_keys', $sug_keys);
            }
        }
        foreach ($mids as $mid) {
            $mid = (int)$mid;

            if (in_array($mid, $available_modules, true)) {
                $module = &$modules[$mid];

                $this_mod_dir = $module->getVar('dirname');

                $use_context = false;

                if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/plugin/' . $this_mod_dir . '/' . $this_mod_dir . '.php') && 1 == $xoopsModuleConfig['search_display_text']) {
                    require_once XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/plugin/' . $this_mod_dir . '/' . $this_mod_dir . '.php';

                    $func = 'b_search_' . $this_mod_dir;

                    $results1 = context_search($func, $queries, $andor, 5, 0);

                    $use_context = true;
                } else {
                    $results1 = &$module->search($queries, $andor, 5, 0);
                }

                if (count($mb_suggest_w) > 0) {
                    if ($use_context) {
                        $results2 = context_search($func, $mb_suggest_w, $andor, 5, 0);
                    } else {
                        $results2 = &$module->search($mb_suggest_w, $andor, 5, 0);
                    }
                } else {
                    $results2 = [];
                }

                $results = array_merge($results1, $results2);

                usort($results, 'sort_by_date');

                $count = count($results);

                if ($count > 5) {
                    $results = array_slice($results, 0, 5);

                    $count = 5;
                }

                if (!is_array($results) || 0 == $count) {
                    $no_match = _SR_NOMATCH;

                    $showall_link = '';
                } else {
                    $no_match = '';

                    for ($i = 0; $i < $count; $i++) {
                        if (isset($results[$i]['image']) && '' != $results[$i]['image']) {
                            $results[$i]['image'] = '/modules/' . $this_mod_dir . '/' . $results[$i]['image'];
                        } else {
                            $results[$i]['image'] = '/modules/' . $mydirname . '/images/posticon.gif';
                        }

                        $results[$i]['title'] = htmlspecialchars($results[$i]['title'], ENT_QUOTES | ENT_HTML5);

                        $results[$i]['link'] = '/modules/' . $module->getVar('dirname') . '/' . $results[$i]['link'];

                        $results[$i]['time'] = !empty($results[$i]['time']) ? formatTimestamp($results[$i]['time']) : '';

                        $results[$i]['uid'] = !empty($results[$i]['uid']) ? (int)$results[$i]['uid'] : '';

                        if (!empty($results[$i]['uid'])) {
                            $results[$i]['uname'] = XoopsUser::getUnameFromId($results[$i]['uid']);
                        }
                    }

                    if (5 == $count) {
                        $search_url = XOOPS_URL . '/modules/' . $mydirname . '/index.php?query=' . urlencode(stripslashes(implode(' ', $queries)));

                        $search_url .= "&amp;mid=$mid&amp;action=showall&amp;andor=$andor&amp;showcontext=$showcontext";

                        $showall_link = '<a href="' . $search_url . '">' . _MD_SHOWALLR . '</a>';
                    } else {
                        $showall_link = '';
                    }
                }

                $xoopsTpl->append('modules', ['name' => $module->getVar('name'), 'results' => $results, 'showall_link' => $showall_link, 'no_match' => $no_match]);
            }

            unset($results1);

            unset($results2);

            unset($results);

            unset($module);
        }
        include 'include/searchform.php';
        $search_form = $search_form->render();
        //Do not remove follows
        $search_form .= '<p><a href="http://www.suin.jp" target="_blank">search</a>(<a href="http://jp.xoops.org/" target="_blank">original</a>)</p>';
        $xoopsTpl->assign('search_form', $search_form);
        break;
    case 'showall':
    case 'showallbyuser':
        require XOOPS_ROOT_PATH . '/header.php';
        $xoopsTpl->assign('xoops_module_header', '<link rel="stylesheet" type="text/css" media="screen" href="' . XOOPS_URL . '/modules/' . $mydirname . '/include/search.css">');
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $result = $db->query('SELECT mid FROM ' . $db->prefix('search') . ' WHERE notshow!=0');
        $undisplayable = [];
        while (list($badmid) = $db->fetchRow($result)) {
            $undisplayable[] = $badmid;
        }
        if (in_array($mid, $undisplayable, true) || !in_array($mid, $available_modules, true)) {
            redirect_header('index.php', 1, _NOPERM);

            exit();
        }
        $GLOBALS['xoopsOption']['template_main'] = 'search_result_all.html';
        $moduleHandler = xoops_getHandler('module');
        $module = $moduleHandler->get($mid);
        $this_mod_dir = $module->getVar('dirname');
        $use_context = false;
        if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/plugin/' . $this_mod_dir . '/' . $this_mod_dir . '.php') && 1 == $xoopsModuleConfig['search_display_text']) {
            require_once XOOPS_ROOT_PATH . '/modules/' . $mydirname . '/plugin/' . $this_mod_dir . '/' . $this_mod_dir . '.php';

            $func = 'b_search_' . $this_mod_dir;

            $results1 = context_search($func, $queries, $andor, 20, $start, $uid);

            $use_context = true;
        } else {
            $results1 = $module->search($queries, $andor, 20, $start, $uid);
        }
        if (count($mb_suggest_w) > 0) {
            if ($use_context) {
                $results2 = context_search($func, $mb_suggest_w, $andor, 20, $start, $uid);
            } else {
                $results2 = $module->search($mb_suggest_w, $andor, 20, $start, $uid);
            }
        } else {
            $results2 = [];
        }
        $results = array_merge($results1, $results2);
        usort($results, 'sort_by_date');
        $count = count($results);
        if (is_array($results) && $count > 0) {
            $next_results = $module->search($queries, $andor, 1, $start + 20, $uid);

            $next_count = count($next_results);

            $has_next = false;

            if (is_array($next_results) && 1 == $next_count) {
                $has_next = true;
            }

            $xoopsTpl->assign('lang_search_results', _MD_SEARCHRESULTS);

            if ('showall' == $action) {
                $xoopsTpl->assign('lang_keyword', _MD_KEYWORDS);

                if ('exact' != $andor) {
                    foreach ($queries as $q) {
                        $keywords = [];

                        $keywords['key'] = htmlspecialchars(stripslashes($q), ENT_QUOTES | ENT_HTML5);

                        $xoopsTpl->append('keywords', $keywords);
                    }
                } else {
                    $keywords = [];

                    $keywords['key'] = '"' . htmlspecialchars(stripslashes($queries[0]), ENT_QUOTES | ENT_HTML5) . '"';

                    $xoopsTpl->append('keywords', $keywords);
                }
            }

            $xoopsTpl->assign('showing', sprintf(_MD_SHOWING, $start + 1, $start + $count));

            $xoopsTpl->assign('module_name', htmlspecialchars($module->getVar('name'), ENT_QUOTES | ENT_HTML5));

            for ($i = 0; $i < $count; $i++) {
                if (isset($results[$i]['image']) && '' != $results[$i]['image']) {
                    $results['image'] = '/modules/' . $module->getVar('dirname') . '/' . $results[$i]['image'];
                } else {
                    $results['image'] = '/modules/' . $mydirname . '/images/posticon.gif';
                }

                $results['title'] = htmlspecialchars($results[$i]['title'], ENT_QUOTES | ENT_HTML5);

                $results['link'] = '/modules/' . $module->getVar('dirname') . '/' . $results[$i]['link'];

                $results['time'] = $results[$i]['time'] ? formatTimestamp($results[$i]['time']) : '';

                $results['uid'] = (int)$results[$i]['uid'];

                $results['context'] = !empty($results[$i]['context']) ? $results[$i]['context'] : '';

                if (!empty($results[$i]['uid'])) {
                    $results['uname'] = XoopsUser::getUnameFromId($results[$i]['uid']);
                }

                $xoopsTpl->append('results', $results);
            }

            $navi = '<table><tr>';

            $search_url = XOOPS_URL . '/modules/' . $mydirname . '/index.php?query=' . urlencode(stripslashes(implode(' ', $queries)));

            $search_url .= "&mid=$mid&action=$action&andor=$andor&showcontext=$showcontext";

            if ('showallbyuser' == $action) {
                $search_url .= "&uid=$uid";
            }

            if ($start > 0) {
                $prev = $start - 20;

                $navi .= "\n" . '<td align="left">';

                $search_url_prev = $search_url . "&start=$prev";

                $navi .= "\n" . '<a href="' . htmlspecialchars($search_url_prev, ENT_QUOTES | ENT_HTML5) . '">' . _MD_PREVIOUS . '</a></td>';
            }

            $navi .= "\n" . '<td>&nbsp;&nbsp;</td>';

            if (false !== $has_next) {
                $next = $start + 20;

                $search_url_next = $search_url . "&start=$next";

                $navi .= "\n" . '<td align="right"><a href="' . htmlspecialchars($search_url_next, ENT_QUOTES | ENT_HTML5) . '">' . _MD_NEXT . '</a></td>';
            }

            $navi .= "\n" . '</tr></table>';

            $xoopsTpl->assign('navi', $navi);
        } else {
            $xoopsTpl->assign('no_match', _MD_NOMATCH);
        }
        include 'include/searchform.php';
        $search_form = $search_form->render();
        //Do not remove follows
        $search_form .= '<p><a href="http://www.suin.jp" target="_blank">search</a>(<a href="http://jp.xoops.org/" target="_blank">original</a>)</p>';
        $xoopsTpl->assign('search_form', $search_form);
        break;
}
require XOOPS_ROOT_PATH . '/footer.php';

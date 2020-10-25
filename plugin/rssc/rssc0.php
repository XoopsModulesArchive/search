<?php

// $Id: rssc0.php,v 1.1 2006/03/27 01:08:56 mikhail Exp $

//================================================================
// Search Module
// plugin for rssc 0.11 <http://linux.ohwada.jp>
// 2006-02-01 K.OHWADA <http://linux.ohwada.jp>
//================================================================

$rssc_dirname = basename(__DIR__);

// --- eval begin ---
eval(
    '

function b_search_' . $rssc_dirname . '( $queryarray , $andor , $limit , $offset , $userid )
{
	return b_search_rssc_base( \'' . $rssc_dirname . '\' , $queryarray , $andor , $limit , $offset , $userid ) ;
}

'
);
// --- eval end ---

// --- rssc_search_base begin ---
if (!function_exists('b_search_rssc_base')) {
    function b_search_rssc_base($dirname, $queryarray, $andor, $limit, $offset, $userid)
    {
        global $xoopsDB;

        $table_config = $xoopsDB->prefix($dirname . '_config');

        $table_feed = $xoopsDB->prefix($dirname . '_feed');

        $showcontext = $_GET['showcontext'] ?? 0;

        // config data

        $conf_data = [];

        $sql1 = 'SELECT * FROM ' . $table_config . ' ORDER BY conf_id ASC';

        $res1 = $xoopsDB->query($sql1, 0, 0);

        if (!$res1) {
            return $block;
        }

        while (false !== ($row1 = $xoopsDB->fetchArray($res1))) {
            $conf_data[$row1['conf_name']] = $row1['conf_value'];
        }

        $future_days = $conf_data['basic_future_days'];

        $future = time() + 86400 * $future_days;    // days

        // search

        $where = '';

        if (0 != $userid) {
            $where .= 'uid=' . $userid . ' ';
        }

        // because count() returns 1 even if a supplied variable

        // is not an array, we must check if $querryarray is really an array

        if (is_array($queryarray) && $count = count($queryarray)) {
            if ($where) {
                $where .= 'AND ';
            }

            $where .= "( search LIKE '%$queryarray[0]%' ";

            for ($i = 1; $i < $count; $i++) {
                $where .= "$andor ";

                $where .= "search LIKE '%$queryarray[$i]%' ";
            }

            $where .= ') ';
        }

        $sql2 = 'SELECT * FROM ' . $table_feed;

        $sql2 .= ' WHERE ' . $where;

        $sql2 .= ' AND updated_unix <' . $future;

        $sql2 .= ' AND published_unix <' . $future;

        $sql2 .= ' ORDER BY updated_unix DESC';

        $result = $xoopsDB->query($sql2, $limit, $offset);

        $ret = [];

        $i = 0;

        // use to sanitize description

        $myts = MyTextSanitizer::getInstance();

        while (false !== ($myrow = $xoopsDB->fetchArray($result))) {
            $ret[$i]['image'] = 'images/home.gif';

            $ret[$i]['link'] = 'singlelink.php?fid=' . $myrow['fid'];

            $ret[$i]['title'] = $myrow['title'];

            $ret[$i]['time'] = $myrow['updated_unix'];

            $ret[$i]['uid'] = 0;

            if ((1 == $showcontext) && (!empty($myrow['content']))) {
                // description begin

                $context = $myrow['content'];

                $context = preg_replace('>/', '> ', $context);

                $context = strip_tags($context);

                $ret[$i]['context'] = search_make_context($context, $queryarray);

                // description end
            }

            $i++;
        }

        return $ret;
    }

    // --- rssc_search_base end ---
}
?>

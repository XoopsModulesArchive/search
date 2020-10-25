<?php

// $Id: weblinks.php,v 1.1 2006/03/27 01:08:56 mikhail Exp $

//================================================================
// Search Module
// plugin for weblinks 0.97 <http://linux.ohwada.jp>
// 2006-02-01 K.OHWADA <http://linux.ohwada.jp>
//================================================================

function b_search_weblinks($queryarray, $andor, $limit, $offset, $userid)
{
    global $xoopsDB;

    $table_link = $xoopsDB->prefix('weblinks_link');

    $showcontext = $_GET['showcontext'] ?? 0;

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

    $sql = "SELECT * FROM $table_link WHERE $where ORDER BY time_update DESC";

    $result = $xoopsDB->query($sql, $limit, $offset);

    $ret = [];

    $i = 0;

    // use to sanitize description

    $myts = MyTextSanitizer::getInstance();

    while (false !== ($myrow = $xoopsDB->fetchArray($result))) {
        $ret[$i]['image'] = 'images/home.gif';

        $ret[$i]['link'] = 'singlelink.php?lid=' . $myrow['lid'];

        $ret[$i]['title'] = $myrow['title'];

        $ret[$i]['time'] = $myrow['time_update'];

        $ret[$i]['uid'] = $myrow['uid'];

        if ((1 == $showcontext) && (!empty($myrow['description']))) {
            // description begin
            $context = $myts->displayTarea($myrow['description'], 0);    // no html
            $context = preg_replace('>/', '> ', $context);

            $context = strip_tags($context);

            $ret[$i]['context'] = search_make_context($context, $queryarray);

            // description end
        }

        $i++;
    }

    return $ret;
}

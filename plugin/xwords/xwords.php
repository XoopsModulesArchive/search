<?php

/**
 * $Id: xwords.php,v 1.1 2006/03/27 01:08:58 mikhail Exp $
 * Module: Wordbook
 * Version: v 1.00
 * Release Date: 8 May 2004
 * Author: hsalazar
 * Licence: GNU
 */

// FILE		::	xwords.php v0.02
// AUTHOR	::	aiba <WEBMASTER @ KANPYO.NET>
// WEB		::	KANPYO <http://www.kanpyo.net>
//

if (!defined('XOOPS_ROOT_PATH')) {
    exit;
}
function b_search_xwords($queryarray, $andor, $limit, $offset, $userid)
{
    $xoopsDB = XoopsDatabaseFactory::getDatabaseConnection();

    $xoopsModule = XoopsModule::getByDirname('xwords');

    $ret = [];

    if (0 != $userid) {
        return $ret;
    }

    $showcontext = $_GET['showcontext'] ?? 0;

    if (1 == $showcontext) {
        $sql = 'SELECT entryID, term, proc, definition, uid, datesub FROM ' . $xoopsDB->prefix('xwords_ent') . ' WHERE submit = 0 AND offline = 0 ';
    } else {
        $sql = 'SELECT entryID, term, proc, uid, datesub FROM ' . $xoopsDB->prefix('xwords_ent') . ' WHERE submit = 0 AND offline = 0 ';
    }

    // because count() returns 1 even if a supplied variable

    // is not an array, we must check if $querryarray is really an array

    $count = count($queryarray);

    if ($count > 0 && is_array($queryarray)) {
        $sql .= "AND ((term LIKE '%$queryarray[0]%' OR proc LIKE '%,%$queryarray[0]%' OR definition LIKE '%$queryarray[0]%')";

        for ($i = 1; $i < $count; $i++) {
            $sql .= " $andor ";

            $sql .= "(term LIKE '%$queryarray[$i]%' OR proc LIKE '%,%$queryarray[$i]%' OR definition LIKE '%$queryarray[$i]%')";
        }

        $sql .= ') ';
    }

    $sql .= 'ORDER BY entryID DESC';

    $result = $xoopsDB->query($sql, $limit, $offset);

    $i = 0;

    $myts = MyTextSanitizer::getInstance();

    while (false !== ($myrow = $xoopsDB->fetchArray($result))) {
        $ret[$i]['image'] = 'images/wb.png';

        $ret[$i]['link'] = 'entry.php?entryID=' . $myrow['entryID'];

        $ret[$i]['title'] = $myrow['term'];

        $ret[$i]['time'] = $myrow['datesub'];

        $ret[$i]['uid'] = $myrow['uid'];

        if (!empty($myrow['definition'])) {
            $context = $myrow['definition'];

            $context = strip_tags($myts->displayTarea(strip_tags($context)));

            $ret[$i]['context'] = search_make_context($context, $queryarray);
        }

        $i++;
    }

    return $ret;
}

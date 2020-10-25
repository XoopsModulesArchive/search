<?php

// $Id: xfsection.php,v 1.1 2006/03/27 01:08:57 mikhail Exp $

//================================================================
// Search Module
// plugin for xfsection 1.10 <http://linux.ohwada.jp>
// 2006-02-01 K.OHWADA <http://linux.ohwada.jp>
//================================================================

function b_search_xfsection($queryarray, $andor, $limit, $offset, $userid)
{
    global $xoopsDB;

    $table_article = $xoopsDB->prefix('xfs_article');

    $showcontext = $_GET['showcontext'] ?? 0;

    $where = 'published>0 AND published<=' . time() . ' ';

    if (0 != $userid) {
        $where .= 'AND uid=' . $userid . ' ';
    }

    // because count() returns 1 even if a supplied variable

    // is not an array, we must check if $querryarray is really an array

    if (is_array($queryarray) && $count = count($queryarray)) {
        $where .= "AND ( (maintext LIKE '%$queryarray[0]%' OR title LIKE '%$queryarray[0]%' OR summary LIKE '%$queryarray[0]%') ";

        for ($i = 1; $i < $count; $i++) {
            $where .= "$andor ";

            $where .= "(maintext LIKE '%$queryarray[$i]%' OR title LIKE '%$queryarray[$i]%' OR summary LIKE '%$queryarray[$i]%') ";
        }

        $where .= ') ';
    }

    $sql = 'SELECT * FROM ' . $table_article . ' WHERE ' . $where . ' ORDER BY published DESC';

    $result = $xoopsDB->query($sql, $limit, $offset);

    $ret = [];

    $i = 0;

    // use to sanitize description

    $myts = MyTextSanitizer::getInstance();

    while (false !== ($myrow = $xoopsDB->fetchArray($result))) {
        $ret[$i]['image'] = 'images/wf.gif';

        $ret[$i]['link'] = 'article.php?page=1&amp;articleid=' . $myrow['articleid'];

        $ret[$i]['title'] = $myrow['title'];

        $ret[$i]['time'] = $myrow['published'];

        $ret[$i]['uid'] = $myrow['uid'];

        if ((1 == $showcontext) && (!empty($myrow['maintext']))) {
            // description begin

            $html = 1;

            $smiley = 1;

            $xcodes = 1;

            $image = 1;

            $br = 1;

            $amp = 0;

            if ($myrow['nohtml']) {
                $html = 0;
            }

            if ($myrow['nosmiley']) {
                $smiley = 0;
            }

            if ($myrow['nobr']) {
                $br = 0;
            }

            if ($myrow['enaamp']) {
                $amp = 1;
            }

            $maintext = $myrow['maintext'];

            $maintextarr = explode('[pagebreak]', $maintext);

            $maintext = $maintextarr[0];

            $maintext = $myts->displayTarea($maintext, $html, $smiley, $xcodes, $image, $br);

            $context = preg_replace('>/', '> ', $maintext);

            $context = strip_tags($context);

            $ret[$i]['context'] = search_make_context($context, $queryarray);

            // description end
        }

        $i++;
    }

    return $ret;
}

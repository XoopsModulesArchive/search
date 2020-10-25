<?php

// search plugin for BluesBB-1.xx
// version 0.12
// Author: Sting_Band
// URL: http://www.bluish.jp/

function b_search_bluesbb($queryarray, $andor, $limit, $offset, $userid)
{
    $db = XoopsDatabaseFactory::getDatabaseConnection();

    global $xoopsUser, $memberHandler;

    $myts = MyTextSanitizer::getInstance();

    $sql = 'SELECT b.post_id,b.topic_id,b.thread_id,b.res_id,b.title,b.message,b.post_time,b.uid,t.topic_style FROM ' . $db->prefix('bluesbb') . ' b LEFT JOIN ' . $db->prefix('bluesbb_topic') . ' t ON t.topic_id=b.topic_id WHERE';

    if (is_object($xoopsUser)) {
        $sql .= ' (t.topic_access = 1 OR t.topic_access = 2 OR t.topic_access = 3 OR t.topic_access = 4 OR t.topic_access = 5';

        $groups = $memberHandler->getGroupsByUser($xoopsUser->getVar('uid'), true);

        foreach ($groups as $group) {
            $sql .= ' OR t.topic_group = ' . $group->getVar('groupid');
        }

        if ($xoopsUser->isAdmin()) {
            $sql .= ' OR t.topic_access = 6';
        }
    } else {
        $sql .= ' (t.topic_access = 1 OR t.topic_access = 2 OR t.topic_access = 5';
    }

    $sql .= ')';

    if (0 != $userid) {
        $sql .= ' AND b.uid=' . $userid . ' ';
    }

    if (is_array($queryarray) && $count = count($queryarray)) {
        $sql .= " AND ((b.name LIKE '%$queryarray[0]%' OR b.mail LIKE '%$queryarray[0]%' OR b.url LIKE '%$queryarray[0]%' OR b.title LIKE '%$queryarray[0]%' OR b.message LIKE '%$queryarray[0]%')";

        for ($i = 1; $i < $count; $i++) {
            $sql .= " $andor ";

            $sql .= "(b.name LIKE '%$queryarray[$i]%' OR b.mail LIKE '%$queryarray[$i]%' OR b.url LIKE '%$queryarray[$i]%' OR b.title LIKE '%$queryarray[$i]%' OR b.message LIKE '%$queryarray[$i]%')";
        }

        $sql .= ') ';
    }

    $sql .= 'ORDER BY b.post_time DESC';

    $result = $db->query($sql, $limit, $offset);

    $ret = [];

    $i = 0;

    while (false !== ($myrow = $db->fetchArray($result))) {
        switch ($myrow['topic_style']) {
            case '1':
                $lt = 'l999#p' . $myrow['post_id'];
                break;
            case '2':
                $lt = ++$myrow['res_id'];
                break;
            case '3':
                $lt = $myrow['post_id'];
                break;
        }

        $ret[$i]['link'] = 'thread.php?thr=' . $myrow['thread_id'] . '&amp;sty=' . $myrow['topic_style'] . '&amp;num=' . $lt;

        $ret[$i]['title'] = htmlspecialchars($myrow['title'], ENT_QUOTES | ENT_HTML5);

        $ret[$i]['time'] = $myrow['post_time'];

        $ret[$i]['uid'] = $myrow['uid'];

        $context = strip_tags($myts->displayTarea($myrow['message'], 0, 1, 1, 1, 1));

        $ret[$i]['context'] = search_make_context($context, $queryarray);

        $i++;
    }

    return $ret;
}

<?php

if (isset($_POST) && isset($_POST['sessionid'])) {
    session_id($_POST['sessionid']);
}

require '../includes/dbconnect.php';

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

$json = new stdClass();
$forumids = forumChildren(15, true);
$json->forums = array();
$json->userid = (int)$session->userid;

if ($session->userid) {
    if ($session->god) {
        $forumids = array_merge($forumids,forumChildren(3, true));
    }
    if ($session->staff) {
        $forumids = array_merge($forumids,forumChildren(2, true));
    }
    if ($session->admin) {
        $forumids = array_merge($forumids,forumChildren(1, true));
    }
}

if ($forumids) {
    $result = mysql_query('
        SELECT forumid, title, parentid 
        FROM forum WHERE forumid IN ('.mysql_real_escape_string(implode(',',$forumids)).')
        ORDER BY title ASC
        ') or die(__LINE__.__FILE__.mysql_error());
    while ($forum = mysql_fetch_object($result)) {
        $forum->forumid = (int)$forum->forumid;
        $forum->parentid = (int)$forum->parentid;
        $forum->title = htmlspecialchars_decode($forum->title);
        $json->forums[] = $forum;
    }
}

echo json_encode($json);

?>

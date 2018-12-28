<?php

require '../includes/dbconnect.php';

if (!intval($_GET['forumid']) > 0) $_GET['forumid'] = 0;
$_GET['page'] = intval($_GET['page'] > 0 ? $_GET['page'] : 1);
if (!$session->settings) $session->settings = array();
if (in_array($_GET['boxtype'],$BOXTYPES)) $session->settings['boxtype'] = $_GET['boxtype'];
if (strlen($_GET['filter'])) $session->settings['filter-'.$_GET['boxtype']] = $_GET['filter'];
colheaders($session->settings['boxtype'],$_GET['page'],$_GET['forumid']);
updateUserSettings($session);
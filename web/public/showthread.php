<?php

include 'includes/dbconnect.php';

$t = mysql_single('SELECT threadid, forumid, title FROM thread WHERE threadid = \''.mysql_real_escape_string($_GET['t']).'\'',__LINE__.__FILE__);
unset($_GET['t']);

$qs = '';
if ($_GET) $qs = '?'.parseget($_GET);


header('HTTP/1.1 301 Moved Permanently');
header('Location: '.URLPATH.'/'.($t->threadid && okcats($t->forumid) ? urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$t->threadid).'.html'.$qs : ''));
exit();
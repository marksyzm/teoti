<?php

include 'includes/dbconnect.php';

$f = mysql_single('SELECT forumid, title FROM forum WHERE forumid = \''.mysql_real_escape_string($_GET['f']).'\'',__LINE__.__FILE__);


unset($_GET['f']);

$qs = '';
if ($_GET) $qs = '?'.parseget($_GET);

header('HTTP/1.1 301 Moved Permanently');
header('Location: '.URLPATH.'/'.($f->forumid && okcats($f->forumid) ? urlify($f->title).'/'.$qs : ''));
exit();
<?php

include 'includes/dbconnect.php';

$u = mysql_single('SELECT usernameurl FROM user WHERE userid = \''.mysql_real_escape_string($_GET['u']).'\'',__LINE__.__FILE__);

unset($_GET['u']);

$qs = '';
if ($_GET) $qs = '?'.parseget($_GET);

header('HTTP/1.1 301 Moved Permanently');
header('Location: '.URLPATH.'/'.($u->usernameurl ? 'members/'.$u->usernameurl.'.html'.$qs : ''));
exit();
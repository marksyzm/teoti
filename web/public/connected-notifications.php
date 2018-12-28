<?php

include_once 'includes/dbconnect.php';
$result = mysql_single('SELECT * FROM gcmuser ORDER BY dateline DESC LIMIT 1', __LINE__.__FILE__);

$result = mysql_query('
    SELECT user.username, gcmuser.dateline FROM gcmuser 
    LEFT JOIN user ON user.userid = gcmuser.userid
    ORDER BY dateline
    ') or die(__LINE__.__FILE__.mysql_error());
	
while ($user = mysql_fetch_object($result)) {
    echo $user->username," last connected on ",date(DATETIMEFORMAT,$user->dateline),"<br />\n";
}

?>
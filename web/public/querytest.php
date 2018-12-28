<?php
$starttime = array_sum(explode(' ',microtime()));
include "includes/dbconnect.php";

//$result = mysql_query("SELECT activity.*, SUM(extra) as `total`, MIN(dateline) AS mindateline, user.username, user.avatar FROM activity LEFT JOIN user ON (user.userid = activity.userid) WHERE forumid IN (16,17,18,19,20,21,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,48,54,55,58,59,61,63,64,65,66,67) AND dateline > 0 AND activity.userid != 2 GROUP BY type, itemid ORDER BY dateline DESC LIMIT 10");
$result = mysql_query("SELECT activity.*, extra as `total`, dateline AS mindateline, user.username, user.avatar FROM activity LEFT JOIN user ON (user.userid = activity.userid) WHERE forumid IN (16,17,18,19,20,21,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,48,54,55,58,59,61,63,64,65,66,67) AND dateline > 0 AND activity.userid != 2 ORDER BY dateline DESC LIMIT 10");
$i = 0;
while ($row = mysql_fetch_object($result)) {
    var_dump($i++);
}
echo "<br>".round(array_sum(explode(' ',microtime())) - $starttime,3).' seconds';

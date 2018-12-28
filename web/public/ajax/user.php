<?
require '../includes/dbconnect.php';

$result = mysql_query('
	SELECT username, userid FROM user 
	WHERE username LIKE \''.mysql_real_escape_string($_GET['q']).'%\'
	'.($session->staff ? '':'AND usergroupid NOT IN ('.BANNEDGROUPS.')').'
	ORDER BY username
	LIMIT 25
	') or die(__LINE__.__FILE__.mysql_error());
while ($user = mysql_fetch_object($result)) echo $user->username,'|',$user->userid,"\n";

<?
require_once '../includes/dbconnect.php';

if (strlen($_GET['p']) > 50) $_GET['p'] = '0';

$result = mysql_query('
	SELECT userid,username FROM post_thanks 
	WHERE postid = \''.mysql_real_escape_string((int)$_GET['p']).'\'
	AND scored '.($_GET['plusminus'] ? '>' : '<').' 0
	') or die(__LINE__.__FILE__.mysql_error());

if (mysql_num_rows($result) <= MAXPOINTLISTSIZE && mysql_num_rows($result)) {
	$i=0;
	echo 'The following people ',($_GET['plusminus'] ? '' : 'dis'),'like this:<br />';
	while ($pt = mysql_fetch_object($result))
		echo ($i++ ? ', ':''),
		//$pt->username
		userlink($pt->username,$pt->userid),PHP_EOL
		;
} else {
	echo (string)mysql_num_rows($result),' people ',($_GET['plusminus'] ? '' : 'dis'),'like this.';
}

/*
$points = new stdClass();	
$points->pointitemsize = mysql_num_rows($result);
$points->pointitemminus = $posts->pointitemplus = 0;
while ($pt = mysql_fetch_object($presult)) {
	$pointitem = new stdClass();
	$pt->scored > 0 ? $points->pointitemplus++ : $points->pointitemminus++;
	if ($posts->pointitemsize <= MAXPOINTSIZE) {
		$pointitem->username = $pt->username;
		$pointitem->usernameurl = urlify($pt->username);
		$pointitem->plusminus =  $pt->scored > 0 ? 'plus':'minus';
	}
	$points->pointitems[] = $pointitem;
}

echo json_encode($points);
*/
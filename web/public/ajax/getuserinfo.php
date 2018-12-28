<?
require '../includes/dbconnect.php';

$u = mysql_single('SELECT userid,usernameurl,viewing FROM user WHERE username = \''.mysql_real_escape_string($_GET['username']).'\'',__LINE__.__FILE__);

$userinfo = new stdClass();
if ($u->userid) {
	$t = mysql_single('SELECT forumid,title FROM thread WHERE threadid = \''.mysql_real_escape_string($u->viewing).'\'',__LINE__.__FILE__);
	
	$userinfo->error = false;
	$userinfo->profile = URLPATH.'/members/'.$u->usernameurl.'.html';
	if (okcats($t->forumid)) {
		$userinfo->threadlink = URLPATH.'/'.urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$u->viewing).'.html';
		$userinfo->threadtitle = $t->title;
	}
	$userinfo->conversation = URLPATH.'/conversation?do=new&include='.$u->userid;
	$userinfo->posts = URLPATH.'/?which=posts&userid='.$u->userid;
	$userinfo->threads = URLPATH.'/?which=threads&userid='.$u->userid;
	$userinfo->points = URLPATH.'/?which=points&userid='.$u->userid;
} else {
	$userinfo->error = true;
}

echo json_encode($userinfo);
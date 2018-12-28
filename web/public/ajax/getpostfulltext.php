<?
require '../includes/dbconnect.php';

$postfulltext = new stdClass();

$postfulltext->error = false;

$p = mysql_single('SELECT threadid,pagetext,username FROM post WHERE postid = \''.mysql_real_escape_string($_GET['p']).'\'',__LINE__.__FILE__);
if ($p->pagetext) {
	$t = mysql_single('SELECT forumid FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
	if (okcats($t->forumid)) {
		$postfulltext->quote = $p->pagetext;
		$postfulltext->username = $p->username;
	} else $postfulltext->error = true;
} else $postfulltext->error = true;

echo json_encode($postfulltext);
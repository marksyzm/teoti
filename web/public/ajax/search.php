<?
require '../includes/dbconnect.php';

$search = new stdClass();
$search->users = array();
$search->threads = array();
$search->conversations = array();

$result = mysql_query('
	SELECT username, userid, avatar, usernameurl FROM user 
	WHERE username LIKE \''.mysql_real_escape_string($_GET['q']).'%\'
	OR username LIKE \'% '.mysql_real_escape_string($_GET['q']).'%\'
	'.($session->staff ? '':'AND usergroupid NOT IN ('.BANNEDGROUPS.')').'
	LIMIT 3
	') or die(__LINE__.__FILE__.mysql_error());
$i = 0;
while ($user = mysql_fetch_object($result)) {
	$user->avatar = rawurlencode($user->avatar ? 'avatar/'.$user->avatar : 'error.png');
	$user->link = 'members/'.$user->usernameurl.'.html';
	$search->users[] = $user;
	$i++;
}

global $session;
$view = $where = array();

if (!$session->staff) $where[] = 'thread.visible = 1';
$listposts = false;

//generate forums applicable


$forumid = (int)$_GET['forumid'];
$userid = (int)$_GET['userid'];

$view = $forumid && okcats($forumid) ? $forumid : 15;

$where[] = 'thread.forumid IN ('.implode(',',forumChildren($view)).')';

if (in_array($_GET['which'],array('threads','posts','points')) && $session->userid) {
	switch($_GET['which']) {
		case 'points': //user posts (pointed only, <>)
			$where[] = 'post.post_thanks_amount <> 0';
		case 'posts': //user posts
			$where[] = 'post.threadid = thread.threadid';
			$listposts = true;
			break;
		case 'threads': //user threads
        default:
            $where[] = 'post.postid = thread.firstpostid';
	}
} 

if ($userid > 0) {
	$where[] = 'post'.($listposts ? '.':'').'userid = \''.mysql_real_escape_string($userid).'\'';
}
		
$_GET['q'] = trim($_GET['q']);

if ($_GET['q']) {
	if (strlen($_GET['q']) > 100) $_GET['q'] = substr($_GET['q'],0,100);
	if (!$listposts) {
		$where[] = 'post.threadid = thread.threadid';
		$groupby = 'GROUP BY thread.threadid ';
	}
	$filterquery = ',MATCH(post.title,post.pagetext) AGAINST(\''.mysql_real_escape_string($_GET['q']).'\') as Relevance';
	$where[] = 'MATCH(post.title,post.pagetext) AGAINST(\'+('.mysql_real_escape_string($_GET['q']).')\' IN BOOLEAN MODE)';
}

$result = mysql_query('
	SELECT 
		thread.thumbnail, thread.title,thread.forumid,thread.threadid,thread.firstpostid,thread.postusername,thread.postuserid
		'.($filterquery ? $filterquery : '')
		.($listposts ? ',post.dateline, post.postid,post.dateline as pdateline, post.title as ptitle, post.userid, post.username, post.pagetext':'').'
	FROM  '.($listposts ||$filterquery ? 'post,':'').'thread
	'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
	'.$groupby.' ORDER BY '.($filterquery ? 'Relevance' : ($listposts ? 'post.dateline':'thread.lastpost')).' DESC
	LIMIT 3
	') or die(__LINE__.__FILE__.mysql_error());
	
if (mysql_num_rows($result)) {
	while ($t = mysql_fetch_object($result)) {
		$t->otitle = $t->title; //otitle = original title, othreadid...
		$t->othreadid = $t->threadid;
		if ($listposts) {
			//set all variables from thread to post vars
			$t->firstpostid = $t->postid;
			$t->threadid = $t->postid;
			$t->postuserid = $t->userid;
			$t->postusername = $t->username;
			$t->title = $t->ptitle ? $t->ptitle : ($t->title ? $t->title : 'Post '.$t->postid);
		}
		$u = mysql_single('SELECT avatar, usernameurl FROM user WHERE userid = \''.mysql_real_escape_string($t->postuserid).'\'',__LINE__.__FILE__);
		$thread = new stdClass();
		$thread->username = $t->postusername;
		$thread->thumbnail = $t->thumbnail ? $t->thumbnail : 'avatar/'.$u->avatar;
		$thread->link = urlify(forumtitle($t->forumid)).'/'.urlify($t->otitle,$t->othreadid).'.html'.($listposts ? '?p='.$t->postid : '');
		$thread->title = $t->ptitle ? $t->ptitle : ($t->title ? $t->title : 'Post '.$t->postid);
		$search->threads[] = $thread;
	}
}





echo json_encode($search);
<?
require_once '../includes/dbconnect.php';

if ($_GET['do'] == 'noteNudge') {
	$json = new stdClass();
	$json->notes = array();
	
	//get available notetypes
	$notetype = mysql_single('
		SELECT GROUP_CONCAT(notetypeid SEPARATOR \',\') AS ids FROM notetype ORDER BY notetypeid
		',__LINE__.__FILE__);
		
	//check notetypes against those disabled by user
	$usernotetype = mysql_single('
		SELECT GROUP_CONCAT(notetypeid SEPARATOR \',\') AS ids FROM usernotetype WHERE userid = \''.mysql_real_escape_string($session->userid).'\' ORDER BY notetypeid
		',__LINE__.__FILE__);
	
	if ($notetype->ids != $usernotetype->ids){ //if the ids are the same in both coumns
		$getnotes = true;	
		$noteids = implode(',',array_diff(explode(',',$notetype->ids),explode(',',$usernotetype->ids)));
	} else {
		$getnotes = false;
	}
	
	
	if ($getnotes){
		$result = mysql_query('
			SELECT *, GROUP_CONCAT(DISTINCT fromuserid ORDER BY dateline SEPARATOR \',\') AS fromuserids  
			FROM notification
			WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
			AND type IN ('.mysql_real_escape_string($noteids).')
			GROUP BY `group`,extra ORDER BY dateline DESC LIMIT 5
			') or die(__LINE__.__FILE__.mysql_error());
		while ($n = mysql_fetch_object($result)) {
			$nt = mysql_single('
				SELECT name FROM notetype WHERE notetypeid = \''.mysql_real_escape_string($n->type).'\'
				',__LINE__.__FILE__);
				
			$note = new stdClass();
			
			if ((int)$_GET['t'] > 0 && $_GET['t'] == $n->itemid && $nt->name == 'newpost') continue;
			$note->noteid = $n->noteid;
			$users = array();
			$userids = explode(',',$n->fromuserids);
			if (count($userids) <= MAXNOTIFYUSERS) foreach ($userids as $userid) {
				$nuser = mysql_single('SELECT username FROM user WHERE userid = \''.mysql_real_escape_string($userid).'\'',__LINE__.__FILE__);
				$users[] = $nuser->username ? $nuser->username : 'Anon';	
			}
			
			switch($nt->name){
				case 'newpost':
					$p = mysql_single('SELECT threadid FROM post WHERE postid = \''.mysql_real_escape_string($n->itemid).'\'',__LINE__.__FILE__);
					$t = mysql_single('SELECT forumid,title FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
					$note->link = URLPATH.'/'.urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$p->threadid).'.html?p='.$n->itemid;
					$note->message = (count($userids) <= MAXNOTIFYUSERS ? implode(', ',$users) : count($userids).' users').' add new posts in '.$t->title;
					break;
				case 'likedislike':
					$p = mysql_single('SELECT threadid FROM post WHERE postid = \''.mysql_real_escape_string($n->itemid).'\'',__LINE__.__FILE__);
					$t = mysql_single('SELECT forumid,title FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
					$note->link = URLPATH.'/'.urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$p->threadid).'.html?p='.$n->itemid;
					$note->message = (count($userids) <= MAXNOTIFYUSERS ? implode(', ',$users) : count($userids).' users').' '.$n->extra.' your post in '.$t->title;
					break;
				case 'conversation':
					$pm = mysql_single('SELECT title FROM pmtext WHERE pmtextid = \''.mysql_real_escape_string($n->itemid).'\'',__LINE__.__FILE__);
					$note->link = URLPATH.'/conversation?pm='.$n->itemid;
					$note->message = (count($userids) <= MAXNOTIFYUSERS ? implode(', ',$users) : count($userids).' users').' added new messages in '.$pm->title;
					break;
				case 'extrapoints':
					$p = mysql_single('SELECT threadid FROM post WHERE postid = \''.mysql_real_escape_string($n->itemid).'\'',__LINE__.__FILE__);
					$t = mysql_single('SELECT forumid,title FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
					$note->link = URLPATH.'/'.urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$p->threadid).'.html?p='.$n->itemid;
					$note->message = (count($userids) <= MAXNOTIFYUSERS ? implode(', ',$users) : count($userids).' users').' gave '.$n->extra.' points for your post in '.$t->title;
					break;
			}
			$json->notes[] = $note;
		}
	}
	echo json_encode($json);
}

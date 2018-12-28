<?

if ($_GET['do'] == 'removemessage') {
	$_SESSION['removemessage'] = 1;
	if (!$_GET['ajax']) header('Location: '.STRIP_REQUEST_URI);
	exit();
}

if ($_GET['do'] == 'oldnew') {
	if (!$session->settings) $session->settings = array();
	$session->settings['old'] = $session->settings['old'] ? false : true;
	updateUserSettings($session);
	header('Location: '.STRIP_REQUEST_URI);
	exit;
}

if ($_GET['do'] == 'subscribe' && (int)$_GET['f'] && !in_array((int)$_GET['f'],array(1,2,3,15))) {
	$forumids = array((int)$_GET['f']);
	
	$children = forumChildren((int)$_GET['f']);
	if ($children) $forumids = $children;
	
	if ($forumids) {
		$subscribe = mysql_single('
			SELECT forumid FROM subscribeforum 
			WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
			AND forumid IN ('.mysql_real_escape_string(implode(',',$forumids)).')
			',__LINE__.__FILE__);
			
		if ($subscribe->forumid) {
			mysql_query('
				DELETE FROM subscribeforum
				WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				AND forumid IN ('.mysql_real_escape_string(implode(',',$forumids)).')
				') or die(__LINE__.__FILE__.mysql_error());
		} else {
			foreach ($forumids as $forumid) {
				mysql_query('
					INSERT INTO subscribeforum SET
					userid = \''.mysql_real_escape_string($session->userid).'\'
					,forumid = \''.mysql_real_escape_string($forumid).'\'
					,emailupdate = \'2\'
					') or die(__LINE__.__FILE__.mysql_error());
			}
		}
	}
	
	header('Location: '.STRIP_REQUEST_URI);
	exit;
}

if ($_POST['do'] == 'insert' && $session->userid) {
	$json = (object)$_POST;
    if ($_POST['type'] == 'shout' && trim($_POST['shout'])) {
        mysql_query('
            INSERT INTO shout SET
            s_by = \''.mysql_real_escape_string($session->userid).'\'
            ,s_time = UNIX_TIMESTAMP()
            ,s_shout = \''.mysql_real_escape_string((string)$_POST['shout']).'\'
            ,forumid = \''.mysql_real_escape_string((int)($_POST['forumid'] == 15 ? 0: $_POST['forumid'])).'\'
            ') or die(__LINE__.__FILE__.mysql_error());
        
        $json->id = mysql_insert_id();
        $json->errors = array();
        $json->forumid = (int)$_POST['forumid'];
    } else {
        $json->errors = array('You must insert text in order to create a shout.');
    }
    echo json_encode($json);
	exit();
}

if (in_array($_GET['toggle'],array('sfw','nsfw','both','latest','updated'))) {
	/*$toggle = explode(',',$_SESSION['toggle']);
	
	if (!in_array('sfw',$toggle) && !in_array('nsfw',$toggle)) $toggle = array_merge($toggle,array('sfw'));
	if ($_GET['toggle'] == 'sfw' && in_array('sfw',$toggle) && in_array('nsfw',$toggle)) {
		foreach ($toggle as $k => $v) if ($v == 'sfw') { unset($toggle[$k]); break;	}
	} elseif ($_GET['toggle'] == 'sfw' && !in_array('sfw',$toggle) && in_array('nsfw',$toggle)) $toggle[] = 'sfw';
	if ($_GET['toggle'] == 'nsfw' && in_array('nsfw',$toggle) && in_array('sfw',$toggle)) {
		foreach ($toggle as $k => $v) if ($v == 'nsfw') { unset($toggle[$k]); break;	}
	} elseif ($_GET['toggle'] == 'nsfw' && !in_array('nsfw',$toggle) && in_array('sfw',$toggle)) $toggle[] = 'nsfw';
	
	if (!in_array('updated',$toggle) && !in_array('latest',$toggle)) $toggle = array_merge($toggle,array('updated'));
	if ($_GET['toggle'] == 'updated' && in_array('latest',$toggle)) foreach ($toggle as $k => $v) if ($v == 'latest') {unset($toggle[$k]); $toggle[] = 'updated'; break;} 
	if ($_GET['toggle'] == 'latest' && in_array('updated',$toggle)) foreach ($toggle as $k => $v) if ($v == 'updated') {unset($toggle[$k]); $toggle[] = 'latest'; break;} 
	
	$_SESSION['toggle'] = implode(',',$toggle);*/
	
	if (!$session->settings) $session->settings = array();
	
	if (in_array($_GET['toggle'],array('latest','updated'))) 
		$session->settings['filter-threads'] = $_GET['toggle'];
	
	if (in_array($_GET['toggle'],array('nsfw','sfw','both')))
		$session->settings['filter-rating'] = $_GET['toggle'];
		
	updateUserSettings($session);
	

	if (!($_GET['do'] == 'ajax')) header('Location: '.STRIP_REQUEST_URI) AND exit();
}
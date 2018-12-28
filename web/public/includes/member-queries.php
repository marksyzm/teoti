<?

$check = array();

if (in_array($_REQUEST['do'],array('insert','update','delete')) && $session->userid) {
	if ($session->userid != $u->userid) $check[] = 'You are not allowed to manage another user\'s blog posts';
	else {
		if (in_array($_POST['do'],array('insert','update')) && $_GET['type'] == 'blog') {
			if (strlen($_POST['textarea_message']) > MAXPOSTLENGTH) $check[] = 'Your blog post must less than '.MAXPOSTLENGTH.' characters';
			if (strlen($_POST['textarea_message']) < MINPOSTLENGTH) $check[] = 'Your blog post must be more than '.(MINPOSTLENGTH-1).' characters';
			if (strlen($_POST['title']) > MAXTITLELENGTH) $check[] = 'Your blog title must be less than '.MAXTITLELENGTH.' characters';
		}
		
		if (in_array($_POST['do'],array('update')) && $_GET['type'] == 'userpage') {
			if (strlen($_POST['pagedata']) > MAXPOSTLENGTH) $check[] = 'Your userpage must less than '.MAXPOSTLENGTH.' characters';
		}
	}
}

if ($_POST['do'] == 'insert' && $session->userid == $u->userid){
	if ($_POST['type'] == 'blog') {
		if (!$check) {
			mysql_query('
				INSERT INTO usernote SET
				userid = \''.mysql_real_escape_string($session->userid).'\'
				,posterid = \''.mysql_real_escape_string($session->userid).'\'
				,dateline = \''.mysql_real_escape_string($time = time()).'\'
				,message = \''.mysql_real_escape_string($_POST['textarea_message']).'\'
				,title = \''.mysql_real_escape_string($_POST['title']).'\'
				,allowsmilies = 1
				') or die(__LINE__.__FILE__.mysql_error());		
			header('Location: '.STRIP_REQUEST_URI);
			exit();	
		}
		
	}
}

if ($_POST['do'] == 'update' && ($session->userid == $u->userid || $session->staff)){
	if ($_POST['type'] == 'userpage') {
		$result = mysql_query('
			SELECT userid FROM userpage WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		
		if (mysql_num_rows($result)) {
			mysql_query('
				UPDATE userpage SET 
				pagedata = \''.mysql_real_escape_string($_POST['pagedata']).'\'
				,dateline = \''.mysql_real_escape_string(time()).'\'
				WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
		} else {
			mysql_query('
				INSERT INTO userpage SET 
				userid = \''.mysql_real_escape_string($u->userid).'\'
				,pagedata = \''.mysql_real_escape_string($_POST['pagedata']).'\'
				,dateline = \''.mysql_real_escape_string(time()).'\'
				') or die(__LINE__.__FILE__.mysql_error());
		}
		
		header('Location: '.STRIP_REQUEST_URI);
		exit();
	}
	
	if ($_POST['type'] == 'blog' && $_GET['usernoteid'] > 0) {
		if (!$check) {
			mysql_query('
				UPDATE usernote SET
				,message = \''.mysql_real_escape_string($_POST['textarea_message']).'\'
				,title = \''.mysql_real_escape_string($_POST['title']).'\'
				WHERE usernoteid = \''.mysql_real_escape_string($_POST['usernoteid']).'\'
				') or die(__LINE__.__FILE__.mysql_error());		
			header('Location: '.STRIP_REQUEST_URI);
			exit();	
		} else {
			$_GET['do'] = 'edit';
		}
	}
}

if ($_GET['do'] == 'delete' && ($session->userid == $u->userid || $session->staff)) {
	if ($_GET['type'] == 'blog' && (int)$_GET['un'] > 0) {
		if (!$check) {
			mysql_query('
				DELETE FROM usernote WHERE usernoteid = \''.mysql_real_escape_string((int)$_GET['un']).'\'
				') or die(__LINE__.__FILE__.mysql_error());		
			header('Location: '.STRIP_REQUEST_URI);
			exit();	
		}
	}
}

if ($_REQUEST['ajax']) {
	if ($check) echo 'error:'.implode('|',$check);
	exit();
}

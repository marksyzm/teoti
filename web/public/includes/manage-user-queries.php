<?
//inserts, updates, deletes, ajax calls
if ($_REQUEST['name'] == 'ajax' && $session->staff) {
	if ($_REQUEST['type'] == 'passwordreset' && $_REQUEST['value'] > 0) {
		$user = mysql_single('
			SELECT * 
			FROM user 
			WHERE userid = \''.mysql_real_escape_string($_REQUEST['value']).'\' 
			',__LINE__.__FILE__);
		
		if ($user->userid > 0){
			$password=generatepw();
			$to = $user->email;
			$name = $userid = $user->username;
			$subject = CLIENTNAME.' - Information';
			$message = '
Here is your '.CLIENTNAME.' username and new password!

Username: '.$userid.'

Password: '.$password;
			$htmlmessage = eregi_replace('[\]','',$htmlmessage);
			include_once PATH.'/classes/class.phpmailer.php';
			$mail = new PHPMailer();
			$mail->From = CLIENTEMAIL;
			$mail->FromName = CLIENTNAME;
			$mail->Subject = $subject;
			$mail->AltBody = $message;
			$mail->MsgHTML(sprintf(EMAILTEMPLATE,$subject,nl2br($message)));
			$mail->AddAddress($to,$name);
			if($mail->Send()) {
				//do update
				$salt = rand(100,999);
				mysql_query('
					UPDATE user SET 
					password = \''.md5(md5($password).$salt).'\'
					,salt = \''.$salt.'\'
					,passworddate = CURDATE()
					WHERE uID = \''.mysql_real_escape_string($_REQUEST['value']).'\'
					') or die(mysql_error());
					
				if ($_SESSION['uid'] == $user->uID) {
					$_SESSION['uinfo'] = md5($password);
				}
				echo '<p class="yellowbox"><span>A new password has been emailed to this user.</span></p>';
				exit();
			}
			echo '<p class="yellowbox"><span>Sorry, the change of password was unsuccessful.</span></p>';
		}	
	}
	
	if ($_REQUEST['type'] == 'woemail') {	
		$result = mysql_query('
			SELECT DISTINCT userid, username
			FROM user
			WHERE usergroupid NOT IN ('.BANNEDGROUPS.')
			AND usergroupid > 0
			AND (email = \'\' OR email IS NULL)
			') or die(mysql_error());
		echo '<p class="yellowbox"><span>users without email<br/><br />';
		while ($user = mysql_fetch_object($result)) 
			echo '<a href="',$_SERVER['PHP_SELF'],'?itemid=',$user->userid,'">',$user->username,'</a><br />';
		echo '</span></p>';
	}
		
	
	if ($_REQUEST['type'] == 'inactive') {	
		$result = mysql_query('
			SELECT DISTINCT userid, username
			FROM user
			WHERE usergroupid IN ('.BANNEDGROUPS.')
			OR usergroupid = 0
			OR usergroupid IS NULL
			') or die(mysql_error());
		echo '<p class="yellowbox"><span>Inactive Users<br/><br />';
		while ($user = mysql_fetch_object($result)) 
			echo '<a href="',$_SERVER['PHP_SELF'],'?itemid=',$user->userid,'">',$user->username,'</a><br />';
		echo '</span></p>';
	}
	
	if ($_REQUEST['type'] == 'duplicate') {	
		$result = mysql_query('
			SELECT 
				username AS getname, 
				username, userid,
				GROUP_CONCAT(userid SEPARATOR \',\') AS `groupid`,  
				COUNT(username) AS `cnt`
			FROM user
			WHERE usergroupid NOT IN ('.BANNEDGROUPS.')
			AND usergroupid > 0
			GROUP BY `getname`
			HAVING `cnt` > 1
			ORDER BY `cnt`
			') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
		echo '<p class="yellowbox"><span>Duplicate users<br/><br />';
		while ($user = mysql_fetch_object($result)) {
			echo '<a href="',$_SERVER['PHP_SELF'],'?itemid=',$user->userid,'">',$user->username,'</a> ';
			$link = split(',',$user->groupid);
			$ctr = 0;
			foreach ($link as $v) echo '<a href="',$_SERVER['PHP_SELF'],'?itemid=',$v,'">[',(++$ctr),']</a> ';
			echo '<br />';
		}
		echo '</span></p>';
	}
	
	
	if ($_REQUEST['type'] == 'alphanames') {	
		$result = mysql_query('
			SELECT DISTINCT userid, username
			FROM user
			WHERE usergroupid NOT IN ('.BANNEDGROUPS.')
			AND usergroupid > 0
			AND username LIKE \''.mysql_real_escape_string($_GET['value']).'%\'
			') or die(mysql_error());
		echo '<p class="yellowbox"><span>Names beginning with \''.$_GET['value'].'\'<br/><br />';
		while ($user = mysql_fetch_object($result)) 
			echo '<a href="',$_SERVER['PHP_SELF'],'?itemid=',$user->userid,'" class="displayblock">',$user->username,'</a>';
		echo '</span></p>';
	}
	
	exit();
}

if (in_array($_REQUEST['do'], array('insert','update','delete')) && $session->admin){
	if ($_POST['do'] == 'insert') {
		$error = '';
		//do the checks
		$user = mysql_single('
			SELECT username FROM user WHERE username = \''.mysql_real_escape_string($_POST['username']).'\'
			',__LINE__.__FILE__);
		if ($user->username) $error .= 'The username: '.$user->username.' has already been taken.<br />';
		$user = mysql_single('
			SELECT email,username FROM user WHERE email = \''.mysql_real_escape_string($_POST['email']).'\'
			',__LINE__.__FILE__);
		if ($user->email) $error .= 'The email address: '.$user->email.' is already in the system under the username: .'.$user->username.'<br />';
		if (!strstr($_POST['email'],'@')) $error .= 'The address given is not a valid email address.';
		if (!trim($_POST['username'])) $error .= 'You must give a username.';
		if (strlen($_POST['username']) < 3) $error .= 'Usernames must be more than 3 characters.';
		if (strlen($_POST['username']) > 30) $error .= 'Usernames must not be more than 25 characters.';
		if (preg_match('/\W\s/',$_POST['username'])) $error .= 'Usernames may not contain spaces or non-word characters.';
		
		
		if (!$error) {
			$password = $_POST['password'] ? $_POST['password'] : generatepw();
			$salt = rand(100,999);
			if ($_POST['autoemail']) {
				//get userid
				$to = $_POST['email'];
				$name = trim($_POST['username']); 
				$subject = CLIENTNAME.' - Information';
			
				$message = '
Here is your '.CLIENTNAME.' username and new password!

Username: '.$name.'

Password: '.$password;
				
				include_once PATH.'/classes/class.phpmailer.php';
				$mail = new PHPMailer();
				$mail->From = CLIENTEMAIL;
				$mail->FromName = CLIENTNAME;
				$mail->Subject = $subject;
				$mail->AltBody = $message;
				$mail->MsgHTML(sprintf(EMAILTEMPLATE,$subject,nl2br($message)));
				$mail->AddAddress($to,$name);
				if(!$mail->Send()) $error .= 'The server could not mail to that address at this point.';
			}
		}
		
		if ($_FILES['avatar']['name']) $image = send_image_to_file('avatar');
		if (is_string($image)) $error .= $image.'<br />';
		
		if (trim($_POST['email']) && trim($_POST['username']) && !$error) {
			
			mysql_query('
				INSERT INTO user SET 
				usergroupid = \''.mysql_real_escape_string($_POST['usergroupid']).'\'
				,username = \''.mysql_real_escape_string($_POST['username']).'\'
				,usernameurl = \''.mysql_real_escape_string(urlify($_POST['username'])).'\'
				,usertitle = \''.mysql_real_escape_string(strip_tags($_POST['usertitle'])).'\'
				,email = \''.mysql_real_escape_string($_POST['email']).'\'
				,location = \''.mysql_real_escape_string(strip_tags($_POST['location'])).'\'
				,password = \''.mysql_real_escape_string(md5(md5($password).$salt)).'\'
				,salt = \''.mysql_real_escape_string($salt).'\'
				,passworddate = CURDATE()
				,joindate = \''.time().'\'
				'.(is_array($image) && count($image) ? ','.implode(',',$image):'').'
				') or die(mysql_error());
				
			$insertid = mysql_insert_id();
		}
		
		if (!$error) $message = 'The user: '.$_POST['username'].' has been successfully created.';
		
		header('Location: '.$_SERVER['PHP_SELF'].'?itemid='.$insertid.urlencode($message ? '&message='.$message : '&error='.$error ));
		exit();
	}
	
	if ($_POST['do'] == 'update' && $_POST['itemid'] > 0) {
		if($_POST['password']) $password = $_POST['password'];
		$salt = rand(100,999);
		
		//do the checks
		$user = mysql_single('
			SELECT * FROM user WHERE userid = \''.mysql_real_escape_string($_POST['itemid']).'\'
			',__LINE__.__FILE__);
		$testuser = mysql_single('
			SELECT username FROM user WHERE username = \''.mysql_real_escape_string($_POST['username']).'\'
			',__LINE__.__FILE__);
		if ($testuser->username && $user->username != $testuser->username) $error .= 'The username: '.$user->username.' has already been taken.<br />';
		$testuser = mysql_single('
			SELECT username FROM user WHERE email = \''.mysql_real_escape_string($_POST['email']).'\'
			',__LINE__.__FILE__);
		if ($testuser->email && $user->email != $testuser->email) $error .= 'The email address: '.$user->email.' is already in the system under the username: .'.$user->username.'<br />';
		if (!strstr($user->email,'@')) $error .= 'The address given is not a valid email address.';
		if (!trim($_POST['username'])) $error .= 'You must give a username.';
		if (strlen($_POST['username']) < 3) $error .= 'Usernames must be more than 3 characters.';
		if (strlen($_POST['username']) > 30) $error .= 'Usernames must not be more than 25 characters.';
		if (preg_match('/\W\s/',$_POST['username'])) $error .= 'Usernames may not contain spaces or non-word characters.';
		$user = mysql_single('
			SELECT userid FROM user WHERE userid = \''.mysql_real_escape_string($_POST['itemid']).'\'
			',__LINE__.__FILE__);
		
		if ($_FILES['avatar']['name']) $image = send_image_to_file('avatar');
		if (is_string($image)) $error .= $image.'<br />';
		
		if ($user->userid > 0 && !$error) {
			$ctr = 0;
			mysql_query('
				UPDATE user SET 
				usergroupid = \''.mysql_real_escape_string($_POST['usergroupid']).'\'
				,username = \''.mysql_real_escape_string($_POST['username']).'\'
                ,usernameurl = \''.mysql_real_escape_string(urlify($_POST['username'])).'\'
				,usertitle = \''.mysql_real_escape_string(strip_tags($_POST['usertitle'])).'\'
				'.($password ? '
					,password = \''.mysql_real_escape_string(md5(md5($password).$salt)).'\'
					,salt = \''.mysql_real_escape_string($salt).'\'
					':'').'
				,email = \''.mysql_real_escape_string($_POST['email']).'\'
				,location = \''.mysql_real_escape_string(strip_tags($_POST['location'])).'\'
				'.(is_array($image) && count($image) ? ','.implode(',',$image):'').'
				WHERE userid = \''.mysql_real_escape_string($_POST['itemid']).'\'
				') or die(mysql_error());
		}
		
		if (!$error) $message = 'The user: '.$_POST['username'].' has been successfully updated.';
		
		header('Location: '.$_SERVER['PHP_SELF'].'?itemid='.$_POST['itemid'].($message ? '&message='.urlencode($message) : '&error='.urlencode($error) ));
		exit();
	}
	
	if ($_REQUEST['do'] == 'delete' && $_REQUEST['itemid'] > 0) {
		$user = mysql_single('
			SELECT username FROM user 
			WHERE userid != \''.mysql_real_escape_string($session->userid).'\'
			AND userid = \''.mysql_real_escape_string($_REQUEST['itemid']).'\'
			',__LINE__.__FILE__);
		
		if ($user->userid) {
			mysql_query('
				DELETE FROM user WHERE userid = \''.mysql_real_escape_string($_REQUEST['itemid']).'\'
				') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
			$message = 'The user: '.$user->username.' has been successfully deleted.';
		} else $error = 'You can\'t delete this user. They either don\'t exist or the person you are trying to delete is you. ';
		header('Location: '.$_SERVER['PHP_SELF'].'?'.urlencode($message ? 'message='.$message : 'error='.$error ));
		exit();
	}
}
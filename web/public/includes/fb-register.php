<?php
	//get user details
  $u = mysql_single('SELECT userid FROM user WHERE fbid = \''.mysql_real_escape_string($fb['id']).'\'',__LINE__.__FILE__);
	
	if ($fb['id'] && $fb['verified']) {
		if (!$u->userid) {
			//if not exists then first check if facebook username matches a user
			$u = mysql_single('
				SELECT userid FROM user 
				WHERE (username = \''.mysql_real_escape_string($fb['username']).'\' OR email = \''.mysql_real_escape_string($fb['email']).'\' OR fbid = \''.mysql_real_escape_string($fb['id']).'\')
				AND facebook = 0 AND fbid = 0
				',__LINE__.__FILE__);
			$mergeuserid = $u->userid;
			
			//if so prompt the user to merge the account with password belonging to the other account...
			if ($_REQUEST['merge']) { //but first retrieve the password if a form was sent via the prompt
				//check that it worked, get userid if it did else throw error and send password to merge script
				$u = mysql_single('SELECT userid,salt FROM user WHERE userid = \''.mysql_real_escape_string($mergeuserid).'\'',__LINE__.__FILE__);
				$u = mysql_single('
					SELECT userid 
					FROM user 
					WHERE password = \''.mysql_real_escape_string(md5(md5($_REQUEST['merge']).$u->salt)).'\'
					AND userid = \''.mysql_real_escape_string($mergeuserid).'\'
					',__LINE__.__FILE__);
					
				if (!$u->userid) $error = 'Sorry, the password you gave is not the same as the one belonging to the account you are trying to merge with.';
				else {
					mysql_query('
						UPDATE user SET fbid = \''.mysql_real_escape_string($fb['id']).'\' WHERE userid = \''.mysql_real_escape_string($mergeuserid).'\'
						') or die(__LINE__.__FILE__.mysql_error());
					
				}
			}
			
			//prompt...
			if ((($mergeuserid && !$_REQUEST['merge']) || (!$u->userid && $_REQUEST['merge'])) && !$_REQUEST['ignore']) {
				//get the merge form
				include PATH.'/facebook-merge.php';
				exit;
			}
						 
			
			//else register the user
			if ($_REQUEST['ignore'] || !$mergeuserid) {
				$fbusername = $fb['username'] ? $fb['username'].($mergeuserid ? '-fb':'') : $fb['name'];
				mysql_query('
					INSERT INTO user SET
					username = \''.mysql_real_escape_string($fbusername).'\'
					,usernameurl = \''.mysql_real_escape_string(urlify($fbusername)).'\'
					,location = \''.mysql_real_escape_string($fb['location']['name'] ? $fb['location']['name'] : $fb['hometown']['name']).'\'
					,usertitle = \'\'
					,email = \''.mysql_real_escape_string($fb['email']).'\'
					,showbirthday = \'0\'
					,password = \'\'
					,passworddate = CURDATE()
					,salt = \''.mysql_real_escape_string(rand(100,999)).'\'
					,joindate = \''.mysql_real_escape_string($time = time()).'\'
					,lastactivity = \''.$time.'\'
					,activate = \'\'
					,usergroupid = \'2\'
					,timezoneoffset = \''.mysql_real_escape_string(is_numeric('0')).'\'
					,facebook = 1
					,fbid = \''.mysql_real_escape_string($fb['id']).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				$u->userid = mysql_insert_id();
			}
		}
		
		//create the session
		$_SESSION['uid'] = $u->userid;
		
		header('Location: '.STRIP_REQUEST_URI);
		exit();
	}
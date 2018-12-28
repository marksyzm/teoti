<?php
	if ($_POST['do'] == 'Register') {
		$privatekey = $_SERVER['HTTP_HOST'] == 'www.t-six.com' ? '6LdCj7sSAAAAADpysor5QiTlJOj4QcpmgW1wLhEq' : '6LcAkLsSAAAAAJvRqX9YhiKw3RhCh8peByWKtYTu';
		$cerror = '';
		$merrors = $errors = array();
		
		//do the checks
		if (strlen($_POST['password']) < 6) $errors[] = 'Your password must be 6 or more characters long.';
		if ($_POST['password'] != $_POST['password2']) $errors[] = 'Your password does not match the confirmation password.';
		
		if (!$_POST['mobile'] && strtolower(trim($_POST['capital'])) != 'london') {
			$errors[] = 'You didn\'t put London as the capital of England!';
		}
		
		if (!strstr($_POST['email'],'@')) $errors[] = 'The address given is not a valid email address.';
		if ($_POST['email'] != $_POST['email2']) $errors[] = 'The email addresses you gave do not match.';
		$user = mysql_single('
			SELECT email,username FROM user WHERE email = \''.mysql_real_escape_string($_POST['email']).'\'
			',__LINE__.__FILE__);
		if ($user->email) {
			$errors[] = 'The email address: '.$user->email.' is already in the system under a different username.';
			$merrors[] = 7;
		}
		
		$user = mysql_single('
			SELECT username FROM user WHERE username = \''.mysql_real_escape_string(trim($_POST['username'])).'\'
			',__LINE__.__FILE__);
		if ($user->username) {
			$errors[] = 'The username: '.$user->username.' has already been taken.';
			$merrors[] = 6;
		}
		if (!trim($_POST['username'])) $errors[] = 'You must provide a username.';
		if (strlen($_POST['username']) < 3) $errors[] = 'Usernames must be more than 2 characters.';
		if (strlen($_POST['username']) > 20) $errors[] = 'Usernames must not be more than 20 characters.';
		$setting = mysql_single('SELECT value FROM setting WHERE varname = \'illegalusernames\'',__LINE__.__FILE__);
		$illnames = explode(' ',preg_replace('/\s{2,}/',' ',trim($setting->value)));
		foreach ($illnames as $illname) {
			if (strstr($_POST['username'],$illname)) {
				$errors[] = 'Your username must not contain any of the following: '.implode(', ',$illnames).'.';
				$merrors[] = 6;
				break;
			}
		}
		if (strlen($_POST['username']) > 20) $errors[] = 'Usernames must not be more than 20 characters.';
		if (!$_POST['mobile']) {
	    $resp = recaptcha_check_answer ($privatekey,
	                                    $_SERVER["REMOTE_ADDR"],
	                                    $_POST["recaptcha_challenge_field"],
	                                    $_POST["recaptcha_response_field"]);
	
	    if (!$resp->is_valid) {
	    	// set the error code so that we can display it
				$errors[] = 'The security word you supplied was incorrect.';
				$cerror = $resp->error;
			}
		}
		
		
		if (!$errors) {
			$salt = rand(100,999);
			$password = md5(md5($_POST['password']).$salt);
			
			$to = $_POST['email'];
			$name = trim($_POST['username']); 
			$subject = CLIENTNAME.' - Registration and Activation Details';
			
			$activation = rand(10000000,99999999);
			/*You now need to activate your account. Please <a href="'.PROTOCOL.'www.'.OURDOMAIN.'">click here</a> to log into '.COMPANYNAME.'.

and then enter the following code into the activation form:

'.$activation.'

Then y*/
			$message = '
Here is your '.CLIENTNAME.' username:

Username: '.$name.'

You are a fully registered member of '.COMPANYNAME.', where you can create threads, style webpages easily, chat, or simply watch our live webpage in motion.

Thanks for becoming a member.

Click <a href="'.PROTOCOL.$_SERVER['HTTP_HOST'].'">here</a> to enter '.COMPANYNAME.'. 
';
			
			include_once PATH.'/classes/class.phpmailer.php';
			$mail = new PHPMailer();
			$mail->From = CLIENTEMAIL;
			$mail->FromName = CLIENTNAME;
			$mail->Subject = $subject;
			$mail->AltBody = $message;
			$mail->MsgHTML(sprintf(EMAILTEMPLATE,$subject,nl2br($message)));
			$mail->AddAddress($to,$name);
			
			if ($mail->Send()) {
				mysql_query('
					INSERT INTO user SET 
					username = \''.mysql_real_escape_string(trim($_POST['username'])).'\'
					,usernameurl = \''.mysql_real_escape_string(urlify($_POST['username'])).'\'
					,location = \''.mysql_real_escape_string($_POST['location']).'\'
					,usertitle = \''.mysql_real_escape_string($_POST['usertitle']).'\'
					,email = \''.mysql_real_escape_string($_POST['email']).'\'
					,birthday_search = \''.mysql_real_escape_string($_POST['date']).'\'
					,birthday = \''.mysql_real_escape_string(implode('-',array_reverse(explode('-',$_POST['date'])))).'\'
					,password = \''.mysql_real_escape_string($password).'\'
					,passworddate = CURDATE()
					,salt = \''.mysql_real_escape_string($salt).'\'
					,joindate = \''.($time=time()).'\'
					,lastactivity = \''.$time.'\'
					,activate = \'\'
					,usergroupid = \'2\'
					,timezoneoffset = \''.mysql_real_escape_string(is_numeric($_POST['timezoneoffset']) ? $_POST['timezoneoffset'] :'0').'\'
					') or die(__LINE__.__FILE__.mysql_error());
				if ($_POST['mobile']){
					$_SESSION['uid'] = mysql_insert_id();
				} else {
					header('Location: '.URLPATH.'/register?success=1');
					exit;
				}
			} else $errors[] = 'There was a problem sending the email. Please try again or contact the webmaster: webmaster@'.OURDOMAIN;
		}
	}
?>

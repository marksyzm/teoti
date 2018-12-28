<?
if ($_POST['forgot'] && strstr($_POST['forgot'],'@')) {
		//check email exists
	$result = mysql_query('
		SELECT userid, salt, email
		FROM user
		WHERE email = \''.mysql_real_escape_string($_POST['forgot']).'\'
		AND facebook = 0
		') or die("Error: ".mysql_error());
	if (mysql_num_rows($result)) {
		//generate password
		$genpass = mt_rand(10000000,99999999);
		$u = mysql_fetch_object($result);
		mysql_query('
			UPDATE user SET 
			password = \''.mysql_real_escape_string(md5(md5($genpass).$u->salt)).'\' 
			WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		
		$to  = $u->email;
		if (!class_exists('PHPMailer')) include_once PATH.'/classes/class.phpmailer.php';
		$mail = new PHPMailer();
		$txtmessage = '
Here is your new '.OURCOMPANY.' password!
'.$genpass.'';
		// Mail it
		$mail->From = 'noreply@'.OURDOMAIN;
		$mail->FromName = OURCOMPANY;
		$mail->Subject = $subject = 'Password change';
		$mail->AltBody = $txtmessage;
		$mail->MsgHTML(sprintf(EMAILTEMPLATE,$subject,nl2br($txtmessage)));
		$mail->AddAddress($to,$name);
		if ($mail->Send()) $error = 'An email has been sent to you with your new password!'; 
		else $error= 'There was a problem with our mail server! We are currently in the process of amending this.';
	} else $error = 'That email address is not on the system, sorry!'; 
	header('Location: '.($_SERVER['HTTP_REFERER'] ? substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],'?')) : URLPATH.'/').'?error='.urlencode($error)) AND exit();
} elseif ($_POST['login']) {
	if ($_POST['rememberme']) {
        session_set_cookie_params(60*60*24*365); 
    }
    
	$user = mysql_single('SELECT salt FROM user WHERE username = \''.mysql_real_escape_string($_POST['username']).'\'',__LINE__.__FILE__);
    
	$result=mysql_query('	
		SELECT * 
		FROM user
		WHERE '.(strstr($_POST['username'],'@') ? 'email':'username').' = \''.mysql_real_escape_string($_POST['username']).'\' 
		AND password = \''.mysql_real_escape_string(
			preg_match('/^[0-9a-f]{32}$/i',$_POST['password']) ? 
			md5($_POST['password'].$user->salt) : 
			md5(md5($_POST['password']).$user->salt)
		).'\'
		AND facebook = 0
		') or die(__LINE__.mysql_error());
		
	$referrer = $_SERVER['HTTP_REFERER'] ? (strpos($_SERVER['HTTP_REFERER'],'?') ? substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],'?')) : $_SERVER['HTTP_REFERER']) : URLPATH.'/';
	if(mysql_num_rows($result) > 0) {
		$user = mysql_fetch_object($result);
		//a little house keeping...
		mysql_query('
			DELETE FROM `sessions` 
			WHERE DATE_ADD(`lastaccessed`, INTERVAL '.ini_get('session.gc_maxlifetime').' SECOND) < NOW()
			') or die(__LINE__.__FILE__.mysql_error());
		
		mysql_query('
			UPDATE user SET 
			lastvisit = \''.($time = time()).'\'
			,lastactivity = \''.$time.'\'
			WHERE userid = \''.mysql_real_escape_string($user->userid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		
		session_regenerate_id();
		$_SESSION['uid']=$user->userid;
		
		if ($_POST['mobile']){
		} else {
			header('Location: '.$referrer);
			exit;
		}
	} else {
		if ($_POST['mobile']){
			
		} else {
			header(
				'Location: '.$referrer
				.'?error='.urlencode('Login failed, make sure you entered the correct user ID/password')
			);
			exit;
		}
	}
} elseif ($_REQUEST['logout']) {
	$_SESSION = array();
	$phpsession->delete();
	//$phpsession->clean(); //clean up old sessions
	/*$_SESSION = array();// Unset all of the session variables.
	if (session_id() != '' && isset($_COOKIE[session_name()])) setcookie(session_name(),'',time()-42000,'/');
	@session_destroy();*/
	if ($_POST['mobile']){
	} else {
		header('Location: '.($_SERVER['HTTP_REFERER'] ? (strpos($_SERVER['HTTP_REFERER'],'?') ? substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],'?')) : $_SERVER['HTTP_REFERER']) : URLPATH.'/'));
		exit;
	}
}
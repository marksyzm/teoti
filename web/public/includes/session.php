<?

//session_start();
//start the node session
//include PATH.'/classes/class.nodesession.php';
//NodeSession::start('127.0.0.1',1337);

include PATH.'/classes/class.session.php';
$phpsession = new Session();
if (!isset($session)) $session = new stdClass();


if ($_POST['forgot'] || $_POST['login'] || $_REQUEST['logout']) include 'login-forms.php';

if ($_REQUEST['facebook'] && !$_SESSION['uid']){
	//get the fb stuff
	include_once '../classes/facebook.php';
  include_once 'fb-session.php';
  include_once 'fb-register.php';
}

if ($_SESSION['uid'] > 0) {
	$scresult = mysql_query('
		SELECT * FROM user
		WHERE userid = \''.$_SESSION['uid'].'\'
		AND usergroupid NOT IN ('.BANNEDGROUPS.')
		') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
	//check session, kill if !username OR !password
	if (!mysql_num_rows($scresult)) {
		//kill session
		$phpsession->delete();
		/*$_SESSION = array();// Unset all of the session variables.
		if (session_id() != '' && isset($_COOKIE[session_name()])) setcookie(session_name(),'',time()-42000,'/');
		@session_destroy();*/
	} else {
		$session = mysql_fetch_object($scresult);
		$session->god = $session->staff = $session->admin = in_array($session->usergroupid,explode(',',ADMINGROUPS)) ? true : false;
		$session->god = $session->staff = in_array($session->usergroupid,explode(',',MODGROUPS)) || $session->admin ? true : false;
		$session->god = in_array($session->usergroupid,explode(',',GODGROUPS)) || $session->staff ? true : false;
		$session->usergroupid ? $session->usergroupid : 0;
		$session->settings = unserialize($session->settings);
		
		if (isset($_GET['changestyle'])) {
			mysql_query('
				UPDATE user SET styleid = \''.mysql_real_escape_string($_GET['changestyle']).'\' WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			header('Location: '.STRIP_REQUEST_URI) and exit();
		}
		mysql_query('
			UPDATE user SET lastactivity = \''.mysql_real_escape_string(time()).'\' WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		
		unset($updateuser);
		
	}
} else {
	$session->settings = unserialize($_SESSION['settings']);
}

// $phpsession->__destruct();
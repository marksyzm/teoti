<?

require '../includes/dbconnect.php';

if ($session->userid) {
	$settingtypes = array(
		'wysiwyg',
	);
	
	if (in_array($_GET['type'],$settingtypes)) {
		if (!$session->settings) $session->settings = array();
		$session->settings[ $_GET['type'] ] = $_GET['setting'];
		updateUserSettings($session);
	}
}

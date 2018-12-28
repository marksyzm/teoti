<?

include '../includes/dbconnect.php';

if ($session->userid && $_POST['json']) {
	include PATH.'/classes/class.skingen.php';
	$skingen = new SkinGen();
	$skingen->skinid = $session->styleid;
	$skingen->session = $session;
	$skingen->storeCSSFile($css);
	$user = mysql_single('SELECT styleid FROM user WHERE userid = \''.mysql_real_escape_string($session->userid).'\'',__LINE__.__FILE__);
	$skin = mysql_single('SELECT * FROM skins WHERE id = \''.mysql_real_escape_string($user->styleid).'\'',__LINE__.__FILE__);
	echo $skin->id ? 'skins/skin_'.$skin->id.'_'.$skin->version.'.css' : 'skin.css'; 
}
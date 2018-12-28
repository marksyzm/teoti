<?
include '../includes/dbconnect.php';

function unlinkRecursive($dir, $deleteRootToo=false) {
	if(!$dh = @opendir($dir)) return;
	while (false !== ($obj = readdir($dh))) {
		if ($obj == '.' || $obj == '..') continue;
		if (!@unlink($dir . '/' . $obj)) unlinkRecursive($dir.'/'.$obj, true);
	}
	closedir($dh);
	if ($deleteRootToo) @rmdir($dir);
	return;
} 

if ($session->userid) {
	$skin = mysql_single('
		SELECT id,user FROM skins WHERE id = \''.mysql_real_escape_string($session->styleid).'\'
		',__LINE__.__FILE__);
	if ($skin->id) {
		if ($session->userid == $skin->user || $session->admin) {
			//delete skin folder
			$dir = realpath(PATH.'/images/skins/'.$skin->id.'/');
			if ($dir) unlinkRecursive( $dir.DIRECTORY_SEPARATOR, true );
			
			//delete template
			if (file_exists($file = PATH.'/lib/skins/skin_'.$skin->id.'.css')) unlink($file);
			//clear the values
			mysql_query('DELETE FROM skins WHERE id = \''.mysql_real_escape_string($skin->id).'\'') or die(__LINE__.__FILE__.mysql_error());
			mysql_query('DELETE FROM skinvalues WHERE skin = \''.mysql_real_escape_string($skin->id).'\'') or die(__LINE__.__FILE__.mysql_error());
			//make sure anyone using the style is switched back to default
			mysql_query('UPDATE user SET styleid = \'\' WHERE styleid = \''.mysql_real_escape_string($skin->id).'\'') or die(__LINE__.__FILE__.mysql_error());
		} else {
			echo 'You were trying to delete someone else\'s style, you sneak!';
		}
	} else echo 'That skin doesn\'t exist yet!';
}
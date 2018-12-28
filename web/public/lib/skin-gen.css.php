<?php 
ini_set('display_errors',1);
require '../includes/dbconnect.php';

include PATH.'/classes/class.skingen.php';

$skingen = new SkinGen((int)$_GET['skin']);

//$cssfiles = array('reset.css','styles.css','icons.css','default.css','autocomplete.css','colorpicker.css');
//foreach ($cssfiles as $cssfile) $skingen->prependCSS .= "\n\n/*** $cssfile ***/\n".file_get_contents(PATH.'/lib/'.$cssfile);

header("Content-type: text/css; charset: UTF-8"); 
header("Cache-Control: must-revalidate"); 
header("Expires: " .gmdate("D, d M Y H:i:s", time() + (60 * 60)) . " GMT"); 

if ($session->admin) {
	$skingen->isadmin = true;
	if ($_GET['refresh']) $skingen->refresh = true;
	
	$stored = $skingen->storeCSSFile();
	include PATH.'/lib/skin.css';
	echo $stored !== false ? '/*stored*/':'/*not stored*/';
}

echo "\n\n",'/*',OURCOMPANY,' StyleSheet was pre-processed in ',round(array_sum(explode(' ',microtime())) - $starttime,5),' seconds*/';
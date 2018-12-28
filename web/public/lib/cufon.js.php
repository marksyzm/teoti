<? 
include '../includes/dbconnect.php';
error_reporting(0); ob_start('ob_gzhandler'); header('Content-type: application/x-javascript; charset: UTF-8'); header("Cache-Control: must-revalidate"); header('Expires: '.gmdate('D, d M Y H:i:s',time()+((60*60*24*3))).' GMT'); 

if ($session->styleid) {
	$style = mysql_single('
		SELECT * FROM eStyle WHERE sID = \''.mysql_real_escape_string($session->styleid).'\'
		',__LINE__.__FILE__);
	
	if ($style->sFont) {
	include 'cufon-yui.js'; ?>
		
<?= $style->sFont ?>
		
Cufon.replace('h1, h2, h3, h4, .box a.cufon',{'hover':true});
		
if ($.browser.msie && $.browser.version < 7) {
	$(function(){ Cufon.now(); });
}
		<?
	}
}
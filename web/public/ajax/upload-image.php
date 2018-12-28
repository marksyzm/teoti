<?
require '../includes/dbconnect.php';

if (count($_FILES) && $session->userid) {
	require_once PATH.'/images/phpthumb.class.php';
	//now upload the image
	if ($_FILES['background-image']['name']) {
		//list($width,$height)=@getimagesize($_FILES['background-image']["tmp_name"]);
		$image = send_image_to_file('background-image',0,'temp');
		if (is_string($image)) $error = $image;
		else list($image) = $image;
	}
}
?>
<html>
<head>
	<title>Upload Image</title>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript"><!--// 
		$(document).ready(function(){
			<? if (trim($error) || !$image) {?>
			alert('<?= jsonparse($error) ?>');
			<? } else { ?>
			window.parent.skinSet.tools.applyStyleElement('<?= $_POST['skintag'] ?>','/images/temp/<?= $image ?>');
			<? } ?>
			$('.status-box',window.parent.document).hide('fast');
			$('.status-box',window.parent.document).hide('fast');
			$('#image-upload-container',window.parent.document).html('');
		});
	//--></script>
</head>
<body><!-- <? var_dump($image) ?>--></body>
</html>
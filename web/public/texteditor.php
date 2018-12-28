<?php
//echo json_encode(new stdClass());die();
$thread = $usesbbcode = true;
include 'includes/dbconnect.php';
$EXTRASCRIPT = '
		<script type="text/javascript" src="'.URLPATH.'/lib/jquery.markitup.js"></script>
		<script type="text/javascript" src="'.URLPATH.'/lib/sets/bbcode/set.js"></script>
		<script type="text/javascript" src="'.URLPATH.'/ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="'.URLPATH.'/ckeditor/adapters/jquery.js"></script>
		<link rel="stylesheet" type="text/css" href="'.URLPATH.'/lib/skins/markitup/style.css?v=1" />
		<link rel="stylesheet" type="text/css" href="'.URLPATH.'/lib/sets/bbcode/style.css?v=1" />
';
include PATH.'/includes/header.php';

?>
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<textarea class="texteditor" name="pagetext"></textarea>
				<div class="teoti-button"><a href="#toggle-wysiwyg" onclick="$('.texteditor').toggleTextEditor(); return false;">Toggle WYSIWYG/BBCode</a></div>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<?

include PATH.'/includes/footer.php';

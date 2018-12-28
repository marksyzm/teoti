<? include '../includes/dbconnect.php'; ?>
<div class="half">
	<h3>BBCode Help</h3>
</div>
<div class="half teoti-button alignright last-left"><a href="#close" class="help-close">Close</a></div>
<?= CLEARBOTH ?>
<?php
$result = mysql_query('
	SELECT * FROM bbcodes
	') or die(__LINE__.__FILE__.mysql_error());
while ($bb = mysql_fetch_object($result)) {?>
	<div class="bbcode-item marginbottom">
		<div class="quarter"><p class="marginright"><strong><?= $bb->title ?></strong></p></div>
		<div class="quarter"><p class="marginright"><strong>[<?= $bb->code ?>]</strong></p></div>
		<div class="half last-left"><?= nl2br($bb->description) ?></div>
		<?= CLEARBOTH ?>
	</div>
<?}

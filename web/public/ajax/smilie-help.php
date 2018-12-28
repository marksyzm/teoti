<? include '../includes/dbconnect.php'; ?>
<div class="half">
	<h3>Smilie Help</h3>
</div>
<div class="half teoti-button alignright last-left"><a href="#close" class="help-close">Close</a></div>
<?= CLEARBOTH ?>
<?php
$result = mysql_query('
	SELECT * FROM smilie ORDER BY displayorder
	') or die(__LINE__.__FILE__.mysql_error());
while ($smilie = mysql_fetch_object($result)) {?>
	<div class="bbcode-item marginbottom">
		<div class="quarter"><p class="marginright"><strong><?= $smilie->smilietext ?></strong></p></div>
		<div class="threequarter last-left" style="overflow:auto"><img src="<?= URLPATH, $smilie->smiliepath ?>" alt="" /></div>
		<?= CLEARBOTH ?>
	</div>
<?}

<div class="pagination<?= $noofpages == 1 ? ' hidethis' : '' ?>">
	<div class="quarter teoti-button prev-button skin-this {'selector':'#whole .teoti-button'}">
		<? if ((int)$_GET['page'] > 1) {
		$tmp = $_GET;
		unset($tmp['t']);
		$tmp['page'] = $tmp['page'] - 1;
		?>
			<a href="<?= STRIP_REQUEST_URI.'?'.htmlspecialchars(parseget($tmp)) ?>" id="paginated">Prev</a>
		<? } else echo '&nbsp;' ?>
	</div>	
	<div class="quarter floatright alignright teoti-button next-button last-right skin-this {'selector':'#whole .teoti-button'}">
		<? if ($nextbool) {
			$tmp = $_GET;
			unset($tmp['t']);
			$tmp['page'] = $tmp['page'] + 1;
		?>
			<a href="<?= STRIP_REQUEST_URI.'?'.htmlspecialchars(parseget($tmp)) ?>">Next</a>
		<? } else echo '&nbsp;' ?>
	</div>
	<div class="aligncenter">
		<p class="light per-page half skin-this {'selector':'#whole .light'}"><?
			//$noofpages = $noofpages > MAXLIMIT ? MAXLIMIT : $noofpages;
			for ($i=1;$i<=$noofpages;$i++){
				$tmp = $_GET;
				unset($tmp['t']);
				$tmp['page'] = $i;
				?>
				<a href="<?= STRIP_REQUEST_URI.'?'.htmlspecialchars(parseget($tmp)) ?>"><?= $_GET['page']==$i ? '<strong>':''?>[<?= $i ?>]<?= $_GET['page']==$i ? '</strong>':''?></a>	
			<?} ?><br/>
		</p>
	</div>
	<?= CLEARBOTH ?>
</div>
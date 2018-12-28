<!-- shoutbox -->
<? $fTitle = forumtitle($_GET['forumid']) ?>
<div id="shoutbox-outer">
	<div id="shoutbox" class="skin-this {'selector':'#shoutbox'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="shoutbox-inner">
				<h2 class="skin-this {'selector':'#whole #main h2'}">
					<a href="#" class="displayblock" id="shout-toggle">Shoutbox<?= $fTitle != 'Home' ? ' - '.$fTitle : '' ?></a>
				</h2>
				<div id="shoutbox-panel">
					<div id="feed-shouts">
						<!-- feed shouts -->
						<p class="remove-me">Loading...</p>
					</div>
					<? if ($session->userid) {?>
					<div class="threequarter">
						<input type="text" id="shout-input" class="updateinput" />
					</div>
					<div class="quarter alignright last-left teoti-button skin-this {'selector':'#whole .teoti-button'}">
						<a href="#submit" id="shout-send">Submit</a>
					</div>
					<?= CLEARBOTH ?>
					<? } ?>
				</div>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

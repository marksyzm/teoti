
<div id="login-outer">
	<div id="login" class="skin-this {'selector':'#login'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="login-inner">
				<? if ($_GET['error']) { ?><p class="error"><?= $_GET['error'] ?></p><? } ?>
				<? if ($_GET['message']) { ?><p class="message"><?= $_GET['message'] ?></p><? } ?>
				
<? if ($session->userid) { ?>

	<!-- insert avatar here -->
	<? if ($session->avatar) {?>
		<div class="node-image">
			<img src="images/phpThumb.php?src=<?= urlencode('avatar/'.$session->avatar) ?>&amp;zc=1&amp;w=22&amp;h=22&amp;f=png" alt="<?= $session->username ?>'s avatar" />
		</div>
	<? } ?>
	<div class="floatright teoti-button skin-this mobile-showthis {'selector':'#whole .teoti-button'}"><!-- submit --><a href="submit">Submit</a></div>		
	<h3 class="skin-this {'selector':'#whole #main h3'}"><?= userlink($session->username,$session->userid) ?></h3>
	<?= CLEARBOTH ?>
	<div class="marginbottom margintop mobile-hidethis">
		<div class="third aligncenter teoti-button skin-this {'selector':'#whole .teoti-button'}"><!-- submit --><a href="submit">Submit</a></div>
		<div class="third aligncenter teoti-button skin-this {'selector':'#whole .teoti-button'}"><!-- help --><a href="http://wiki.teoti.com/help/Main_Page" target="_blank">Wiki</a></div>
		<?
		$browser = strstr($_SERVER['HTTP_USER_AGENT'],$br='MSIE');
		$version = $browser{5};
		$ismsie = strpos($browser, $br) !== false && $version < 7 ? true : false;
		if (!$ismsie && !$member) { ?>
		<div class="third aligncenter last-left teoti-button skin-this {'selector':'#whole .teoti-button'}">
			<!-- submit --><a href="#style-button" id="design-button">Style</a>
		</div>
		<? } ?>
		<?= CLEARBOTH ?>
	</div>
	<div class="login-buttons">
		<? $flink = ($_GET['forumid'] > 0 ? urlify(forumtitle($_GET['forumid'])).'/':'');?>
		<div class="login-icon">
			<a href="conversation" class="icon-conversation tooltipify" title="Conversations">
				<img src="images/blank.gif" alt="Conversation" /><br />
				<span id="session-conversations"><?= (string)((int)$session->pmunread) ?></span>
			</a>
			<a href="notifications" class="icon-notifications notification-latest tooltipify" title="Notifications">
				<img src="images/blank.gif" alt="Notifications" /><br />
				<span id="session-notifications"><?= (string)$session->notifications ?></span>
			</a>
			<a href="<?= $flink ?>?which=threads&amp;userid=<?= $session->userid ?>" class="icon-threads tooltipify" title="Threads">
				<img src="images/blank.gif" alt="Threads" /><br />
				<span id="session-threads"><?= (string)$session->threads ?></span>
			</a>
			<a href="<?= $flink ?>?which=posts&amp;userid=<?= $session->userid ?>" class="icon-posts tooltipify" title="Posts">
				<img src="images/blank.gif" alt="Posts" /><br />
				<span id="session-posts"><?= (string)$session->posts ?></span>
			</a>
			<a href="<?= $flink ?>?which=points&amp;userid=<?= $session->userid ?>" class="icon-points tooltipify" title="Points">
				<img src="images/blank.gif" alt="Points" /><br />
				<span id="session-points"><?= (string)$session->post_thanks_thanked_times ?></span>
			</a>
			<span class="login-icon-span icon-extrapoints tooltipify" title="Points left">
				<img src="images/blank.gif" alt="Points left" /><br />
				<span id="session-limit"><?= (string)$session->limit_points ?></span>
			</span>
			<?= CLEARBOTH ?>
		</div>
	</div>
	
	<div class="margintop aligncenter">
			<span class="mobile-showthis"><a href="#showhide-boxes" class="boxes-showhide">More</a> &bull; </span>
			<a href="<?= 'members/',urlify($session->username),'.html' ?>">Profile</a> 
			&bull; <a href="account">Account</a> 
			&bull; <a href="sitelog">Sitelog</a> 
			&bull; <a href="<?= STRIP_REQUEST_URI ?>?logout=1">Log out</a> 
	</div>
<? } else { ?>
	<? include PATH.'/includes/login-form.php' ?>
<? } ?>
				<div class="mobile-hidethis navigation-mobile">
					<? include PATH.'/includes/activity.php' ?>
				</div>
				<? include PATH.'/includes/search-box.php' ?>
				<? include PATH.'/includes/boxes.php' ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

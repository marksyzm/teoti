<?

include PATH.'/includes/old-functions.php';

if ((int)$_GET['forumid'] < 1 || !okcats((int)$_GET['forumid'])) {
    $forumids = implode(',',forumChildren(15));
} else {
    $forumids = implode(',',forumChildren((int)$_GET['forumid']));
}



$boxestop = array(
	array(
		'name' => 'Sticky',
		'forumid' => $forumids,
		'where' => array(
			'forumid IN ('.$forumids.')','sticky = 1', 'visible = 1',
		),
		'orderby' => 'lastpost DESC', 'limit' => '10', 'page' => ($_GET['do'] == 'old' ? $_GET['page'] : '1'),
	),
	array(
		'name' => 'Updated',
		'forumid' => $forumids ,
		'where' => array(
			'forumid IN ('.$forumids.')','visible = 1',
		),
		'orderby' => 'lastpost DESC', 'limit' => '10', 'page' => ($_GET['do'] == 'old' ? $_GET['page'] : '1'),
	),
);

$boxesleft = array(
	array(
		'name' => 'Hot',
		'forumid' => $forumids,
		'where' => array(
			'forumid IN ('.$forumids.')','dateline > \''.(time()-(2*24*60*60)).'\'', 'visible = 1',
		),
		'orderby' => 'thread_score DESC', 'limit' => '10', 'page' => ($_GET['do'] == 'old' ? $_GET['page'] : '1'),
	),
	array(
		'name' => 'News',
		'forumid' => '33' ,
		'where' => array(
			'forumid = 33','visible = 1',
		),
		'orderby' => 'lastpost DESC', 'limit' => '10', 'page' => ($_GET['do'] == 'old' ? $_GET['page'] : '1'),
	),
	array(
		'name' => 'Random',
		'forumid' => $forumids ,
		'where' => array(
			'forumid IN ('.$forumids.')','visible = 1',
		),
		'orderby' => 'RAND()', 'limit' => '10', 'nopage' => '1',
	),
); 

$boxright = array(
	'name' => 'Latest',
	'forumid' => $forumids ,
	'where' => array(
		'forumid IN ('.$forumids.')','visible = 1',
	),
	'orderby' => 'dateline DESC', 'limit' => '40', 'page' => ($_GET['do'] == 'old' ? $_GET['page'] : '1')
);

if ($_GET['do'] == 'old') include PATH.'/includes/old-queries.php';

include PATH.'/includes/header.php';
?>
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<div id="oldskool">
					<table>
						<tr>
							<!-- left column -->
							<td class="oldskool-column-left">
								
								<div class="old-node-outer">
									<div class="old-node skin-this {'selector':'#main .old-node'}">
										<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
										<div class="body-left"><div class="body-right"><div class="body-inner">
											<div class="old-node-inner">
												<? if ($session->userid) {?>
													<? if ($session->avatar) {?>
														<div class="node-image">
															<img src="<?= URLPATH ?>/images/phpThumb.php?src=<?= urlencode('avatar/'.$session->avatar) ?>&amp;zc=1&amp;w=22&amp;h=22&amp;f=png" alt="<?= $session->username ?>'s avatar" />
														</div>
													<? } ?>
													<h3 class="skin-this {'selector':'#whole #main h3'}"><?= userlink($session->username,$session->userid) ?></h3>
													<?= CLEARBOTH ?>
													<p>
														<a href="<?= URLPATH ?>/conversation">Conversations</a><br />
														<small>
															Unread <span id="session-conversations"><?= (string)$session->pmunread ?><span class="light skin-this {'selector':'#whole .light'}"> / <?= (string)$session->pmtotal ?></span></span>
														</small>
													</p>
													
													<?/*<p>
														<a href="<?= URLPATH ?>/?toggle=sfw">Home</a><br />
														<? if ($session->god) {?>
															<a href="<?= URLPATH ?>/?toggle=nsfw">Dungeon</a><br />
															<a href="<?= URLPATH ?>/?toggle=both">Alternate</a>
														<? } ?>
													</p>*/?>
													
													<p>
														<a href="<?= URLPATH ?>/?which=points&amp;userid=<?= $session->userid ?>">My Points</a> 
														( <span id="session-points"><?= (string)intval($session->post_thanks_thanked_times) ?></span> )<br />
														Points left
														( <span id="session-limit"><?= (string)intval($session->limit_points) ?></span> )<br />
														<a href="<?= URLPATH ?>/?which=posts&amp;userid=<?= $session->userid ?>">My Posts</a> 
														( <span id="session-posts"><?= (string)intval($session->posts) ?></span> )<br />
														<a href="<?= URLPATH ?>/?which=threads&amp;userid=<?= $session->userid ?>">My Threads</a> 
														( <span id="session-threads"><?= (string)intval($session->threads) ?></span> )<br />
														<a href="<?= URLPATH ?>/notifications" class="login-text notification-latest">Notifications</a> 
														( <span id="session-notifications"><?= (string)$session->notifications ?></span> )<br />
														<a href="<?= URLPATH ?>/conversation?do=new">New Conversation</a>
													</p>
													
													<div class="teoti-button {'selector':'#whole .teoti-button'}">
														<p>
															<a href="<?= URLPATH ?>/submit">Submit</a> 
															<a href="#style-button" id="design-button">Style</a>
														</p>
													</div>
													
													<p>
														<a href="<?= URLPATH ?>/sitelog">Sitelog</a><br />
														<a href="<?= URLPATH ?>/?do=oldnew">Switch to new layout</a><br />
														<a href="<?= STRIP_REQUEST_URI ?>/?logout=1">Log out</a>
													</p>
												<? } else {?>
													<? include PATH.'/includes/login-form.php' ?>
												<? } ?>
											</div>
										</div></div></div>
										<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
									</div>
								</div>
								
							</td>
							<!-- END left column -->
							
							<!-- main column -->
							<td class="oldskool-column-main">
								<? include PATH.'/includes/banner.php'; ?>
								<table><!-- 3 columns -->
									<tr>
									<?
									foreach ($boxestop as $box) {?>
										<td class="oldskool-column-<?= strtolower($box['name']) ?>">
											<div class="old-node-outer">
												<div class="old-node skin-this {'selector':'#main .old-node'}">
													<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
													<div class="body-left"><div class="body-right"><div class="body-inner">
														<div class="old-node-inner">
															<h3 class="skin-this {'selector':'#whole #main h3'}"><?= $box['name'] ?></h3>
															<div id="old-node-<?= strtolower($box['name']) ?>">
																<? threadLoader( $box, $session ); ?>
															</div>
														</div>
													</div></div></div>
													<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
												</div>
											</div>
										</td>
									<? } ?>
										<td class="oldskool-column-scores">
											<div class="old-node-outer">
												<div class="old-node skin-this {'selector':'#main .old-node'}">
													<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
													<div class="body-left"><div class="body-right"><div class="body-inner">
														<div class="old-node-inner">
															<h3 class="skin-this {'selector':'#whole #main h3'}">Scores</h3>
															<div id="old-node-scores">
																<? userLoader(); ?>
															</div>
														</div>
													</div></div></div>
													<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
												</div>
											</div>
										</td>
									</tr>
								</table>
										
									
								<table><!-- 2 columns -->
									<tr>
										<td class="oldskool-column-stuff">
											<? 
											
											foreach ($boxesleft as $box) {?>
											<div class="old-node-outer">
												<div class="old-node skin-this {'selector':'#main .old-node'}">
													<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
													<div class="body-left"><div class="body-right"><div class="body-inner">
														<div class="old-node-inner">
															<h3 class="skin-this {'selector':'#whole #main h3'}"><?= $box['name'] ?></h3>
															<div id="old-node-<?= strtolower($box['name']) ?>">
																<? threadLoader( $box, $session ) ?>
															</div>
														</div>
													</div></div></div>
													<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
												</div>
											</div>
											<? } ?>
										</td>
										<td class="oldskool-column-latest">
											<div class="old-node-outer">
												<div class="old-node skin-this {'selector':'#main .old-node'}">
													<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
													<div class="body-left"><div class="body-right"><div class="body-inner">
														<div class="old-node-inner">
															<h3 class="skin-this {'selector':'#whole #main h3'}">Latest</h3>
															<div id="old-node-latest">
																<? threadLoader( $boxright, $session ) ?>
															</div>
														</div>
													</div></div></div>
													<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
												</div>
											</div>
										</td>
									</tr>
								</table>
									
										
								
							</td>
							<!-- END main column -->
							
							<!-- right column -->
							<td class="oldskool-column-right">
								
								
								<div class="old-node-outer">
									<div class="old-node skin-this {'selector':'#main .old-node'}">
										<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
										<div class="body-left"><div class="body-right"><div class="body-inner">
											<div class="old-node-inner">
												<h3 class="skin-this {'selector':'#whole #main h3'}">Search</h3>
												<form action="<?= STRIP_REQUEST_URI ?>" method="get">
													<input type="text" name="filter" />
												</form>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="spent808@gmail.com">
<input type="hidden" name="lc" value="GB">
<input type="hidden" name="item_name" value="TEOTI - The End Of The Internet">
<input type="hidden" name="no_note" value="0">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
<p style="text-align: center;">
<input type="image" src="images/buddy.png" style="border: none" name="submit" alt="Donate to TEOTI via PayPal">
</p>
</form>
												
												<p class="aligncenter">
													<a href="http://www.cafepress.co.uk/teotishop" target="_blank">Get a TEOTI T-Shirt</a>
												</p>
												<p class="aligncenter"><a href="https://play.google.com/store/apps/details?id=com.oxsrc.teoti" target="_blank">Get the Android App</a></p>
												
												<? include PATH.'/includes/whosonline.php' ?>

											</div>
										</div></div></div>
										<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
									</div>
								</div>
							</td>
							<!-- END right column -->
						</tr>
					</table>
				</div>
				
				<? $note = mysql_single('
					SELECT dateline FROM notification WHERE userid = \''.mysql_real_escape_string($session->userid).'\' ORDER BY dateline DESC LIMIT 1
					',__LINE__.__FILE__);?>
				<div id="constants" class="{'filter-threads':'<?= $session->settings['filter-threads'] ?>','filter-rating':'<?= $session->settings['filter-rating'] ?>','lastnote':'<?= $note->dateline ? $note->dateline : '0' ?>'}"><!-- constants for post node feed --></div>

			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<?
include PATH.'/includes/shoutbox.php';

include PATH.'/includes/footer.php';
exit;

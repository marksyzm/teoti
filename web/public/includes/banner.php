<div id="banner-outer">
	<div id="banner" class="skin-this {'selector':'#banner'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="banner-inner">
				<? 
					$bu = mysql_single('SELECT COUNT(userid) as ct FROM user',__LINE__.__FILE__);
					$bt = mysql_single('SELECT COUNT(threadid) as ct FROM thread',__LINE__.__FILE__);
					$bp = mysql_single('SELECT COUNT(postid) as ct FROM post',__LINE__.__FILE__); 
				?>
				<?= $bu->ct ?> Member<?= $bu->ct == 1 ? ' Has':'s Have' ?> Submitted <?= $bt->ct ?> 
				Thread<?= $bt->ct == 1 ? '':'s' ?> Containing <?= $bp->ct ?> Post<?= $bp->ct == 1 ? '':'s' ?>.
				<? unset($bu,$bt,$bp); ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<? if (!$session->settings['old'] || $_GET['filter'] || !($session->settings['old'] && ($_GET['forumid'] == 3 || !$_GET['forumid'])) || $_GET['which'] || !$feed) { ?>
<p class="aligncenter"><a href="http://www.cafepress.co.uk/teotishop" class="aligncenter" target="_blank">Visit the TeotiShop!</a></p>
<p class="aligncenter"><a href="https://play.google.com/store/apps/details?id=com.oxsrc.teoti" target="_blank">Get the Android App</a></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="spent808@gmail.com">
<input type="hidden" name="lc" value="GB">
<input type="hidden" name="item_name" value="TEOTI - The End Of The Internet">
<input type="hidden" name="no_note" value="0">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
<p style="text-align: center">
<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" style="border: none" name="submit" alt="Donate to TEOTI - The End Of The Internet">
</p>
</form>
<div id="share">
	<? $url = htmlspecialchars(urlencode('http'.($_SERVER['SERVER_PORT'] == 443 ? 's':'').'://'.$_SERVER['HTTP_HOST'].($_SERVER['REQUEST_URI'] != '/' ? $_SERVER['REQUEST_URI']:''))) ?>
	<a href="http://digg.com/submit?phase=2&amp;url=<?= $url ?>" class="icon-digg" title="Digg"><img src="<?= URLPATH ?>/images/blank.gif" alt="Digg" /></a>
	<a href="http://www.facebook.com/sharer.php?u=<?= $url ?>&amp;t=<?= urlencode($PAGETITLE) ?>" class="icon-facebook" title="Facebook"><img src="<?= URLPATH ?>/images/blank.gif" alt="Facebook" /></a>
	<a href="http://www.stumbleupon.com/submit?url=<?= $url ?>&amp;t=<?= urlencode($PAGETITLE) ?>" class="icon-stumbleupon" title="StumbleUpon"><img src="<?= URLPATH ?>/images/blank.gif" alt="StumbleUpon" /></a>
	<a href="http://www.twitter.com/home?status=<?= urlencode('I\'m just checking out '.$url.' - recommended!') ?>" class="icon-twitter" title="Twitter"><img src="<?= URLPATH ?>/images/blank.gif" alt="Twitter" /></a>
	<a href="http://www.google.com/reader/link?url=<?= $url ?>&amp;title=<?= urlencode($PAGETITLE) ?>" class="icon-google-buzz" title="Google Buzz"><img src="<?= URLPATH ?>/images/blank.gif" alt="Google Buzz" /></a>
	<a href="#bookmark" class="icon-favourites button-bookmark" title="Bookmark"><img src="<?= URLPATH ?>/images/blank.gif" alt="Favourites" /></a>
	<a href="mailto:?subject=<?= rawurlencode($PAGETITLE) ?>&amp;body=<?= $url ?>" class="icon-email last-left" title="Email"><img src="<?= URLPATH ?>/images/blank.gif" alt="Email" /></a>
	<?= CLEARBOTH ?>
</div>
<? } ?>

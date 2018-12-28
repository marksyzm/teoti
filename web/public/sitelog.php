<?php
$usesbbcode=true;
require_once 'includes/dbconnect.php';
$pagetitle = 'Sitelog';
$bbcode = bbcode();
$bbcode->setRootParagraphHandling (false); //remove bbcode paragraph formatting

if ($_POST['monthly'] && $session->admin) {
	/*$user = mysql_single('
		SELECT username,userid,post_thanks_thanked_times 
		FROM user
		ORDER BY post_thanks_thanked_times DESC LIMIT 1
		',__LINE__.__FILE__);*/
	$user = mysql_single('
		SELECT username,userid,post_thanks_thanked_times 
		FROM user WHERE usergroupid NOT IN (6) 
		ORDER BY post_thanks_thanked_times DESC LIMIT 1
		',__LINE__.__FILE__);
	
	//get top scores
	
	$result = mysql_query('
		SELECT username,userid,post_thanks_thanked_times 
		FROM user WHERE usergroupid NOT IN (6) 
		ORDER BY post_thanks_thanked_times DESC LIMIT 10
		') or trigger_error(__LINE__.__FILE__.mysql_error(),E_USER_ERROR);
	/*$result = mysql_query('
		SELECT username,userid,post_thanks_thanked_times 
		FROM user 
		ORDER BY post_thanks_thanked_times DESC LIMIT 10
		') or trigger_error(__LINE__.__FILE__.mysql_error(),E_USER_ERROR);*/
	$scores = '';
	while ($score = mysql_fetch_object($result)) {
		$scores .= '[url='.PROTOCOL.$_SERVER['HTTP_HOST'].'/members/'.urlify($score->username).'.html]'.$score->username.'[/url]: '.$score->post_thanks_thanked_times."\n";
	}
	
	$message = date('F Y')
		.' Monthly Point Contest Winner -> [img]/apricot/images/new_icons/trophy.png[/img]
		[size=5][url=http://www.t-six.com/members/'.urlify($user->username).'.html]'.$user->username.'[/url][/size]
		[img]/apricot/images/new_icons/trophy.png[/img]';
	
	mysql_query('
		INSERT INTO sitelog SET 
		message = \''.mysql_real_escape_string($message).'\'
		,dateline = \''.($time = time()).'\'
		,giverid = \''.mysql_real_escape_string($session->userid).'\'
		,givername = \''.mysql_real_escape_string($session->username).'\';
		') or die(__LINE__.__FILE__.mysql_error());
	
	mysql_query('
		UPDATE user SET 
		post_thanks_thanked_times = 0;
		') or die(__LINE__.__FILE__.mysql_error());
	
	mysql_query('
		UPDATE userpage SET gspoints = 0;
		') or die(__LINE__.__FILE__.mysql_error());
	
	
	mysql_query('
		INSERT INTO thread SET 
		title = \''.date('F Y').' Point Contest\'
		,lastpost = \''.mysql_real_escape_string($time).'\'
		,lastposter = \''.mysql_real_escape_string($session->username).'\'
		,forumid = 21
		,open = 1
		,visible = 1
		,postusername = \''.mysql_real_escape_string($session->username).'\'
		,postuserid = \''.mysql_real_escape_string($session->userid).'\'
		,dateline = \''.mysql_real_escape_string($time).'\'
		,iconid = '.DEFAULTICONID.'
		') or die(__LINE__.__FILE__.mysql_error());
	$threadinsert = mysql_insert_id();
	
	$post = 'And the winner of the '.date('F Y').' point contest with '.$user->post_thanks_thanked_times.' points is...
	
[center][size=7][url='.PROTOCOL.$_SERVER['HTTP_HOST'].'/members/'.urlify($user->username).'.html]'.$user->username.'[/url][/size][/center]
	
Please contact [url='.PROTOCOL.$_SERVER['HTTP_HOST'].'/conversation?do=new&include='.$session->userid.']ME[/url] to claim [b]one[/b] of the following prizes:
	
[list]
[*][url=http://www.cafepress.co.uk/teotishop]A TEOTI T-Shirt of your 
choice[/url]
[*][url=https://www.amazon.com]Anything to the cost of £25 from 
Amazon[/url]
[*]£25 via Paypal
[/list]

	
Here are the top 10 scores for last month:

'.$scores.'

Good luck all of you in '.date('F',strtotime('+1 month')).'\'s point contest!
	';
	
	mysql_query('
		INSERT INTO post SET
		threadid = \''.mysql_real_escape_string($threadinsert).'\'
		,username = \''.mysql_real_escape_string($session->username).'\'
		,userid = \''.mysql_real_escape_string($session->userid).'\'
		,title = \''.date('F Y',strtotime('-1 month')).' Point Contest\'
		,dateline = \''.mysql_real_escape_string($time).'\'
		,updated = \''.mysql_real_escape_string($time).'\'
		,pagetext = \''.mysql_real_escape_string($post).'\'
		,html = \''.mysql_real_escape_string($bbcode->parse($post)).'\'
		,allowsmilie = 1
		,showsignature = 0
		,ipaddress = \''.mysql_real_escape_string(userip()).'\'
		,iconid = '.DEFAULTICONID.'
		,visible = \'1\'
		,point_lock = \'0\'
		,starter_only = 1
		') or die(__LINE__.__FILE__.mysql_error());
	
	$postinsert = mysql_insert_id();
		
	mysql_query('
		UPDATE thread SET 
		firstpostid = \''.mysql_real_escape_string($postinsert).'\' 
		WHERE threadid = \''.mysql_real_escape_string($threadinsert).'\'
		') or die(__LINE__.__FILE__.mysql_error());
			
	
	//put the user who created the thread onto the subscription list
	mysql_query('
		INSERT INTO subscribethread SET
		userid = \''.mysql_real_escape_string($session->userid).'\'
		,threadid = \''.mysql_real_escape_string($threadinsert).'\'
		,emailupdate = 0
		,folderid = 0
		,canview = 1
		') or die(__LINE__.__FILE__.mysql_error());
	
	if (!$_POST['cron']) header('Location: '.STRIP_REQUEST_URI);
	exit();
}

if ($_POST['daily'] && $session->staff) {
	mysql_query('
		UPDATE user SET
		limit_points = 25
		WHERE usergroupid IN ('.ADMINGROUPS.','.MODGROUPS.','.GODGROUPS.')
		') or die(__LINE__.__FILE__.mysql_error());
	
	mysql_query('
		UPDATE user SET
		limit_points = 10
		WHERE usergroupid IN ('.REGULARGROUPS.')
		') or die(__LINE__.__FILE__.mysql_error());
		
	mysql_query('
		INSERT INTO sitelog SET
		message = \'Daily Points Reset\'
		,dateline = \''.mysql_real_escape_string(time()).'\'
		') or die(__LINE__.__FILE__.mysql_error());
		
	if (!$_POST['cron']) header('Location: '.STRIP_REQUEST_URI);
	exit();
}

if ($_POST['sitelog'] && $session->staff) {
	//format all the settings to maximum. if out of bounds, return error message

	$user = mysql_single('
		SELECT userid FROM user 
		WHERE user'.(is_numeric($_POST['getterid']) ? 'id':'name').' = \''.mysql_real_escape_string(trim($_POST['getterid'])).'\'
		',__LINE__.__FILE__);
		
	$_POST['getterid'] = $user->userid; 
	$error = '';	
	if (!$user->userid) $error .= 'The user you provided doesn\'t exist!<br />';
	if ($session->userid == $user->userid) $error .= 'I can\'t believe you actually tried to give yourself points via the sitelog. What a dick.<br />';
	if ($_POST['points'] > 100) $_POST['points'] = '100';
	if ($_POST['points'] < -500) $_POST['points'] = '-500';
	
	if (!trim($_POST['message'])) $error = 'You must provide a reason! <br />';
		
	if (!$error) {
		mysql_query('
			INSERT INTO sitelog SET
			getterid = \''.mysql_real_escape_string($user->userid).'\'
			,sitelogpts = \''.mysql_real_escape_string((string)$_POST['points']).'\'
			,message = \''.mysql_real_escape_string((string)$_POST['message']).'\'
			,giverid = \''.mysql_real_escape_string($session->userid).'\'
			,dateline = \''.mysql_real_escape_string(time()).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		
		mysql_query('
			UPDATE user SET 
			post_thanks_thanked_times = post_thanks_thanked_times + \''.mysql_real_escape_string((string)$_POST['points']).'\'
			,user_total_score = user_total_score + \''.mysql_real_escape_string((string)$_POST['points']).'\'
			WHERE userid = \''.mysql_real_escape_string($user->userid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		
	}
	
	header('Location: '.URLPATH.'/sitelog'.($error ? '?error='.urlencode($error) : ''));
	exit();
}

include PATH.'/includes/header.php';
?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<h2 class="skin-this {'selector':'#whole #main h2'}">Sitelog</h2>
				<? if ($_GET['error']) {?><h3 class="error skin-this {'selector':'#whole #main h3'}"><?= $_GET['error'] ?></h3><? } ?>
			<?
			$result = mysql_query('SELECT sitelog.* FROM sitelog ORDER BY dateline DESC LIMIT 40') or die(__LINE__.__FILE__.mysql_error());
			while ($sitelog = mysql_fetch_object($result)) {
				$giver = mysql_single('SELECT * FROM user WHERE userid = \''.mysql_real_escape_string($sitelog->giverid).'\'',__LINE__.__FILE__);
				$getter = mysql_single('SELECT * FROM user WHERE userid = \''.mysql_real_escape_string($sitelog->getterid).'\'',__LINE__.__FILE__);
				?>
				<div class="feed-node-outer feed-node odd-outer  {'datetime':'<?= $t->lastpost ?>','threadid':'<?= $t->threadid ?>'}" id="feed-node-<?= $t->threadid ?>">
					<div class="feed-node odd">
						<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
						<div class="body-left"><div class="body-right"><div class="body-inner">
							<div class="feed-node odd-inner">
								<p>
									<? if ($sitelog->getterid > 0) { ?><?= userlink('',$giver->userid)?> has given 
									<?= userlink('',$getter->userid),' ',$sitelog->sitelogpts ?> points <? } ?>
									<?= $bbcode->parse($sitelog->message) ?>
									(<?= ago($sitelog->dateline,$session->timezoneoffset) ?>)
								</p>
							</div>
						</div></div></div>
						<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
					</div>
				</div>
			<? } ?>
				<? if ($session->staff) {?>
				<form action="<?= STRIP_REQUEST_URI ?>" method="post">
					Give 
					<input type="text" name="getterid" style="width:100px;" />
					<small>(exact username or id)</small>
					<input type="text" name="points" style="width:50px;" /> 
					points  
					<input type="text" name="message" style="width:300px;" /><small>(message)</small> 
					<input type="submit" name="sitelog" value="Submit" />	
				</form>
				<? } ?>		
				<? if ($session->admin) {?>
				<form action="<?= STRIP_REQUEST_URI ?>" method="post" onsubmit="return confirm('Are you sure you want to reset the monthly points?');">
					<input type="submit" name="monthly" value="Monthly Reset" />
				</form>
				<? } ?>
				<? if ($session->admin) {?>
				<form action="<?= STRIP_REQUEST_URI ?>" method="post" onsubmit="return confirm('Are you sure you want to reset the daily points?');">
					<input type="submit" name="daily" value="Daily Reset" />
				</form>
				<? } ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<?

include PATH.'/includes/footer.php';

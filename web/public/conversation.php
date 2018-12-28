<?php
$conversation = true;
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'insert') $usesbbcode = true;

include 'includes/dbconnect.php';

$check = array(); //get modification checks
if ($_GET['check']) {
	$tmp = unserialize($_GET['check']);
	if ($tmp) $check = array_merge($check,$tmp);
	unset($tmp);
}

include PATH.'/includes/conversation-functions.inc.php';
//if id is given then do the checks and get the values n shit
if (in_array($_REQUEST['do'],array('insert','update','ajax','delete')))
	include PATH.'/includes/conversation-queries.inc.php';

//legacy fix for old pm's
$result = mysql_query('
	SELECT pmtextid,dateline FROM pmtext WHERE lastpm = 0 OR lastpm IS NULL
	') or die(__LINE__.__FILE__.mysql_error());
while ($pm = mysql_fetch_object($result)) 
	mysql_query('
		UPDATE pmtext SET lastpm = \''.mysql_real_escape_string($pm->dateline).'\' 
		WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
		') or die(__LINE__.__FILE__.mysql_error());
unset($pm);

if ($session->userid) {
	if ($_GET['pm'] > 0) 
		$pm = mysql_single('SELECT * FROM pmtext WHERE pmtextid = \''.mysql_real_escape_string((int)$_GET['pm']).'\'',__LINE__.__FILE__);
	
	$parts = array();
	if ($pm->pmtextid > 0) {
		//if the user is reading this thread and the thread is marked as unread...
		$part = mysql_single('
			SELECT pmid,userid FROM pm 
			WHERE pmtextid = \''.mysql_real_escape_string((int)$pm->pmtextid).'\' 
			AND userid = \''.mysql_real_escape_string($session->userid).'\'
			AND messageread = 0
			',__LINE__.__FILE__);
		
		//legacy fixing
		$pmn = mysql_single('
			SELECT * FROM pmnode WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
			',__LINE__.__FILE__);
		if (!$pmn->pmnodeid) { 
			mysql_query('
				INSERT INTO pmnode SET
				pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
				,userid = \''.mysql_real_escape_string($pm->fromuserid).'\'
				,message = \''.mysql_real_escape_string($pm->message).'\'
				,dateline = \''.mysql_real_escape_string($pm->dateline).'\'
				') or die(__LINE__.__FILE__.mysql_error());
		}
		//end legacy fixing
			
		if ($part->pmid) {
			//then remove 1 from their unread list count and update the participant table row (called pm)
			mysql_query('
				UPDATE pm SET messageread = 1, unread = 0 WHERE pmid = \''.mysql_real_escape_string($part->pmid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			updateConversationCount($part->userid);
		}
		
		$pagetitle = htmlspecialchars_decode($pm->title);
		$parts = mysql_single('
			SELECT GROUP_CONCAT(userid SEPARATOR \',\') AS userids FROM pm WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
			',__LINE__.__FILE__);
		$parts = explode(',',$parts->userids);
	}
	
	if ($parts && !in_array($session->userid,$parts)) unset($pm,$parts);
	
	if ($_GET['include']) {
		if (!is_array($parts)) $parts = array();
		if ((int)$_GET['include'] > 0) $parts[] = (int)$_GET['include'];
	}
	
	if ((int)$_GET['report'] > 0) {
		
		$p = mysql_single('SELECT * FROM post WHERE postid = \''.mysql_real_escape_string($_GET['report']).'\'',__LINE__.__FILE__);
		$u = mysql_single('SELECT username FROM user WHERE userid = \''.mysql_real_escape_string($p->userid).'\'',__LINE__.__FILE__);
		$t = mysql_single('SELECT title,forumid,threadid FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
		
		if ($p->postid && $t->threadid && okcats($t->forumid)) {	
			$m = mysql_single('SELECT GROUP_CONCAT(userid SEPARATOR \',\') as mods FROM user WHERE usergroupid IN ('.MODGROUPS.')',__LINE__.__FILE__);
			if (!is_array($parts)) $parts = array();
			$parts = array_merge($parts,explode(',',$m->mods));
			
			$settitle = 'Reported: Post by '.$u->username.' in thread: '.$t->title;
			$setmessage = "I find the following content unsuitable: \n\n[url=".PROTOCOL.$_SERVER['HTTP_HOST'].URLPATH.'/'.urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$t->threadid).".html?p=".$p->postid."]".$p->pagetext.'[/url]';
		}
	}
	
	if ($parts && !in_array($session->userid,$parts)) $parts[] = $session->userid;
}

if ($session->userid) {
	 
	$EXTRASCRIPT = '
		<script type="text/javascript" src="lib/jquery.markitup.js"></script>
		<script type="text/javascript" src="lib/sets/bbcode/set.js"></script>
		<link rel="stylesheet" type="text/css" href="lib/skins/markitup/style.css" />
		<link rel="stylesheet" type="text/css" href="lib/sets/bbcode/style.css" />
		<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="ckeditor/adapters/jquery.js"></script>
		';

	if ((int)$_GET['pm']) {
		mysql_query('
			DELETE FROM notification 
			WHERE type = 3
			AND itemid = \''.mysql_real_escape_string((int)$_GET['pm']).'\'
			AND userid = \''.mysql_real_escape_string($session->userid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
			
		updateUserNotifications();
	}
}

include PATH.'/includes/header.php';
if ($pm->pmtextid > 0 || $_GET['do'] == 'new') { 
	//$bbcode = bbcode();
	?>
<!-- shoutbox -->
<div id="shoutbox-outer">
	<div id="shoutbox" class="skin-this {'selector':'#shoutbox'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="shoutbox-inner">
				<form action="<?= STRIP_REQUEST_URI ?>" method="post" id="submit-post" class="text-editor">
					<h2 class="light skin-this {'selector':'#whole #main h2'}">
						<span class="lighter skin-this {'selector':'#whole-lighter'}">Conversation: </span>
						<? if ($_GET['do'] == 'new') { ?>
							<input type="text" name="title" value="<?= $settitle ?>" class="largeinput updateinput" />
						<? } else { ?>
							<?= $pm->title ?>
						<? } ?>
					</h2>
					<? if ($check) {?><p class="error"><?= implode('<br />',$check) ?></p><? } ?>
					<? if ($_GET['message']) {?><p class="message"><?= $_GET['message'] ?></p><? } ?>
					<!-- this is the comments feed -->
					<div class="participants-box node-content node-content-nopoints dblmarginbottom">
						<div class="mobile-conversation-header">
							<div class="teoti-button half skin-this {'selector':'#whole .teoti-button'}">
								<p class="light">Participants:</p>
								<!-- -->
								<div id="participants">
									<div class="hidden-inputs">
									<? if (!$parts) {?>
										<input type="hidden" name="participants[]" value="<?= $session->userid ?>" />
									<?} else {
										foreach ($parts as $part) {?>
										<input type="hidden" name="participants[]" value="<?= $part ?>" />
									<?} 
									} ?>
									</div>
									<? 
									if (!$parts) echo userlink($session->username,$session->userid,false);
									else {
										foreach ($parts as $part) { 
											$u = mysql_single('SELECT username FROM user WHERE userid = \''.mysql_real_escape_string($part).'\'',__LINE__.__FILE__);
											if ($u->username) {
												//if this is your conversation and this is a user other than you 
												// or you are staff and this is not you or the owner of this thread then you can delete users. Confusing.
												if (
													($pm->fromuserid == $session->userid && $part != $session->userid) 
													|| ($session->staff && $part != $pm->fromuserid && $session->userid != $part)
												) { 
													echo 
														'<a href="',STRIP_REQUEST_URI,'?do=delete&amp;type=participant&amp;pm=',$pm->pmtextid
														,'&amp;participant=',$part,'" class="remove-me {\'userid\':\'',$part,'\'}">',$u->username,'</a> ';
												} else echo userlink($u->username,$part,false),' ';
											}
										}
									}
									?>
								</div>
							</div>
							<? if ($session->staff || $pm->fromuserid == $session->userid || $_GET['do'] == 'new') { ?>
							<div class="invite-participant">
								<p class="light">Invite Participants:</p>
								<div class="alignright">
									<input type="text" id="invite-participant" name="userid" />
									<input type="submit" name="addparticipant" value="invite" />
								</div>
							</div>
							<? } ?>
						</div>
						<?= CLEARBOTH ?>
					</div>
					
					<?= $hasfirst = firstImage('','#',$session->userid,$session->usernameurl,$session->avatar,false) ?>
					<div class="node-content node-content-nopoints node-content-hasimage">
						<h3 class="skin-this {'selector':'#whole #main h3'}"><?= userlink($session->username,$session->userid) ?></h3>
						<textarea id="textarea_message" name="textarea_message"><?= $setmessage ?></textarea>
						<div class="threequarter teoti-button">
							<a href="ajax/bbcode-help.php" class="bbcode-help">BBCode Help</a>
							<a href="ajax/smilie-help.php" class="smilie-help">Smilie Help</a>
							<a href="#toggle-wysiwyg" class="toggle-wysiwyg" title="A WYSIWYG is a word style editor which stands for 'What you see is what you get'. BBCode is our standard tags based code - see our help file">Use <?= $session->settings['wysiwyg'] ? 'BBCode':'WYSIWYG' ?></a>
						</div>
						<div class="alignright quarter last-left">
							<input type="hidden" name="do" value="insert" />
							<input type="hidden" name="pm" value="<?= $pm->pmtextid ?>" />
							<input type="hidden" id="conversation-type" name="type" value="<?= $pm->pmtextid ? 'message':'conversation' ?>" />
							<input type="submit" value="submit" accesskey="s" />
						</div>
						<?= CLEARBOTH ?>
						<div id="help-box"><!-- --></div>
					</div>
				</form>
				<? $noofpages = generateConversationNode($pm->pmtextid) ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<div id="constants" class="{'pm':'<?= $pm->pmtextid ?>','pmlimit':'<?= PMFEEDLIMIT ?>','curpagetotal':'<?= $noofpages ?>','curpage':'<?= $_GET['page'] ?>'}"><!-- constants for post node feed --></div>

<? } elseif ($session->userid) { ?>
<!-- list all conversations -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<div class="mobile-conversation-header">
					<div class="half">
						<h2 class="skin-this {'selector':'#whole #main h2'}">Conversations</h2>
					</div>
					<div class="half last-left teoti-button alignright skin-this {'selector':'#whole .teoti-button'}">
						<a href="<?= STRIP_REQUEST_URI,($_GET['show'] == 'unread' ? '':'?show=unread') ?>"><?= $_GET['show'] == 'unread' ? 'Show All':'Show Unread' ?></a>
						<a href="<?= STRIP_REQUEST_URI ?>?do=new">Add Conversation</a>
					</div>
				</div>
				<?= CLEARBOTH ?>
				<? if ($check) {?><p class="error"><?= implode('<br />',$check) ?></p><? } ?>
				<? if ($_GET['message']) {?><p class="message"><?= $_GET['message'] ?></p><? } ?>
				<? $numrows = listConversations() ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<? } else { ?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<h2 class="skin-this {'selector':'#whole #main h2'}">Sorry!</h2>
				<p>The conversation you are trying to access doesn't seem to exist (or you don't have access to it)!</p>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<?
}
include PATH.'/includes/footer.php';
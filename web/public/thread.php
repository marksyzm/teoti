<?php

//echo json_encode(new stdClass());die();
if (isset($_GET['do']) && $_GET['do'] == 'merge') $usesbbcode = true;
$thread = true;
include 'includes/dbconnect.php';

include PATH.'/includes/thread-functions.php';
//if id is given then do the checks and get the values n shit
if (in_array($_REQUEST['do'],array('undelete','softdelete','merge','ajax','points'))) {
    include PATH.'/includes/thread-queries.inc.php';
}

$t = mysql_single('SELECT * FROM thread WHERE threadid = \''.$mysqli->real_escape_string($_GET['t']).'\''.($session->staff ? '':' AND visible = 1'),__LINE__.__FILE__);
$ok = okcats($t->forumid);

if ($t->threadid > 0 && $ok) {
	$_GET['forumid'] = $t->forumid;
	$pagetitle = htmlspecialchars_decode($t->title);
	$p = mysql_single('SELECT html,post_thanks_amount,dateline,updated,point_lock FROM post WHERE postid = \''.$mysqli->real_escape_string($t->firstpostid).'\'',__LINE__.__FILE__);
	$f = mysql_single('SELECT title FROM forum WHERE forumid = \''.$mysqli->real_escape_string($t->forumid).'\'',__LINE__.__FILE__);
	$u = mysql_single('SELECT avatar, post_thanks_thanked_times, posts, threads, location, usertitle, styleid FROM user WHERE userid = \''.$mysqli->real_escape_string($t->postuserid).'\'',__LINE__.__FILE__);
	
	if ($t->styleid) $session->styleid = $t->styleid;
	if ($t->threadtype == 1) $session->styleid = $u->styleid;
	
	$mysqli->query('
		UPDATE thread SET views = views + 1 WHERE threadid = \''.$mysqli->real_escape_string($t->threadid).'\'
		') or die(__LINE__.__FILE__.$mysqli->error);
	
	include_once PATH.'/classes/class.autokeyword.php';
	$params['content'] = $p->pagetext;
	$kw = new autokeyword(array('content'=> $p->pagetext), 'utf-8');
	$metakw = $kw->get_keywords();
	$metadesc = $t->description;
	
	if ($session->userid){
		$mysqli->query('
			DELETE FROM notification 
			WHERE type IN (1,2,6) 
			AND itemid IN (SELECT postid FROM post WHERE threadid = \''.$mysqli->real_escape_string($t->threadid).'\')
			AND userid = \''.$mysqli->real_escape_string($session->userid).'\';
			') or die(__LINE__.__FILE__.$mysqli->error);
		
		$mysqli->query('
			UPDATE user SET viewing = \''.$mysqli->real_escape_string($t->threadid).'\' WHERE userid = \''.$mysqli->real_escape_string($session->userid).'\'
			') or die(__LINE__.__FILE__.$mysqli->error);
		
		updateUserNotifications();
		
		$EXTRASCRIPT = '
		<script type="text/javascript" src="lib/jquery.markitup.js"></script>
		<script type="text/javascript" src="lib/sets/bbcode/set.js"></script>
		<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="ckeditor/adapters/jquery.js"></script>
		<link rel="stylesheet" type="text/css" href="lib/skins/markitup/style.css?v=1" />
		<link rel="stylesheet" type="text/css" href="lib/sets/bbcode/style.css?v=1" />
';
	}
}



include PATH.'/includes/header.php';
if ($t->threadid > 0 && $ok) { 
	//$bbcode = bbcode();
	?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<div id="thread-head">
					<?= points($p->post_thanks_amount,$t->firstpostid,($session->userid && $t->postuserid == $session->userid ? false : true)) ?>
					<? if ($u->avatar) {?>
					<div id="thread-user">
						<div class="node-image mobile-hidethis">
							<a href="members/<?= urlify($t->postusername) ?>.html">
                                <img src="images/avatar/<?= rawurlencode($u->avatar) ?>" alt="<?= $t->postusername ?>" />
                            </a>
						</div>
						<div class="node-image mobile-showthis">
							<a href="members/<?= urlify($t->postusername) ?>.html">
                                <img src="images/phpThumb.php?src=<?= rawurlencode('avatar/'.$u->avatar) ?>&amp;zc=1&amp;w=64&amp;h=48&amp;f=png" alt="<?= $t->postusername ?>" />
                            </a>
						</div>
					</div>
					<? } ?>
					<div id="thread-title">
						<div id="thread-icon">
							<a href="<?= urlify($f->title) ?>/" class="icon-<?= urlify($f->title) ?>">
								<img src="images/blank.gif" alt="" />
							</a>
						</div>
						<h3 class="skin-this {'selector':'#whole #main h3'}"><?= $t->title.($t->visible != 1 ? ' [DELETED]':'') ?></h3>
						<p>
							<? if ($p->point_lock) {?><span class="light skin-this {'selector':'#whole .light'}">Point locked, </span><? } ?>
							<? if ($t->sticky) {?><span class="light skin-this {'selector':'#whole .light'}">Stickied, </span><? } ?>
							<? if (!$t->open) {?><span class="light skin-this {'selector':'#whole .light'}">Closed, </span><? } ?>
							<span class="light skin-this {'selector':'#whole .light'}"><?= (string)$t->replycount ?> comments, </span> 
							<span class="light skin-this {'selector':'#whole .light'}"><?= (string)$t->views ?> views, </span> 
							<span class="light skin-this {'selector':'#whole .light'}">posted <?= ago($t->dateline) ?> in</span> 
                            <a href="<?= urlify($f->title) ?>/"><?= $f->title ?></a>
							<span class="light skin-this {'selector':'#whole .light'}">by </span> <?= userlink($t->postusername,$t->postuserid) ?>
							<br />
							<span class="lighter skin-this {'selector':'#whole .lighter'}">
								<?= $t->postusername ?> has 
								<?= $u->posts ? $u->posts : '0' ?> posts, 
								<?= $u->threads ? $u->threads : '0' ?> threads, 
								<?= $u->post_thanks_thanked_times ? $u->post_thanks_thanked_times : '0' ?> points<?= 
								$u->location ? ', location: '.$u->location : '' ?>
								<?= $u->usertitle ? '<br />'.$u->usertitle : '' ?>
							</span>
							<br />
							<a href="http://twitter.com/share" class="twitter-share-button" data-url="<?= htmlspecialchars(STRIP_LINK) ?>" data-count="horizontal" data-via="teoticommunity">Tweet</a>
							<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
							<iframe src="http://www.facebook.com/plugins/like.php?app_id=236638149699115&amp;href=<?= urlencode(STRIP_LINK) ?>&amp;send=false&amp;layout=button_count&amp;width=120&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font=lucida+grande&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:21px;" allowTransparency="true"></iframe>
						</p>
					</div>
					<?= CLEARBOTH ?>
				</div>
				<!-- now knock out the content -->
				<div id="feed-node-<?= $t->firstpostid?>" class="feed-node-outer  {'datetime':'<?= $p->dateline ?>','updated':'<?= $p->updated ?>','postid':'<?= $t->firstpostid ?>','username':'<?= $t->postusername ?>'}">
					<div class="node-snippet clearfix"><?= $p->html ?></div>
					
				</div>
				<? if ($t->related) { ?>
				<div class="teoti-button skin-this {'selector':'#whole .teoti-button'}">
					<a href="<?php echo (preg_match('/^https?:\/\//i',$t->related) ? '':'http://').$t->related ?>" target="_blank" class="button-related-link">Related Link</a>
				</div>
				<? } ?>
				<? if ($session->userid) { ?>
				<div id="thread-tools">
					<!-- this is where the point arrows go -->
					<div class="teoti-button alignright skin-this {'selector':'#whole .teoti-button'}">
						<!-- points buttons -->
						<? if ($session->userid != $t->postuserid && $session->limit_points && !$p->point_lock) { ?>
						<span class="extra-points-button-header">Extra Points: </span>
						<? 
						$pt = mysql_single('
							SELECT * FROM points WHERE postid = \''.$mysqli->real_escape_string($t->firstpostid).'\' AND userid = \''.$mysqli->real_escape_string($session->userid).'\'
							',__LINE__.__FILE__);
						$maxpts = POINTSLIMIT - $pt->amount;
						$maxpts = $maxpts > $session->limit_points ? $session->limit_points : $maxpts;
						
						foreach (array(1,2,3,4,5,10,25) as $k => $ptarr){
							if ($ptarr > $maxpts) break; //if button value is greater than maximum number of points available to give then break
							?>
						<a href="<?= STRIP_REQUEST_URI ?>" class="extra-points {'do': 'points', 'p':'<?= $t->firstpostid ?>', 'amount':'<?= $ptarr ?>' }"><?= $ptarr ?></a>
						<? } ?>
						<? } ?>
						<!-- edit, delete, report, quote buttons -->
						<a href="conversation?do=new&amp;report=<?= $t->firstpostid ?>" style="margin-left:10px" title="Report this thread<?= $session->staff ? ' ('.$p->ipaddress.')' : '' ?>">!</a>
						<a href="<?= STRIP_REQUEST_URI ?>?quote=<?= $t->firstpostid ?>" class="quote-button {'p':'<?= $t->firstpostid ?>'}">Quote</a>
						<? if ($t->postuserid == $session->userid || $session->staff){ ?>
                            <a href="submit?t=<?= $t->threadid ?>">Edit</a>
                        <? } ?>
						<? if ($t->postuserid == $session->userid || $session->staff) {?>
                            <a href="#<?= $t->visible != 1 ? 'un':'' ?>delete" class="delete {'p':'<?= $t->firstpostid ?>','do':'<?= $t->visible != 1 ? 'un':'' ?>delete', 'type':'thread'}">
                                <?= $t->visible != 1 ? 'Und':'D' ?>elete
                            </a>
                        <? } ?>
					</div>
				</div>
				<? } ?>
				<div id="extra-points-box-outer">
					<?
					$result = $mysqli->query('SELECT * FROM points WHERE postid = \''.$mysqli->real_escape_string($t->firstpostid).'\'') or die(__LINE__.__FILE__.$mysqli->error);
					if ($result->num_rows()) {?>
					<h3 class="style-this {'selector':'#whole h3'}">Extra Points Given by:</h3>
					<div id="extra-points-box">
					<?
						$i = 0;
						while ($pt = $result->fetch_object()) 
							echo ($i++ ? ', ':''),userlink($pt->username,$pt->userid),' (<span id="extra-points-',$pt->userid,'">',(string)intval($pt->amount),'</span>)';
					?>
					</div>
					<?
					}
					?>
				</div>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<!-- comments box uses the shoutbox id and posts use the node class -->

<!-- shoutbox -->
<div id="shoutbox-outer">
	<div id="shoutbox" class="skin-this {'selector':'#shoutbox'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="shoutbox-inner">
				<h2 class="light skin-this {'selector':'#whole #main h2'}">Comments</h2>
				<!-- this is the comments feed -->
				<? $noofpages = generateCommentsNode($t->threadid,$t->firstpostid) ?>
				
				<h2 class="skin-this {'selector':'#whole #main h2'}"><span id="comment-add-edit">Add Comment</span></h2>
				<? if ($session->userid && $t->open) {?>
				<?= $hasfirst = firstImage('','#',$session->userid,$session->usernameurl,$session->avatar,false) ?>
				<form action="submit" method="post" id="submit-post" class="text-editor">
					<div id="submit-post-box">
						<h3 class="skin-this {'selector':'#whole #main h3'}"><?= userlink($session->username,$session->userid) ?></h3>
						<textarea class="pagetext" id="textarea_pagetext" name="textarea_pagetext" rows="5" cols="1"></textarea>
						<div class="threequarter teoti-button">
                            <a href="ajax/bbcode-help.php" class="bbcode-help">BBCode Help</a>
                            <a href="ajax/smilie-help.php" class="smilie-help">Smilie Help</a>
                            <a href="#toggle-wysiwyg" class="toggle-wysiwyg" title="A WYSIWYG is a word style editor which stands for 'What you see is what you get'. BBCode is our standard tags based code - see our help file">Use <?= $session->settings['wysiwyg'] ? 'BBCode':'WYSIWYG' ?></a>
						</div>
						<div class="quarter last-left alignright">
							<input type="hidden" name="do" value="insert" />
							<input type="hidden" name="threadid" value="<?= $_GET['t'] ?>" />
							<input type="hidden" name="postid" value="" />
							<input type="hidden" name="type" value="post" />
							<input type="submit" value="Submit" accesskey="s" />
						</div>
						<?= CLEARBOTH ?>
						<div id="help-box"><!-- --></div>
					</div>
				</form>
				
				<? 
				/*$result = $mysqli->query('
					SELECT username,userid,avatar,usernameurl FROM user 
					WHERE viewing = \''.$mysqli->real_escape_string($t->threadid).'\' 
					AND lastactivity > \''.$mysqli->real_escape_string(time()-VIEWTIME).'\'
					') or die(__LINE__.__FILE__.$mysqli->error);*/
				?>
				<div id="whos-watching">
					<h2 class="skin-this {'selector':'#whole #main h2'}">Who's Watching</h2>
					<div id="whos-watching-watchers">
						<? /*while ($watcher = $result->fetch_object()) {?><a href="members/<?= $watcher->usernameurl ?>.html" class="watcher" title="<?= htmlspecialchars($watcher->username) ?>"><img src="images/phpThumb.php?src=<?= urlencode($watcher->avatar ? 'avatar/'.$watcher->avatar :'error.png') ?>&amp;zc=1&amp;w=22&amp;h=22&amp;f=png" alt="" title="" /></a><? }*/ ?>
                        
						<div class="popup hidethis"><!-- --></div>
					</div>
				</div>
				
				<? if ($session->staff) {?>
				<br />
				<div class="teoti-button skin-this {'selector':'#whole .teoti-button'}">
					<a href="#merge-threads" class="button-merge-threads" data-selector="#merge-threads-form">Merge Threads</a>
				</div>
				<form id="merge-threads-form" action="<?= STRIP_REQUEST_URI ?>" method="get" class="hidden">
					<p class="light skin-this {'selector':'#whole .light'}">Insert below the whole URL or id of the thread you want to merge this thread into.</p>
					Thread: <input type="text" name="merge" value="" class="updateinput" />
					<br />Reason: <input type="text" name="reason" value="Repost" class="updateinput" />
					<input type="hidden" name="do" value="merge" />
					<br /><input type="submit" name="merge-threads" value="Submit" />
				</form>
				<? } ?>
				
				<? } else {?>
					
					<h3 class="error skin-this {'selector':'#whole #main h3'}">
						<a href="#login" class="button-login-thread">Log in</a> via teoti,
						<button class="facebook-connect"><img src="images/facebook-connect-button.gif" alt="Facebook Connect" /></button>
						or <a href="register">register</a> to add a comment!
					</h3>
				<? } ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<div id="constants" class="{'threadid':'<?= $t->threadid ?>','postlimit':'<?= POSTFEEDLIMIT 
	?>','curpagetotal':'<?= $noofpages ?>','curpage':'<?= $_GET['page'] ?>','posthighlight':'<?=
	$_GET['p'] > 0 ? (int)$_GET['p'] : '0'?>'}"><!-- constants for post node feed --></div>

<? } else { ?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<h2 class="skin-this {'selector':'#whole #main h2'}">Sorry!</h2>
				<p>The thread you are trying to access doesn't seem to exist (or you don't have access to it)!</p>
				<!-- now knock out the content -->
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<?
}
include PATH.'/includes/footer.php';

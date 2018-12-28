<?php
include 'includes/dbconnect.php';
$pagetitle = 'Notifications';
if ($_GET['do'] == 'delete' && $session->userid && (int)$_GET['itemid'] && (int)$_GET['type']) {
	mysql_query('
		DELETE FROM notification 
		WHERE type = \''.mysql_real_escape_string((int)$_GET['type']).'\'
		AND itemid = \''.mysql_real_escape_string((int)$_GET['itemid']).'\'
		AND userid = \''.mysql_real_escape_string($session->userid).'\'
		') or die(__LINE__.__FILE__.mysql_error());
	updateUserNotifications();
	header('Location: '.STRIP_REQUEST_URI); 
	exit;
}

if ($_GET['do'] == 'deleteall' && $session->userid) {
	mysql_query('
		DELETE FROM notification 
		WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
		') or die(__LINE__.__FILE__.mysql_error());
	updateUserNotifications();
	header('Location: '.STRIP_REQUEST_URI); 
	exit;
}

if ($_GET['do'] == 'forget' && $session->userid && (int)$_GET['itemid'] && (int)$_GET['type']) {
	$t = mysql_single('
		SELECT threadid FROM post WHERE postid = \''.mysql_real_escape_string($_GET['itemid']).'\'
		',__LINE__.__FILE__);
	
	mysql_query('
		DELETE FROM subscribethread
		WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
		AND threadid = \''.mysql_real_escape_string($t->threadid).'\'
		') or die(__LINE__.__FILE__.mysql_error());
	
	mysql_query('
		DELETE FROM notification 
		WHERE type = \''.mysql_real_escape_string((int)$_GET['type']).'\'
		AND itemid IN (SELECT postid FROM post WHERE threadid = \''.mysql_real_escape_string($t->threadid).'\')
		AND userid = \''.mysql_real_escape_string($session->userid).'\';
		') or die(__LINE__.__FILE__.mysql_error());
	updateUserNotifications();
	header('Location: '.STRIP_REQUEST_URI); 
	exit;
}

if (!$session->userid) {
  header ('HTTP/1.1 301 Moved Permanently');
  header ('Location: ./');
  exit;
}
include PATH.'/includes/header.php';
?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<h2 class="light skin-this {'selector':'#whole #main h2'}">Notifications</h2>
				<div class="teoti-button">
					<a href="<?= STRIP_REQUEST_URI ?>?do=deleteall" onclick="if (confirm('Are you sure you want to delete all your notifications?')) {$.get($(this).attr('href')); $('.feed-node-outer').slideUp(function(){ $(this).remove(); }); } return false;">Delete All</a>
				</div>
				<? if ($_GET['error']) {?><h3 class="error skin-this {'selector':'#whole #main h3'}"><?= $_GET['error'] ?></h3><? } ?>
				<div id="notification-feed">
			<?
			$ntresult = mysql_query('
				SELECT * FROM notetype
				') or die(__LINE__.__FILE__.mysql_error());
			while ($nt = mysql_fetch_object($ntresult)) {
				$eresult = mysql_query('
					SELECT extra
					FROM notification
					WHERE userid = \''.mysql_real_escape_string($session->userid).'\' 
					AND type = \''.mysql_real_escape_string($nt->notetypeid).'\'
					GROUP BY extra
					') or die(__LINE__.__FILE__.mysql_error());
				if (mysql_num_rows($eresult)) {
					$n = 0;
					while ($e = mysql_fetch_object($eresult)) {
						$result = mysql_query('
							SELECT *, GROUP_CONCAT(DISTINCT fromuserid ORDER BY dateline SEPARATOR \',\') AS fromuserids
							FROM notification 
							WHERE userid = \''.mysql_real_escape_string($session->userid).'\' 
							AND type = \''.mysql_real_escape_string($nt->notetypeid).'\'
							AND (extra = \''.mysql_real_escape_string($e->extra).'\''.(!$e->extra ? ' OR extra IS NULL':'').')
							GROUP BY `group` ORDER BY dateline DESC LIMIT 50
							') or die(__LINE__.__FILE__.mysql_error());
						if (mysql_num_rows($result)) {?>
							<h3 class="light skin-this {'selector':'#whole #main h3'}"><?= sprintf($nt->description,$e->extra) ?></h2>
							<?
							while ($n = mysql_fetch_object($result)) {
								?>
								<div class="feed-node-outer  {'datetime':'<?= $t->lastpost ?>','threadid':'<?= $t->threadid ?>'}" id="feed-node-<?= $t->threadid ?>">
									<div class="feed-node skin-this {'selector':'#content-col .feed-node'}">
										<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
										<div class="body-left"><div class="body-right"><div class="body-inner">
											<div class="feed-node-inner">
												<div class="floatleft twothirds">
													<p>
														<?
														$users = array();
														$userids = explode(',',$n->fromuserids);
														if (count($userids) <= MAXNOTIFYUSERS) foreach ($userids as $userid) $users[] = userlink('',$userid);	
														switch($nt->name){
															case 'newpost':
																$p = mysql_single('SELECT threadid FROM post WHERE postid = \''.mysql_real_escape_string($n->itemid).'\'',__LINE__.__FILE__);
																$t = mysql_single('SELECT forumid,title FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
																$link = urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$p->threadid).'.html?p='.$n->itemid;
																?>
																<?= count($userids) <= MAXNOTIFYUSERS ? implode(', ',$users) : count($userids).' users' ?> 
																added new posts in <a href="<?= $link ?>"><?= $t->title ?></a>.
																<?
																break;
															case 'likedislike':
																$p = mysql_single('SELECT threadid FROM post WHERE postid = \''.mysql_real_escape_string($n->itemid).'\'',__LINE__.__FILE__);
																$t = mysql_single('SELECT forumid,title FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
																$link = urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$p->threadid).'.html?p='.$n->itemid;
																?>
																<?= count($userids) <= MAXNOTIFYUSERS ? implode(', ',$users) : count($userids).' users' ?> 
																<?= $e->extra ?> your <a href="<?= $link ?>">post</a> in <a href="<?= $link ?>"><?= $t->title ?></a>.
																<?
																break;
															case 'conversation':
																$pm = mysql_single('SELECT title FROM pmtext WHERE pmtextid = \''.mysql_real_escape_string($n->itemid).'\'',__LINE__.__FILE__);
																$link = 'conversation?pm='.$n->itemid;
																?>
																<?= count($userids) <= MAXNOTIFYUSERS ? implode(', ',$users) : count($userids).' users' ?> 
																added new messages in <a href="<?= $link ?>"><?= $pm->title ?></a>.
																<?
																break;
															case 'extrapoints':
																$p = mysql_single('SELECT threadid FROM post WHERE postid = \''.mysql_real_escape_string($n->itemid).'\'',__LINE__.__FILE__);
																$t = mysql_single('SELECT forumid,title FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
																$link = urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$p->threadid).'.html?p='.$n->itemid;
																?>
																<?= count($userids) <= MAXNOTIFYUSERS ? implode(', ',$users) : count($userids).' users' ?> 
																gave <?= $e->extra ?> extra points for your <a href="<?= $link ?>">post</a> in <a href="<?= $link ?>"><?= $t->title ?></a>.
																<?
																break;
														}
														?>
													</p>
												</div>
												<div class="third floatright alignright last-right teoti-button skin-this {'selector':'#whole .teoti-button'}">
													<a href="<?= $link ?>">Go</a>
													<a href="<?= STRIP_REQUEST_URI ?>?do=forget&amp;itemid=<?= $n->itemid ?>&amp;type=<?= $n->type ?>" class="forget {'itemid':'<?= $n->itemid ?>','type':'<?= $n->type ?>','do':'forget'}">Forget</a>
													<a href="<?= STRIP_REQUEST_URI ?>?do=delete&amp;itemid=<?= $n->itemid ?>&amp;type=<?= $n->type ?>" class="delete delete-nocallback {'itemid':'<?= $n->itemid ?>','type':'<?= $n->type ?>','do':'delete'}">Delete</a>
												</div>
												<?= CLEARBOTH ?>
											</div>
										</div></div></div>
										<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
									</div>
								</div>
							<?
								$n++; 
							} 
						}
					}
				}
			}
					$result = mysql_query('
						SELECT * FROM history WHERE userid = \''.mysql_real_escape_string($session->userid).'\' ORDER BY dateline DESC
						') or die(__LINE__.__FILE__.mysql_error());
					if (mysql_num_rows($result)) {?>
					<div id="history-container">
						<h2 class="light skin-this {'selector':'#whole #main h2'}">History</h2>
						<div id="history-items">
							<? while ($history = mysql_fetch_object($result)) { 
								$t = mysql_single('
									SELECT title, forumid,threadid FROM thread WHERE threadid = \''.mysql_real_escape_string($history->itemid).'\'
									',__LINE__.__FILE__);
								$forumtitle = forumtitle($t->forumid);
								$forumurl = urlify($forumtitle);
								$url = $forumurl.'/'.urlify($t->title,$history->itemid).'.html';
								if ($t->threadid){
								?>
							<div class="history-item">
								<div class="history-icon">
									<a href="<?= $url ?>" class="icon-<?= $forumurl ?>"><img src="images/blank.gif" alt="<?= $forumtitle ?>" /></a>
								</div>
								<div class="history-link"><a href="<?= $url ?>"><?= $t->title ?></a></div>
								<?= CLEARBOTH ?>
							</div>
							<? }
							} ?>
						</div>
					</div>
					<? } ?>
					<?= CLEARBOTH ?>
				</div>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<?
include PATH.'/includes/footer.php';
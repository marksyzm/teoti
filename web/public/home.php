<?
require 'includes/dbconnect.php';

$filter = $feed = true;
include 'includes/index-functions.php';

if ($_REQUEST['toggle']|| in_array($_REQUEST['do'],array('ajax','removemessage','oldnew','subscribe'))) include PATH.'/includes/index-queries.php';
if ($_GET['forumid']) $_GET['forumid'] = forumid($_GET['forumid']);

if ($session->settings['old'] && (!$_GET['filter'] && (!$_GET['forumid'] || $_GET['forumid'] == 3) && !$_GET['which'])) include PATH.'/old.php';

include 'includes/header.php';
?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<?/*<div class="quarter">*/?>
					<h2 class="skin-this {'selector':'#whole #main h2'}"><?= forumtitle($_GET['forumid']) ?></h2>
				<?/*</div>
				<div class="threequarter last-left" id="filter-box">
					<div class="teoti-button alignright skin-this {'selector':'#whole .teoti-button'}">
						<? 
							$fthreads = $session->settings['filter-threads'] ? $session->settings['filter-threads'] : 'latest';
							$frating = $session->settings['filter-rating'] ? $session->settings['filter-rating'] : 'sfw';
							
							if ((int)$_GET['forumid'] && $session->userid && !in_array((int)$_GET['forumid'],array(1,2,3,15))) {
								$forumids = array((int)$_GET['forumid']);
	
								$children = forumChildren((int)$_GET['forumid']);
								if ($children) $forumids = $children;
								
								if ($forumids) {
									$subscribe = mysql_single('
										SELECT forumid FROM subscribeforum 
										WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
										AND forumid IN ('.mysql_real_escape_string(implode(',',$forumids)).')
										',__LINE__.__FILE__);
								}
						?>
							<a href="<?= STRIP_REQUEST_URI ?>?do=subscribe&amp;f=<?= $_GET['forumid'] ?>" class="marginright skip"><?= $subscribe->forumid ? 'Uns':'S' ?>ubscribe</a>
						<? 
							}
						if (!in_array($_GET['which'],array('posts','points'))) { ?>
						Threads: 
						<a href="<?= STRIP_REQUEST_URI ?>?toggle=<?= $fthreads == 'updated' ? 'latest':'updated' ?>" class="marginright"><?= ucfirst($fthreads) ?></a>
						<? } ?>
						Search: 
						<input type="text" name="search-filter" id="search-filter" class="{'userid':'<?= jsonparse(trim($_GET['userid'])) ?>','which':'<?= trim(jsonparse($_GET['which'])) ?>'}" value="<?= $_GET['filter'] ?>" />
						<?= CLEARBOTH ?>
					</div>
					<? if (!in_array($_GET['which'],array('posts','points'))) { ?>
					<div class="popup filter-threads">
						<a href="<?= STRIP_REQUEST_URI ?>?toggle=updated" class="{'name':'Updated'} <?= !$fthreads || $fthreads == 'updated' ? 'active':'' ?>">Updated</a>
						<a href="<?= STRIP_REQUEST_URI ?>?toggle=latest" class="{'name':'Latest'} <?= $fthreads == 'latest' ? ' active':'' ?>">Latest</a>
					</div>
					<? } 
						unset($fthreads,$frating);
					?>
				</div>
				<?= CLEARBOTH ?>*/?>
				
				<div id="feed-nodes" class="<?= $_GET['which'] ? 'feed-nodes-post' : '' ?>">
					<!-- feed nodes -->
					<? $numrows = generateFeedNode($_GET['forumid']) ?>
				</div>
				<div id="pagination" class="teoti-button skin-this margintop {'selector':'#whole .teoti-button'}">
					<div class="half">
						<? if ($_GET['page'] > 1) {
							$tmp = $_GET;
							unset($tmp['forumid']);
							$tmp['page'] = $tmp['page'] - 1;
							?>
							<a href="<?= STRIP_REQUEST_URI ?>?<?= htmlspecialchars(parseget($tmp)) ?>"
							   id="paginated">Prev</a>
						<? } else { ?>&nbsp;<? } ?>
					</div>
					<div class="half last-left alignright">
						<? if ($numrows) {
							$tmp = $_GET;
							unset($tmp['forumid']);
							$tmp['page'] = $tmp['page'] + 1;
							?><a href="<?= STRIP_REQUEST_URI ?>?<?= htmlspecialchars(parseget($tmp)) ?>">
								Next</a><? } else { ?>&nbsp;<? }
						unset($tmp);
						?>
					</div>
					<?= CLEARBOTH ?>
				</div>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<? $note = mysql_single('
	SELECT dateline FROM notification WHERE userid = \''.$mysqli->real_escape_string($session->userid).'\' ORDER BY dateline DESC LIMIT 1
	',__LINE__.__FILE__);?>
<div id="constants" class="{'filter-threads':'<?= $session->settings['filter-threads'] ?>','filter-rating':'<?= $session->settings['filter-rating'] ?>'}"><!-- constants for post node feed --></div>

<?
include 'includes/shoutbox.php';
include 'includes/footer.php';

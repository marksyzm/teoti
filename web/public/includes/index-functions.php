<?


//this creates the front page nodes via JSON object or PHP. This varies dependant on ajax call or page load
function generateFeedNode($forumid=0) {
	$page = (int)$_GET['page'];
	$which = $_GET['which'];
	$userid = $_GET['userid'];
	$filter = trim($_GET['filter']);

	if (!$page) $page = 1;

	global $session;
	global $mysqli;
	$view = $where = array();
	
	if (!$session->staff) $where[] = 'thread.visible = 1';
	$listposts = false;
	
	//generate forums applicable
	//$frating = in_array($session->settings['filter-rating'],array('nsfw','sfw','both')) ? $session->settings['filter-rating'] : 'sfw';
	$frating = 'sfw';
	//$fthreads = in_array($session->settings['filter-threads'],array('latest','updated')) ? $session->settings['filter-threads'] : 'latest';
	$fthreads = 'latest';
	
	switch ($frating) {
		//case 'nsfw': $view[] = 3; break;
		//case 'both': $view[] = 3;
		default: $view[] = 15;
	}
	
	if (!$session->god) $view = array(15);
	
	$where[] = 'thread.forumid IN ('.implode(',',forumChildren($forumid > 0 ? $forumid : implode(',',$view))).')';
	
	if (in_array($which,array('threads','posts','points')) && $session->userid) {
		switch($which) {
			case 'points': //user posts (pointed only, <>)
				$where[] = 'post.post_thanks_amount <> 0';
			case 'posts': //user posts
				$where[] = 'post.threadid = thread.threadid';
				$listposts = true;
				break;
			case 'threads': //user threads
            default:
                //$where[] = 'post.postid = thread.firstpostid';
		}
	} 
	
	if ($userid)
		$where[] = 'post'.($listposts ? '.':'').'userid = \''.$mysqli->real_escape_string($userid).'\'';
	
	if ($page > 0) $page = ($page - 1) * FEEDLIMIT;
	
	if ($filter) {
		if (strlen($filter) > 100) $filter = substr($filter,0,255);
		if (!$listposts) {
			$where[] = 'post.threadid = thread.threadid';
		}
		$where[] = 'MATCH(post.title,post.pagetext) AGAINST(\''.$mysqli->real_escape_string($filter).'\' IN NATURAL LANGUAGE MODE)';
	}

	$result = $mysqli->query('
		SELECT
			' .($listposts ? 'post.dateline, post.postid,post.dateline as pdateline, post.title as ptitle, post.userid, post.username, post.pagetext,':'DISTINCT thread.threadid,').'
			thread.*
		FROM  '.($listposts || $filter ? 'post,':'').'thread
		'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
		'.($filter ? '' : ' ORDER BY '.($listposts ? 'post.':'thread.').($fthreads == 'latest' || $listposts ? 'dateline':'lastpost').' DESC' ).'
		LIMIT '.($page ? $page.',':'').FEEDLIMIT) or die(__LINE__.__FILE__.$mysqli->error);
	
	if ($result->num_rows) {
		while ($t = $result->fetch_object()) {
			$t->otitle = $t->title; //otitle = original title, othreadid...
			$t->othreadid = $t->threadid;
			$t->datetime = $t->lastpost;
			if ($fthreads == 'latest') $t->datetime = $t->dateline;
			if ($listposts) {
				//set all variables from thread to post vars
				$t->firstpostid = $t->postid;
				$t->dateline = $t->lastpost = $t->pdateline;
				$t->threadid = $t->postid;
				$t->postuserid = $t->userid;
				$t->postusername = $t->username;
				$t->title = $t->ptitle ? $t->ptitle : ($t->title ? $t->title : 'Post '.$t->postid);
				$t->description = $t->pagetext;
			}
			//TODO: add post_thanks amount to threads table and get this data from the main query
			$p = mysql_single('SELECT post_thanks_amount FROM post WHERE postid = \''.$mysqli->real_escape_string($t->firstpostid).'\'',__LINE__.__FILE__);
			$forumtitle = forumtitle($t->forumid);
			$forumtitleurl = urlify($forumtitle);
			$u = mysql_single('SELECT avatar, usernameurl FROM user WHERE userid = \''.$mysqli->real_escape_string($t->postuserid).'\'',__LINE__.__FILE__);
			$link = $forumtitleurl.'/'.urlify($t->otitle,$t->othreadid).'.html'.($listposts ? '?p='.$t->postid : '');
		?>
			<div class="feed-node-outer feed-node {'datetime':'<?= $t->datetime ?>','threadid':'<?= $t->threadid ?>','created':'<?= $t->dateline ?>'}" id="feed-node-<?= $t->threadid ?>">
				<div class="feed-node skin-this {'selector':'#content-col .feed-node'}">
					<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
					<div class="body-left"><div class="body-right"><div class="body-inner">
						<div class="feed-node-inner">
							<?= points($p->post_thanks_amount,$t->firstpostid,($session->userid && $t->postuserid == $session->userid ? false : true)) ?>
							<?= $hasfirst = firstImage($t->thumbnail,$link,$t->postuserid,$u->usernameurl,$u->avatar) ?>
							<div class="node-content<?= $hasfirst ? ' node-content-hasimage':''?>">
								<div class="node-icon">
									<a href="<?= $link  ?>" class="icon-<?= $forumtitleurl ?>"><img src="images/blank.gif" alt="" /></a> 
								</div>
								<small class="node-time time-ago lighter skin-this {'selector':'#whole .lighter'}"><?= ago($t->dateline,$session->timezoneoffset) ?></small>
								<h3 class="node-title skin-this {'selector':'#whole #main h3'}">
									<!-- node title -->
									<a href="<?= $link ?>"><?= $t->title.($t->visible != 1 ? ' [DELETED]':'') ?></a>&nbsp;
								</h3>
								<div class="node-snippet light skin-this {'selector':'#whole .light'}"><!-- node paragraph -->
									<? if (trim($t->description)) {?><p class=""><?= showBrief($t->description,DESCLENGTH) ?></p><? } ?>
									<p class="lighter skin-this node-snippet-description {'selector':'#whole .lighter'}">
										<?= $t->replycount ?> Comments 
										&bull; Created by <?= $t->postusername ? userlink($t->postusername,$t->postuserid) : 'Anon' ?>
										<? if ($t->lastpost > $t->dateline) { ?>
										&bull; Last post by <?= userlink($t->lastposter) ?>
										<? } ?>
									</p>
								</div>
							</div>
							<?= CLEARBOTH ?>
						</div>
					</div></div></div>
					<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
				</div>
			</div>
		<?
		}
	} else {
		echo '<p class="light skin-this {\'selector\':\'#whole .light\'}">No Results</p>';
	}
	return $result;
}


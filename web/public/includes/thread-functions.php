<?

//this creates the comment nodes via JSON object or PHP. This varies depending on ajax call or page load
function generateCommentsNode($threadid,$firstpostid) {
	$done = false;
		global $session;
		$view = $where = array();
		$where[] = 'threadid = \''.mysql_real_escape_string($threadid).'\'';
		$where[] = 'postid != \''.mysql_real_escape_string($firstpostid).'\'';
		if (!$session->staff) $where[] = 'visible = 1';
		
		$p = mysql_single('
			SELECT COUNT(postid) as totalnumrows, GROUP_CONCAT(postid ORDER BY post.dateline SEPARATOR \',\') AS postids
			FROM post
			'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
			ORDER BY post.dateline ASC
			',__LINE__.__FILE__);
		
		$totalnumrows = $p->totalnumrows;
		//if $_GET[p] is valid, get page number
		if ((int)$_GET['p'] > 0 && !isset($_GET['page'])) {
			$tmp = explode(',',$p->postids);
			if (in_array((int)$_GET['p'],$tmp)) 
				$_GET['page'] = ceil((array_search((int)$_GET['p'],$tmp)+1)/POSTFEEDLIMIT);
		}
		
		$noofpages = ceil($totalnumrows/POSTFEEDLIMIT);
		$noofpages = $noofpages > 0 ? $noofpages : 1;
		if (!((int)$_GET['page'] > 0) && $noofpages > 0) $_GET['page'] = $noofpages; //set current page
		$page = ((int)$_GET['page']-1)*POSTFEEDLIMIT;
		$nextbool = ($totalnumrows - $page) > POSTFEEDLIMIT ? true:false;
		
		$result = mysql_query('
			SELECT * FROM post
			'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
			ORDER BY post.dateline ASC
			LIMIT '.($page ? $page.',':'').POSTFEEDLIMIT.'
			') or die(__LINE__.__FILE__.mysql_error());
		$numrows = mysql_num_rows($result);

		include PATH.'/includes/pagination.inc.php';
		?>
		<div id="post-nodes"><!-- post nodes -->
		<?
		if ($numrows) {
			while ($p = mysql_fetch_object($result)) {
				$u = mysql_single('SELECT username, avatar, usernameurl FROM user WHERE userid = \''.mysql_real_escape_string($p->userid).'\'',__LINE__.__FILE__);
			?>
				<div class="feed-node-outer feed-node {'datetime':'<?= $p->dateline ?>','updated':'<?= $p->updated ?>','postid':'<?= $p->postid ?>','username':'<?= jsonparse($u->username) ?>'}" id="feed-node-<?= $p->postid ?>">
					<div class="feed-node skin-this {'selector':'#content-col .feed-node'}">
						<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
						<div class="body-left"><div class="body-right"><div class="body-inner">
							<div class="feed-node-inner">
								<? if ($p->visible != 1) {?>
								<p class="light"><strong>
									[Post by <?= $p->username ?> deleted - 
                                    <a href="#undelete" class="undelete {'p':'<?= $p->postid ?>','do':'undelete', 'type':'post'}">click here</a> 
                                    to recover or 
									<a href="#preview-post" class="preview-post { 'p':'<?= $p->postid ?>' }">here</a> to preview]
								</strong></p>
								<?} else {?>
								<?= points($p->post_thanks_amount,$p->postid,(($session->userid && $p->userid == $session->userid) || $p->point_lock ? false : true)) ?>
								<?= $hasfirst = firstImage('','#',$p->userid,$u->usernameurl,$u->avatar,false) ?>
								<div class="node-content<?= $hasfirst ? ' node-content-hasimage':''?>">
									<!-- node paragraph -->
									<small class="lighter time-ago floatright skin-this {'selector':'#whole .lighter'}"><?= ago($p->dateline,$session->timezoneoffset) ?></small>
									<h3 class="skin-this {'selector':'#whole #main h3'}"><?= ($p->username ? userlink($p->username,$p->userid) : 'Anon') ?></h3>
									<div class="node-snippet clearfix"><?= $p->html ?></div>
									<div>
										<div class="teoti-button alignright skin-this {'selector':'#whole .teoti-button'}">
											<!-- edit/delete buttons -->
											<? if ($session->userid) {?>
												<a href="<?= URLPATH ?>/conversation?do=new&amp;report=<?= $p->postid ?>" title="Report this post<?= $session->staff ? ' ('.$p->ipaddress.')' : '' ?>">!</a>
												<a href="<?= STRIP_REQUEST_URI ?>?quote=<?= $p->postid ?>" class="quote-button {'p':'<?= $p->postid ?>'}">Quote</a>
												<? if ($session->staff || $session->userid == $p->userid) {?>
													<a href="<?= URLPATH ?>/submit?p=<?= $p->postid ?>" class="edit">Edit</a>
													<a href="#delete" class="delete {'p':'<?= $p->postid ?>','do':'delete','type':'post'}">Delete</a> 
												<? } ?>
											<? } ?>
										</div>
										<?= CLEARBOTH ?>
									</div>
								</div>
								<?= CLEARBOTH ?>
								<? } ?>
							</div>
						</div></div></div>
						<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
					</div>
				</div>
			<?
			}
			?>
			
			<?
			$done = true;
		}
	
	if (!$done) echo '<p class="error remove-this">No Comments yet!</p>';
	?>
	</div>
	<?
	include PATH.'/includes/pagination.inc.php';
	
	return $noofpages;
}




strip_url('t');
<?

//this creates the comment nodes via JSON object or PHP. This varies depending on ajax call or page load
function generateBlogsNode($userid,$bbcode) {
	$done = false;
	?>
	<div id="blog-nodes">
	<?
	if ((int)$userid > 0) {
		global $session;
		$view = array();
		$where = array(
			'postuserid = \''.mysql_real_escape_string($userid).'\'',
			'threadtype = 1', 'visible = 1'
		);
		
		$view = array(15);
		if ($session->god) $view[] = 3;
		if ($session->mod) $view[] = 2;
		if ($session->admin) $view[] = 1;
		
		$where[] =  'thread.forumid IN ('.implode(',',forumChildren(implode(',',$view))).')';
		
		$un = mysql_single('
			SELECT COUNT(threadid) as totalnumrows FROM thread
			'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
			ORDER BY thread.dateline DESC
			',__LINE__.__FILE__);
		
		$totalnumrows = $un->totalnumrows;
		
		$noofpages = ceil($totalnumrows/BLOGFEEDLIMIT);
		$noofpages = $noofpages > 0 ? $noofpages : 1;
		
		if (!((int)$_GET['page'] > 0)) $_GET['page'] = 1; //set current page
		$page = ((int)$_GET['page']-1)*BLOGFEEDLIMIT;
		$nextbool = ($totalnumrows - $page) > BLOGFEEDLIMIT ? true:false;
		
		$result = mysql_query('
			SELECT * FROM thread
			'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
			ORDER BY thread.dateline DESC
			LIMIT '.($page ? $page.',':'').BLOGFEEDLIMIT.'
			') or die(__LINE__.__FILE__.mysql_error());
		$numrows = mysql_num_rows($result);

		if ($numrows) {
			while ($t = mysql_fetch_object($result)) {
				$p = mysql_single('SELECT pagetext FROM post WHERE postid = \''.mysql_real_escape_string($t->firstpostid).'\'',__LINE__.__FILE__);
				$forumtitle = forumtitle($t->forumid);
				$forumtitleurl = urlify($forumtitle);
				$link = URLPATH.'/'.$forumtitleurl.'/'.urlify($t->title,$t->threadid).'.html';
			?>
				<div class="feed-node-outer feed-node-outer {'datetime':'<?= $t->dateline ?>','updated':'<?= $t->dateline ?>','userid':'<?= $un->usernoteid ?>'}" id="feed-node-<?= $p->userid ?>">
					<div class="feed-node">
						<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
						<div class="body-left"><div class="body-right"><div class="body-inner">
							<div class="feed-node-inner">
								<?/*= $hasfirst = firstImage('','#',$u->userid,$u->username,$u->avatar,false) */?>
								<div class="node-content node-content-nopoints">
									<!-- node paragraph -->
									<div class="node-icon">
										<a href="<?= $link  ?>" class="icon-<?= $forumtitleurl ?>"><img src="<?= URLPATH ?>/images/blank.gif" alt="" /></a>
									</div>
									<small class="node-time time-ago lighter skin-this {'selector':'#whole .lighter'}"><?= ago($t->dateline,$session->timezoneoffset) ?></small>
									<h3 class="skin-this {'selector':'#whole #main h3'}"><a href="<?= $link  ?>"><?= $t->title ?></a></h3>
									<div class="node-snippet">
										<!-- comments -->
										<? 
											$txtpos = stripos($p->pagetext,'[break]');
											echo $bbcode->parse($txtpos === false ? $p->pagetext : substr($p->pagetext,0,$txtpos)); 
											if ($txtpos !== false) echo ' <p class="alignright"><a href="'.$link.'">Read more...</a></p>';
										?>
										<?= CLEARBOTH ?>
										<p class="alignright teoti-button">
											<?= $t->views ? $t->views : '0' ?> views &bull; 
											<?= $t->replycount ? $t->replycount : '0' ?> comments
											<a href="<?= $link  ?>">Add a comment</a>
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
			?>
			
			<?
			$done = true;
		}
	}
	if (!$done) echo '<p class="error remove-this">No Comments yet!</p>';
	?>
	</div>
	<div id="pagination" class="<?= $noofpages == 1 ? 'hidethis' : '' ?>">
		<div class="quarter teoti-button prev-button skin-this {'selector':'#whole .teoti-button'}">
			<? if ((int)$_GET['page'] > 1) {
			$tmp = $_GET;
			unset($tmp['t']);
			$tmp['page'] = $tmp['page'] - 1;
			?>
				<a href="<?= STRIP_REQUEST_URI.'?'.htmlspecialchars(parseget($tmp)) ?>" id="paginated">Prev</a>
			<? } else echo '&nbsp;' ?>
		</div>	
		<div class="quarter floatright alignright teoti-button next-button skin-this {'selector':'#whole .teoti-button'}">
			<? if ($nextbool) {
				$tmp = $_GET;
				unset($tmp['t']);
				$tmp['page'] = $tmp['page'] + 1;
			?>
				<a href="<?= STRIP_REQUEST_URI.'?'.htmlspecialchars(parseget($tmp)) ?>">Next</a>
			<? } else echo '&nbsp;' ?>
		</div>
		<div class="aligncenter">
			<p class="light per-page"><?
				//$noofpages = $noofpages > MAXLIMIT ? MAXLIMIT : $noofpages;
				for ($i=1;$i<=$noofpages;$i++){
					$tmp = $_GET;
					unset($tmp['t']);
					$tmp['page'] = $i;
					?>
					<a href="<?= STRIP_REQUEST_URI.'?'.htmlspecialchars(parseget($tmp)) ?>"><?= $_GET['page']==$i ? '<strong>':''?>[<?= $i ?>]<?= $_GET['page']==$i ? '</strong>':''?></a>	
				<?} ?><br/>
			</p>
		</div>
		<?= CLEARBOTH ?>
	</div>
	<?
	return $noofpages;
}


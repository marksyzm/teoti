<?

//updates user message numbers. A little server intensive but it keeps counts accurate. 
function updateConversationCount($users) {
	global $session;
	if ((is_string($users) || is_numeric($users)) && strlen($users)) $users = explode(',',(string)$users);
	if (!is_array($users) && $users) return false; 
	if ($users) { 
		foreach ($users as $user) {
			$part = mysql_single('
				SELECT COUNT(pmid) AS pmtotal FROM pm,pmtext 
				WHERE userid = \''.mysql_real_escape_string($user).'\' 
				AND pmtext.pmtextid = pm.pmtextid
				GROUP BY userid
				',__LINE__.__FILE__);
			$pmtotal = $part->pmtotal;
			$part = mysql_single('
				SELECT COUNT(pmid) AS pmunread FROM pm,pmtext 
				WHERE userid = \''.mysql_real_escape_string($user).'\' 
				AND pmtext.pmtextid = pm.pmtextid
				AND messageread = 0 
				GROUP BY userid
				',__LINE__.__FILE__);
			mysql_query('
				UPDATE user SET 
				pmtotal = \''.mysql_real_escape_string($pmtotal).'\'
				,pmunread = \''.mysql_real_escape_string($part->pmunread).'\'
				WHERE userid = \''.mysql_real_escape_string($user).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			if ($session->userid == $user) { //failsafe for right hand column
				$session->pmtotal = $pmtotal;
				$session->pmunread = $part->pmunread;
			}
		}
	}
}

//this creates the comment nodes via JSON object or PHP. This varies depending on ajax call or page load
function generateConversationNode($pmid) {
	$done = false;
	
	global $session;
	$view = $where = array();
	$where[] = 'pmtextid = \''.mysql_real_escape_string($pmid).'\'';

	$pmn = mysql_single('
		SELECT COUNT(pmnodeid) as totalnumrows FROM pmnode
		'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
		ORDER BY pmnode.dateline ASC
		',__LINE__.__FILE__);
	
	$totalnumrows = $pmn->totalnumrows;
	
	$noofpages = ceil($totalnumrows/PMFEEDLIMIT);
	$noofpages = $noofpages > 0 ? $noofpages : 1;
	//if (!(int)$_GET['page']) $_GET['page'] = 1;
	if (!((int)$_GET['page'] > 0) && $noofpages > 0) $_GET['page'] = $noofpages; //set current page
	$page = ((int)$_GET['page']-1)*PMFEEDLIMIT;
	$nextbool = ($totalnumrows - $page) > PMFEEDLIMIT ? true:false;
	
	$result = mysql_query('
		SELECT * FROM pmnode
		'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
		ORDER BY pmnode.dateline ASC
		LIMIT '.($page ? $page.',':'').PMFEEDLIMIT.'
		') or die(__LINE__.__FILE__.mysql_error());
	$numrows = mysql_num_rows($result);
	$pmns = array();
	if ($numrows) {
		while ($tmp = mysql_fetch_object($result))
			$pmns[] = $tmp;
		if ($pmns) $pmns = array_reverse($pmns);
		$done = true;
	}
	
	include PATH.'/includes/pagination.inc.php';
	
	?>
	<div id="post-nodes">
	<?
	
	foreach ($pmns as $pmn) {
		$u = mysql_single('SELECT username, usernameurl, avatar FROM user WHERE userid = \''.mysql_real_escape_string($pmn->userid).'\'',__LINE__.__FILE__);
	?>
		<div class="feed-node-outer feed-node {'datetime':'<?= $pmn->dateline ?>','pmnid':'<?= $pmn->pmnodeid ?>'}" id="feed-node-<?= $pmn->pmnodeid ?>">
			<div class="feed-node skin-this {'selector':'#content-col .feed-node'}">
				<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
				<div class="body-left"><div class="body-right"><div class="body-inner">
					<div class="feed-node-inner">
						<?= $hasfirst = firstImage('','#',$pmn->userid,$u->usernameurl,$u->avatar,false) ?>
						<div class="node-content node-content-nopoints<?= $hasfirst ? ' node-content-hasimage':''?>">
							<!-- node paragraph -->
							<span class="lighter time-ago floatright skin-this {'selector':'#whole .lighter'}"><?= ago($pmn->dateline,$session->timezoneoffset) ?></span> 
							<h3 class="skin-this {'selector':'#whole #main h3'}"><?= ($u->username ? userlink($u->username,$pmn->userid) : 'Anon') ?></h3>
							<div class="node-snippet">
								<!-- comment here -->
								<?/*= $bbcode->parse($pmn->message)*/ ?>
								<?= $pmn->html ?>
							</div>
							<div class="teoti-button alignright skin-this {'selector':'#whole .teoti-button'}">
								<!-- edit/delete buttons -->
								<? if ($session->staff || $session->userid == $pmn->userid) {?>
									<a href="#delete-message" class="delete {'do':'delete','type':'message','pmnodeid':'<?= $pmn->pmnodeid ?>'}">Delete</a> 
								<? } ?>
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
	
	if (!$done) echo '<p class="error remove-this">No Conversations yet!</p>';
	?>
	</div>
	<?
	include PATH.'/includes/pagination.inc.php';
	
	return $noofpages;
}


function listConversations(){
	global $session;
	
	$where = array(
		'pm.userid = \''.mysql_real_escape_string($session->userid).'\''
		,'pmtext.pmtextid = pm.pmtextid'
		//,'pm.folderid >= 0'
	);
	
	if (in_array($_GET['show'],array('unread'))) $where[] = '(pm.unread > 0 OR pm.messageread = 0)';
	
	$pm = mysql_single('
		SELECT COUNT(pmtext.pmtextid) AS totalnumrows FROM pm, pmtext
		'.($where ? 'WHERE '.implode("\nAND ",$where) : '').'
		ORDER BY pmtext.lastpm DESC
		',__LINE__.__FILE__);
	
	$totalnumrows = $pm->totalnumrows;
		
	$noofpages = ceil($totalnumrows/POSTFEEDLIMIT);
	$noofpages = $noofpages > 0 ? $noofpages : 1;
	if (!(int)$_GET['page']) $_GET['page'] = 1;
	$page = ((int)$_GET['page']-1)*POSTFEEDLIMIT;
	$nextbool = ($totalnumrows - $page) > POSTFEEDLIMIT ? true:false;
	
	$result = mysql_query('
		SELECT pm.*, pmtext.* FROM pm, pmtext
		'.($where ? 'WHERE '.implode("\nAND ",$where) : '').'
		GROUP BY pmtext.pmtextid ORDER BY pmtext.lastpm DESC
		LIMIT '.($page ? $page.',':'').POSTFEEDLIMIT.'
		') or die(__LINE__.__FILE__.mysql_error());
    ?>
    <div id="conversations">
    <?php
	while ($pm = mysql_fetch_object($result)) {?>
		<div id="conversation-<?= $pm->pmtextid ?>">
			<div class="twothirds">
				<div class="twothirds">
					<h3 class="skin-this {'selector':'#whole #main h3'}">
						<a href="<?= STRIP_REQUEST_URI ?>?pm=<?= $pm->pmtextid ?>"><?= $pm->title ?></a>
						<?= $pm->messageread == 0 ? ' <span class="light skin-this {\'selector\':\'#whole .light\'}">(unread)</span>' : '' ?>
					</h3>
					<?
					$u = mysql_single('
						SELECT GROUP_CONCAT(user.username SEPARATOR \', \') as usernames FROM user,pm 
						WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\' 
						AND pm.userid != \''.mysql_real_escape_string($session->userid).'\'
						AND user.userid = pm.userid
						ORDER BY username
						',__LINE__.__FILE__);
					?>
					<p><span class="light skin-this {'selector':'#whole .light'}">Participants: </span><?= $u->usernames ?></p>
				</div>
				<div class="third last-left teoti-button alignright skin-this {'selector':'#whole .teoti-button'}">
					<p>
						<? if ($session->userid == $pm->fromuserid || $session->staff) { ?>
							<?/*<a href="<?= STRIP_REQUEST_URI ?>?do=delete&amp;type=conversation&amp;pm=<?= $pm->pmtextid ?>" onclick="return confirm('Are you sure you want to delete this conversation?');">Delete</a>*/?>
							<a href="#delete-conversation" class="delete {'do':'delete','type':'conversation','pm':'<?= $pm->pmtextid ?>'}">Delete</a>
						<? } else { ?>&nbsp;<? } ?>
					</p>
				</div>
				<?= CLEARBOTH ?>
			</div>
			<div class="third last-left">
				<p class="light alignright skin-this {'selector':'#whole .light'}">
					<?= $pm->unread ? $pm->unread : '0' ?> unread messages
					<br />Latest: <?= ago($pm->lastpm,$session->timezoneoffset)?>
					<br /><span class="lighter skin-this {'selector':'#whole .lighter'}">Created: <?= ago($pm->dateline,$session->timezoneoffset)?></span>
				</p>
			</div>
			<?= CLEARBOTH ?>
		</div>
	<?}
	
	if (!$totalnumrows) echo '<p class="error remove-this">No Comments yet!</p>';
	include PATH.'/includes/pagination.inc.php';
	?>
    </div>
    <?php
    
	return $numrows;
}


strip_url('t');
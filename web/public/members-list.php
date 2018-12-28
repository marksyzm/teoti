<?php
require_once 'includes/dbconnect.php';
$pagetitle = 'Members List';
include PATH.'/includes/header.php';
?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<h2 class="skin-this {'selector':'#whole #main h2'}">Members List</h2>
				<div>
					<p>Filter by name:
					<?
						if ($_GET['char'] && $_GET['char'] != 'other' && !preg_match( '/\w/i',$_GET['char'])) $_GET['char'] = '';
						$get = $_GET;
						
						for ($character = 65; $character < 91; $character++) {
							$char = chr($character);
							$get['char'] = $char;
							unset($get['page']);
							echo 
								'<a href="',STRIP_REQUEST_URI,($get ? '?'.parseget($get):''),'">'
									,($_GET['char'] == $char ? '<strong>':''),'[',$char,']',($_GET['char'] == $char ? '</strong>':'')
								,'</a> '
							;
						}
						$get['char'] = 'other';
						echo 
							'<a href="',STRIP_REQUEST_URI,($get ? '?'.parseget($get):''),'">'
								,($_GET['char'] == 'other' ? '<strong>':''),'[Other]',($_GET['char'] == 'other' ? '</strong>':'')
							,'</a> '
						;
						unset($get['char']);
						echo '<a href="',STRIP_REQUEST_URI,($get ? '?'.parseget($get):''),'">[Remove this filter]</a> ';
					?>
					</p>
					<p>Filter by group:
					<?
						$get = $_GET;
						unset($get['page']);
						$groups = array('Admin','Mod','God','Regular');
						foreach ($groups as $group) {
							$get['group'] = $group;
							echo 
								'<a href="',STRIP_REQUEST_URI,($get ? '?'.parseget($get):''),'">'
									,($_GET['group'] == $group ? '<strong>':''),'[',$group,']',($_GET['group'] == $group ? '</strong>':'')
								,'</a> '
							;
						}
						unset($get['group']);
						echo '<a href="',STRIP_REQUEST_URI,($get ? '?'.parseget($get):''),'">[Remove this filter]</a> ';
					?>
					</p>
					
					<p>Order by:
					<?
						$orderbys = array('username','joindate','joindate DESC','lastactivity DESC');
						if (!in_array($_GET['orderby'],$orderbys)) $_GET['orderby'] = 'username';
					
						$get = $_GET;
						unset($get['page']);
						
						foreach ($orderbys as $orderby) {
							$get['orderby'] = $orderby;
							echo 
								'<a href="',STRIP_REQUEST_URI,($get ? '?'.parseget($get):''),'">'
									,($_GET['orderby'] == $orderby ? '<strong>':''),'[',$orderby,']',($_GET['orderby'] == $orderby ? '</strong>':'')
								,'</a> '
							;
						}
						$get['orderby'] = 'username';
						echo '<a href="',STRIP_REQUEST_URI,($get ? '?'.parseget($get):''),'">[Remove this filter]</a> ';
					?>
					</p>
					
					<?
					$where = array();
					if ($_GET['char']) $where[] = 'username REGEXP \'^'.mysql_real_escape_string($_GET['char'] == 'other' ? '[^a-zA-Z]' : $_GET['char']).'\'';
					if (in_array($_GET['group'],$groups)) $where[] = 'usergroupid IN ('.constant(strtoupper($_GET['group'].'GROUPS')).')';
					
					$u = mysql_single('
						SELECT COUNT(userid) as totalnumrows
						FROM user
						'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
						ORDER BY user.'.$_GET['orderby'].'
						',__LINE__.__FILE__);
						
					$totalnumrows = $u->totalnumrows;
					
					$noofpages = ceil($totalnumrows/POSTFEEDLIMIT);
					$noofpages = $noofpages > 0 ? $noofpages : 1;
					if (!((int)$_GET['page'] > 0)) $_GET['page'] = 1; //set current page
					$page = ((int)$_GET['page']-1)*POSTFEEDLIMIT;
					$nextbool = ($totalnumrows - $page) > POSTFEEDLIMIT ? true:false;
					
					$result = mysql_query('
						SELECT * FROM user
						'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
						ORDER BY user.'.$_GET['orderby'].'
						LIMIT '.($page ? $page.',':'').POSTFEEDLIMIT.'
						') or die(__LINE__.__FILE__.mysql_error());
					$numrows = mysql_num_rows($result);
			
					if ($numrows) {
						while ($u = mysql_fetch_object($result)) {
						?>
							<div class="feed-node-outer feed-node" id="user-node-<?= $u->userid ?>">
								<div class="feed-node skin-this {'selector':'#content-col .feed-node'}">
									<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
									<div class="body-left"><div class="body-right"><div class="body-inner">
										<div class="feed-node-inner">
											<?= $hasfirst = firstImage('','#',$u->userid,$u->usernameurl,$u->avatar,false) ?>
											<div class="node-content<?= $hasfirst ? ' node-content-hasimage':''?>">
												<!-- node paragraph -->
												<h3 class="skin-this {'selector':'#whole #main h3'}"><?= ($u->username ? userlink($u->username,$u->userid) : 'Anon') ?></h3>
												<div class="node-snippet">
													<!-- comments -->
													<?= $u->posts ? $u->posts : '0' ?> posts, 
													<?= $u->threads ? $u->threads : '0' ?> threads, 
													<?= $u->post_thanks_thanked_times ? $u->post_thanks_thanked_times : '0' ?> points
													<?= $u->user_total_score ? $u->user_total_score : '0' ?> total points (all time)<br /> 
													Last seen <?= ago($u->lastactivity) ?>, 
													Joined <?= ago($u->joindate), ($u->location ? ', '.$u->location : '') ?>
													
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
				
					if (!$done) echo '<p class="error remove-this">No members yet!</p>';
					?>
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
							<p class="light per-page skin-this {'selector':'#whole .light'}"><?
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
					<? if ($session->admin && $session->userid == 2) {
						$result = mysql_query('
							SELECT userid,username FROM user WHERE lastactivity > (UNIX_TIMESTAMP()-(60*60*24))
							ORDER BY lastactivity DESC
							') or die(__LINE__.__FILE__.mysql_error());
						?>
					<h2 class="skin-this {'selector':'#whole #main h2'}">Last seen within 24 hours (<?= mysql_num_rows($result) ?>)</h2>
					<p><?
					
					$i=0;
					while ($u = mysql_fetch_object($result)) 
						echo ($i++ ? ', ':''),userlink($u->username,$u->userid);
					?></p>
					<?
						$result = mysql_query('
							SELECT userid,username FROM user WHERE lastactivity > (UNIX_TIMESTAMP()-(60*60*24*7))
							ORDER BY lastactivity DESC
							') or die(__LINE__.__FILE__.mysql_error());
					?>
					<h2 class="skin-this {'selector':'#whole #main h2'}">Last seen within 7 days (<?= mysql_num_rows($result) ?>)</h2>
					<p><?
					
					$i=0;
					while ($u = mysql_fetch_object($result)) 
						echo ($i++ ? ', ':''),userlink($u->username,$u->userid);
					?></p>
					<? } ?>
					
				</div>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<?

include PATH.'/includes/footer.php';
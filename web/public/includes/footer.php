										

									<? if (!$session->settings['old'] || $_GET['filter'] || !($session->settings['old'] && ($_GET['forumid'] == 3 || !$_GET['forumid'])) || $_GET['which'] || !$feed) {?>
									</div>			
									<!-- end content column -->
									
									<?= CLEARBOTH ?>
									<? } ?>
								</div>
							</div></div></div>
							<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
						</div>
					</div>
					<!-- end main content wrapper -->
					
					
					
				</div>
			</div></div></div>
			<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
		</div>
		
		<div class="teoti-button" id="quote-button"><a href="#quote">Quote</a></div>
		<div id="notification-box"><!-- the notification popup box --></div>
		<div id="userinfo-box" class="popup"><!-- the userinfo popup box --></div>
		
	</div>
	
	<!-- footer -->
	<div id="footer-outer">
		<div id="footer" class="skin-this {'selector':'#footer'}">
			<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
			<div class="body-left"><div class="body-right"><div class="body-inner">
				<div id="footer-inner"><!-- -->
					<?
						$ignore = array();
						$starr = array(array('id' => '','name'=>'Default Style'));
						$result = $mysqli->query('
							SELECT id, name FROM skins WHERE user = \''.$mysqli->real_escape_string($session->userid).'\' ORDER BY name
							') or die(__LINE__.__FILE__.$mysqli->error);
						while ($row = $result->fetch_object()) {
							$ignore[] = $row->id;
							$starr[] = array('id' => $row->id,'name' => $row->name);
						}
						$result = $mysqli->query('
							SELECT skins.id,skins.name FROM skins, skinthemes WHERE skins.id = skinthemes.skin '.($ignore ? 'AND skins.id NOT IN ('.implode(',',$ignore).')':'').' ORDER BY name
							') or die(__LINE__.__FILE__.$mysqli->error);
						while ($row = $result->fetch_object()) $starr[] = array('id' => $row->id,'name' => $row->name);
						unset($row,$result,$ignore);
					?>
					<div class="floatright last-right alignright other-feeds">
						<a href="http://www.facebook.com/theendoftheinternet" target="_blank" class="icon-facebook"><img src="<?= URLPATH ?>/images/blank.gif" alt="www.facebook.com/theendoftheinternet" /></a> 
						<a href="http://www.twitter.com/<?= TWITTER ?>" target="_blank" class="icon-twitter"><img src="<?= URLPATH ?>/images/blank.gif" alt="@<?= TWITTER ?>" /></a> 
						<a href="<?= URLPATH ?>/external.php" target="_blank" title="RSS" class="icon-rss"><img src="<?= URLPATH ?>/images/blank.gif" alt="RSS" /></a>
					</div>
					<div>
						<!-- styler selection and management-->
						<span class="mobile-hidethis">
							<?= OURCOMPANY ?> v<?= VERSION ?>
							&bull; <?= round(array_sum(explode(' ',microtime())) - $starttime,3).' seconds'; ?>
						&bull; </span>
						<a href="members-list">Members list</a>
						&bull; <a href="./?do=oldnew">Switch to <?= $session->settings['old'] ? 'new' : 'old' ?> layout</a>
						<? if ($session->userid) {?>
							<? if ($starr) {?>
							&bull; 
							<select class="select-redirect">
								<? foreach($starr as $v){ ?>
								<option value="<?= $v['id'] ?>"<?= $session->styleid == $v['id'] ? ' selected="selected"':''?>><?= $v['name'] ? $v['name'] : 'Style '.$v['id'] ?></option>
								<? } ?>
							</select>
							<? } ?>
							<? if ($session->admin) {?>&bull; <a href="<?= URLPATH ?>/manage-user">Manage Users</a><? } ?>
						<? } ?>
					</div>
					
					
					<?= CLEARBOTH ?>
				</div>
			</div></div></div>
			<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
		</div>
	</div>
	<!-- end footer -->
</div>
<? $note = mysql_single('
	SELECT dateline FROM notification WHERE userid = \''.$mysqli->real_escape_string($session->userid).'\' ORDER BY dateline DESC LIMIT 1
	',__LINE__.__FILE__);
$obj = new stdClass();
$obj->usess = session_id();
$obj->stripRequestUri = STRIP_REQUEST_URI;
$obj->isLoggedIn = !!$session->userid;
$obj->forumId = (int)($_GET['forumid'] > 0 ? $_GET['forumid'] : 15);
$obj->lastnote = !!$note->dateline ? $note->dateline : 0;
$obj->which = $_GET['which'];
$obj->sessUserId = (int)$session->userid;
$obj->timezoneOffset = ( $session->timezoneoffset ? $session->timezoneoffset :'0' ) * 60 * 60;
$obj->wysiwyg = !!$session->settings['wysiwyg'];
$obj->staff = !!$session->staff;
?>
<div id="usess" class='<?php echo json_encode($obj) ?>'><!-- --></div>
<?php
$scriptvars = array();
$scriptvars[] = 'v=102';
if ($feed) $scriptvars[] = 'feed=1';
if ($shout) $scriptvars[] = 'shout=1';
if ($dragdrop) $scriptvars[] = 'dragdrop=1';
if ($thread) $scriptvars[] = 'thread=1';
if ($member) $scriptvars[] = 'member=1';
if ($conversation) $scriptvars[] = 'conversation=1';
if ($submit) $scriptvars[] = 'submit=1';
?>
<script src="http://<?= $_SERVER['HTTP_HOST'] ?>/socket.io/socket.io.js?v=9.10"></script>
<script src="lib/jquery.js.php<?= count($scriptvars) ? '?'.implode('&amp;',$scriptvars) : '' ?>"></script>
<?= $EXTRASCRIPT ? $EXTRASCRIPT : '' ?>

<script>

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-4220014-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>
</html>
<? 
ob_end_flush(); 
?>

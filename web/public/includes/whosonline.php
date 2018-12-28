<?
$result = $mysqli->query('
	SELECT username,avatar,usernameurl FROM user WHERE lastactivity > '.$mysqli->real_escape_string(time() - 60*60*4).' ORDER BY lastactivity DESC
	') or die(__LINE__.__FILE__.$mysqli->error);
if ($result->num_rows) {
?>
	<!-- who's online -->
	<div id="whosonline-outer">
		<div id="whosonline" class="skin-this {'selector':'#whosonline'}">
			<input type="hidden" class="imageid" />
			<input type="hidden" class="imagepath" />
			<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
			<div class="body-left"><div class="body-right"><div class="body-inner">
				<div id="whosonline-inner">
					<div class="clearfix">
						<? while ($who = $result->fetch_object()) {?>
						<a href="<?= URLPATH ?>/members/<?= $who->username ?>.html" title="<?= htmlspecialchars($who->username) ?>" class="who">
							<img src="<?= URLPATH ?>/images/phpThumb.php?src=<?= urlencode($who->avatar ? 'avatar/'.$who->avatar :'error.png') ?>&amp;zc=1&amp;w=22&amp;h=22&amp;f=png" alt="<?= htmlspecialchars($who->username) ?>" style="width:22px;height:22px;" />
						</a>
						<? } ?>
					</div>
					<div class="popup hidethis"><!-- --></div>
					<div class="margintop">
					<? 
					$result = $mysqli->query('
						SELECT username, usernameurl, birthday_search FROM user WHERE CONCAT(DAY(birthday_search),\' \',MONTH(birthday_search)) = \''.$mysqli->real_escape_string(date('j n')).'\'
						AND userid NOT IN (\''.$mysqli->real_escape_string(BANNED).'\')
						AND showbirthday > 0
						AND (activate = \'\' OR activate IS NULL)
						') or die(__LINE__.__FILE__.$mysqli->error);
					if ($result->num_rows()) { ?>
					<? while ($who = $result->fetch_object()) {
							echo($i++ ? ', ':''),userlink($who->username,$who->userid),' (',date('Y')-date('Y',strtotime($who->birthday_search)),')';
						}
					} ?>
					</div>
				</div>
			</div></div></div>
			<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
		</div>
	</div>
<? }

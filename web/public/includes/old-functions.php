<?

function threadLoader($settings,$session){
	if (!$settings['page'] || !is_numeric($settings['page']) || $sessions['nopage']) $settings['page'] = 1;
	$settings['limit'] = $settings['limit'] ? $settings['limit']:'10';
	$settings['orderby'] = $settings['orderby'] ? $settings['orderby']:'dateline';
	
	$result = mysql_query('
		SELECT threadid,title,forumid
		FROM thread
		'.(count($settings['where']) ? 'WHERE '.implode("\nAND ",$settings['where']): '').'
		ORDER BY '.$settings['orderby'].'
		LIMIT '.(($settings['page']-1)*$settings['limit']).','.$settings['limit'].'
		') or die(__LINE__.__FILE__.mysql_error());
	if (!$settings['nopage'] && mysql_num_rows($result)) $nextbool = true;
	while ($thread = mysql_fetch_object($result)) {
		$forumtitle = forumtitle($thread->forumid);
		$urlft = urlify($forumtitle);
		?>
			<div class="col-head-bit">
				<div class="col-head-icon">
					<a href="<?= URLPATH,'/',$urlft,'/',urlify($thread->title,$thread->threadid)?>.html" class="icon-<?= $urlft ?>">
						<img src="<?= URLPATH ?>/images/blank.gif" alt="<?= $forumtitle ?>" />
					</a>
				</div>
				<div class="col-head-link with-icon without-score">
					<a href="<?= URLPATH,'/',$urlft,'/',urlify($thread->title,$thread->threadid)?>.html">
						<?= $thread->title ?>
					</a>
				</div>
				<?= CLEARBOTH ?>
			</div>
		<?
	}
	
	if (!$settings['nopage']) {?>
		<div class="half teoti-button skin-this {'selector':'#whole .teoti-button'}">
		<? if ($settings['page'] > 1) { ?>
			<a href="#prev-page" 
				class="paginate {'do':'old','name':'<?= strtolower($settings['name']) ?>','page':'<?= $settings['page']-1 ?>'}"
				onclick="$.get('./',$(this).metadata(),function(r){ $('#old-node-<?= strtolower($settings['name']) ?>').html(r) }); return false;">Prev</a>
		<? } else {?>&nbsp;<? } ?>
		</div>
		<div class="half alignright teoti-button last-left skin-this {'selector':'#whole .teoti-button'}">
			<? if ($nextbool) { ?>
				<a href="#next-page" 
					class="paginate {'do':'old','name':'<?= strtolower($settings['name']) ?>','page':'<?= $settings['page']+1 ?>'}"
					onclick="$.get('./',$(this).metadata(),function(r){ $('#old-node-<?= strtolower($settings['name']) ?>').html(r) }); return false;">Next</a>
			<? } else {?>&nbsp;<? } ?>
		</div>
		<?= CLEARBOTH ?>
	<?
	}
}

function userLoader ($page=1) {
	if (!$page || !is_numeric($page)) $page = 1;
	$result = mysql_query('
		SELECT userid,username,usernameurl,post_thanks_thanked_times
		FROM user
		#WHERE usergroupid NOT IN ('.ADMINGROUPS.','.MODGROUPS.')
		ORDER BY post_thanks_thanked_times DESC
		LIMIT '.(((int)$page-1) * 10).',10
		') or die(__LINE__.__FILE__.mysql_error());
	if (mysql_num_rows($result)) $nextbool = true;
	while ($user = mysql_fetch_object($result)) {
		?>
			<div class="col-head-bit">
				<div class="col-head-points light skin-this {'selector':'#whole .light'}"><?= number_format($user->post_thanks_thanked_times) ?></div>
				<div class="col-head-link">
					<a href="<?= URLPATH,'/members/',$user->usernameurl ?>.html" class="userlink">
						<?= $user->username ?>
					</a>
				</div>
				<?= CLEARBOTH ?>
			</div>
		<?
	}
	
	?>
		<div class="half teoti-button skin-this {'selector':'#whole .teoti-button'}">
		<? if ($page > 1) { ?>
			<a href="#prev-page" 
				class="paginate {'do':'old','name':'scores','page':'<?= $page-1 ?>'}" 
				onclick="$.get('./',$(this).metadata(),function(r){ $('#old-node-scores').html(r) }); return false;">Prev</a>
		<? } else {?>&nbsp;<? } ?>
		</div>
		<div class="half alignright teoti-button last-left skin-this {'selector':'#whole .teoti-button'}">
			<? if ($nextbool) { ?>
				<a href="#next-page" 
					class="paginate {'do':'old','name':'scores','page':'<?= $page+1 ?>'}"
					onclick="$.get('./',$(this).metadata(),function(r){ $('#old-node-scores').html(r) }); return false;">Next</a>
			<? } else {?>&nbsp;<? } ?>
		</div>
		<?= CLEARBOTH ?>
	<?
}

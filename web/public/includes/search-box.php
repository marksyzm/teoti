<form action="./" method="get" id="">
	<div class="margintop marginbottom search-box">
		<input type="text" name="filter" id="search-filter" placeholder="Search" class="updateinput {'userid':'<?= jsonparse(trim($_GET['userid'])) ?>','which':'<?= trim(jsonparse($_GET['which'])) ?>'}" value="<?= htmlspecialchars($_GET['filter']) ?>" />
		<a href="#advanced-search" class="hidethis">Advanced</a>
		<div class="search-advanced hidethis">
			Type:
			<div class="clearfix">
				<div class="half">
					<label>
						<input type="radio" name="which" value="threads" <?= empty($_GET['which']) || $_GET['which'] == 'threads' ? 'checked="checked"':'' ?> /> 
						Threads
					</label>
				</div>
				<div class="half last-left">
					<label>
						<input type="radio" name="which" value="posts" <?= $_GET['which'] == 'posts' ? 'checked="checked"':'' ?> /> 
						Posts
					</label>
				</div>
			</div>
			<div class="teoti-button">
				Username:
				<input type="hidden" name="userid" value="" />
				<input type="text" class="search-username updateinput" />
			</div>
			<div class="teoti-button">
				Category:
				<input type="hidden" name="forumurl" value="" />
				<input type="hidden" name="forumid" value="" />
				<input type="text" class="search-category updateinput" />
			</div>
			<div class="aligncenter margintop">
				<input type="submit" value="Search" />
			</div>
		</div>
	</div>
</form>
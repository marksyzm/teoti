<?php
require 'includes/dbconnect.php';
require PATH.'/includes/manage-user-queries.php';
require PATH.'/includes/header.php';
//display

if ($session->admin) {
	if ($_GET['itemid'] > 0) 
		$user = mysql_single('SELECT * FROM user WHERE userid = \''.mysql_real_escape_string($_GET['itemid']).'\'',__LINE__.__FILE__);
	?>
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				
	<h2 class="light skin-this {'selector':'#whole #main h2'}">Find/Edit User</h2>
	<table class="updateinput">
		<tr>
			<td class="leftcell">
				<label for="usersearch">Search</label>
			</td>
			<td>
				<input type="text" name="usersearch" id="usersearch" value="" style="width:200px;" />
			</td>
		</tr>
	</table>
	<h2 class="skin-this {'selector':'#whole #main h2'}"><?= ($_REQUEST['itemid'] ? 'Edit':'Create').' User' ?></h2>
	<?
	echo $_GET['message'] ? '<p class="message"><span>'.$_GET['message'].'</span></p><br />' : '';
	echo $_GET['error'] ? '<p class="error"><span>'.$_GET['error'].'</span></p><br />' : '';
	if ($_REQUEST['itemid']){
	?>
	<div id="passbox">
        <a href="#" data-type="passwordreset" data-name="ajax" data-value="<?= $user->userid ?>" class="button-standard-ajax" data-selector="#passbox">Reset this user's password &amp; email it</a>
    </div>
	<br /><span><a href="<?= $_SERVER['PHP_SELF']?>?do=delete&amp;itemid=<?= $user->userid ?>" class="button-confirm" data-message="Are you sure you want to delete this user?">Delete User</a></span><br /><br />
<? } ?>
	<form action="/manage-user" method="post" enctype="multipart/form-data">
		<table class="updateinput">
			<tr>
				<td class="leftcell">User name</td>
				<td><input type="text" name="username" class="updateinput" value="<?= $user->username ?>" /></td>
			</tr>
			<tr>
				<td>Type of User</td>
				<td>
					<select name="usergroupid">
						<option value="2">Registered User</option>
						<option value="8"<?= in_array($user->usergroupid,explode(',',BANNEDGROUPS)) ? ' selected="selected"':'' ?>>Banned User</option>
						<option value="6"<?= in_array($user->usergroupid,explode(',',ADMINGROUPS)) ? ' selected="selected"':'' ?>>Admin</option>
						<option value="5"<?= in_array($user->usergroupid,explode(',',MODGROUPS)) ? ' selected="selected"':'' ?>>Mod</option>
						<option value="10"<?= $user->usergroupid == 10 ? ' selected="selected"':'' ?>>God</option>
						<option value="12"<?= $user->usergroupid == 12 ? ' selected="selected"':'' ?>>Uber God</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Email</td>
				<td><input type="text" name="email" class="updateinput" value="<?= $user->email ?>" /></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><input type="text" name="password" class="updateinput" value="" /></td>
			</tr>
			<tr>
				<td>Location</td>
				<td><input type="text" name="location" class="updateinput" value="<?= $user->location ?>" /></td>
			</tr>
			<tr>
				<td>User Title</td>
				<td><input type="text" name="usertitle" class="updateinput" value="<?= $user->usertitle ?>" /></td>
			</tr>
			<tr>
				<td>Avatar</td>
				<td>
					<input type="file" name="avatar" /><br />
					<? if ($user->avatar) {?>
						<img src="images/avatar/<?= $user->avatar ?>" alt="<?= $user->username ?>'s avatar" />
					<?} else {?>
						No avatar set
					<? } ?>
				</td>
			</tr>
			<? if (!($user->userid > 0)) {?>
			<tr>
				<td>Automatically email user with account details?:</td>
				<td><input type="checkbox" name="autoemail" value="1" /></td>
			</tr>
			<? } ?>
			<tr>
				<td class="leftcell">&nbsp;</td>
				<td>
					<input type="hidden" name="do" value="<?= $_REQUEST['itemid'] > 0 ? 'update': 'insert' ?>" />
					<input type="hidden" name="itemid" value="<?= $user->userid ?>" />
					<input type="submit" value="<?= $_REQUEST['itemid'] > 0 ? 'Update': 'Insert' ?>" />
				</td>
			</tr>
		</table>	
		</form>
		<br />
	<? if ($_REQUEST['itemid']) {?><p>&nbsp;</p><p><a href="<?= $_SERVER['PHP_SELF'] ?>">Return to 'Create User'</a><? } ?>
	<p>&nbsp;</p>
	<hr />
	<p>Users by first letter of username</p>
	<p>
	<?
	for ($character = 65; $character < 91; $character++) {
        echo '<a href="#" class="button-standard-ajax" style="font-weight:bold" data-name="ajax" data-type="alphanames" data-value="',chr($character),'" data-selector="#alphanames">[',chr($character),']</a> ';
    }

	?>
	</p>
	<br /><div id="alphanames"><!-- --></div><?
	$result = mysql_query('
		SELECT DISTINCT userid
		FROM user
		WHERE usergroupid NOT IN ('.BANNEDGROUPS.')
		AND usergroupid > 0
		AND email = \'\' LIMIT 1
		') or die(mysql_error());
	if (mysql_num_rows($result) > 0) {
        echo '<br /><span id="woemail"><a href="#" class="button-standard-ajax" style="font-weight:bold" data-name="ajax" data-type="woemail" data-selector="#alphanames">Users without emails</a></span>';
    }
	
	$result = mysql_query('
		SELECT DISTINCT userid
		FROM user
		WHERE usergroupid IN ('.BANNEDGROUPS.')
		OR usergroupid = 0
		OR usergroupid IS NULL
		') or die(mysql_error());
	if (mysql_num_rows($result) > 0) {
        echo '<br /><span id="inactive"><a href="#"  class="button-standard-ajax" style="font-weight:bold" data-name="ajax" data-type="inactive" data-selector="#alphanames">Inactive users</a></span>';
    }
	$result = mysql_query('
		SELECT 
			CONCAT(username) AS `getname`, 
			COUNT(username) AS `cnt`
		FROM user
		WHERE usergroupid NOT IN ('.BANNEDGROUPS.')
		AND usergroupid > 0
		GROUP BY `getname`
		HAVING `cnt` > 1
		ORDER BY `cnt` LIMIT 1
		') or die(mysql_error());
	if (mysql_num_rows($result) > 0) {
        echo '<br /><span id="duplicate"><a href="#" class="button-standard-ajax" style="font-weight:bold" data-name="ajax" data-type="duplicate" data-selector="#alphanames">Duplicate users (by username)</a></span>';
    }

	//probably want to make similar users too
?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<?
}

require PATH.'/includes/footer.php'; 

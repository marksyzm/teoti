<?
$member = $usesbbcode = true;
include 'includes/dbconnect.php';

$check = array(); //get modification checks
if ($_GET['check']) {
	$tmp = unserialize($_GET['check']);
	if ($tmp) $check = array_merge($check,$tmp);
	unset($tmp);
}



//legacy fixing
$result = mysql_query('
	SELECT username,userid FROM user WHERE usernameurl = \'\' OR usernameurl IS NULL
	') or die(__LINE__.__FILE__.mysql_error());
while ($u = mysql_fetch_object($result)) {
	mysql_query('
		UPDATE user SET usernameurl = \''.mysql_real_escape_string(urlify($u->username)).'\' WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
		') or die(__LINE__.__FILE__.mysql_error());
}


/*if ($_GET['u']) {
	$result = mysql_query('
		SELECT userid,username FROM user
		') or die(__LINE__.__FILE__.mysql_error());
	while ($u = mysql_fetch_object($result)) {
		if (urlify($u->username) == $_GET['u']) {
			$userid = $u->userid;
			break; //to reduce load
		}
	}
}*/

$u = mysql_single('SELECT * FROM user WHERE usernameurl = \''.mysql_real_escape_string($_GET['u']).'\'',__LINE__.__FILE__);

include PATH.'/includes/member-functions.php';
//if id is given then do the checks and get the values n shit
if (in_array($_REQUEST['do'],array('insert','update','delete','ajax')))
	include PATH.'/includes/member-queries.php';

if ($u->userid > 0) {
	$pagetitle = htmlspecialchars_decode($u->username);
	$session->styleid = $u->styleid; //show member's own style
	$t = mysql_single('SELECT title,forumid FROM thread WHERE threadid = \''.mysql_real_escape_string($u->viewing).'\'',__LINE__.__FILE__);
}


if ($session->userid && ($session->staff || $session->userid == $u->userid)) {
	$EXTRASCRIPT = '
		<script type="text/javascript" src="lib/jquery.markitup.js"></script>
		<script type="text/javascript" src="lib/sets/bbcode/set.js"></script>
		<link rel="stylesheet" type="text/css" href="lib/skins/markitup/style.css" />
		<link rel="stylesheet" type="text/css" href="lib/sets/bbcode/style.css" />
		<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="ckeditor/adapters/jquery.js"></script>
';
}

include PATH.'/includes/header.php';

if ($_GET['do'] == 'edit' && ($session->userid == $u->userid || $session->staff)) {
?>
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">

				<form action="<?= STRIP_REQUEST_URI ?>" method="post" id="submit-post" class="text-editor">
					<h2 class="skin-this {'selector':'#whole #main h2'}">Edit Userpage</h2>
					<? 
					$up = mysql_single('SELECT pagedata FROM userpage WHERE userid = \''.mysql_real_escape_string($u->userid).'\'',__LINE__.__FILE__);
					if (count($_POST)) $up = (object)$_POST;
					if ($check) { ?><p class="error"><?= implode('<br />',$check) ?></p><? } ?>
					
					<textarea class="userpage updateinput" name="pagedata"><?= $up->pagedata ?></textarea>
					<div class="threequarter teoti-button">
                        <a href="ajax/bbcode-help.php" class="bbcode-help">BBCode Help</a>
                        <a href="ajax/smilie-help.php" class="smilie-help">Smilie Help</a>
                        <a href="#toggle-wysiwyg" class="toggle-wysiwyg" title="A WYSIWYG is a word style editor which stands for 'What you see is what you get'. BBCode is our standard tags based code - see our help file">Use <?= $session->settings['wysiwyg'] ? 'BBCode':'WYSIWYG' ?></a>
					</div>
					<div class="alignright quarter last-left">
						<input type="hidden" name="do" value="update" />
						<input type="hidden" name="userid" value="<?= $u->userid ?>" />
						<input type="hidden" name="type" value="userpage" />
						<input type="submit" value="submit" />
					</div>
					<?= CLEARBOTH ?>
					<div id="help-box"><!-- --></div>
				</form>
				
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<?	
} elseif ($u->userid > 0) { 
	$bbcode = bbcode();
	?>

<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<div>
					<div id="thread-user">
						<? if ($u->avatar) {?>
						<div class="node-image">
							<a href="<?= URLPATH ?>/members/<?= urlify($u->username) ?>.html"><img src="<?= URLPATH ?>/images/avatar/<?= $u->avatar ?>" alt="<?= $t->postusername ?>" /></a>
						</div>
						<? } ?>
						<span class="light skin-this {'selector':'#whole .light'}">
							<?= $u->posts ? $u->posts : '0' ?> posts<br />
							<?= $u->threads ? $u->threads : '0' ?> threads<br />
							<?= $u->post_thanks_thanked_times ? $u->post_thanks_thanked_times : '0' ?> points<br />
							<?= $u->user_total_score ? $u->user_total_score : '0' ?> total points (all time)<br />
							<?= $u->location ? $u->location : '' ?><br />
							Last seen <?= ago($u->lastactivity) ?><br />
							Joined <?= ago($u->joindate) ?>
						</span>
						<?= CLEARBOTH ?>
					</div>
					<div id="thread-title">
						<h2 class="skin-this {'selector':'#whole #main h2'}"><?= userlink($u->username,$u->userid) ?></h2>
						<p>
							<? if ($session->userid != $u->userid) { ?><a href="<?= URLPATH ?>/conversation?do=new&amp;include=<?= $u->userid ?>">Have a conversation with <?= $u->username ?></a><br /><? } ?>
							<a href="<?= URLPATH ?>/?which=posts&amp;userid=<?= $u->userid ?>">View posts by <?= $u->username ?></a><br />
							<a href="<?= URLPATH ?>/?which=points&amp;userid=<?= $u->userid ?>">View liked/disliked posts by <?= $u->username ?></a><br />
							<a href="<?= URLPATH ?>/?which=threads&amp;userid=<?= $u->userid ?>">View threads by <?= $u->username ?></a><br />
							<? if (okcats($t->forumid)) {?>
							<a href="<?= URLPATH ?>/<?= urlify(forumtitle($t->forumid)) ?>/<?= urlify($t->title,$u->viewing) ?>.html"><?= $u->username ?> last viewed <?= $t->title ?></a><br />
							<? } ?>
							
						</p>
					</div>
					<?= CLEARBOTH ?>
				</div>
				
				<div id="userpage">
					<? if ($session->userid == $u->userid || $session->staff) { ?><div class="teoti-button alignright"><a href="<?= STRIP_REQUEST_URI ?>?do=edit">Edit</a></div><? } ?>
					<!-- user page -->
					<?
						$up = mysql_single('SELECT pagedata FROM userpage WHERE userid = \''.mysql_real_escape_string($u->userid).'\'',__LINE__.__FILE__);
						if (trim($up->pagedata)) {?>
							<h2 class="light skin-this {'selector':'#whole #main h2'}">About Me</h2>
							<?
							echo $bbcode->parse($up->pagedata); 
						}
					?>
				</div>
				<h2 class="light skin-this {'selector':'#whole #main h2'}">Blog Posts</h2>
				<!-- this is the comments feed -->
				<? $noofpages = generateBlogsNode($u->userid,$bbcode) ?>
				
				<? 
				$result = mysql_query('
					SELECT * FROM skins WHERE user = \''.mysql_real_escape_string($u->userid).'\' ORDER BY name
					') or die(__LINE__.__FILE__.mysql_error());
				if (mysql_num_rows($result)) {
					$i = 0;
					?>
				<h2 class="light skin-this {'selector':'#whole #main h2'}">User Styles</h2>
				<div id="userstyles" class="teoti-button">
				<? while ($userskin = mysql_fetch_object($result)) {
					?><a href="<?= URLPATH ?>/?changestyle=<?= $userskin->id ?>"><?
					echo $userskin->name ? $userskin->name :'Style '.$userskin->id;
					?></a><? 
				} ?>
				</div>
				<? } ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<!-- comments box uses the shoutbox id and posts use the node class -->



<div id="constants" class="{'bloglimit':'<?= BLOGFEEDLIMIT ?>','curpagetotal':'<?= $noofpages ?>','curpage':'<?= $_GET['page'] ?>'}"><!-- constants for post node feed --></div>

<? } else { ?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<h2 class="skin-this {'selector':'#whole #main h2'}">Sorry!</h2>
				<p>The member page you are trying to access doesn't seem to exist (or you don't have access to it)!</p>
				<!-- now knock out the content -->
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<?
}
include PATH.'/includes/footer.php';
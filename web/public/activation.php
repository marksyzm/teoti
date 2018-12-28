<?
$isactivation = true;
require_once 'includes/dbconnect.php';

if (!$_SESSION['answer']) $_SESSION['answer'] = '';

if ($session->userid) {
	if ($_POST['do'] == 'Activate') {
		if (trim($_POST['activate']) == $session->activate) {
			mysql_query('UPDATE user SET activate = \'\' WHERE userid = \''.mysql_real_escape_string($session->userid).'\'') or die(__LINE__.__FILE__.mysql_error());
		} else $error = 'Activation unsuccessful. Please use the code we supplied via email.';
		header('Location: '.URLPATH.'/activation?'.($error ? 'error='.urlencode($error) : 'success=1')) AND exit();
	}
}

include PATH.'/includes/header.php'; ?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<div class="aligncenter">
						<div class="threequarter margincenter floatnone alignleft">
				<? if (!$session->userid) {?>
					<h2 class="skin-this {'selector':'#whole #main h2'}">You're not logged in :/</h2>
					<p>You can't activate your account if you're not logged in! </p>
				<? } elseif (!$session->activate) { ?>
					<h2 class="skin-this {'selector':'#whole #main h2'}">Activation Successful</h2>
					<p>You are now fully registered with <?= COMPANYNAME ?>. Feel free to change your site style, set your avatar or carelessly insult one of our members at random. It's totally worth it.</p>
				<? } else { ?>
					<? if ($_GET['error']) {?><br /><h3 class="error aligncenter skin-this {'selector':'#whole #main h3'}"><?= $_GET['error'] ?></h3><? } ?>
					<form action="<?= STRIP_REQUEST_URI ?>" method="post" enctype="multipart/form-data">
						<h2 class="aligncenter skin-this {'selector':'#whole #main h2'}">Activate your <?= COMPANYNAME ?> account</h2>
						<p class="lighter aligncenter skin-this {'selector':'#whole .lighter'}">Please enter the activation code that we supplied to you via your email address (<?= $session->email ?>) into the box below and then submit the form by clicking 'activate'.</p>
						<div class="quarter alignright">
							<p class="marginright">Activation code:</p>
						</div>
						<div class="threequarter lastleft">
							<p><input type="text" name="activate" class="updateinput" value="<?= $_POST['code'] ?>" /></p>
						</div>
						<?= CLEARBOTH ?>
						<p class="aligncenter"><input type="submit" value="Activate" name="do" class="largeinput" /></p>
						<?= CLEARBOTH ?>
					</form>
				<? } ?>
					</div>
					<?= CLEARBOTH ?>
				</div>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<?
include PATH.'/includes/footer.php';

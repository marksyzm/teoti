<?
require_once 'includes/dbconnect.php';

include PATH.'/includes/header.php'; ?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<div class="aligncenter">
					<div class="threequarter margincenter floatnone alignleft">
					<? /*  || ($session->userid && !$session->fbid) || ($session->userid && $session->fbid && $session->facebook) */?>
					<? if ( ($fb['id'] && !$session->fbid && !$session->userid) ) {?>
						<form action="<?= STRIP_REQUEST_URI ?>" method="post" enctype="multipart/form-data">
							<h2 class="aligncenter skin-this {'selector':'#whole #main h2'}">
								<?= $session->username ? $session->username : $fb['username'] ?>: 
								<span class="light">Merge your <?= COMPANYNAME ?> account with your Facebook Account</span>
							</h2>
							<? if ($fb['id'] && !$session->userid) { ?>
							<!-- a user that has just been matched as a potential merger -->
							<p class="aligncenter skin-this {'selector':'#whole .lighter'}">
								Your username: <strong><?= $fb['username'] ?></strong> has been detected as matching a member in our system. 
							</p>
							<p class="aligncenter skin-this {'selector':'#whole .lighter'}">
								If this teoti user account is your own and you want to log into this account via Facebook Connect or access <?= COMPANYNAME 
								?>'s Facebook facilities by merging these accounts then please enter the password for that account below.</p>
							<div class="quarter alignright">
								<p class="marginright">Password:</p>
							</div>
							<div class="threequarter lastleft">
								<p><input type="password" name="merge" class="updateinput" value="<?= $_POST['password'] ?>" /></p>
								<input type="hidden" name="facebook" value="1" />
							</div>
							<?= CLEARBOTH ?>
							<p class="aligncenter">
								<input type="button" value="Ignore This" class="button-confirm" data-message="Are you sure you want to ignore this procedure? You can change this later." data-url="<?php echo STRIP_REQUEST_URI ?>?facebook=1&amp;ignore=1" />
								<input type="submit" value="Merge Accounts" name="do" class="largeinput" />
							</p>
							<?= CLEARBOTH ?>
							<? } ?>
						</form>
					<? } else { ?>
						<h2>Disconnect <?= COMPANYNAME ?> Account from Facebook</h2>
						<p>You can remove your Facebook account from your Teoti account</p>
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

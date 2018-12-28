	<h3 class="aligncenter skin-this {'selector':'#whole #main h3'}"><a href="<?= URLPATH ?>/register">Create Account</a></h3>
	<form action="<?= STRIP_REQUEST_URI ?>" method="post">
		<div class="half">
			<small class="light skin-this {'selector':'#whole .light'}">Username</small>
			<input type="text" name="username" class="updateinput" />
		</div>
		<div class="half last-left">
			<small class="light skin-this {'selector':'#whole .light'}">Password</small>
			<input type="password" name="password" id="password" class="updateinput" />
		</div>
		<?= CLEARBOTH ?>
		<div class="half">
			<small class="light skin-this {'selector':'#whole .light'}">Remember me</small>
			<input type="checkbox" name="rememberme" />
		</div>
		<div class="half last-left">
			<input type="submit" name="login" value="login" class="fullwidth"  />
		</div>
		<?= CLEARBOTH ?>
	</form>
	<div id="fb-root"><!-- --></div>

  <div class="half aligncenter">
  	<a href="#forgot-password" class="button-forgot-password">Forgot TEOTI password?</a>
  </div>
  <div class="half last-left aligncenter">
  	<button id="facebook-connect" class="facebook-connect"><img src="images/facebook-connect-button.gif" alt="Facebook Connect" /></button>
	</div>
	<?= CLEARBOTH ?>
	<form action="<?= STRIP_REQUEST_URI ?>" method="post" class="hidethis form-forgot-password">
		<div class="half">
			<small class="light skin-this {'selector':'#whole .light'}">Email</small>
			<input type="text" name="forgot" class="updateinput" />
		</div>
		<div class="half last-left">
			<small>&nbsp;</small><br />
			<input type="submit" value="reset password" class="fullwidth"  />
		</div>
		<?= CLEARBOTH ?>
	</form>
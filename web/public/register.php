<?
require 'includes/dbconnect.php';
require 'includes/recaptchalib.php';

$publickey = $_SERVER['HTTP_HOST'] == 'www.t-six.com' ? '6LdCj7sSAAAAAL3ECs5xLRDzR7GCNoNerl1-pO3J' : '6LcAkLsSAAAAADPpz1r9qunb8DX4NT033U0gbEc8';

if (!$_SESSION['answer']) $_SESSION['answer'] = '';

if (!$session->userid && $_POST) {
	include PATH.'/includes/register-queries.php';
}
if ($session->userid || $_POST['mobile']) {
	if ($_POST['mobile']){
	} else {
		header('Location: '.URLPATH.'/');
		exit();
	}
} else {
include PATH.'/includes/header.php'; ?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<div class="aligncenter">
					<div class="threequarter margincenter floatnone alignleft">
						<? if ($_GET['success']) { ?>
						<h2 class="skin-this {'selector':'#whole #main h2'}">Registration Successful</h2>
						<p>Thank you for registering with <?= COMPANYNAME ?>. An email with your credentials has been sent to you and you may now log in as a fully fledged member of <?= COMPANYNAME ?>.</p>
						<?/*, along with an activation code.</p>
						<p>To activate your account, simply log in with your username and password and you will see the activation page. Copy the activation code where it says 'enter your code here'.</p>
						<p>Then submit the form and you will become a fully fledged member of <?= COMPANYNAME ?>. All of us here welcome you on board! </p>*/?>
						<? } else { ?>
						<form action="<?= STRIP_REQUEST_URI ?>" method="post" enctype="multipart/form-data">
							<h2 class="aligncenter">Register with <?php echo COMPANYNAME ?></h2>
							<? if ($errors) {?><p class="error"><?php echo implode('<br />',$errors) ?></p><? } ?>
							<p class="lighter aligncenter skin-this {'selector':'#whole .lighter'}">Please enter a username. This must not contain spaces or special characters.</p>
							<div class="quarter alignright">
								<p class="marginright">Username:</p>
							</div>
							<div class="threequarter lastleft">
								<p><input type="text" name="username" class="updateinput largeinput" value="<?= $_POST['username'] ?>" /></p>
							</div>
							<?= CLEARBOTH ?>
							<div class="quarter alignright">
								<p class="marginright">Email address:</p>
							</div>
							<div class="threequarter lastleft">
								<p><input type="text" name="email" class="updateinput largeinput" value="<?= $_POST['email'] ?>" /></p>
							</div>
							<?= CLEARBOTH ?>
							<div class="quarter alignright">
								<p class="marginright">Confirm email address:</p>
							</div>
							<div class="threequarter lastleft">
								<p><input type="text" name="email2" class="updateinput largeinput" value="<?= $_POST['email2'] ?>" /></p>
							</div>
							<?= CLEARBOTH ?>
							<p class="lighter aligncenter skin-this {'selector':'#whole .lighter'}">Your password must be more than 5 characters long.</p>
							<div class="quarter alignright">
								<p class="marginright">Password:</p>
							</div>
							<div class="threequarter lastleft">
								<p><input type="password" name="password" class="updateinput largeinput" /></p>
							</div>
							<?= CLEARBOTH ?>
							<div class="quarter alignright">
								<p class="marginright">Confirm password:</p>
							</div>
							<div class="threequarter lastleft">
								<p><input type="password" name="password2" class="updateinput largeinput" /></p>
							</div>
							<?= CLEARBOTH ?>
							<h3 class="aligncenter skin-this {'selector':'#whole #main h3'}">Personal Details</h3>
							<div class="quarter alignright">
								<p class="marginright">Date of Birth:</p>
							</div>
							<div class="threequarter lastleft">
								<? jsCalendar(date($_POST['date'] ? $_POST['date'] : MYSQLDATE, time()-(60*60*24*365*28)),'date',100) ?>
							</div>
							<?= CLEARBOTH ?>
							<div class="quarter alignright">
								<p class="marginright">What is the capital of England?</p>
							</div>
							<div class="threequarter lastleft">
									<p><input type="text" name="capital" value="<?= $_POST['capital'] ?>" class="updateinput largeinput" /></p>
							</div>
							<?= CLEARBOTH ?>
							<div class="quarter alignright">
								<p class="marginright">Location:</p>
							</div>
							<div class="threequarter lastleft">
									<p><input type="text" name="location" value="<?= $_POST['location'] ?>" class="updateinput largeinput" /></p>
							</div>
							<?= CLEARBOTH ?>
							<div class="quarter alignright">
								<p class="marginright">Time Zone:</p>
							</div>
							<div class="threequarter lastleft">
									<p>
										<? if (!is_numeric($_POST['timezoneoffset']) || !$_POST['timezoneoffset']) $_POST['timezoneoffset'] = '0' ?>
										<select name="timezoneoffset">
											<option value="-12"<?= $_POST['timezoneoffset'] == '-12' ? ' selected="selected"':''?>>(GMT -12:00) Eniwetok, Kwajalein</option>
											<option value="-11"<?= $_POST['timezoneoffset'] == '-11' ? ' selected="selected"':''?>>(GMT -11:00) Midway Island, Samoa</option>
											<option value="-10"<?= $_POST['timezoneoffset'] == '-10' ? ' selected="selected"':''?>>(GMT -10:00) Hawaii</option>
											<option value="-9"<?=  $_POST['timezoneoffset'] == '-9' ? ' selected="selected"':''?>>(GMT -9:00) Alaska</option>
											<option value="-8"<?=  $_POST['timezoneoffset'] == '-8' ? ' selected="selected"':''?>>(GMT -8:00) Pacific Time (US &amp; Canada)</option>
											<option value="-7"<?=  $_POST['timezoneoffset'] == '-7' ? ' selected="selected"':''?>>(GMT -7:00) Mountain Time (US &amp; Canada)</option>
											<option value="-6"<?=  $_POST['timezoneoffset'] == '-6' ? ' selected="selected"':''?>>(GMT -6:00) Central Time (US &amp; Canada), Mexico City</option>
											<option value="-5"<?=  $_POST['timezoneoffset'] == '-5' ? ' selected="selected"':''?>>(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>
											<option value="-4"<?=  $_POST['timezoneoffset'] == '-4' ? ' selected="selected"':''?>>(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
											<option value="-3.5"<?=$_POST['timezoneoffset'] == '-3.5' ? ' selected="selected"':''?>>(GMT -3:30) Newfoundland</option>
											<option value="-3"<?=  $_POST['timezoneoffset'] == '-3' ? ' selected="selected"':''?>>(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
											<option value="-2"<?=  $_POST['timezoneoffset'] == '-2' ? ' selected="selected"':''?>>(GMT -2:00) Mid-Atlantic</option>
											<option value="-1"<?=  $_POST['timezoneoffset'] == '-1' ? ' selected="selected"':''?>>(GMT -1:00 hour) Azores, Cape Verde Islands</option>
											<option value="0"<?=   $_POST['timezoneoffset'] == '0' ? ' selected="selected"':''?>>(GMT) Western Europe Time, London, Lisbon, Casa.</option>
											<option value="1"<?=   $_POST['timezoneoffset'] == '1' ? ' selected="selected"':''?>>(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris</option>
											<option value="2"<?=   $_POST['timezoneoffset'] == '2' ? ' selected="selected"':''?>>(GMT +2:00) Kaliningrad, South Africa</option>
											<option value="3"<?=   $_POST['timezoneoffset'] == '3' ? ' selected="selected"':''?>>(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
											<option value="3.5"<?= $_POST['timezoneoffset'] == '3.5' ? ' selected="selected"':''?>>(GMT +3:30) Tehran</option>
											<option value="4"<?=   $_POST['timezoneoffset'] == '4' ? ' selected="selected"':''?>>(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
											<option value="4.5"<?= $_POST['timezoneoffset'] == '4.5' ? ' selected="selected"':''?>>(GMT +4:30) Kabul</option>
											<option value="5"<?=   $_POST['timezoneoffset'] == '5' ? ' selected="selected"':''?>>(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
											<option value="5.5"<?= $_POST['timezoneoffset'] == '5.5' ? ' selected="selected"':''?>>(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
											<option value="6"<?=   $_POST['timezoneoffset'] == '6' ? ' selected="selected"':''?>>(GMT +6:00) Almaty, Dhaka, Colombo</option>
											<option value="7"<?=   $_POST['timezoneoffset'] == '7' ? ' selected="selected"':''?>>(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
											<option value="8"<?=   $_POST['timezoneoffset'] == '8' ? ' selected="selected"':''?>>(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
											<option value="9"<?=   $_POST['timezoneoffset'] == '9' ? ' selected="selected"':''?>>(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
											<option value="9.5"<?= $_POST['timezoneoffset'] == '9.5' ? ' selected="selected"':''?>>(GMT +9:30) Adelaide, Darwin</option>
											<option value="10"<?=  $_POST['timezoneoffset'] == '10' ? ' selected="selected"':''?>>(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
											<option value="11"<?=  $_POST['timezoneoffset'] == '11' ? ' selected="selected"':''?>>(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
											<option value="12"<?=  $_POST['timezoneoffset'] == '12' ? ' selected="selected"':''?>>(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
										</select>
									</p>
							</div>
							<?= CLEARBOTH ?>
							<div class="threequarter floatright">
									<?= recaptcha_get_html($publickey, $cerror) ?>
							</div>
							<?= CLEARBOTH ?>
							<!-- don't forget to put timezone selection and captcha form here -->
							<p class="aligncenter"><input type="submit" value="Register" name="do" class="largeinput" /></p>
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
}
<?
require 'includes/dbconnect.php';

if ($session->userid) {
	if (!$session->fbid && !$session->facebook && $_GET['merge'])  {
		//get fb session
		include_once PATH.'/classes/facebook.php';
		include PATH.'/includes/fb-session.php';
		if ($fb['id'])
			mysql_query('
				UPDATE user SET 
				fbid = \''.mysql_real_escape_string($fb['id']).'\'
				WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			$u = mysql_single('
				SELECT userid,user_total_score,post_thanks_thanked_times,pmtotal,pmunread,posts,threads FROM user 
				WHERE fbid = \''.mysql_real_escape_string($fb['id']).'\' AND facebook = 1
				',__LINE__.__FILE__);
			if ($u->userid) {
				mysql_query('
					UPDATE user SET 
					user_total_score = user_total_score + \''.mysql_real_escape_string($u->user_total_score).'\'
					,post_thanks_thanked_times =  post_thanks_thanked_times + \''.mysql_real_escape_string($u->post_thanks_thanked_times).'\'
					,pmtotal =  pmtotal + \''.mysql_real_escape_string($u->pmtotal).'\'
					,pmunread =  pmunread + \''.mysql_real_escape_string($u->pmunread).'\'
					,posts =  posts + \''.mysql_real_escape_string($u->posts).'\'
					,threads =  threads + \''.mysql_real_escape_string($u->threads).'\'
					WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE thread SET 
					postuserid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE postuserid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE post SET 
					userid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE pmnode SET 
					userid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE pmtext SET 
					fromuserid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE fromuserid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE notification SET 
					userid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE usernote SET 
					userid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE skins SET 
					user = \''.mysql_real_escape_string($session->userid).'\'
					WHERE user = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE post_thanks SET 
					userid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE history SET 
					userid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE sitelog SET 
					getterid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE getterid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE sitelog SET 
					giverid = \''.mysql_real_escape_string($session->userid).'\'
					WHERE giverid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					UPDATE shout SET 
					s_by = \''.mysql_real_escape_string($session->userid).'\'
					WHERE s_by = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				mysql_query('
					DELETE FROM user WHERE userid = \''.mysql_real_escape_string($u->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				
			}
			header('Location: '.STRIP_REQUEST_URI);
			exit();
	}
	
	if ($_POST['do'] == 'Update') {
		if (count($_FILES)) require_once PATH.'/images/phpthumb.class.php';
		//if (count($_FILES)) require_once PATH.'/classes/thumbnail.inc.php';
		$message = $password = $error = '';
		
		if ($_POST['oldpassword'] && $_POST['newpassword']) {
			$user = mysql_single('
				SELECT password FROM user WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				',__LINE__.__FILE__);
			if (md5(md5($_POST['oldpassword']).$session->salt) == $user->password) {
				if ($_POST['newpassword'] == $_POST['newpassword2']) {
					if (strlen($_POST['newpassword']) > 5) {
						$salt = mt_rand(0,999);//new salt
						$salt = str_repeat(3-strlen($salt),'0').$salt; //add missing 0's
						//new password
						$password = md5(md5($_POST['newpassword']).$salt);
						$_SESSION['uinfo'] = $password; //reset session password
					} else $error .= 'Sorry, your password was\'nt altered. Your password must be longer than 5 characters.<br />';
				} else $error .= 'Sorry, your password was\'nt altered. Your new password does not match the confirmation password.<br />';
			} else $error .= 'Sorry, your password was\'nt altered. The details you gave for your old password does not match the one stored.<br />';
		}
		
		$newemail = false;
		if ($_POST['newemail']) {
			if ($_POST['newemail'] == $_POST['newemail2']) {
				if (strstr($_POST['newemail'],'@')) {
					$newemail = true;
				} else $error .= 'Sorry, your email address was\'nt valid. <br />';
			} else $error .= 'Sorry, your email was\'nt altered. Your new email address does not match the confirmation email address.<br />';
		}
		
		if ($_FILES['avatar']['name']) $image = send_image_to_file('avatar');
		if (is_string($image)) $error .= $image.'<br />';
		if (!$error) {
			mysql_query('
				UPDATE user SET 
				location = \''.mysql_real_escape_string($_POST['location']).'\'
				,usertitle = \''.mysql_real_escape_string($_POST['usertitle']).'\'
				'.($newemail ? ',email = \''.mysql_real_escape_string($_POST['newemail']).'\'':'').'
				,showbirthday = \''.mysql_real_escape_string($_POST['showbirthday']).'\'
				,birthday_search = \''.mysql_real_escape_string($_POST['date']).'\'
				,usertitle = \''.mysql_real_escape_string($_POST['usertitle']).'\'
				,birthday = \''.mysql_real_escape_string(implode('-',array_reverse(explode('-',$_POST['date'])))).'\'
				'.(is_array($image) && count($image) ? ','.implode(',',$image):'').'
				'.($password ? '
				,password = \''.mysql_real_escape_string($password).'\'
				,salt = \''.mysql_real_escape_string($salt).'\'
				,passworddate = CURDATE()' : '').'
				,timezoneoffset = \''.mysql_real_escape_string(is_numeric($_POST['timezoneoffset']) ? $_POST['timezoneoffset'] :'0').'\'
				WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			if (is_array($_POST['notification'])){	
				foreach ($_POST['notification'] as $n) {
					if (!$row = mysql_single('
						SELECT notetypeid FROM usernotetype 
						WHERE userid = \''.mysql_real_escape_string($session->userid).'\' AND notetypeid = \''.mysql_real_escape_string((int)$n).'\'
						',__LINE__.__FILE__)) {
						mysql_query('
							INSERT INTO usernotetype SET 
							userid = \''.mysql_real_escape_string($session->userid).'\'
							,notetypeid = \''.mysql_real_escape_string($n).'\'
							') or die(__LINE__.__FILE__.mysql_error());
					}
				}
			}
			//then delete those that aren't there
			$n = mysql_single('SELECT GROUP_CONCAT(notetypeid SEPARATOR \',\') as notetypes FROM notetype',__LINE__.__FILE__);
			$notetypes = explode(',',$n->notetypes);
			if ($_POST['notification']) $notetypes = array_diff($notetypes,$_POST['notification']);
			
			if ($notetypes) {
				mysql_query('
					DELETE FROM usernotetype
					WHERE userid = \''.mysql_real_escape_string($session->userid).'\' 
					AND notetypeid IN ('.mysql_real_escape_string(implode(',',$notetypes)).')
					') or die(__LINE__.__FILE__.mysql_error());
			}
		}
		
            header('Location: '.URLPATH.'/account?'.($error ? 'error='.urlencode($error) : 'success=1')) AND exit();
	}
}

include PATH.'/includes/header.php'; ?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<? if ($session->userid) {?>
					<h2 class="skin-this {'selector':'#whole #main h2'}">Your Account</h2>
					<? if ($_GET['error']) {?><h3 class="error skin-this {'selector':'#whole #main h3'}"><?= $_GET['error'] ?></h3><? } ?>
					<? if ($_GET['success']) {?><h3 class="message skin-this {'selector':'#whole #main h3'}">Your details were saved.</h3><? } ?>
					<div class="aligncenter">
						<div class="threequarter margincenter floatnone alignleft">
							<? if (!$session->fbid && !$session->facebook) { ?>
							<div id="fb-root"><!-- --></div>
						  <p class="aligncenter">Log on to Facebook to connect this account with your facebook account<br />to be able to log on with your <?= COMPANYNAME ?> or Facebook password.</p>
						  <p class="aligncenter">
						  	<button class="facebook-connect"><img src="images/facebook-connect-button.gif" alt="Facebook Connect" /></button>
						  </p>
						  <?= CLEARBOTH ?>
							<? } ?> 
							<form action="<?= STRIP_REQUEST_URI ?>" method="post" enctype="multipart/form-data">
								<? if (!$session->facebook) {?>
								<h3 class="aligncenter skin-this {'selector':'#whole #main h3'}">Change Password</h3>
								<p class="lighter aligncenter skin-this {'selector':'#whole .lighter'}">Leave blank to keep current password</p>
								<div class="quarter alignright">
									<p class="marginright">Old password:</p>
								</div>
								<div class="threequarter lastleft">
									<p><input type="password" name="oldpassword" class="updateinput" /></p>
								</div>
								<?= CLEARBOTH ?>
								<div class="quarter alignright">
									<p class="marginright">New password:</p>
								</div>
								<div class="threequarter lastleft">
									<p><input type="password" name="newpassword" class="updateinput" /></p>
								</div>
								<?= CLEARBOTH ?>
								<div class="quarter alignright">
									<p class="marginright">Confirm password:</p>
								</div>
								<div class="threequarter lastleft">
									<p><input type="password" name="newpassword2" class="updateinput" /></p>
								</div>
								<?= CLEARBOTH ?>
								<? } ?>
								<div class="quarter alignright">
									<p class="marginright">New email address:</p>
								</div>
								<div class="threequarter lastleft">
									<p><input type="text" name="newemail" class="updateinput" value="" /></p>
								</div>
								<?= CLEARBOTH ?>
								<div class="quarter alignright">
									<p class="marginright">Confirm email address:</p>
								</div>
								<div class="threequarter lastleft">
									<p><input type="text" name="newemail2" class="updateinput" value="" /></p>
								</div>
								<?= CLEARBOTH ?>
								<h3 class="aligncenter  skin-this {'selector':'#whole #main h3'}">Personal Details</h3>
								<div class="quarter alignright">
									<p class="marginright">Date of Birth:</p>
								</div>
								<div class="threequarter lastleft">
									<? jsCalendar($session->birthday_search,'date',100) ?>
								</div>
								<?= CLEARBOTH ?>
								<div class="quarter alignright">
									<p class="marginright">Show Birthday:</p>
								</div>
								<div class="threequarter lastleft">
										<p><input type="checkbox" name="showbirthday" value="1" <?= $session->showbirthday ? 'checked="checked"':'' ?> class="updateinput" /></p>
								</div>
								<?= CLEARBOTH ?>
								<div class="quarter alignright">
									<p class="marginright">Location:</p>
								</div>
								<div class="threequarter lastleft">
										<p><input type="text" name="location" value="<?= $session->location ?>" class="updateinput" /></p>
								</div>
								<?= CLEARBOTH ?>
								<div class="quarter alignright">
									<p class="marginright">User Title:</p>
								</div>
								<div class="threequarter lastleft">
										<p><input type="text" name="usertitle" value="<?= $session->usertitle ?>" class="updateinput" /></p>
								</div>
								<?= CLEARBOTH ?>
								<div class="quarter alignright">
									<p class="marginright">Time Zone:</p>
								</div>
								<div class="threequarter lastleft">
										<p>
											<select name="timezoneoffset">
												<option value="-12"<?= $session->timezoneoffset == '-12' ? ' selected="selected"':''?>>(GMT -12:00) Eniwetok, Kwajalein</option>
												<option value="-11"<?= $session->timezoneoffset == '-11' ? ' selected="selected"':''?>>(GMT -11:00) Midway Island, Samoa</option>
												<option value="-10"<?= $session->timezoneoffset == '-10' ? ' selected="selected"':''?>>(GMT -10:00) Hawaii</option>
												<option value="-9"<?=  $session->timezoneoffset == '-9' ? ' selected="selected"':''?>>(GMT -9:00) Alaska</option>
												<option value="-8"<?=  $session->timezoneoffset == '-8' ? ' selected="selected"':''?>>(GMT -8:00) Pacific Time (US &amp; Canada)</option>
												<option value="-7"<?=  $session->timezoneoffset == '-7' ? ' selected="selected"':''?>>(GMT -7:00) Mountain Time (US &amp; Canada)</option>
												<option value="-6"<?=  $session->timezoneoffset == '-6' ? ' selected="selected"':''?>>(GMT -6:00) Central Time (US &amp; Canada), Mexico City</option>
												<option value="-5"<?=  $session->timezoneoffset == '-5' ? ' selected="selected"':''?>>(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>
												<option value="-4"<?=  $session->timezoneoffset == '-4' ? ' selected="selected"':''?>>(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
												<option value="-3.5"<?=$session->timezoneoffset == '-3.5' ? ' selected="selected"':''?>>(GMT -3:30) Newfoundland</option>
												<option value="-3"<?=  $session->timezoneoffset == '-3' ? ' selected="selected"':''?>>(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
												<option value="-2"<?=  $session->timezoneoffset == '-2' ? ' selected="selected"':''?>>(GMT -2:00) Mid-Atlantic</option>
												<option value="-1"<?=  $session->timezoneoffset == '-1' ? ' selected="selected"':''?>>(GMT -1:00 hour) Azores, Cape Verde Islands</option>
												<option value="0"<?=   $session->timezoneoffset == '0' ? ' selected="selected"':''?>>(GMT) Western Europe Time, London, Lisbon, Casa.</option>
												<option value="1"<?=   $session->timezoneoffset == '1' ? ' selected="selected"':''?>>(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris</option>
												<option value="2"<?=   $session->timezoneoffset == '2' ? ' selected="selected"':''?>>(GMT +2:00) Kaliningrad, South Africa</option>
												<option value="3"<?=   $session->timezoneoffset == '3' ? ' selected="selected"':''?>>(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
												<option value="3.5"<?= $session->timezoneoffset == '3.5' ? ' selected="selected"':''?>>(GMT +3:30) Tehran</option>
												<option value="4"<?=   $session->timezoneoffset == '4' ? ' selected="selected"':''?>>(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
												<option value="4.5"<?= $session->timezoneoffset == '4.5' ? ' selected="selected"':''?>>(GMT +4:30) Kabul</option>
												<option value="5"<?=   $session->timezoneoffset == '5' ? ' selected="selected"':''?>>(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
												<option value="5.5"<?= $session->timezoneoffset == '5.5' ? ' selected="selected"':''?>>(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
												<option value="6"<?=   $session->timezoneoffset == '6' ? ' selected="selected"':''?>>(GMT +6:00) Almaty, Dhaka, Colombo</option>
												<option value="7"<?=   $session->timezoneoffset == '7' ? ' selected="selected"':''?>>(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
												<option value="8"<?=   $session->timezoneoffset == '8' ? ' selected="selected"':''?>>(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
												<option value="9"<?=   $session->timezoneoffset == '9' ? ' selected="selected"':''?>>(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
												<option value="9.5"<?= $session->timezoneoffset == '9.5' ? ' selected="selected"':''?>>(GMT +9:30) Adelaide, Darwin</option>
												<option value="10"<?=  $session->timezoneoffset == '10' ? ' selected="selected"':''?>>(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
												<option value="11"<?=  $session->timezoneoffset == '11' ? ' selected="selected"':''?>>(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
												<option value="12"<?=  $session->timezoneoffset == '12' ? ' selected="selected"':''?>>(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
											</select>
										</p>
								</div>
								<?= CLEARBOTH ?>
								<h3 class="aligncenter skin-this {'selector':'#whole #main h3'}">Change Avatar</h3>
								<p class="marginright lighter aligncenter skin-this {'selector':'#whole .lighter'}">
									<? if ($session->avatar) {?>
										<img src="images/avatar/<?= $session->avatar ?>" alt="<?= $session->username ?>'s avatar" />
									<?} else {?>
										No avatar set
									<? } ?>
								</p>
								<p class="aligncenter"><input type="file" name="avatar" /></p>
								<?= CLEARBOTH ?>
								<h3 class="aligncenter skin-this {'selector':'#whole #main h3'}">Settings</h3>
								<p class="aligncenter">
									Disable Notifications:<br />
									<span class="lighter skin-this {'selector':'#whole .lighter'}">Items that you have taken part in can automatically send notifications on screen. You can disable these by ticking the boxes below and clicking 'update'.</span>
								</p>
								
								<? $result = mysql_query('SELECT * FROM notetype') or die(__LINE__.__FILE__.mysql_error());
								$i = 1;
								while ($notetype = mysql_fetch_object($result)) {
									$unt = mysql_single('
										SELECT notetypeid FROM usernotetype 
										WHERE userid = \''.mysql_real_escape_string($session->userid).'\' 
										AND notetypeid = \''.mysql_real_escape_string($notetype->notetypeid).'\'
										',__LINE__.__FILE__);
									?>
								<div class="fifth aligncenter<?= $i++ % 5 == 0 ? ' last-left':''?>">
									<label>
										<input type="checkbox" name="notification[]" value="<?= $notetype->notetypeid ?>" <?= $unt->notetypeid == $notetype->notetypeid ? 'checked="checked"':'' ?> /><br />
										<?= sprintf(htmlspecialchars($notetype->description),'like/dislike') ?>
									</label>
								</div>
								<? } ?>
								<?= CLEARBOTH ?>
								<p class="aligncenter"><input type="submit" value="Update" name="do" /></p>
							</form>
						</div>
						<?= CLEARBOTH ?>
					</div>
				<? } else {?>
					<h2 class="skin-this {'selector':'#whole #main h2'}">Sorry</h2>
					<p>You have to be logged in to view this page</p>
				<? } ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>
<?
include PATH.'/includes/footer.php';
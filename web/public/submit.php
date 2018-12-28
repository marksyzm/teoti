<?php

$submit = $usesbbcode = true;

include 'includes/dbconnect.php';

$postmode = false;
$check = array();

$EXTRASCRIPT = '
		<script type="text/javascript" src="lib/jquery.markitup.js"></script>
		<script type="text/javascript" src="lib/sets/bbcode/bigset.js"></script>
		<link rel="stylesheet" type="text/css" href="lib/skins/markitup/style.css?v=1" />
		<link rel="stylesheet" type="text/css" href="lib/sets/bbcode/bigstyle.css?v=1" />
		<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="ckeditor/adapters/jquery.js"></script>
		';

//if id is given then do the checks and get the values n shit
//if ($_POST['preview']) death(!$_POST['preview']);
if (in_array($_POST['do'],array('update','insert')) && !$_POST['preview'])
	include PATH.'/includes/submit-queries.php';
	
include PATH.'/includes/header.php';
?>
<!-- main left column -->
<div id="content-outer">
	<div id="content" class="skin-this {'selector':'#content'}">
		<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
		<div class="body-left"><div class="body-right"><div class="body-inner">
			<div id="content-inner">
				<? if ($_POST['preview']) {?>
				<h2 class="skin-this {'selector':'#whole #main h2'}">Preview:</h2>
				<?
					if (strlen($_POST['preview']) > MAXPOSTLENGTH) $_POST['preview'] = substr($_POST['preview'],0,MAXPOSTLENGTH);
					$bbcode = bbcode();
					echo $bbcode->parse($_POST['textarea_pagetext']);
				}
				
				//get contents (check that user has permissions too)
				if ($_GET['p'] > 0) {
					$p = mysql_single('
						SELECT postid,pagetext,title,threadid FROM post
						WHERE postid = \''.mysql_real_escape_string($_GET['p']).'\'
						'.($session->staff ? '':'AND userid = \''.mysql_real_escape_string($session->userid).'\'').'
						',__LINE__.__FILE__);
					if ($p->postid) {
						$postmode = true;
						$t = mysql_single('SELECT title FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
						$threadtitle = $t->title;
						$t = $p;
						$t->textarea_pagetext = $p->pagetext;
					} 
				} elseif ($_GET['t'] > 0) {
					$t = mysql_single('
						SELECT * FROM thread 
						WHERE threadid = \''.mysql_real_escape_string($_GET['t']).'\'
						'.($session->staff ? '':'AND postuserid = \''.mysql_real_escape_string($session->userid).'\'').'
						',__LINE__.__FILE__);
					$p = mysql_single('
						SELECT postid,pagetext FROM post WHERE postid = \''.mysql_real_escape_string($t->firstpostid).'\'
						',__LINE__.__FILE__);
					if (!$p->postid) unset($t);
					else $t->textarea_pagetext = $p->pagetext;
					unset($p);
				}
				
				if ($_POST['type'] == 'post') $postmode = true;
				?>
				<h2 class="skin-this {'selector':'#whole #main h2'}"><?= 
					($t->threadid ? 'Edit':'Submit'),' '
					,($postmode ? 'post' : 'thread')
					,($postmode ? ($t->title ? ': '.$p->title : '').' of thread: '.$threadtitle  : ': '.$t->title)
				?></h2>
				<? 
				if (count($check) || $_POST['preview'])	{
					//if there was an error with the last submission or this is a preview then get previous values.
					$t = (object)$_POST; //cast post values as array
				?>
				<p class="error"><?= implode('<br />',$check) ?></p>
				<? } ?>	
				<? if ($session->userid) {?>
				<form action="<?= STRIP_REQUEST_URI ?>" method="post" id="submit-form" class="text-editor">
					<? if ($_GET['error']) {?><h3 class="error skin-this {'selector':'#whole #main h3'}"><?= $_GET['error'] ?></h3><? } ?>
					<input type="hidden" name="do" value="<?= $t->threadid > 0 ? 'update':'insert'?>" />
					<input type="hidden" name="thumbnail" value="<?= $t->thumbnail ?>" />
					<? if ($t->threadid > 0) { ?><input type="hidden" name="threadid" value="<?= $t->threadid ?>" /><? } ?>
					<? if ($t->postid > 0) { ?><input type="hidden" name="postid" value="<?= $t->postid ?>" /><? } ?>
					<input type="hidden" name="type" value="<?= $postmode ? 'post':'thread' ?>" />
					<? if (!$postmode) {?>
					<p>
						<span class="lighter skin-this {'selector':'#whole .light'}">Type:</span> 
						<label>
							<input 
								type="radio" class="switch-threadtype" name="threadtype" value="0" <?= isset($t->threadtype) || !$t->threadtype ? ' checked="checked"':'' ?> 
								rel="Standard thread - simply add a title, content and category and away you go." />
							Manual 
						</label>
						<label>
							<input 
								type="radio" class="switch-threadtype switch-threadtype-auto" name="threadtype" value="0"
								rel="Auto generate a thread from a link you found - save yourself time and energy creating a thread!" />
							Auto 
						</label>
						<label>
							<input 
								type="radio" class="switch-threadtype" name="threadtype" value="1" <?= $t->threadtype == 1 ? ' checked="checked"':'' ?> 
								rel="A blog article works just like a standard thread, but it also shows on your user page. The full content of this thread article is shown in your profile. To split your article into an intro on your blog simply type [break] at the appropriate point." />
								Blog 
						</label>
						<!--<label>
							<input 
								type="radio" class="switch-threadtype" name="threadtype" value="2" <?= $t->threadtype == 2 ? ' checked="checked"':'' ?> 
								rel="Single images or videos go here so the public can vote on them. Make sure you give it some tags and a decent title so people can find it again!" />
								Bunker 
						</label>-->
					</p>
					<p class="submit-info"><!-- --></p>
					<div class="switch-threadtype-autoshowhide">
						<p class="light skin-this {'selector':'#whole .light'}">
							Title:<br/>
							<input type="text" name="title" value="<?= $t->title ?>" class="largeinput updateinput" />
						</p>
						
						<? } ?>
						<p class="light skin-this {'selector':'#whole .light'}">
							Content: <br />
							<textarea class="pagetext" name="textarea_pagetext"><?= $t->textarea_pagetext ?></textarea>
						</p>
						<div class="teoti-button">
                            <a href="ajax/bbcode-help.php" class="bbcode-help">BBCode Help</a>
                            <a href="ajax/smilie-help.php" class="smilie-help">Smilie Help</a>
                            <a href="#toggle-wysiwyg" class="toggle-wysiwyg" title="A WYSIWYG is a word style editor which stands for 'What you see is what you get'. BBCode is our standard tags based code - see our help file">Use <?= $session->settings['wysiwyg'] ? 'BBCode':'WYSIWYG' ?></a>
							<? if ($session->staff && !$postmode) {
						
								$ignore = array();
								$starr = array(array('id' => '','name'=>'Default Style'));
								$result = mysql_query('
									SELECT id, name FROM skins WHERE user = \''.mysql_real_escape_string($session->userid).'\' ORDER BY name
									') or die(__LINE__.__FILE__.mysql_error());
								while ($row = mysql_fetch_object($result)) {
									$ignore[] = $row->id;
									$starr[] = array('id' => $row->id,'name' => $row->name);
								}
								$result = mysql_query('
									SELECT skins.id,skins.name FROM skins, skinthemes WHERE skins.id = skinthemes.skin '.($ignore ? 'AND skins.id NOT IN ('.implode(',',$ignore).')':'').' ORDER BY name
									') or die(__LINE__.__FILE__.mysql_error());
								while ($row = mysql_fetch_object($result)) $starr[] = array('id' => $row->id,'name' => $row->name);
								unset($row,$result,$ignore);
								
								if ($starr) {?>
									Select Style
									<select name="styleid">
										<? foreach($starr as $v){ ?>
										<option value="<?= $v['id'] ?>"<?= $t->styleid == $v['id'] ? ' selected="selected"':''?>><?= $v['name'] ? $v['name'] : 'Style '.$v['id'] ?></option>
										<? } ?>
									</select>
								<? 
								}
							} ?>
						</div>
					</div>
					<p class="light skin-this {'selector':'#whole .light'}">
							Related Link:<br/>
							<input type="text" name="related" value="<?= $t->related?>" placeholder="http://" class="largeinput updateinput input-related" />
					</p>
					<div class="switch-threadtype-autohideshow hidethis marginbottom teoti-button skin-this {'selector':'#whole .teoti-button'}">
						<br />
						<a href="#auto-retrieve" class="retrieve-auto">Auto Retrieve</a>
						<h2 class="hidethis skin-this margintop {'selector':'#whole #main h2'}">Choose a thumbnail</h2>
						<div class="auto-thumbnails"><!-- --></div>
					</div>
					
					<div id="help-box"><!-- --></div>
					
					<p><!-- --></p>
					
					<div class="quarter">
						<input type="checkbox" name="point_lock" value="1"<?= $t->point_lock ? ' checked="checked"':''?> /> Point lock
					</div>
					<? if (!$postmode) {?>
					<div class="quarter marginleft">
						<input type="checkbox" name="open" value="1"<?= $t->open || !isset($t->open) ? ' checked="checked"':''?> /> Open
					</div>
					<? if ($session->staff) {?>
					<div class="quarter">
						<input type="checkbox" name="sticky" value="1"<?= $t->sticky ? ' checked="checked"':''?> /> Sticky
					</div>
					<? /*if ($t->threadid) { ?>
					<div class="quarter">
						<input type="checkbox" name="visible" value="2"<?= $t->visible == 2 ? ' checked="checked"':''?> /> Soft delete
					</div>
					<? } */
						}
					} ?>
					<?= CLEARBOTH ?>
					<? if (!$postmode) {?>
					<p><!-- --></p>
					<h2 class="skin-this {'selector':'#whole #main h2'}">Choose a category</h2><br /><br />
					<div class="teoti-button skin-this {'selector':'#whole .teoti-button'}">
					<?
						$category = array();
						$ppfs = array(15);
						if ($session->god) $ppfs[] = 3;
						if ($session->staff) $ppfs[] = 2; 
						if ($session->admin) $ppfs[] = 1;
						if ($t->forumid) $parentid = forumparentid($t->forumid);
						
						foreach ($ppfs as $ppf) {
							$result = mysql_query('
								SELECT forumid, title FROM forum WHERE parentid = \''.mysql_real_escape_string($ppf).'\'
								') or die(__LINE__.__FILE__.mysql_error());
							if (!mysql_num_rows($result)){
								$result = mysql_query('
									SELECT forumid, title FROM forum WHERE forumid = \''.mysql_real_escape_string($ppf).'\'
									') or die(__LINE__.__FILE__.mysql_error());
							}
							while ($pf = mysql_fetch_object($result)) {
								$categories[] = $pf->forumid;
								?>
								<a class="light choose-category skin-this {'selector':'#whole .light'}<?= $parentid && $parentid == $pf->forumid ? ' active':''
									?>" href="#<?= urlify($pf->title) ?>" id="category-<?= $pf->forumid ?>">
									<?= $pf->title ?>
								</a>
							<?}
						}
					?>	
					</div>
					<br />
					<? foreach ($categories as $category) {?>
						<div id="category-box-<?= $category ?>" class="showhide-category<?= $category == $parentid ? ' showthis':''?>">
							<? $forums = forumChildren($category); 
							foreach ($forums as $forum) {
							$forumtitle = forumtitle($forum);
							?>
							<div class="third select-forum">
								<label class="displayblock">
									<input type="radio" class="category-chosen" name="forumid" value="<?= $forum ?>"<?= $t->forumid == $forum ? ' checked="checked"':''?> />
									<a class="icon-<?= urlify($forumtitle) ?>"><img src="images/blank.gif" alt="<?= $forumtitle ?>" /></a>
									<?= $forumtitle ?>
								</label>
							</div>
							<? } ?>
							<?= CLEARBOTH ?>
						</div>
					<? } ?>
					<div id="category-chosen"<? if(!$t->forumid) {?>class="hidethis"<? } ?>><br />
						<h3 class="skin-this {'selector':'#whole #main h3'}">Category Chosen: 
							<span><? if ($t->forumid > 0) echo forumtitle($t->forumid) ?><!-- title of category goes here --></span>
						</h3>
					</div>
				<? } ?>
					<input type="hidden" name="submit" id="button-action" value="1" />
					<p class="aligncenter">
						<input type="submit" value="Submit" />
					</p>
					
				</form>
				<? } else {?>
				<p>Sorry, you must be logged in to submit a thread!</p>
				<? } ?>
			</div>
		</div></div></div>
		<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
	</div>
</div>

<?

include PATH.'/includes/footer.php';
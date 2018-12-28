<?php
//headers
#if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start('ob_gzhandler'); else ob_start();
if ($_GET['thid'] > 0) {
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
}

header('content-type: text/html; charset: utf-8'); 
?>
<!doctype html>
<html class="no-js" lang="en"> 
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="theme-color" content="#39419e">
	<meta name="robots" content="index, follow" />
	<meta name="google-site-verification" content="Jn6-TtgRCk7skCPxSg6FvoU7hwQDNAqBb8S_P3DDzC4" /><!-- teoti.co.uk -->
	<meta name="author" content="TEOTI - The End Of The Internet" />
	<meta name="googlebot" content="index, follow, archive" />
	<meta name="msnbot" content="index, follow, archive" />
	<meta name="copyright" content="TEOTI" />
	<meta name="description" content="<?= htmlspecialchars($metadesc ? $metadesc : DEFAULTDESC) ?>" />
	<meta name="keywords" content="<?= htmlspecialchars($metakw ? $metakw : DEFAULTKW) ?>" />
	<meta name="google-site-verification" content="aUlDLY0eiPQr_UtN6bMTStEXrCV73xv8C04Gl49r5do" />
	<meta http-equiv="content-language" content="en, ZH" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<? if ($t->thumbnail || $u->avatar) {?><meta property="og:image" content="<?= htmlspecialchars($t->thumbnail ? $t->thumbnail : PROTOCOL.$_SERVER['HTTP_HOST'].URLPATH.'/images/avatar/'.$u->avatar) ?>"/><? } ?>
	<meta property="og:description" content="<?= htmlspecialchars(isset($metadesc) ? $metadesc : DEFAULTDESC) ?>" />
	<base href="<?= PROTOCOL.$_SERVER['HTTP_HOST'].URLPATH ?>/" />
	<link href="favicon.ico" type="image/x-icon" rel="shortcut icon" />
	<link rel="alternate" type="application/rss+xml" title="Teoti - The End Of The Internet" href="external.php">
	<?
		$stylevars = array();
		$stylevars[] = 'v=89';

        $skin = mysql_single('SELECT * FROM skins WHERE id = \''.$mysqli->real_escape_string($session->styleid).'\'',__LINE__.__FILE__);
	?>
	<link href="lib/standard.css<?= count($stylevars) ? '?'.implode('&amp;',$stylevars) : '' ?>" rel="stylesheet" type="text/css" />
	<link href="lib/skin<?= 
		$_GET['debug'] ? 
			'-gen.css.php'.($_GET['refresh'] ? '?refresh=1':'') 
			: ($skin->id ? 's/skin_'.$skin->id.'_'.$skin->version.'.css':'.css'.(count($stylevars) ? '?'.implode('&amp;',$stylevars) : '')) 
		?>" rel="stylesheet" id="generated-stylesheet" type="text/css" />
	<script src="lib/modernizr.js"></script>
	<title><?= $PAGETITLE = ($pagetitle ? htmlspecialchars($pagetitle) :OURCOMPANY).($pagetitle ? '':' - '.COMPANYTITLE) ?></title>
</head>
<body>
<div id="image-upload-container"><!-- --></div>
<div id="whole" class="skin-this {'selector':'#whole'}">
	<div class="popup status-box"><!-- --></div>
	<div id="main-outer">
		<div id="main" class="skin-this {'selector':'#main'}">
			<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
			<div class="body-left"><div class="body-right"><div class="body-inner">
				<div id="main-inner">
					
					<div id="header-outer">
						<div id="header" class="skin-this {'selector':'#header'}">
							<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
							<div class="body-left"><div class="body-right"><div class="body-inner">
								<div id="header-inner">
									<a href="./"><!-- link to home page --></a>
								</div>
							</div></div></div>
							<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
						</div>
					</div>
					<div id="header-menu-outer"<? if (!$session->god || $session->userid == 2) {?> class="mobile-hidethis"<? } ?>>
						<div id="header-menu" class="skin-this {'selector':'#header-menu'}">
							<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
							<div class="body-left"><div class="body-right"><div class="body-inner">
								<div id="header-menu-inner">
									<!-- header menu links -->
									<? getMenu(0,$filter ? $_GET['forumid']:0) ?>
								</div>
							</div></div></div>
							<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
						</div>
					</div>
					
					<!-- wrapper for the main columns -->
					<div id="body-outer">
						<div id="body" class="skin-this {'selector':'#body'}">
							<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
							<div class="body-left"><div class="body-right"><div class="body-inner">
								<div id="body-inner">
									
									<? 
									if (!$session->settings['old'] || $_GET['filter'] || !($session->settings['old'] && ($_GET['forumid'] == 3 || !$_GET['forumid'])) || $_GET['which'] || !$feed) {?>
									<!-- right column -->
									<div id="col-right-outer">
										<div id="col-right" class="skin-this {'selector':'#col-right'}">
											<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
											<div class="body-left"><div class="body-right"><div class="body-inner">
												<div id="col-right-inner">
													<? include PATH.'/includes/navigation.php'; ?>
												</div>
											</div></div></div>
											<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
										</div>
									</div>
									<!-- end right column -->
									
									<div id="content-col">
										<!-- content column -->
									<? } ?>

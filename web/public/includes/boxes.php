<?
//work out which box is open by session, else go to default (first)
$boxset = $session->settings['box'];
if (!$boxset) $boxset = 'hot';
$thisfilter = '2 Days';
?>

<div class="mobile-hidethis navigation-mobile">
	<div class="box">
		<div class="clearfix">
			<div class="box-type half">
				<div class="teoti-button style-this {'selector':'#whole .teoti-button'}">
					<a href="#select-box-type" class="box-type-select"><?= ucwords($boxset) ?></a>
				</div>
				<div class="popup popup-boxtype">
					<? foreach ($BOXTYPES as $boxtype){ 
						switch($boxtype){
							case 'hot':
								$filtarr = array('2'=>'2 Days','7'=>'1 Week','28'=>'1 Month','0'=>'All Time');
								$filter = strlen($session->settings['filter-'.$boxtype]) ? $filtarr[$session->settings['filter-'.$boxtype]] : '2 Days';
								break;
							case 'random':
								$filter = 'Refresh';
								break;
							case 'scores':
								$filtarr = array('month'=>'This Month','all'=>'All Time');
								$filter = $session->settings['filter-'.$boxtype] ? $filtarr[$session->settings['filter-'.$boxtype]] : 'This Month';
								break;
							default:
								$filter = '';
						}
						if ($boxset == $boxtype) {
							$thisfilter = $filter;
						}
					?>
					<a class="box-type-button box-type-button-<?= $boxtype ?> {'forumid':'<?php echo (int)$_GET['forumid'] ?>','boxtype':'<?php echo $boxtype ?>','filter':'<?php echo $session->settings['filter-'.$boxtype] ?>','filtername':'<?php echo $filter ?>'}"><?php echo ucwords($boxtype) ?></a>
					<? } ?>
				</div>
			</div>
			<div class="box-filter half alignright last-left">
				<!-- filter-->
				<div class="teoti-button style-this <? echo $thisfilter ? '':'hidethis' ?> {'selector':'#whole .teoti-button'}">
					<a href="#select-filter-type" id="filter-<?= $boxset ?>" class="box-filter-select {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $boxset ?>'}"><?= $thisfilter ?></a>
				</div>
				<div class="popup popup-hot">
					<a class="box-filter-button {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'hot','filter':'2'}">2 Days</a>
					<a class="box-filter-button {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'hot','filter':'7'}">1 Week</a>
					<a class="box-filter-button {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'hot','filter':'28'}">1 Month</a>
					<a class="box-filter-button {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'hot','filter':'0'}">All Time</a>
				</div>
				<div class="popup popup-scores">
					<a class="box-filter-button {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'scores','filter':'month'}">This Month</a>
					<a class="box-filter-button {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'scores','filter':'all'}">All Time</a>
				</div>
			</div>
		</div>
		<div id="box-contents">
			<div class="col-head-links">
				<!-- right column header content -->
				<? colheaders($boxset,$_GET['hpage'],$_GET['forumid']) ?>
			</div>
		</div>
	</div>
</div>
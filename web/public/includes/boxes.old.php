<?
//work out which box is open by session, else go to default (first)
$boxset = $session->settings['box'];
if (!$boxset) $boxset = 'hot';
foreach ($BOXTYPES as $box) { ?>
<div class="box default {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $box ?>'}<?= $boxset == $box ?' active' :''?>" id="<?= $box ?>">
	<div class="header-outer">
		<div class="header skin-this {'selector':'<?= $boxset == $box ?'#col-right .box.active .header' :''?>','altselector':'#<?= $box ?> .header'}">
			<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>
			<div class="body-left"><div class="body-right"><div class="body-inner">
				<div class="header-inner">
					<div class="half"><a class="cufon"><?= ucwords($box) ?></a></div>
					<div class="half alignright last-left">
						<!-- filter-->
						<? 
						switch($box){
							case 'hot':
								$filtarr = array('2'=>'2 Days','7'=>'1 Week','28'=>'1 Month','0'=>'All Time');
								$filter = strlen($session->settings['filter-'.$box]) ? $filtarr[$session->settings['filter-'.$box]] : '2 Days';
								break;
							case 'random':
								$filter = 'Refresh';
								break;
							case 'scores':
								$filtarr = array('month'=>'This Month','all'=>'All Time');
								$filter = $session->settings['filter-'.$box] ? $filtarr[$session->settings['filter-'.$box]] : 'This Month';
								break;
							default:
								$filter = '';
						}
						if ($filter) {?>
							<a id="filter-<?= $box ?>" class="filter cufon {'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $box ?>'}"><?= $filter ?></a>
							<? if (in_array($box,array('hot','scores'))) {?>
								<div class="popup">
									<? if ($box == 'hot') {?>
									<a class="{'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $box ?>','filter':'2'}">2 Days</a>
									<a class="{'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $box ?>','filter':'7'}">1 Week</a>
									<a class="{'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $box ?>','filter':'28'}">1 Month</a>
									<a class="{'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $box ?>','filter':'0'}">All Time</a>
									<? } ?>
									<? if ($box == 'scores') {?>
									<a class="{'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $box ?>','filter':'month'}">This Month</a>
									<a class="{'forumid':'<?= $_GET['forumid'] ?>','boxtype':'<?= $box ?>','filter':'all'}">All Time</a>
									<? } ?>
								</div>
							<? } ?>
						<? } ?>
					</div>
					<div class="clearboth"><!-- --></div>
				</div>
			</div></div></div>
			<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>
		</div>
	</div>
				
	<div id="contents-<?= $box ?>">
		<div class="col-head-links<? if ($boxset == $box) echo ' show-me' ?>"><!-- right column header content -->
			<? if ($boxset == $box) colheaders($box,$_GET['hpage'],$_GET['forumid']) ?>
		</div>
	</div>
</div>
<? }
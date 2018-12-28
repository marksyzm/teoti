<?
include 'includes/dbconnect.php';
header('Content-Type: text/xml; charset=UTF-8'); 
$RSS = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
	<channel>
		<title>
			'.OURCOMPANY.' - '.COMPANYTITLE.'
		</title>
		<language>
			en-us
		</language>
		<description>
			Top News and Hot Topics
		</description>
		<link>
			'.PROTOCOL.$_SERVER['HTTP_HOST'].'
		</link>
	</channel>
</rss>
');

$where = array();
$where = array();
$where[] = 'thread.visible = 1';
$where[] = 'thread.forumid IN ('.implode(',',forumChildren(15)).')';

$result = mysql_query('
	SELECT * FROM thread 
	'.(count($where) ? 'WHERE '.implode("\nAND ",$where) : '').'
	ORDER BY dateline DESC LIMIT 50
	') or die(__LINE__.__FILE__.mysql_error());
	

	

$channel = $RSS->channel;
$imgpath = PROTOCOL.$_SERVER['HTTP_HOST'].URLPATH.'/images/phpThumb.php?w=88&h=88&zc=1&';

while ($t = mysql_fetch_object($result)) { 
	$img = '';
	$link = PROTOCOL.$_SERVER['HTTP_HOST'].URLPATH.'/'.urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$t->threadid).'.html';
	$item = $channel->addChild('item');
	$title = $item->addChild('title');
	$node= dom_import_simplexml($title);   
	$no = $node->ownerDocument;   
	$node->appendChild($no->createCDATASection(iconv("UTF-8","UTF-8//IGNORE",$t->title))); 
	
	if ($t->thumbnail){
		$img = $imgpath.(strstr($t->thumbnail,'.png') ? 'f=png&':'').'src='.urlencode($t->thumbnail);
	} elseif ($t->postuserid) {
		$u = mysql_single('SELECT avatar FROM user WHERE userid = \''.mysql_real_escape_string($t->postuserid).'\'',__LINE__.__FILE__);
		//var_dump($u->avatar);exit;
		if ($u->avatar)
			$img = $imgpath.(strstr($u->avatar,'.png') ? 'f=png&':'').'src='.urlencode('avatar/'.$u->avatar);
	}
	
	if ($img) {
		$image = $item->addChild('thumbnail');
		
		
		
		$image->addAttribute('url',$img);
		$image->addAttribute('height','88');
		$image->addAttribute('width','88');
		
		
		//$image = $item->addChild('image');
		//$image->addChild('link',$link);
		//$image->addChild('url',$img);
		//
		//$title = $image->addChild('title');
		//$node= dom_import_simplexml($title);   
		//$no = $node->ownerDocument;   
		//$node->appendChild($no->createCDATASection($t->title)); 
	}
	
	$item->addChild('pubDate',date('D, d M Y '.MYSQLTIME,$t->dateline).'  +0000');
	$item->addChild('link',$link);
	$description = $item->addChild('description');
	$node= dom_import_simplexml($description);   
	$no = $node->ownerDocument;   
	$node->appendChild($no->createCDATASection(iconv("UTF-8","UTF-8//IGNORE",$t->description).($img ? '<br /><img src="'.$img.'" style="float:right;" alt="" />' : '')));   
}



//echo $channel->item[7]->asXML();

echo $RSS->asXML();
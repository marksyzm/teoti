<?
$smilereplace=$smilesearch=array();
// Unify line breaks of different operating systems
function convertlinebreaks ($str) {
    return preg_replace ("/\015\012|\015|\012/", "\n", $str);
}

//any url with spaces
/*function parseurls($str){
	return $str;
	//return preg_replace('`([^]])((www\.|https?://|ftp://|file://)[-A-Za-z0-9+&@#/%?=~_|!:,.;?]*[-A-Za-z0-9+&?@#/%=~_|])([^[])`i','$1[url]$2[/url]$4',$str);
}*/

function auto_link($text) {
	return $text;
  $pattern = "/[^\"'](((http[s]?:\/\/)|(www\.))(([a-z][-a-z0-9]+\.)?[a-z][-a-z0-9]+\.[a-z]+(\.[a-z]{2,2})?)\/?[a-z0-9.,_\/~#&=;%+?-]+[a-z0-9:\/#=?]{1,1})[^\"']/is";
  $text = preg_replace($pattern, " <a href='$1' target=\"_blank\">$1</a>", $text);
  // fix URLs without protocols
  $text = preg_replace("/href='www/", "href='http://www", $text);
  return $text;
}

function removehtmlwrappers($str) {
	//$s = array('&amp;','<','>');
	//$r = array('&amp;','&lt;','&gt;');
	//return str_replace($s,$r,$str);
	return htmlspecialchars($str,ENT_NOQUOTES,'UTF-8',false);
}

function removeblogbreaks ($str) {
	return str_replace('[break]','',$str);
}

// Remove everything but the newline character
function bbcode_stripcontents ($text) {
    return preg_replace ("/[^\n]/", '', $text);
}

function bbcode_fontfamily ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	if (!trim($attributes['default'])) return $content;
	return '<span style="font-family:'.$attributes['default'].'">'.$content.'</span>';
}

function bbcode_url ($action, $attributes, $content, $params, $node_object) {
	if (!isset ($attributes['default'])) {
    $text = $url = $content;
  } else {
    $url = $attributes['default'];
    $text = $content;
  }
	
	if ($action == 'validate') {
    if (substr ($url, 0, 5) == 'data:' || substr ($url, 0, 5) == 'file:'
      || substr ($url, 0, 11) == 'javascript:' || substr ($url, 0, 4) == 'jar:') {
        return false;
    }
    return true;
  }
  
  switch(true) {
  	case strstr($url,'youtube.com') && strstr($url,'v=') && !$attributes['default']:
  		$code = strstrb(str_replace(array('v=','#'),array('',''),strstr($url,'v=')),'&');
  		return bbcode_youtube($action, $attributes, $code, $params, $node_object, true);
	case strstr($url, 'youtu.be') && !$attributes['default']:
		$code = strstrb(str_replace(array('youtu.be/' ,'#'), array('',''), strstr($url,'youtu.be/' )),'&');
		return bbcode_youtube($action, $attributes, $code, $params, $node_object, true);
	case (strstr($url, 'i.imgur.com') || strstr($url, 'imgur.com')) && !strstr($url, '.jpg') && !strstr($url, '/comment') && !$attributes['default']:
		$url = strstr($url, 'i.imgur.com/') ? strstr($url, 'i.imgur.com/') : strstr($url, 'imgur.com/');
		$code = strstrb(str_replace(array('i.imgur.com/', 'imgur.com/', '#', 'gallery/','.gifv', '.mp4','.webm'), array('','','','','','',''), $url),'&');
		return bbcode_imgur($action, $attributes, $code, $params, $node_object, true);
  	case strstr($url,'facebook.com') && strstr($url,'v=') && !$attributes['default']:
  		$code = strstrb(str_replace(array('v=','#'),array('',''),strstr($url,'v=')),'&');
  		return bbcode_facebook($action, $attributes, $code, $params, $node_object, true); 
  	case strstr($url,'vimeo.com/') && !$attributes['default']:
  		$code = strstrb(str_replace(array('vimeo.com/','#'),array('',''),strstr($url,'vimeo.com/')),'&');
  		return bbcode_vimeo($action, $attributes, $code, $params, $node_object, true); 
  	case strstr($url,'metacafe') && !$attributes['default']:
  		return bbcode_metacafe($action, $attributes, $url, $params, $node_object, true); 
  	case $url == 'flash' || (strtolower(strrchr($url,'.')) == '.swf' && !$attributes['default']):
  		if ($url == 'flash') $url = $text;
  		return bbcode_flash($action, $attributes, $url, $params, $node_object); 
  	case $url == 'mp3' || $url == 'flv' || $url == 'media' || in_array(strtolower(strrchr($url,'.')),array('.mp3','.flv','.mp4')) && !$attributes['default']:
  		if ($url == 'mp3' || $url == 'flv' || $url == 'media') $url = $text;
  		return bbcode_media($action, $attributes, $url, $params, $node_object); 
  	case $url == 'img' || (in_array(strtolower(strrchr($url,'.')),array('.jpg','.gif','.jpeg','.png')) && $text == $url) && !$attributes['default']:
  		return bbcode_thumbnail($action, $attributes, $url, $params, $node_object); 
  	case $url == 'link':
  	default:
  		if (!strstr($url,'http://') && !strstr($url,'https://')) $url = 'http://'.$url;
  		return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
  }
}




function bbcode_thumbnail($action, $attributes, $content, $params, $node_object){
	if ($action == 'validate') {
    if (substr ($content, 0, 5) == 'data:' || substr ($content, 0, 5) == 'file:'
      || substr ($content, 0, 11) == 'javascript:' || substr ($content, 0, 4) == 'jar:') {
        return false;
    }
    return true;
  }
	return 
		'<a href="'.htmlspecialchars($content).'" target="_blank">'
			.'<img src="'.URLPATH.'/images/phpThumb.php?src='.htmlspecialchars($content).'&amp;w='.GALLERYW.'&amp;h='.GALLERYH.'&amp;zc=1&amp;fltr[]=wmt|^Xx^Y|10|B|FFFFFF||100|0||000000|100|x" alt="" />'
		.'</a>'
	;
}

function bbcode_related($action, $attributes, $content, $params, $node_object) {  
	if ($action == 'validate') {
    if (substr ($content, 0, 5) == 'data:' || substr ($content, 0, 5) == 'file:'
      || substr ($content, 0, 11) == 'javascript:' || substr ($content, 0, 4) == 'jar:') {
        return false;
    }
    return true;
  }
  return '<div class="teoti-button related-link skin-this {\'selector\':\'#whole .teoti-button\'}"><a href="'.htmlspecialchars($content).'" target="_blank">Related Link</a></div>';
}

function bbcode_size ($action, $attributes, $content, $params, $node_object) {
    $size = $attributes['default'];
    if (!is_numeric($size) || !trim($size)) $size = 2;
		if ((int)$size > 7) $size = 7;
		if ((int)$size < 1) $size = 1;
		//$size = 8-$size; //invert size
    if ($action == 'validate') {
        if (substr ($size, 0, 5) == 'data:' || substr ($size, 0, 5) == 'file:'
          || substr ($size, 0, 11) == 'javascript:' || substr ($size, 0, 4) == 'jar:') {
            return false;
        }
        return true;
    }
    return '<span class="font-size-'.$size.'">'.$content.'</span>';
}

function bbcode_color ($action, $attributes, $content, $params, $node_object) {
    $color = $attributes['default'];
    
    if ($action == 'validate') {
        if (substr ($color, 0, 5) == 'data:' || substr ($color, 0, 5) == 'file:'
          || substr ($color, 0, 11) == 'javascript:' || substr ($color, 0, 4) == 'jar:') {
            return false;
        }
        return true;
    }
    return '<font style="color:'.$color.'">'.$content.'</font>';
}

function bbcode_img ($action, $attributes, $content, $params, $node_object) {
	$origfile = '';
	/*global $ispost;
	if (defined('MAX'.($ispost ? 'POST':'THREAD').'IMGWIDTH') && constant('MAX'.($ispost ? 'POST':'THREAD').'IMGWIDTH') > 0) {
		//set content to resized path
		$origfile = $content;
		$content = URLPATH.'/images/phpThumb.php?src='.rawurlencode($content).'&w='.constant('MAX'.($ispost ? 'POST':'THREAD').'IMGWIDTH');
	}*/
	
	if ($action == 'validate') {
		if (substr($content,0,5) == 'data:' || substr($content,0,5) == 'file:'
			|| substr($content,0,11) == 'javascript:' || substr($content,0,4) == 'jar:')
			return false;
		return true;
  }
  switch($attributes['default']) {
  	case 'left': $class='floatleft'; break;
  	case 'right': $class='floatright'; break;
  	default: $class=''; break;
  }
  return '<img src="'.htmlspecialchars($content).'" alt="" class="'.($origfile ? '{}':'').($class ? ' '.$class :'').'" />';
}

function bbcode_list ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	return '<'.($attributes['default'] == 1 ? 'o':'u').'l>'.$content.'</'.($attributes['default'] == 1 ? 'o':'u').'l>';
}

function bbcode_code ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	return bbcode_section($action, $attributes, $content, $params, $node_object,'code');
}

function bbcode_section ($action, $attributes, $content, $params, $node_object, $type='quote') {
	if ($action == 'validate') return true;
	return '
	<div class="section-outer skin-this {\'selector\':\'#whole .section\'}">
		<span class="light skin-this {\'selector\':\'#whole .light\'}">'
			.($type != 'quote' ? ucfirst($type):'Quote')
			.($attributes['default'] && $type == 'quote' ? ' by '.$attributes['default']:'')
		.':</span>
		<div class="section'.($type == 'code' ? ' code':'').'">
			'.($type == 'code' ? '<code><pre>':'').$content.($type == 'code' ? '</pre></code>':'').'
		</div>
	</div>';
}

function bbcode_youtube ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	return '
	<div class="embed-responsive embed-responsive-16by9">
		<iframe src="https://www.youtube.com/embed/'.$content.'" frameborder="0" allowfullscreen></iframe>
	</div>
	<div class="teoti-button skin-this {\'selector\':\'#whole .teoti-button\'}"><a href="http://www.youtube.com/watch?v='.$content.'" target="_blank">View original</a></div>';
	
	
	//return bbcode_flash($action, $attributes, 'http://www.youtube.com/v/'.trim($content).'?fs=1', $params, $node_object, VIDEOHEIGHT,'',true);
}

function bbcode_imgur  ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	return '
	<div class="embed-responsive embed-responsive-16by9">
		<video loop controls preload="none" poster="http://i.imgur.com/'.$content.'l.jpg">
			<source src="//i.imgur.com/'.$content.'.mp4" type="video/mp4"></source>
			<source src="//i.imgur.com/'.$content.'.webm" type="video/webm"></source>
		</video>
	</div>';
}

function bbcode_facebook ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	return bbcode_flash($action, $attributes, 'http://www.facebook.com/v/'.trim($content).'?fs=1', $params, $node_object, VIDEOHEIGHT,'',true);
}

function bbcode_metacafe ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	$code = substr(str_replace('/watch/','/fplayer/',$content),0,-1).'.swf';
	return bbcode_flash($action, $attributes, $code, $params, $node_object, VIDEOHEIGHT,'',false);
}

function bbcode_vimeo ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	return '
<div class="embed-responsive embed-responsive-16by9">
	<iframe src="http://player.vimeo.com/video/'.$content.'" frameborder="0" allowfullscreen></iframe>
</div>
	<div class="teoti-button skin-this {\'selector\':\'#whole .teoti-button\'}"><a href="http://www.vimeo.com/'.$content.'" target="_blank">Get File</a></div>';
}

function bbcode_media ($action, $attributes, $content, $params, $node_object) { //video, audio
	if ($action == 'validate') return true;
	$height = strtolower(strstr($content,'.mp3')) ? AUDIOHEIGHT : VIDEOHEIGHT;
	return bbcode_flash($action, $attributes, 'lib/player.swf', $params, $node_object, $height, $content);
}

function bbcode_flash ($action, $attributes, $content, $params, $node_object, $height=0, $flashvars = '', $viewsource=true, $title='') {
	if ($action == 'validate') return true;
	$height = $height ? $height : FLASHHEIGHT;
	$width = $width ? $width : '100%';
	return '
	<object 
		classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" 
		codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" 
		width="'.$width.'" height="'.$height.'" name="audio-'.urlify($content).'" id="audio-'.urlify($content).'">
		<param name="movie" value="'.trim($content).'" />
		<param name="allowFullScreen" value="true" />
		<param name="allowscriptaccess" value="always" />
		<param name="wmode" value="transparent" />
		'.($flashvars ? '<param name="flashvars" value="file='.urlencode($flashvars).'" />':'').'
		<embed 
			src="'.trim($content).'" 
			type="application/x-shockwave-flash" allowscriptaccess="always" 
			allowfullscreen="true" width="'.$width.'" height="'.$height.'"
			pluginspage="http://www.macromedia.com/go/getflashplayer" 
			wmode="transparent"
			'.($flashvars ? 'flashvars="file='.urlencode($flashvars).'"' : '').'
		/>
	</object>
	'.($viewsource ? '<div class="teoti-button skin-this {\'selector\':\'#whole .teoti-button\'}"><a href="'.($flashvars ? $flashvars : $content).'" target="_blank">Get File</a></div>' : '')
	;
}

function bbcode_gmap ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') return true;
	return $content;
}

function parse_smilies($str){
	global $smilesearch,$smilereplace;
	return str_replace($smilesearch,$smilereplace,$str);
}

//format the text with bbcode search/replacements
function bbcode($wysiwyg=false) {
	//set up parser, sort out new lines and unparse html
	global $smilesearch,$smilereplace;
	
	$result = mysql_query('
		SELECT smilietext,smiliepath FROM smilie
		') or die(__LINE__.__FILE__.mysql_error());
	while ($smilie = mysql_fetch_object($result)) {
		$smilesearch[] = $smilie->smilietext;
		$smilereplace[] = '<img src="'.htmlspecialchars($smilie->smiliepath).'" alt="'.htmlspecialchars($smilie->smilietitle).'" />';
	}
	unset($result);
	
	$bbcode = new StringParser_BBCode ();
	
	
	$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'removehtmlwrappers'); //remove html parsing
	$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'convertlinebreaks'); // Unify line breaks of different operating systems
	$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'parse_smilies'); 
	$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'removeblogbreaks'); //remove break tags for user blogs
	$bbcode->addFilter (STRINGPARSER_FILTER_POST, 'auto_link'); //any url with spaces before/after it gets wrapped in a link
	$bbcode->addFilter (STRINGPARSER_FILTER_POST, 'longwords'); // stop long words from breaking page
	
	$bbcode->setGlobalCaseSensitive (false); //don't really want our casing to be case sensitive
	$bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), 'nl2br');
	$bbcode->addParser ('list', 'bbcode_stripcontents');
	
	//then roll through bbcode items in db 
	$result = mysql_query('SELECT * FROM bbcodes'.($wysiwyg  ? ' WHERE wysiwyg = 1': '')) or die(__LINE__.__FILE__.mysql_error());
	while ($b = mysql_fetch_object($result)) {
		if ($b->code) {
			$params = $allowed_in = $not_allowed_in = array();
			foreach (array('params','allowed_in','not_allowed_in') as $item) {
				if ($item == 'params') $tmps = explode('|||',$b->$item);
				else $tmps = explode(',',$b->$item);
				$tmparr = array();
				foreach ($tmps as $tmp) {
					if (trim($tmp)) {
						if (strstr($tmp,':::')) {
							$tm = explode(':::',$tmp);
							$tmparr[$tm[0]] = $tm[1];
						} else
							$tmparr[] = $tmp;
					}
				}
				switch($item) {
					case 'params': $params = $tmparr; break;
					case 'allowed_in': $allowed_in = $tmparr; break;
					case 'not_allowed_in': $not_allowed_in = $tmparr; break;
				}
			}
			unset($tmparr,$tmps,$tmp,$tm);
			
			$callback = $b->callback && function_exists($b->callback) ? $b->callback : null;
			
			$bbcode->addCode($b->code, $b->type, $callback, $params, $b->content_type, $allowed_in, $not_allowed_in);
	  }
	}
	
	
	
	$bbcode->addCode ('list', 'callback_replace', 'bbcode_list', array ('usecontent_param'=>'default'),'list', array ('block', 'listitem'), array ());
	$bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'), 'listitem', array ('list'), array ());
	$bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
	$bbcode->setCodeFlag ('*', 'paragraphs', true);
	$bbcode->setCodeFlag ('quote', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag ('html', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag ('code', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag ('php', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag ('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
	$bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
	$bbcode->setOccurrenceType ('img', 'image');
	$bbcode->setMaxOccurrences ('image', MAXIMAGENUM); //to stop ridiculous threads & smilies
	$bbcode->setRootParagraphHandling ($wysiwyg ? false : true);
	
	return $bbcode;
}

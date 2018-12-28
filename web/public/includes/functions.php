<?php

function showBrief($str, $length, $step=0) {
  $str = strip_tags($str);
  $str = explode(' ',trim($str));
  return implode(' ',array_slice($str, $length*$step, $length));
}

function clearUTF($s) {
  /*$r = '';
  $s1 = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
  for ($i = 0; $i < strlen($s1); $i++) {
      $ch1 = $s1[$i];
      $ch2 = mb_substr($s, $i, 1);
      $r .= $ch1=='?'?$ch2:$ch1;
  }*/
  return preg_replace('/[^\x00-\x7f]/','',$s);
}

function urlify($orig,$num=array()){
	if (!is_array($orig) && !is_string($orig)) return '';
	if (!is_array($orig)) $orig = array($orig);
	if (!is_array($num)) $num = array($num);
	$orig = array_map('htmlspecialchars_decode',array_map('clearUTF',$orig));
	$i = 0;
	$return = array();
	//$search = array('-','/','.','$','+','"',"'",'`',',',':',';',);
	$replace = '';
	//$search = '/^[A-Za-z0-9]/i';
	$search= array('-','&quot;','!','@','#','$','%','^','&','*','(',')','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
	//,a,an,and,as,at,before,but,by,for,from,is,in,into,like,of,off,on,onto,per,since,than,the,this,that,to,up,via,with
	$commonwords = explode(',','&,\',/,\\');
	foreach ($orig as $string) {
		$tmpstray = explode(' ',$string);
		$stray = array();
		foreach ($tmpstray as $tmp) 
			if (!@in_array(strtolower($tmp),$commonwords)) 
				$stray[] = strtolower(str_replace($search,$replace,$tmp));
		unset($tmpstray,$commonwords);
		$return[] =	count($stray) ? preg_replace('/-+/','-',(count($num) ? $num[$i++].'-':'').implode('-',$stray)) : $num[$i++];
	}
	return implode('_',$return);
}

//sanity shizzle
if (get_magic_quotes_gpc()) {
	function stripslashes_array($array) {
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}
	$_COOKIE = stripslashes_array($_COOKIE);
	$_FILES = stripslashes_array($_FILES);
	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	$_REQUEST = stripslashes_array($_REQUEST);
}

function format_size ($dsize) {
	if (strlen($dsize) <= 9 && strlen($dsize) >= 7) return number_format($dsize / 1048576,1).' MB';
	elseif (strlen($dsize) >= 10) return number_format($dsize / 1073741824,1).' GB';
	else return number_format($dsize / 1024,1).' KB';
}

if (count($_FILES)) {
	define('MARKER',0xff);
	define('SOI',0xd8);
	define('EOI',0xd9);
	define('JFIF',0xe0);
	define('APP',0xe0);
	define('QUANT',0xdb);
	define('HUFF',0xc4);
	define('SOF0',0xc0);
	define('SOF1',0xc1);
	define('SOF2',0xc2);
	define('SOS',0xda);
	define('ED',0xed);
	define('EE',0xee);
	define('DD',0xdd);
	
	function u16($fp) {  
		$res = ord(fread($fp, 1)) << 8;
	  $res += ord(fread($fp, 1));
	  return $res;
	}
	
	function jpeghdr($fp) {  
		if(ord(fread($fp, 1)) != MARKER || ord(fread($fp, 1)) != SOI)
	    return false;
	  $length = 2;
	  $res = false;
	  while(ord(fread($fp, 1)) == MARKER) {  
	  	$cod = ord(fread($fp, 1));
	    if ($cod == SOS)
	      break;
	    $len = u16($fp);
	    if ($cod == QUANT || $cod == HUFF || $cod == DD || $cod == SOF0 || $cod == SOF1)
	      $length += 2 + $len;
	    if ($cod == SOF0 || $cod == SOF1) {  
	    	$len -= 5;
	      $res = array();
	      $res["prec"] = ord(fread($fp, 1));
	      $res["height"] = u16($fp);
	      $res["width"] = u16($fp);
	    }  
	    else if ($cod == SOF2)
	      return false;
	    else if ($cod != JFIF && $cod != QUANT && $cod != HUFF && $cod != DD && ($cod & APP) != APP)
	      return false;
	    fseek($fp, $len-2, 1);
	  }
	  if ($cod == SOS) {  
	  	$pos = ftell($fp);
	    fseek($fp, 0, 2);
	    $length += ftell($fp) - $pos;
	    $res['length'] = $length;
	    return $res;
	  }
	  return false;
	}
	
	function testjpeg($name) {  
		if (!($fp = @fopen($name, "rb")))
	    return false;
	  $res = jpeghdr($fp);
	  fclose($fp);
	  return $res;
	}
	
	function readjpeg($fp) {  
		rewind($fp);
	  if (ord(fread($fp, 1)) != MARKER || ord(fread($fp, 1)) != SOI)
	    return false;
	  $buf = chr(MARKER) . chr(SOI);
	  while (ord(fread($fp, 1)) == MARKER) {  
	  	$cod = ord(fread($fp, 1));
	    if ($cod == SOS)
	      break;
	    if ($cod == QUANT || $cod == HUFF || $cod == DD || $cod == SOF0 || $cod == SOF1) { 
	    	$len = u16($fp);
	      $buf .= chr(MARKER) . chr($cod);
	      $buf .= chr($len >> 8);
	      $buf .= chr($len & 0xff);
	      $buf .= fread($fp, $len-2);
	    } else {  
	    	$len = u16($fp);
	      fseek($fp, $len-2, 1);
	    }
	  }
	  $buf .= chr(MARKER).chr($cod);
	  while($next = fread($fp, 4096))
	    $buf .= $next;
	  return $buf;
	}
}

function send_image_to_file ($filetags, $itemid=0, $method='insert') {
	if (!is_array($filetags) && is_string($filetags)) $filetags = array($filetags);
	else $error = 'Invalid file tag variable (see webmaster)!';
	if (!class_exists('phpthumb')) {
        $error = 'Thumbnail class not included!';
        trigger_error($error,E_USER_ERROR);
        return $error;
    }
	$error = '';
	$mysqlquery = array();
	$imgtypes = array('image/jpg','image/jpeg','image/gif','image/png');
	$fileexts = array('jpg','gif','jpeg','png');
	foreach ($filetags as $filetag) {
		if ($_FILES[$filetag]['name']) {
			$origfiletype=$filetype=trim(strtolower(strrchr($_FILES[$filetag]["name"],'.')),'.');
			$innerpath = PATH.'/images/'.($method=='temp'?'temp/': $filetag.'/');
			$dir = $innerpath.($method=='temp'?'':($itemid ? $itemid.'/':''));
			if (!is_dir($innerpath))
				if (!mkdir($innerpath, 0755, true))
					$error.='Error: directory '.$innerpath.' "can\'t exist!" Please check with your Webmaster.';
			if (!is_dir($dir)) 
				if (!mkdir($dir, 0755, true)) 
					$error.= 'Error: directory '.$dir.' "can\'t exist!" Please check with your Webmaster.';
			if (!$imageinfo = getimagesize($_FILES[$filetag]["tmp_name"])) $error .= 'Sorry, the file must be an image (jpg, gif or png).';
			else {
				if (!in_array($imageinfo['mime'],$imgtypes)) $error .= "Sorry, the image must be a jpg, gif or png.";
				else {	
					if (!strstr(str_ireplace('jpeg','jpg',$imageinfo['mime']),str_ireplace('jpeg','jpg',$filetype)))
						$filetype = str_ireplace('image/','',str_ireplace('jpeg','jpg',$imageinfo['mime']));
					if (filesize(realpath($_FILES[$filetag]['tmp_name'])) > MAXIMGFILESIZE) $error .= 'Sorry, the file must be smaller than '.format_size(MAXIMGFILESIZE);
					else {
						$getfilename = substr_replace(str_replace(' ','_',$_FILES[$filetag]['name']),$filetype,(strlen(basename($_FILES[$filetag]['name'])) - strlen($origfiletype)));
						$canDo = false;
						if (!$error) {
							do {
								if (file_exists($dir.$filename)) //rename if so	
									$filename = ($ctr++).$getfilename;
								else $canDo = true;
							} while (!$canDo);
							list($width,$height)=$imageinfo;
							@move_uploaded_file($_FILES[$filetag]['tmp_name'],$dir.$filename);
							
							list($maxwidth,$maxheight) = function_exists('getdefinedimagesize') ? getdefinedimagesize($filetag) : array(MAXIMGWIDTH,MAXIMGHEIGHT);
							if ($width > $maxwidth || $height > $maxheight) {
                                $phpThumb = new phpthumb();
                                $phpThumb->setSourceFilename($dir.$filename);
                                $phpThumb->setParameter('w', $maxwidth);
                                $phpThumb->setParameter('h', $maxheight);
                                $phpThumb->setParameter('ar', 'x');
                                $phpThumb->setParameter('q', 90);
                                if (!$phpThumb->GenerateThumbnail()) {
                                    $error = 'Could not resize image! Please contact administrator.';
                                } else {
                                    if (!$phpThumb->RenderToFile($dir.$filename)) {
                                        $error = 'Could not overwrite file after resizing - please contact administrator.';
                                    }
                                }
                                
								/*$thumb = new Thumbnail($dir.$filename);
								$thumb->resize($maxwidth,$maxheight);
								$thumb->save($dir.$filename,90);*/
                                
							} 
							
							if (in_array($filetype,array('jpg','jpeg'))) {
								if (!($jh = testjpeg($dir.$filename))) {
									$imagedata = getimagesize($dir.$filename);
								  $width = $imagedata[0];
								  $height = $imagedata[1];
								  $im2 = ImageCreateTrueColor($width, $height);
								  $image = ImageCreateFromJpeg($dir.$filename);
								  imageCopyResampled($im2, $image, 0, 0, 0, 0, $width, $height, $imagedata[0], $imagedata[1]);
								  @imagejpeg($im2, $dir.$filename);
								}
							}
							
							//get tag query
							if ($method == 'insert' || $method == 'update')
								$mysqlquery[] = basename($_FILES[$filetag]['name']) ? '`'.$filetag.'` = \''.$mysqli->real_escape_string($filename).'\'' : '' ;
							elseif ($method == 'temp')
								$mysqlquery[] = basename($_FILES[$filetag]['name']) ? $filename : '' ;
							
						}
					}
				}
			}
			if ($error) break;
		}
	}
	if ($error) return $error; //string
	else return $mysqlquery; //array
}

function getdefinedimagesize($filetag) {
	switch ($filetag) {
		case 'avatar': 
            return array(250,200);
        case 'image': 
            if (isset($_POST) && isset($_POST['fullsize']) && $_POST['fullsize'] == "1") {
                return array(MAXIMGWIDTH,MAXIMGHEIGHT);
            } else {
                return array(1024,768);
            }
		default: 
            return array(MAXIMGWIDTH,MAXIMGHEIGHT);
	}
}


//displays information from the object and kills the page
function death($val) {
	echo '<pre>';
	var_dump($val);
	echo '</pre>';
	die();
}

if (!function_exists('money_format')) {
	function money_format($format, $number) {
		return number_format($number,2);
	}
}

function mysql_single($query,$linefile='') {
	global $mysqli;
	$result = $mysqli->query($query) or trigger_error(($linefile ? $linefile:(__LINE__.__FILE__)).$mysqli->error,E_USER_ERROR);
	return $result->fetch_object();
}

function strstrbi($haystack, $needle, $before_needle=FALSE, $include_needle=TRUE, $case_sensitive=FALSE) {
	if($case_sensitive) {
		$pos=strpos($haystack,$needle);
	} else {
		$pos=strpos(strtolower($haystack),strtolower($needle));
	}
	if($pos===FALSE) return FALSE;
	if($before_needle==$include_needle) $pos+=strlen($needle);
	if($before_needle) return substr($haystack,0,$pos);
	return substr($haystack,$pos);
}

//date is either false or mysql date, idsuffix means the div is called set.$idsuffix, querystring output is regdate
//$past is between now and # years in the past, same for future.
function jsCalendar($date=false,$idsuffix='date',$past=0,$future=20,$js=true) {
	$idsuffix=str_replace('[]','',$idsuffix);
	$d = $date ? explode('-',$date) : array(0,0,0);
	echo '<div id="get',$idsuffix,'" class="hasCalendar" data-id-suffix="'.$idsuffix.'">
		<select class="day">
			<option value="-1">Day:</option>';
		for ($i=1;$i<=31;$i++)
			echo '<option value="',$i,'"',($d[2]==$i ?' selected="selected"':''),'>',$i,'</option>',"\n\t\t\t";
		echo '</select>',"\n\t\t",
		'<select class="month">
			<option value="-1">Month:</option>',"\n\t\t\t";
		for ($i=1;$i<=12;$i++) 
			echo '<option value="',$i,'"',($d[1]==$i ?' selected="selected"':''),'>',date('F',strtotime(sprintf('2008-%d-01',$i))),'</option>',"\n\t\t\t";
		echo '</select>',"\n\t\t",'
		<select class="year">
			<option value="-1">Year:</option>',"\n";
		$futureyear = $d[0] > (date('Y')+1+$future) ? $d[0]:(date('Y')+1+$future);
		$pastyear=date('Y')-$past;
		for ($i=$pastyear;$i <= $futureyear;$i++)
			echo '<option value="',$i,'"',($d[0]==$i ?' selected="selected"':''),'>',$i,'</option>',"\n\t\t\t";
		echo '</select>
		<input type="hidden" id="reg',$idsuffix,'" name="',$idsuffix,'" value="',$date,'" />
	</div>
	';
}

function flipdate($dt, $seperator_in = '-', $seperator_out = '-') {
	return implode($seperator_out, array_reverse(explode($seperator_in, $dt)));
}

function generatepw() {
	return mt_rand(10000000,99999999);
}

function generateun($ufn, $uln, $uid){
	$ufn = preg_replace('/[^a-zA-Z0-9]/si', '', $ufn);
	$uln = preg_replace('/[^a-zA-Z0-9]/si', '', $uln);
	return strtolower($ufn{0}).strtolower(substr($uln,0,4)).$uid;
}

function ordinalize($number) {
	if (in_array(($number % 100),range(11,13))){
		return $number.'th';
	}else{
		switch (($number % 10)) {
			case 1:
				return $number.'st';
				break;
			case 2:
				return $number.'nd';
				break;
			case 3:
				return $number.'rd';
			default:
				return $number.'th';
			break;
		}
	}
}

function jsonparse($text,$dbl=false,$nl = true) { //dbl = double quotes, nl = include newlines,
	$text = str_replace("\n", ($nl ? '\n':''), str_replace("\r", "", str_replace("\\","\\\\",$text)));
	if ($dbl) return str_replace('"','\"',$text);
	else return str_replace('\'','\\\'',$text);
}

function tagify ($thisinsertid,$regkeywords,$module) {
	global $mysqli;
	global $session;
	if ($regkeywords) {
		$module=$mysqli->real_escape_string($module);
		$posttmpvar = trim($regkeywords);
		$posttmpvar = $posttmpvar[strlen($posttmpvar)-1] == ',' ? substr($posttmpvar,0,-1): $posttmpvar;
		$tmpkeywords = @explode(',',$posttmpvar);
		$i=0;
		foreach ($tmpkeywords as $tmp) {
			if (trim($tmp)) $keywords[] = trim($mysqli->real_escape_string($tmp));
			$i++;
			if ($i > MAXKEYWORDS) break;
		}
		foreach($keywords as $keyword) {
			$result = $mysqli->query('SELECT * FROM tTags WHERE tName LIKE \''.trim($keyword).'\'') or trigger_error(__LINE__.$mysqli->error,E_USER_ERROR);
			$row = $result->fetch_object();
			$kwinsertid=0;
			if (!in_array($keyword,@explode(',',COMMONWORDS))) {
				if (!$result->num_rows) {
					$mysqli->query('INSERT INTO tTags SET tName = \''.$keyword.'\'') or die(__LINE__.$mysqli->error);
					$kwinsertid = $mysqli->insert_id;
				} 
				
				$kwinsertid = $kwinsertid ? $kwinsertid : $row->tID;
				//check if already in artist list of words, then insert
				$result = $mysqli->query('
					SELECT * FROM tTagItems WHERE tiItem = \''.$thisinsertid.'\' AND tiItemType = \''.$module.'\' AND tiTag = \''.$kwinsertid.'\'
					') or die(__LINE__.$mysqli->error);
				if (!$result->num_rows) {
					$mysqli->query("
						INSERT INTO tTagItems SET
						tiTag = '".$kwinsertid."',
						tiItemType = '".$module."',
						tiItem = '".$thisinsertid."',
						tiUser = '".$session->userid."',
						tiAdded = NOW()
						") or die(__LINE__.$mysqli->error);
				}
			}
		}
		
		//now get list of keywords and compare them to current data
		$result = $mysqli->query('
		 	SELECT tTags.* 
		 	FROM tTagItems
		 	LEFT JOIN tTags ON (tID = tiTag)
		 	WHERE tiItem = \''.$thisinsertid.'\'
		 	AND tiItemType = \''.$module.'\'
		 	') or die(__LINE__.$mysqli->error);
		$deletearray=array();
		while ($row = $result->fetch_object())
		 	if (!in_array($row->tName,$keywords) && $row->tID > 0) 
		 		$deletearray[]=$row->tID;
		
		//remove the items that no longer exist
		if (@count($deletearray)) 
		$mysqli->query('
				DELETE FROM tTagItems 
				WHERE tiItem = \''.$thisinsertid.'\'
				AND tiItemType = \''.$module.'\'
				AND tiTag IN ('.@implode(',',$deletearray).')
				') or die(__LINE__.$mysqli->error);
	}
}


function activity($type,$method,$itemid,$forumid,$line=0,$extra=''){
	if (!class_exists('UserAgent')) include PATH.'/classes/useragent.class.php';
	$agent = new UserAgent();
	global $session;
	global $mysqli;
	list($browser,$brver) = $agent->check_browser($_SERVER['HTTP_USER_AGENT']);
	list($os,$osver) = $agent->check_os($_SERVER['HTTP_USER_AGENT']);
	$notetype = mysql_single('
		SELECT notetypeid FROM notetype WHERE name = \''.$mysqli->real_escape_string($type).'\'
		',__LINE__.__FILE__);
	$mysqli->query('
		INSERT INTO activity SET 
		userid = \''.$mysqli->real_escape_string($session->userid).'\'
		,username = \''.$mysqli->real_escape_string($session->username).'\'
		,itemid = \''.$mysqli->real_escape_string($itemid).'\'
		,forumid = \''.$mysqli->real_escape_string($forumid).'\'
		,method = \''.$mysqli->real_escape_string($method).'\'
		,type = \''.$mysqli->real_escape_string($notetype->notetypeid).'\'
		,extra = \''.$mysqli->real_escape_string($extra).'\'
		
		,ip = \''.$mysqli->real_escape_string($_SERVER['HTTP_X_FORWARD_FOR'] ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR']).'\'
		,dateline = UNIX_TIMESTAMP()
		,browser = \''.$mysqli->real_escape_string($browser).'\'
		,browserver = \''.$mysqli->real_escape_string($brver).'\'
		,os = \''.$mysqli->real_escape_string($os).'\'
		,osver = \''.$mysqli->real_escape_string($osver).'\'
		,sessionid = \''.$mysqli->real_escape_string(session_id()).'\'
		,script = \''.$mysqli->real_escape_string(basename($_SERVER['PHP_SELF'])).'\'
		,line = \''.$mysqli->real_escape_string($line).'\'
		') or trigger_error(__LINE__.$mysqli->error,E_USER_ERROR);
    return (int)$mysqli->insert_id;
}

function matchrows($mysql) {
	$mysql = str_replace('Rows matched: ','',$mysql);
	return $mysql{0} > 0 ? true : false;
}

function strstrb($h,$n){ return array_shift(explode($n,$h,2)); }
//phpinfo();exit();

function parseget( $array = NULL, $convention = '%s' ){
	$query = '';
	if( is_array($array) && count( $array ) > 0){
		if( function_exists( 'http_build_query' ) ){//is < php5.0
			$query = http_build_query( $array );
		} else {
			$query = '';
			foreach( $array as $key => $value ){
				if( is_array( $value ) ){
					$new_convention = sprintf( $convention, $key ) . '[%s]';
					$query .= parseget( $value, $new_convention );
				} else {
					$key = urlencode( $key );
					$value = urlencode( $value );
					$query .= sprintf( $convention, $key ) . "=$value&"; 
				}
			}
		}
	}
	return $query;
} 

function strip_url($item){
	$items = explode('-',$_REQUEST[$item]);
	if (isset($_REQUEST[$item]) && !is_numeric(trim($_REQUEST[$item]))) 
		$_POST[$item] = $_GET[$item] = $_REQUEST[$item] = $items[0];
		//$_POST[$item] = $_GET[$item] = $_REQUEST[$item] = substr(strrchr($_REQUEST[$item],'-'),1);
}

/*function orangify($str) {
	if (count(explode(' ',$str)) > 1) return substr($str, 0, strrpos($str, ' ')).' <span class="orange">'.substr($str,-(strlen($str)-1-strrpos($str,' '))).'</span>';
	return substr($str,0,-2).'<span class="orange">'.substr($str,strlen($str)-2).'</span>';
}*/

function getMenu($itemid=0,$forumid=0) {
	global $session;
	global $mysqli;
	echo 
		"\n\t\t\t",'<div class="menu-'.($itemid ? 'dropdown-':''),'button skin-this {\'selector\':\'#header-menu .menu-'
		,($itemid ? 'dropdown-':''),'button\'}">',"\n\t\t\t";
	//default links
	if (!$itemid) {
		echo 
			'<div class="distinct ignore"><a href="./',($session->god ? '?toggle=sfw':''),'" class="'
			,(!$forumid ? ' active':''),'">Home</a></div>',"\n\t\t\t"
		;
		if ($session->god && $session->userid != 2) {
			echo '<div class="distinct ignore"><a href="./dungeon/" class="',($forumid == 15 ? ' active':''),'">Dungeon</a></div>',"\n\t\t\t";
			/* echo 
				'<div class="distinct ignore"><a href="./?toggle=both" class="'
				,(!$forumid && !is_numeric($forumid) && $session->settings['filter-rating'] == 'both' ? ' active':'')
				,'">Alternate</a></div>',"\n\t\t\t"
			; */
		}
		
	}
	
	//$frating = in_array($session->settings['filter-rating'],array('nsfw','sfw','both')) ? $session->settings['filter-rating'] : 'sfw';
	
	if ($forumid != 3 || $itemid > 0) {
		$where = array();
		$where[] = 'forum.parentid = '.($itemid > 0 ? ' \''.$itemid.'\'':'\'15\'').'';
		$result = $mysqli->query('
			SELECT forum.*, parentForum.parentid as hasParent
			FROM forum
			LEFT JOIN forum as parentForum ON (parentForum.parentid = forum.forumid)
			'.(count($where) ? 'WHERE '.implode("\nAND ",$where): '').'
			GROUP BY forum.forumid ORDER BY forum.title
			') or trigger_error(__LINE__.$mysqli->error,E_USER_ERROR);
		while ($menu = $result->fetch_object()) {
			if ($menu->hasParent) 
				echo 
					'<div class="distinct"><a href="',urlify($menu->title),'/" class="'
					,($menu->forumid == $forumid ? ' active':''),'">',$menu->title,'</a>'
					,getMenu($menu->hasParent,$forumid),"</div>\n\t\t\t";
			else 
				echo ($itemid ? '':'<div class="distinct">'),'<a href="',urlify($menu->title),'/">',$menu->title,'</a>',($itemid ? '':'</div>'),"\n\t\t\t\t";
		}
	}
	$perms = array();
	if ($session->god) $perms[] = 3;
	if ($session->staff) $perms[] = 2;
	if ($session->admin) $perms[] = 1;
	if (!$itemid) {
		//get dungeon, admin, mods shiznit etc.
		foreach($perms as $v) {
			if ($v) {
				$where = array();
				if ($v < 3) {
					$where[] = '(forum.parentid = \'-1\' OR forum.parentid IS NULL)';
					$where[] = 'forum.forumid = \''.$v.'\'';
				} else {
					$where[] = 'forum.parentid = \''.$v.'\'';
				}
				if (($v == 3 && in_array($forumid,forumChildren(3,true))) || $v < 3) {
					$result = $mysqli->query('
						SELECT forum.*, parentForum.parentid as hasParent
						FROM forum
						LEFT JOIN forum as parentForum ON (parentForum.parentid = forum.forumid)
						'.(count($where) ? 'WHERE '.implode("\nAND ",$where): '').'
						GROUP BY forum.forumid ORDER BY forum.title
						') or trigger_error(__LINE__.$mysqli->error,E_USER_ERROR);
					while ($menu = $result->fetch_object()) {
						if ($menu->hasParent) 
							echo 
								'<div class="distinct"><a href="',urlify($menu->title),'/" class="',($menu->forumid == $forumid ? ' active':''),'">',$menu->title,'</a>'					
								,getMenu($menu->hasParent,$forumid),"</div>\n\t\t\t";
						else 
							echo ($itemid ? '':'<div class="distinct">'),'<a href="',urlify($menu->title),'/">',$menu->title,'</a>',($itemid ? '':'</div>'),"\n\t\t\t\t";
					}
				}
			}
		}
	}
	
	echo ($itemid ? '': CLEARBOTH),"\n\t\t\t</div>";
}


function forumChildren($id,$all=false) {
	global $mysqli;
	$result = $mysqli->query('
		SELECT childforum.forumid AS childid, forum.forumid
		FROM forum
		LEFT JOIN forum AS childforum ON (childforum.parentid = forum.forumid)
		WHERE forum.forumid IN ('.$mysqli->real_escape_string($id).')
		') or die(__LINE__.__FILE__.$mysqli->error);
	//$ids = $prev;
	$children = $ids = array();
	while ($row = $result->fetch_object()) {
		if ($all) {
			if ($row->childid) $children[] = $row->childid;
			$ids[] = $row->forumid;
		} else {
			if ($row->childid) $children[] = $row->childid;
			else $ids[] = $row->forumid;
		}
		//$ids = array_merge($ids,forumChildren($row->childid,$ids));
	}
	if (count($children))
		foreach ($children as $v)
			$ids = array_merge($ids,forumChildren($v,$all));
	
	return count($ids) ? array_unique($ids) : array('0');
}

function getFirstImage($str) {
	if (strstr($str,'youtube.com')) {
		$code = strstrb(strstrb(str_replace(array('v=','#'),array('',''),strstr($str,'v=')),'&'),'[/');
		if ($code && strlen($code) < 20) return 'http://img.youtube.com/vi/'.$code.'/0.jpg';
	}
	
	if (preg_match('~\[youtube\](.+?)\[/youtube\]~mi',$str,$matches)) {
		if ($matches && strlen($matches[0]) < 20) return 'http://img.youtube.com/vi/'.$matches[0].'/0.jpg';
	}
		
	
	if (preg_match('~\[(img|wrapr?)\](.+?)\[/\1\]~mi',$str,$matches)) 
		return preg_replace('/\[\/?(img|wrapr?)\]/i','',$matches[0]);
	return '';
}

function firstImage($str,$link='#',$userid='0',$usernameurl='',$avatar='',$find = true,$extra = '') {
	$prepath = 'images/phpThumb.php?w='.THUMBW.'&amp;h='.THUMBH.'&amp;zc=1&amp;';
	if ($find && $str) {
			if (strstr($str,'.png')) $prepath .= 'f=png&amp;';
			return '
			<div class="node-image skin-this {\'selector\':\'#whole .node-image\'}">
				<a href="'.($link ? $link : $str).'"><img src="'.$prepath.'src='.urlencode($str).'" alt="" /></a>
				'.($extra ? '<br />'.$extra : '').'
			</div>';
	}
	return '
		<div class="node-image skin-this {\'selector\':\'#whole .node-image\'}">'.
			(trim($avatar) ? 
			'<a href="members/'.$usernameurl.'.html"><img src="'.$prepath.(strstr($avatar,'.png') ? 'f=png&amp;':'').'src='
			.urlencode('avatar/'.$avatar).'" alt="" /></a>
			'.($extra ? '<br />'.$extra : '').'
		' :($extra ? '<div class="node-image">'.$extra.'</div>' : '
			<a href="'.($str && $str != '#' ? $str : 'members/'.$usernameurl).'.html" class="userlink"><img src="images/error.png" alt="" /></a>
			')).'
		</div>';
}

function longword($string,$limit=25,$chop=20){
	$text = explode(' ',$string);
	$s = $r = array();
	foreach($text as $key => $value) {
		$length = strlen($value);
		if($length >= $limit){
			$new = '';
			for($i=0;$i<=$length;$i+=$chop) //break up into sections of 10
				$new .= substr($value, $i, $chop).' ';
			$s[] = $value;
			$r[] = $new;
		}
	}
	return str_replace($s,$r,$string);
}

function strip_bbcode($str,$tagstoignore = '') {
	return strip_tags(str_replace(array('[',']'), array('<','>'), $str),$tagstoignore);
}

function forumid($s) {
	global $mysqli;
	$forumid = '';
	$result = $mysqli->query('SELECT title,forumid FROM forum ORDER BY title') or die(__LINE__.__FILE__.$mysqli->error);
	while ($forum = $result->fetch_object()) {
		if ($s == urlify($forum->title)) {
			$forumid = $forum->forumid;
			break;
		}
	}
	unset($result,$forum);
	if (!okcats($forumid)) $forumid = '';
	return $forumid;
}

function forumparentid($id) {
	global $mysqli;
	$forum = mysql_single('SELECT parentid FROM forum WHERE forumid = \''.$mysqli->real_escape_string($id).'\'',__LINE__.__FILE__);
	return $forum->parentid;
}

function forumtitle($id){
	global $mysqli;
	if (!$id) $id = 15;
	$f = mysql_single('SELECT title FROM forum WHERE forumid =\''.$mysqli->real_escape_string($id).'\'',__LINE__.__FILE__);
	return $f->title ? $f->title : 'Home';
}

function points($pts='0',$postid='0',$active=true) {
	global $session;
	global $mysqli;
	if ($session->userid && $active)
		$points = mysql_single('
			SELECT id,scored FROM post_thanks 
			WHERE postid = \''.$mysqli->real_escape_string($postid).'\' 
			AND userid = \''.$mysqli->real_escape_string($session->userid).'\' LIMIT 1
			',__LINE__.__FILE__);
	$inc = $session->userid ? ($active ? 'points.php?postid='.$postid.'&amp;give=1':'#') : 'register';
	$dec = $session->userid ? ($active ? 'points.php?postid='.$postid.'&amp;give=-1':'#') : 'register';
	$inactive = ' inactive" onclick="return false';
	//put these in later: &#9650; &#9660;
	return '
		<div class="teoti-points skin-this {\'selector\':\'#whole .teoti-points\'}'.($active ? '':' inactive').'" id="teoti-points-'.$postid.'">
			<a href="'.$inc.'" class="points-inc'.($points->scored > 0 ? ' isset':'').($active ? '':$inactive).'" title="Like"><!-- like --></a>
			<div class="points-bit"><span>'.($pts ? (string)$pts : '0').'</span></label></div>
			<a href="'.$dec.'" class="points-dec'.($points->scored < 0 ? ' isset':'').($active ? '':$inactive).'" title="dislike"><!-- dislike --></a>
			<div class="hidethis popup"><!-- --></div>
		</div>
	';/*<br />Point<label>s*/
}

function userlink($username,$id = 0,$hascolor=true,$class='') {
	global $mysqli;
	$user = mysql_single('
		SELECT usergroupid,username FROM user 
		WHERE userid = \''.$mysqli->real_escape_string($id).'\' OR username = \''.$mysqli->real_escape_string(trim($username)).'\'
		',__LINE__.__FILE__);
		
	/*if ($id > 0 && !$user->usergroupid)
		$user = mysql_single('
			SELECT usergroupid,username FROM user 
			WHERE username = \''.$mysqli->real_escape_string(trim($username)).'\'
			',__LINE__.__FILE__);*/
			
	if (!$username) $username = $user->username;
	//get colour classes
	$color = '';
	if ($hascolor) {
		if (in_array($user->usergroupid,explode(',',ADMINGROUPS)) && $user->usergroupid > 0) $color = '#FF0000';
		if (in_array($user->usergroupid,explode(',',MODGROUPS)) && $user->usergroupid > 0) $color = '#880088';
	}
	if (!$user->usergroupid) return $username ? $username : 'Anon';
	return '<a href="members/'.urlify($username).'.html"'.($color ? ' style="color:'.$color.'"':'').' class="userlink'.$class.'">'.$username.'</a>';
}

function ago($datefrom,$zone = '0',$suffix = true) {
  if($datefrom==0) return 'A long time ago';
  $dateto = time(); 
  $difference = $dateto - $datefrom;
  switch(true) {
		case($dateto-60 < $datefrom):
      $datediff = $difference;
      $res = ($datediff==1 ? $datediff.' second ' : $datediff.' seconds ').($suffix ? 'ago':'');
      break;
		case($dateto-60*60 < $datefrom):
			$datediff = floor($difference / 60); //ago($datefrom+(60*$datediff),$zone,false) //not necessary
      $res = ($datediff==1 ? $datediff.' minute ' : $datediff.' minutes ').($suffix ? 'ago':'');
      break;
		case($dateto-60*60*24 < $datefrom):
			$datediff = floor($difference / 60 / 60);
			$res = ($datediff==1 ? $datediff.' hour ' : $datediff.' hours ').ago($datefrom+(3600*$datediff),$zone,false).($suffix ? 'ago':'');
			break; 
    /*case(strtotime('-1 week', $dateto) < $datefrom):
			$day_difference = 1;
			while (strtotime('-'.$day_difference.' day', $dateto) >= $datefrom) $day_difference++;
			$datediff = $day_difference;
			$res = $datediff==1 ? 'yesterday' : $datediff.' days ago';
			break;
    case(strtotime('-1 month', $dateto) < $datefrom):
			$week_difference = 1;
			while (strtotime('-'.$week_difference.' week', $dateto) >= $datefrom) $week_difference++;
			$datediff = $week_difference;
			$res = $datediff==1 ? 'last week' : $datediff.' weeks ago';
			break;           
    case(strtotime('-1 year', $dateto) < $datefrom):
      $months_difference = 1;
      while (strtotime('-'.$months_difference.' month', $dateto) >= $datefrom) $months_difference++;
      $datediff = $months_difference;
      $res = $datediff==1 ? $datediff.' month ago' : $datediff.' months ago';
      break;
    case(strtotime('-1 year', $dateto) >= $datefrom):
      $year_difference = 1;
      while (strtotime('-'.$year_difference.' year', $dateto) >= $datefrom) $year_difference++;
      $datediff = $year_difference;
      $res = $datediff==1 ? $datediff.' year ago' : $datediff.' years ago';
      break;*/
    default:
    	$res = dateFormat($datefrom,$zone);
    	break;
  }
  return $res;
}

function dateFormat($date,$zone='0') {
	if (!$zone) return date(DATETIMEFORMAT,is_numeric($date) ? $date : strtotime($date));
	if (!is_numeric($date)) return date(DATETIMEFORMAT,strtotime($date.' '.($zone > 0 ? '+'.$zone : $zone).' hours')); 
	return date(DATETIMEFORMAT,strtotime((int)($zone > 0 ? '+'.$zone : $zone).' hours',$date));
}

function colheaders($type='hot',$page=1,$forumid='') {
	//generate query
	global $BOXTYPES,$session;
	global $mysqli;
	if (!$type) $type = 'hot';
	if ($session->settings['box'] != $type){
		$session->settings['box'] = $type;
		updateUserSettings($session);
	}
	$nopage = false;
	if (in_array($type,$BOXTYPES)) {
		if (!is_numeric($page)) $page = 1;
		$where = array();
		$view = array();
		$frating = in_array($session->settings['filter-rating'],array('nsfw','sfw','both')) ? $session->settings['filter-rating'] : 'sfw';
		switch ($frating) {
			case 'nsfw': $view[] = 3; break;
			case 'both': $view[] = 3;
			default: $view[] = 15;
		}
		//individual where vars
		switch($type){
			case 'hot':
				$filter = $session->settings['filter-'.$type];
				$filter = in_array($filter,array('2','7','28','0')) ? $filter:'2';
				$time = $filter ? time()-(60*60*24*$filter) : 0;
				if ($filter && $time) $where[] = 'dateline > '.$time;
				$orderby = 'ORDER BY thread_score DESC';
				break;
			case 'random':
				$where[] = 'thread_score > 50';
				$orderby = 'ORDER BY RAND()';
				$nopage = true;
				break;
			case 'sticky':
				$where[] = 'sticky = 1';
				$orderby = 'ORDER BY lastpost DESC';
				break;
			case 'scores':
				$user_score = $session->settings['filter-'.$type] == 'all' ? 'user_total_score' : 'post_thanks_thanked_times';
				$orderby = 'ORDER BY '.$user_score.' DESC';
				$username = 'username';
				$userid = 'userid';
				break;
			case 'profiles':
				$where[] = 'threadtype = 1 AND visible = 1';
				$user_score = 'thread_score';
				$username = 'postusername';
				$userid = 'postuserid';
				$orderby = 'ORDER BY thread.dateline DESC';
				break;
		}
		
		//queries & regular vars
		switch(true) {
			case in_array($type,array('hot','random','sticky')) :
				$where[] = 'forumid IN('.implode(',',forumChildren(intval($forumid) > 0 ? intval($forumid) : implode(',',$view))).')';
				$where[] = 'visible = 1';
				$pageSel = 'threadid';
				$query = '
					SELECT %s 
					FROM thread 
					'.(count($where) ? 'WHERE '.implode("\nAND ",$where):'').'
					'.$orderby.'
					LIMIT %s,%s
					';
				break;
			case $type == 'scores':
				$pageSel = 'userid';
				//$where[] = 'usergroupid NOT IN ('.ADMINGROUPS.','.MODGROUPS.')';
				$query = '
					SELECT %s 
					FROM user
					'.(count($where) ? 'WHERE '.implode("\nAND ",$where):'').'
					'.$orderby.'
					LIMIT %s,%s
					';
				break;
			case $type == 'profiles':
				$pageSel = 'thread.postuserid';
				$query = '
					SELECT %s
					FROM thread
					'.(count($where) ? 'WHERE '.implode("\nAND ",$where):'').'
					'.$orderby.'
					LIMIT %s,%s';
				break;
		}
		//get the shiznit
		$result = $mysqli->query(sprintf($query,'*',(($page-1)*COLHEADLIMIT),COLHEADLIMIT)) or die(__LINE__.__FILE__.$mysqli->error);
		if (!$nopage && $result->num_rows) $nextbool = true;
		?>
		<div>
		<? if ($result->num_rows) {
			while ($header = $result->fetch_object()) {
				switch (true){
					case in_array($type,array('hot','random','sticky')) : 
						$forumtitle = forumtitle($header->forumid);
						$urlft = urlify($forumtitle);
						?>
					<div class="col-head-bit clearfix">
						<div class="col-head-icon">
							<a href="<?= $urlft,'/',urlify($header->title,$header->threadid)?>.html" class="icon-<?= $urlft ?>">
								<img src="images/blank.gif" alt="<?= $forumtitle ?>" />
							</a>
						</div>
						<div class="col-head-points light skin-this {'selector':'#whole .light'}"><?= number_format($header->thread_score) ?></div>
						<div class="col-head-link with-icon">
							<a href="<?= $urlft,'/',urlify($header->title,$header->threadid)?>.html">
								<?= $header->title ?>
							</a>
						</div>
					</div>
					<? break;
					case in_array($type,array('profiles')):
					$forumtitle = forumtitle($header->forumid);
					$urlft = urlify($forumtitle);
					?>
					<div class="col-head-bit clearfix">
						<div class="col-head-icon">
							<a href="<?= $urlft,'/',urlify($header->title,$header->threadid)?>.html" class="icon-<?= $urlft ?>">
								<img src="images/blank.gif" alt="<?= $forumtitle ?>" />
							</a>
						</div>
						<div class="col-head-points light skin-this {'selector':'#whole .light'}"><?= number_format($header->thread_score) ?></div>
						<div class="col-head-link with-icon">
							<a href="<?= $urlft,'/',urlify($header->title,$header->threadid)?>.html">
								<?= $header->title ?>
							</a>
							<?= userlink($header->$username,$header->$userid) ?>
						</div>
					</div>
					<? break;
					case in_array($type,array('scores')): ?>
					<div class="col-head-bit clearfix">
						<div class="col-head-points light skin-this {'selector':'#whole .light'}">
							<?= number_format(intval($header->$user_score)) ?>
						</div>
						<div class="col-head-link">
							<?= userlink($header->$username,$header->$userid) ?>
						</div>
					</div>
					<? break;
				} 
			}
			if (!$nopage){?>
				<div class="clearfix">
					<div class="half teoti-button skin-this {'selector':'#whole .teoti-button'}">
						<? if ($page > 1) { ?><a href="<?= STRIP_REQUEST_URI,(($page-1) > 1 ? '?hpage='.($page-1):'') 
							?>" class="paginate {'forumid':'<?= $forumid ?>','boxtype':'<?= $type ?>','page':'<?= $page-1 ?>'}">Prev</a>
						<? } else {?>&nbsp;<? } ?>
					</div>
					<div class="half alignright teoti-button last-left skin-this {'selector':'#whole .teoti-button'}">
						<? if ($nextbool) { ?><a href="<?= STRIP_REQUEST_URI ?>?hpage=<?= 
							$page+1 ?>" class="paginate {'forumid':'<?= $forumid ?>','boxtype':'<?= $type ?>','page':'<?= 
							$page+1 ?>'}">Next</a>
						<? } else {?>&nbsp;<? } ?>
					</div>
				</div>
			<? }
		} else {?>
			<div class="col-head-link">No Results</div>
	<?}?>
		</div>
	<?}
}

function usergroupname($usergroupid) {
	switch (true){
		case in_array($usergroupid,explode(',',ADMINGROUPS)):
			return 'Admin';
		case in_array($usergroupid,explode(',',MODGROUPS)):
			return 'Mod'; 
		case in_array($usergroupid,explode(',',BANNEDGROUPS)):
			return 'Banned'; 
		case $usergroupid == 10:
			return 'God';
		case $usergroupid == 12:
			return 'Uber God';
	}
	return 'Registered User';
}

function userip() {
	return $_SERVER['HTTP_X_FORWARD_FOR'] ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'];
}

//get array of forums the user can use. compare array id or just return the array
function okcats($id = 0) {
	global $session;
	$okcats = forumChildren(15,true);
	if ($session->god) $okcats = array_merge($okcats,forumChildren(3,true));
	if ($session->staff) $okcats = array_merge($okcats,forumChildren(2,true));
	if ($session->admin) $okcats = array_merge($okcats,forumChildren(1,true));
	if ($id > 0) return in_array((int)$id,$okcats);
	return $okcats;
}


function notify($type,$id,$extra='',$group='') {
	global $session;
	global $mysqli;
	if ($type && $id && $session->userid) {
		$userids = array($session->userid);
		$notetype = mysql_single('SELECT notetypeid FROM notetype WHERE name = \''.$mysqli->real_escape_string($type).'\'',__LINE__.__FILE__);
		if ($notetype->notetypeid) {
			$time = time();
			switch($type){
				case 'newpost':
					//get subscribed user list excluding the poster
					$p = mysql_single('
						SELECT threadid FROM post WHERE postid = \''.$mysqli->real_escape_string($id).'\'
						',__LINE__.__FILE__);
					$result = $mysqli->query('
						SELECT userid FROM subscribethread 
						WHERE threadid = \''.$mysqli->real_escape_string($p->threadid).'\' 
						AND userid != \''.$mysqli->real_escape_string($session->userid).'\'
						') or die(__LINE__.__FILE__.$mysqli->error);
					while ($st = $result->fetch_object()) {
						//if notification is not in user ignore list then insert notification
						if (!mysql_single('
							SELECT userid FROM usernotetype 
							WHERE notetypeid = \''.$mysqli->real_escape_string($notetype->notetypeid).'\' 
							AND userid = \''.$mysqli->real_escape_string($st->userid).'\'
							',__LINE__.__FILE__)) {
							$userids[] = $st->userid;
							$mysqli->query('
								INSERT INTO notification SET 
								userid = \''.$mysqli->real_escape_string($st->userid).'\'
								,fromuserid = \''.$mysqli->real_escape_string($session->userid).'\'
								,type = \''.$mysqli->real_escape_string($notetype->notetypeid).'\'
								,itemid = \''.$mysqli->real_escape_string($id).'\'
								,dateline = \''.$mysqli->real_escape_string($time).'\'
								,extra = \''.$mysqli->real_escape_string($extra).'\'
								,`group` = \''.$mysqli->real_escape_string($p->threadid).'\'
								') or die(__LINE__.__FILE__.$mysqli->error);
						}
					}
					break;
				case 'likedislike':
				case 'extrapoints':
					if ($p = mysql_single('SELECT userid FROM post WHERE postid = \''.$mysqli->real_escape_string((int)$id).'\'',__LINE__.__FILE__)){
						//check they even want this notification first
						if (!mysql_single('
							SELECT userid FROM usernotetype 
							WHERE notetypeid = \''.$mysqli->real_escape_string($notetype->notetypeid).'\' 
							AND userid = \''.$mysqli->real_escape_string($p->userid).'\'
							',__LINE__.__FILE__)) {
							$userids[] = $p->userid;
							//check like/dislike exists
							if (!$n = mysql_single('
								SELECT noteid FROM notification 
								WHERE itemid = \''.$mysqli->real_escape_string($id).'\' 
								AND fromuserid = \''.$mysqli->real_escape_string($session->userid).'\'
								AND type = \''.$mysqli->real_escape_string($notetype->notetypeid).'\'
								',__LINE__.__FILE__)) {
								$mysqli->query('
									INSERT INTO notification SET 
									userid = \''.$mysqli->real_escape_string($p->userid).'\'
									,fromuserid = \''.$mysqli->real_escape_string($session->userid).'\'
									,type = \''.$mysqli->real_escape_string($notetype->notetypeid).'\'
									,itemid = \''.$mysqli->real_escape_string((int)$id).'\'
									,dateline = \''.$mysqli->real_escape_string($time).'\'
									,extra = \''.$mysqli->real_escape_string($extra).'\'
									,`group` = \''.$mysqli->real_escape_string((int)$id).'\'
									') or die(__LINE__.__FILE__.$mysqli->error);
							} else {
								//update the previous like/dislike to the new setting
								$mysqli->query('
									UPDATE notification SET 
									extra = \''.$mysqli->real_escape_string($extra).'\'
									,dateline = \''.$mysqli->real_escape_string($time).'\'
									WHERE noteid = \''.$mysqli->real_escape_string($n->noteid).'\'
									') or die(__LINE__.__FILE__.$mysqli->error);
							}
						}
					}
					break;
				case 'conversation':
					
					//if user is in conversation and is not the user in session then...
					$result = $mysqli->query('
						SELECT userid FROM pm WHERE pmtextid = \''.$mysqli->real_escape_string($id).'\'
						AND userid != \''.$mysqli->real_escape_string($session->userid).'\'
						') or die(__LINE__.__FILE__.$mysqli->error);
					while ($st = $result->fetch_object()) {
						//check they even want this notification first
						if (!mysql_single('
							SELECT userid FROM usernotetype 
							WHERE notetypeid = \''.$mysqli->real_escape_string($notetype->notetypeid).'\' 
							AND userid = \''.$mysqli->real_escape_string($st->userid).'\'
							',__LINE__.__FILE__)) {
								
							$userids[] = $st->userid;
							//if the person hasn't been notified that there are new messages by this person yet..
							if (!mysql_single('
								SELECT noteid FROM notification 
								WHERE userid = \''.$mysqli->real_escape_string($st->userid).'\'
								AND fromuserid = \''.$mysqli->real_escape_string($session->userid).'\'
								AND itemid = \''.$mysqli->real_escape_string($id).'\'
								AND type = \''.$mysqli->real_escape_string($notetype->notetypeid).'\'
								',__LINE__.__FILE__)) {
								//insert notification
								$mysqli->query('
									INSERT INTO notification SET 
									userid = \''.$mysqli->real_escape_string($st->userid).'\'
									,fromuserid = \''.$mysqli->real_escape_string($session->userid).'\'
									,type = \''.$mysqli->real_escape_string($notetype->notetypeid).'\'
									,itemid = \''.$mysqli->real_escape_string($id).'\'
									,dateline = \''.$mysqli->real_escape_string($time).'\'
									,extra = \''.$mysqli->real_escape_string($extra).'\'
									,`group` = \''.$mysqli->real_escape_string((int)$id).'\'
									') or die(__LINE__.__FILE__.$mysqli->error);
							}
						}
					}
					break;
				case 'newblog':
					
					break;
			}
			
			updateUserNotifications($userids);
			
		}
	}
}

function updateUserNotifications($userids = array()){
	global $mysqli;
	$sessiononly = false;
	if (!$userids) {
		global $session;
		$userids = array($session->userid);
		$sessiononly = true;
	}
	
	if ($userids){
		foreach ($userids as $userid) {
			$notifications = mysql_single('SELECT COUNT(DISTINCT `group`) as `num` FROM notification WHERE userid = \''.$mysqli->real_escape_string($userid).'\'',__LINE__.__FILE__);
			
			if ($sessiononly) $session->notifications = $notifications->num;
			
			$mysqli->query('
				UPDATE user SET 
				notifications = \''.$mysqli->real_escape_string($notifications->num).'\'
				WHERE userid = \''.$mysqli->real_escape_string($userid).'\'
				') or die(__LINE__.__FILE__.$mysqli->error);
		}
	}
}

function cleanNodePara($str) {
	//~\[([^\]]+?)(=[^\]]+?)?\](.+?)\[/\1\]~mi
	//first remove urls, then bbcode tags, then split long words up.
	//'/([^\s]{25})/mi';$r = '$1 ' //longwords
	$search = array('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i','/\s+/');
	$str = strip_bbcode($str);
	$str = preg_replace($search,array('',' '),$str);
	
	//$str = htmlspecialchars($str);
	$str = str_replace(array("\r","\n"),array('',' '),$str);
	$str = longword($str);
	$str = iconv('UTF-8','UTF-8//IGNORE',$str);
	//$str = convert_to_utf8($str);
	//$str = preg_replace('/(\[quote\].*)+(\[\/quote\].*)+/','',$str);
	//$str = utf8_encode($str);
	return $str;
}

function updateHistory($itemid,$type=1) {
	global $mysqli;
	global $session;
	if ($history = mysql_single('
		SELECT id FROM history WHERE itemid = \''.$mysqli->real_escape_string($itemid).'\' AND type = \''.$mysqli->real_escape_string($type).'\' AND userid = \''.$mysqli->real_escape_string($session->userid).'\'
		',__LINE__.__FILE__)) {
		$query = 'UPDATE history SET dateline = \''.$mysqli->real_escape_string(time()).'\' WHERE id = \''.$mysqli->real_escape_string($history->id).'\'';
	} else {
		$query = 'INSERT INTO history SET 
		userid = \''.$mysqli->real_escape_string($session->userid).'\'
		,itemid = \''.$mysqli->real_escape_string($itemid).'\'
		,dateline = \''.$mysqli->real_escape_string(time()).'\'
		,type = \''.$mysqli->real_escape_string($type).'\'';
	}
	$mysqli->query($query) or die(__LINE__.__FILE__.$mysqli->error);
	//trim history by fixed amount
	$result = $mysql->query('
		SELECT id FROM history 
		WHERE userid = \''.$mysqli->real_escape_string($session->userid).'\'
		ORDER BY dateline DESC LIMIT '.HISTORYLIMIT.',999999999
		') or die(__LINE__.__FILE__.$mysqli->error);
	$historyids = array();
	while ($history = $result->fetch_object()) $historyids[] = $history->id;
	if ($historyids) $mysqli->query('DELETE FROM history WHERE id IN ('.implode(',',$historyids).')') or die(__LINE__.__FILE__.$mysqli->error);
}


function updateUserSettings($session){
	global $mysqli;
	if ($session->userid) {
		$mysqli->query('
			UPDATE user 
			SET settings = \''.$mysqli->real_escape_string(serialize($session->settings)).'\' 
			WHERE userid = \''.$mysqli->real_escape_string($session->userid).'\'
			') or die(__LINE__.__FILE__.$mysqli->error);
	} else $_SESSION['settings'] = serialize($session->settings);
}

function getThreadType($int){
	switch($int){
		case 1:
			return 'blog';
			break;
		default:
			return 'thread';
	}
}
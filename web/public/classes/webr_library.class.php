<?

class webr_library {
	var $type = 0;
	var $error = '';
	var $filepath='';
	var $ext='';
	var $typename='';
	var $dir='';
	var $method='update';
	
	var $islang = array();
	
	const MARKER = 0xff;
	const SOI = 0xd8;
	const EOI = 0xd9;
	const JFIF = 0xe0;
	const APP = 0xe0;
	const QUANT = 0xdb;
	const HUFF = 0xc4;
	const SOF0 = 0xc0;
	const SOF1 = 0xc1;
	const SOF2 = 0xc2;
	const SOS = 0xda;
	const ED = 0xed;
	const EE = 0xee;
	const DD = 0xdd;
	
	function u16($fp) {  
		$res = ord(fread($fp, 1)) << 8;
	  $res += ord(fread($fp, 1));
	  return $res;
	}
	
	function jpeghdr($fp) {  
		if(ord(fread($fp, 1)) != self::MARKER || ord(fread($fp, 1)) != self::SOI)
	    return false;
	  $length = 2;
	  $res = false;
	  while(ord(fread($fp, 1)) == self::MARKER) {  
	  	$cod = ord(fread($fp, 1));
	    if ($cod == self::SOS)
	      break;
	    $len = $this->u16($fp);
	    if ($cod == self::QUANT || $cod == self::HUFF || $cod == self::DD || $cod == self::SOF0 || $cod == self::SOF1)
	      $length += 2 + $len;
	    if ($cod == self::SOF0 || $cod == self::SOF1) {  
	    	$len -= 5;
	      $res = array();
	      $res["prec"] = ord(fread($fp, 1));
	      $res["height"] = $this->u16($fp);
	      $res["width"] = $this->u16($fp);
	    }  
	    else if ($cod == self::SOF2)
	      return false;
	    else if ($cod != self::JFIF && $cod != self::QUANT && $cod != self::HUFF && $cod != self::DD && ($cod & self::APP) != self::APP)
	      return false;
	    fseek($fp, $len-2, 1);
	  }
	  if ($cod == self::SOS) {  
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
	  $res = $this->jpeghdr($fp);
	  fclose($fp);
	  return $res;
	}
	
	function readjpeg($fp) {  
		rewind($fp);
	  if (ord(fread($fp, 1)) != self::MARKER || ord(fread($fp, 1)) != self::SOI)
	    return false;
	  $buf = chr(self::MARKER) . chr(self::SOI);
	  while (ord(fread($fp, 1)) == self::MARKER) {  
	  	$cod = ord(fread($fp, 1));
	    if ($cod == self::SOS)
	      break;
	    if ($cod == self::QUANT || $cod == self::HUFF || $cod == self::DD || $cod == self::SOF0 || $cod == self::SOF1) { 
	    	$len = $this->u16($fp);
	      $buf .= chr(self::MARKER) . chr($cod);
	      $buf .= chr($len >> 8);
	      $buf .= chr($len & 0xff);
	      $buf .= fread($fp, $len-2);
	    } else {  
	    	$len = $this->u16($fp);
	      fseek($fp, $len-2, 1);
	    }
	  }
	  $buf .= chr(self::MARKER).chr($cod);
	  while($next = fread($fp, 4096))
	    $buf .= $next;
	  return $buf;
	}
	
	function webr_library() {
		if (!class_exists('Thumbnail')) die('Thumbnail class not included!');
	}
	
	function upload($filefield) {
		if ($_FILES[$filefield]['name']) { 
			list($this->type,$this->error) = $this->definetype($filefield);
			if ($this->type && !$this->error) 
				list($this->error) = $this->checkheader($filefield);
			if ($this->type && !$this->error) 
				list($querybit,$this->error) = $this->movefile($filefield,$this->type);
			return array($this->error,($this->error ? '':$querybit));
		} else return array('','');
	}
	
	function definetype($filefield) {
		$result = mysql_query('
			SELECT * FROM eLibraryTypes 
			WHERE ltIsExternal = 0 AND ltExtensions != \'\'
			ORDER BY ltName ASC
			') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
		$this->ext = trim(strtolower(strrchr($_FILES[$filefield]['name'],'.')),'.');
		$type=0;
		while ($row = mysql_fetch_object($result)) {
			if (trim($row->ltExtensions)){
				$exts = explode(',',$row->ltExtensions);
				foreach ($exts as $k => $v) {
					if ($this->ext == trim($v)) {
						$type = $row->ltID; 
						$this->typename = $row->ltName;
						$this->dir = $row->ltDirectory ? $row->ltDirectory:str_replace(' ','',strtolower($row->ltName));
						break 2;
					}
				}
			}
		}
		
		if ($type == 0) 
			$error = 'Sorry, the extension ('.$this->ext.') of the file ('.basename($_FILES[$filefield]['name']).') that you uploaded does not match the list of extensions in our database and therefore this file was not uploaded.';
		
		if (!$error) $error = $this->checkheader($filefield);
		
		return array($type,$error);
	}
	
	function checkheader($filefield) {
		switch (strtolower($this->typename)) {
			case 'images':
				if (!$imageinfo = getimagesize($_FILES[$filefield]["tmp_name"])) return 'Sorry, this image must belong to one of the following extensions: '.@implode($extensions);
				else {
					if ($imageinfo['mime'] != 'image/'.($this->ext == 'jpg'? 'jpeg':$this->ext)) 
						return 'Sorry, this image is invalid - it may contain malformed data or has the wrong extension.';
				}
				return ''; 
			case 'flash': return '';
			case 'audio': return '';
			case 'documents': return '';
			case 'other': return '';
		}
	}
	
	function movefile ($filefield,$type) { 
		//check paths can/do exist; create if don't
		$path = '';
		if (!$_SESSION['uid']) $error = 'You need to be logged in before you can go any further.';
		else {
			$path = $_SERVER['DOCUMENT_ROOT'].'/clientfiles/'.$_SESSION['uid'].'/file/'.$this->dir.'/';
			if (!is_dir($path))
				if (!mkdir($path, 0775, true))
					$error ='Error: directory '.$path.' "can\'t exist!" Please check with your Webmaster.';
		}
		
		//check file size
		if (filesize(realpath($_FILES[$filefield]['tmp_name'])) > MAXFILESIZE) $error = 'Sorry, the file is too big! Must be smaller than '.format_size(MAXFILESIZE);
		
		//resize if image, else just upload
		if (!$error) {
			$origfilename = $filename = basename($_FILES[$filefield]['name']);
			$canDo = false;
			$ctr=0;
			do {
				if (file_exists($path.$filename)) //rename if so	
					$filename = ($ctr++).$origfilename;
				else $canDo = true;
			} while (!$canDo);
			$this->filepath = $path.$filename;
			
			@move_uploaded_file($_FILES[$filefield]['tmp_name'],$this->filepath);
			
			if ($type == 1 && in_array($this->ext,array('jpg','jpeg'))) {
				if (!($jh = $this->testjpeg($this->filepath))) {
					$imagedata = getimagesize($this->filepath);
				  $width = $imagedata[0];
				  $height = $imagedata[1];
				  $im2 = ImageCreateTrueColor($width, $height);
				  $image = ImageCreateFromJpeg($this->filepath);
				  imageCopyResampled($im2, $image, 0, 0, 0, 0, $width, $height, $imagedata[0], $imagedata[1]);
				  @imagejpeg($im2, $this->filepath);
				}
			}
			
			/*if ($this->dir == 'image') {
				list($width,$height)=getimagesize($_FILES[$filefield]["tmp_name"]);
				$thumb = new Thumbnail($this->filepath);
				//get max width/height
				list($maxwidth,$maxheight) = getdefinedimagesize($filefield);
				if ($width > $maxwidth || $height > $maxheight) $thumb->resize($maxwidth,$maxheight);
				$thumb->save($this->filepath,90); 
			}*/
			
			//get tag query
			
			if ($this->method == 'insert' || $this->method == 'copy')
				$querybit = (basename($_FILES[$filefield]['name'])) ? "`".$filefield."` = '".mysql_real_escape_string($filename)."'" : '' ;
			elseif ($this->method == 'update')
				$querybit = (basename($_FILES[$filefield]['name'])) ? "`".$filefield."` = '".mysql_real_escape_string($filename)."'" : '' ;
			elseif ($this->method == 'temp')
				$querybit = (basename($_FILES[$filefield]['name'])) ? $filename : '' ;
			
		}
		
		return array(($querybit ? $querybit : ''),$error);
	}
	
}

<?

class SkinGen {
	
	public	 	$skinid = 0; //id of the skin you are using/managing
	public 		$selectorGroups = array();
	private 	$skintags = array();
	public		$prependCSS = '';
	public		$isadmin = false;
	public		$gatherStyleElements = false;
	private		$author = 
'/***********************************************
************************************************

	Author: Mark Elphinstone-Hoadley
	Company: t-six.com
	Copyright: 2011: Mark Elphinstone-Hoadley / t-six.com

************************************************
************************************************/';
	
	public function __construct($skinid = 0) {
		if ($skinid) $this->skinid = $skinid;
		
		$result = mysql_query('
			SELECT * FROM skintags ORDER BY id ASC
			') or die(__LINE__.__FILE__.mysql_error());
		while ($st = mysql_fetch_object($result)) 
			$this->skintags[$st->skintag] = array( 'id' => $st->id, 'csstag' => $st->csstag, 'unit'=>$st->unit );
	}
	
	public function buildSelectorGroups($selector='',$skinselectorparent=0){
		
		if ($selector) {
			$result = mysql_query('
				SELECT skinselectors.*
				FROM skinselectors 
				WHERE skinselectors.selector = \''.mysql_real_escape_string($selector).'\'
				LIMIT 1
				') or die(__LINE__.__FILE__.mysql_error());
		} else {
			$result = mysql_query('
				SELECT skinselectors.*, childselectors.parent AS hasChild 
				FROM skinselectors 
				LEFT JOIN skinselectors AS childselectors ON (childselectors.parent = skinselectors.id)
				WHERE skinselectors.parent = \''.mysql_real_escape_string($skinselectorparent ? $skinselectorparent :'0').'\'
				GROUP BY skinselectors.id
				ORDER BY `order` ASC
				') or die(__LINE__.__FILE__.mysql_error());
		}
		
		while ($skinselector = mysql_fetch_object($result)) {
			$defaults = explode("\n",str_replace("\r",'',$skinselector->defaults));
			$variables = explode(',',$skinselector->variables);
			$multigroupnames = $groupnames = $skingroups = array();
			
			$selobj = mysql_single('
				SELECT values FROM skinvalues 
				WHERE skin = \''.mysql_real_escape_string($this->skinid).'\' AND selector = \''.mysql_real_escape_string($skinselector->selector).'\'
				',__LINE__.__FILE__);
			
			$selectorobject = unserialize($selobj->values);
						
			foreach ($defaults as $default) {
				list($skintag,$defaultvalueattr) = explode('=',$default,2);
				list($defaultvalue,$groupname,$range,$affects) = explode('|',$defaultvalueattr);
				
				$skintagvalue = $selectorobject && $selectorobject[$skintag] ? $selectorobject[$skintag] : $defaultvalue;
				$skintagunit = $this->skintags[$skintag]['unit'];
				
				//if selector is rounded then split any border csstags up for placement in top and bottom outer layers
				$multigroupnames = array();
				if (in_array('rounded-corners',$variables)) {
					switch ($skintag) {
						case 'border-color':
						case 'border-width':
							$multigroupnames = array(
								array( 'csstag' => str_replace('border-','border-left-',$this->skintags[$skintag]['csstag']), 'groupname'=> '-inner' ),
								array( 'csstag' => str_replace('border-','border-right-',$this->skintags[$skintag]['csstag']), 'groupname'=> '-inner' ),
								array( 'csstag' => str_replace('border-','border-top-',$this->skintags[$skintag]['csstag']), 'groupname'=> ' .top-inner' ),
								array( 'csstag' => str_replace('border-','border-bottom-',$this->skintags[$skintag]['csstag']), 'groupname'=> ' .bottom-inner' ),
							);
							if (!in_array('-inner',$groupnames)) $groupnames[] = '-inner';
							if (!in_array(' .top-inner',$groupnames)) $groupnames[] = ' .top-inner';
							if (!in_array(' .bottom-inner',$groupnames)) $groupnames[] = ' .bottom-inner';
							break;
						case 'background-color':
							$multigroupnames = array(
								array( 'csstag' => $this->skintags[$skintag]['csstag'], 'groupname'=> ' .top-inner' ),
								array( 'csstag' => $this->skintags[$skintag]['csstag'], 'groupname'=> ' .bottom-inner' ),
								array( 'csstag' => $this->skintags[$skintag]['csstag'], 'groupname'=> '-inner' ),
							);
							if (!in_array(' .top-inner',$groupnames)) $groupnames[] = ' .top-inner';
							if (!in_array(' .bottom-inner',$groupnames)) $groupnames[] = ' .bottom-inner';
							break;
					}
				} 
				
				
				if (!$multigroupnames) $multigroupnames = array( array( 'csstag' => $this->skintags[$skintag]['csstag'], 'groupname'=> $groupname ) );
				
				if (!in_array($groupname,$groupnames)) $groupnames[] = $groupname;
				
				
				foreach ($multigroupnames as $multigroupname) {
					foreach ($groupnames as $k => $gn) {
						if ($gn == $multigroupname['groupname']) {
							$skingroups[$k][] = array( 
								'skintag'=> $skintag, 
								'csstag' => $multigroupname['csstag'], 
								'groupname' => $multigroupname['groupname'],
								'unit' => $skintagunit, 
								'affects' => $affects,
								'range' => $range,
								'value' => $skintagvalue,
							);
							break;
						}
					}
				}
			}
			
			
			//now sort the groups into an easy to loop through list
			$skinlist = array();
			foreach ($skingroups as $skingroup) {
				foreach ($skingroup as $skinlistitem) { 
					$skinlist[] = $skinlistitem;
				}
			}
			
			//after skin groups have been built roll through group names adding them to the style array
			$grouped = array( 
				'id' => $skinselector->id,
				'selector' => $skinselector->selector,
				'name' => $skinselector->name,
				'skinlist' => $skinlist,
				'variables' => $variables,
				'extras' => str_replace("\r",'',$skinselector->extras), 
				'custom' => $skinselector->custom,
				'parents' => $this->getParents($skinselector->parent),
			);
			
			if ($selector) return $grouped;
			else {
				$this->selectorGroups[] = $grouped;
				
				if ($skinselector->hasChild && !$selector) {
					$this->buildSelectorGroups($selector,$skinselector->id);
				}
			}
		}
	}
	
	public function buildCSS() {
		$affects = $trianglesetting = $roundedsetting = array();
		$roundeditems = array('.top-left','.top-right','.bottom-left','.bottom-right');
		$css = $lastskinlistname = '';
		if (!$this->gatherStyleElements) $css = "@charset \"utf-8\";\n".$this->author."\n\n".$this->prependCSS;
		if ($this->selectorGroups) {
			foreach ($this->selectorGroups as $selectorgroup) {
				if ($this->gatherStyleElements) $css .= '<style type="text/css" id="style-element-'.$selectorgroup['id']."\" class=\"style-element\">\n";
				$lastskinlistname = '';
				$affects = $trianglesetting = $roundedsetting = $groupnames = array();
				$css .= "\n/*".$selectorgroup['name'].'*/';
				$triangle = $selectorgroup['selector'] == '#whole .teoti-points' ? true : false;
				//get all group names first
				foreach ($selectorgroup['skinlist'] as $k => $skinlist) {
					if (!$k || $skinlist['groupname'] != $lastskinlistname) {
						if ($skinlist['groupname'] != $lastskinlistname && $k) $css .= "}";
						$groupnames = explode(',',$skinlist['groupname']);
						$css .= "\n";
						foreach ($groupnames as $n => $groupname) $css .= ($n ? ', ':'').$selectorgroup['selector'].$groupname;
						//add the extras...
						if ($selectorgroup['extras']) {
							$extras = explode("\n",$selectorgroup['extras']);
							foreach ($extras as $extra) {
								
								list($exsel,$exgroup) = explode('|',$extra);
								if ($exgroup == $skinlist['groupname']) $css .= ', '.$exsel;
							}
						}
						
						$css .= ' { ';
					}
					
					//now build the css tags
					$css .= $this->formatCSSRule($skinlist);//set css tag
					
					
					if ($triangle && in_array($skinlist['skintag'],array('link-color-hover','link-color')))
						$trianglesetting[$skinlist['skintag']] = $skinlist['value'];
					
					if (
						in_array('rounded-corners',$selectorgroup['variables'])
						&& in_array($skinlist['skintag'],array('border-color','border-width','rounded-corners','background-color'))
						&& !$roundedsetting[$skinlist['skintag']]) {
						//get background color, border color, border width and rounded corner radius. 
						$roundedsetting[$skinlist['skintag']] = $skinlist['value'];
					}
					
					if ($skinlist['affects'])
						$affects[] = array( 'data' => $skinlist['affects'],'value' => $skinlist['value'],'range' => $skinlist['range'], );
					
					$lastskinlistname = $skinlist['groupname'];//send group name to next looped item
				}
				//close the last css tag
				$css .= '}';
								
				if ($affects) {//apply what this element also affects
					foreach ($affects as $affect) {
						$affsel = $affcss = $affcsstag = $comparestring = '';
						$compareval = $affect['value'];
						list($affsel,$affcss) = explode('=',$affect['data']);
						list($affcsstag,$comparestring) = explode(':',$affcss);
						$range = explode(',',$affect['range']);
						$compares = explode(',',$comparestring);
						
						if ($comparestring && $range && $compares) {
							foreach ($range as $k => $rangeval) {
								if ($rangeval == $affect['value'] && trim($compares[$k])) {
									$compareval = $compares[$k];
									break;
								}
							}
						}
						
						$css .= "\n/*affected selector*/\n".$affsel.' { '.$affcsstag.': '.$compareval.(is_numeric($compareval) ? 'px':'').'; }';
					}
				}
				
				if (in_array('rounded-corners',$selectorgroup['variables']) && $roundedsetting) {
					//generate rounded corners css
					foreach ($roundeditems as $roundeditem) {
						$css .= "\n".$selectorgroup['selector'].' '.$roundeditem.'  {';
						$css .= ' padding: 0; background-color:transparent; background-image:none; background-repeat: no-repeat;';
						if ($roundedsetting['rounded-corners'] > 0) {
							$css .= ' background-image: url('.URLPATH.'/images/rounded.php?c='.str_replace('#','',$roundedsetting['background-color']);
							$css .= '&r='.($roundedsetting['rounded-corners']+$roundedsetting['border-width']).'&bc='.str_replace('#','',$roundedsetting['border-color']);
							$css .= '&b='.$roundedsetting['border-width'].'); ';
							$css .= 'padding'.str_replace(array('.top','.bottom'),'',$roundeditem).': '.($roundedsetting['rounded-corners']+$roundedsetting['border-width']).'px; ';
							$css .= ' background-position: '.implode(' ',array_reverse(explode('-',str_replace('.','',trim($roundeditem))))).'; ';
						}
						$css .= '}';
					}
					$rvs = $roundedsetting['border-color'].' solid '.$roundedsetting['border-width'].'px; background-color:'.$roundedsetting['background-color'].'; ';
					$rvs .= 'height: '.$roundedsetting['rounded-corners'].'px !important; ';
					$rvs .= $roundedsetting['border-width'] > 0 && $roundedsetting['rounded-corners'] > 0 ? 
						'height: '.($roundedsetting['rounded-corners'] + $roundedsetting['border-width']).'px; ' : '';
					$css .= "\n".$selectorgroup['selector'].' .top-inner { border-top: '.$rvs.' }';
					$css .= "\n".$selectorgroup['selector'].' .bottom-inner { border-bottom: '.$rvs.' }';
				}
				
				if ($triangle && $trianglesetting) {
					$pturl = PROTOCOL.$_SERVER['HTTP_HOST'].URLPATH.'/images/shapes/triangle_';
					$pturl .= (str_replace('#','',preg_match('/[A-Fa-f0-9]{6}/',$trianglesetting['link-color']) ? $trianglesetting['link-color'] : '585E9C'));
					$pturl .= '_'.(str_replace('#','',preg_match('/[A-Fa-f0-9]{6}/',$trianglesetting['link-color-hover']) ? $trianglesetting['link-color-hover'] : '010767')).'.png';
					$css .= "\n/*triangle*/\n".$selectorgroup['selector'].' a { background-image: url('.URLPATH.'/images/phpThumb.php?src='.urlencode($pturl).'.&w=36&h=36&f=png); }';
				}
				
				$css .= $selectorgroup['custom']; //extra custom css to be appended
				
				if ($this->gatherStyleElements) $css .= "\n</style>";
			}
		}
		
		return $css;
	}
	
	private function formatCSSRule($skinlist) { //this can be managed via database later
		switch (true) {//then build the css settings
			case $skinlist['csstag'] == 'rounded-corners': return '';
			case $skinlist['csstag'] == 'width': return $skinlist['csstag'].': '.($skinlist['value'] && is_numeric($skinlist['value']) ? $skinlist['value'].$skinlist['unit'] :'auto').'; ';
			case $skinlist['skintag'] == 'align': return $skinlist['csstag'].': '.($skinlist['value'] ? $skinlist['value'] :'0 auto').'; ';
			case $skinlist['unit'] == 'px': return $skinlist['csstag'].': '.($skinlist['value'].( is_numeric($skinlist['value']) ? $skinlist['unit'] : '' )).'; ';
			case $skinlist['unit'] == 'url': return $skinlist['csstag'].': '.($skinlist['value'] && $skinlist['value'] != 'none' ? 'url('.URLPATH.$skinlist['value'].')':'none').'; ';
			default: return $skinlist['csstag'].': '.$skinlist['value'].'; ';
		}
	}
	
	public function getJSON($selector = ''){ 
		//either build all or one selector group based on selector
		$return = (object)($selector ? $this->buildSelectorGroups($selector) : new stdClass());
		
		if ($this->gatherStyleElements) {
			$this->buildSelectorGroups();
			$return->styleElements = $this->buildCSS();
			$return->skintags = $this->skintags;
			global $session;
			$skin = mysql_single('SELECT name FROM skins WHERE id = \''.mysql_real_escape_string($session->styleid).'\'',__LINE__.__FILE__);
			$return->name = $skin->name;
		}
		
		return json_encode($return);
	}
	
	public function storeCSSFile($cssString) {
		$dir = PATH.'/lib/skins/';
		//remember to append all general styling to css builder
		if ($this->skinid) {
			if (!is_dir($dir)) mkdir($dir,0755,true);
			
			return file_put_contents($dir.'skin-'.$this->skinid.'.css',$cssString);
		} elseif ($this->isadmin) {
			//write default to file
			return file_put_contents(PATH.'/lib/skin.css',$cssString);
		}
		return false;
	}
	
	private function getParents($id) {
		$return = array();
		$selector = mysql_single('SELECT selector,parent,name FROM skinselectors WHERE id = \''.mysql_real_escape_string($id).'\'',__LINE__.__FILE__);
		array_unshift($return,'<a href="#" class="palette-canvas-selector {\'selector\':\''.$selector->selector.'\'}">'.$selector->name.'</a>');
		if ($selector->parent) $return = array_merge($this->getParents($selector->parent),$return);
		return $return;
	}
	
	public function saveYourSkin($json) {
		//check it's saveable first
		if (!$json || !($canvasObjectArray = json_decode($json))) return false;
		//if skin exists then update version number & date
		
		
		//else create skin
				
		
		//roll through each item, check if exists -> create new/update -> store as serialised array 
		if ($this->skinid && $selector->id) {
			foreach ($skintags as $skintag) {
				
			}
		}
	}
}
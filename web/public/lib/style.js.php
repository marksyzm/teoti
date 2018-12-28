<? 
require '../includes/dbconnect.php';
error_reporting(0); ob_start("ob_gzhandler"); header("Content-type: application/x-javascript; charset: UTF-8"); header("Cache-Control: must-revalidate"); header("Expires: " . gmdate("D, d M Y H:i:s", time() + (60 * 60)) . " GMT"); 

$row = mysql_single('SELECT GROUP_CONCAT(stName) as tags FROM eStyleTags',__LINE__.__FILE__);
?>

var 
	TAGS = '<?= $row->tags ?>',clearboth = '<div class="clearboth"><!-- --></div>'
	,clearbothmargin = '<div class="clearboth" style="padding-top:6px;"><!-- --></div>', activeEl
	,COLWRAP='', ISCOL='', NAMETAG='',SLICED='',EL
	,UNLOAD = false
	,NOSETTING = ['transparent','inherit','',null]
	,BGPOS = 	'<option value="0% 0%">left top</option>'+
						'<option value="50% 0%">center top</option>'+
						'<option value="100% 0%">right top</option>'+
						'<option value="0% 50%">left center</option>'+
						'<option value="50% 50%">center center</option>'+
						'<option value="100% 50%">right center</option>'+
						'<option value="0% 100%">left bottom</option>'+
						'<option value="50% 100%">center bottom</option>'+
						'<option value="100% 100%">right bottom</option>'
//	,cvsColOut, cvsColHover
;
			
function savePage(preview){
	var cssVar, query = '', inc = 0, lasttag = '';
	if (preview === undefined) preview = false;
	$(TAGS).each(function(){
		cssVar = new Object();
		
		var 
			thisEl = $(this), meta = thisEl.metadata(), revert = thisEl.data('revert'), imageid = thisEl.find('input.imageid:first').val(),
			sliced = meta.slice == '1' ? '-inner':'', outer = meta.outer == '1' ? '-outer':'';
//		if (meta.tag == '#left-col-menu .top-level') {
//		}
		var bside = new Array();
		//set 'constants'
		SLICED = sliced; EL = this; NAMETAG = meta.tag; INNER = $(NAMETAG+SLICED); TOPINNER = $(NAMETAG+' .top-inner');
		
		if (lasttag != meta.tag) { //skip if last tag is same
			cssVar['tag[]'] = meta.tag;
			lasttag = meta.tag;
			
			if (typeof imageid != 'undefined' && imageid.length > 0 && !hasGrad() && !hasRound()) { // if has imageid set and not rounded or gradient'd
				cssVar['seBGImage[]'] = imageid;
			} else 
				cssVar['seBGImage[]'] = '';
			cssVar['seGradient[]'] = hasGrad() ? '1':'0';
			
			cssVar['seShading[]'] = hasShade() ? '1':'0';
			
			if (hasRound()) {
				if ($('.top-inner:first',EL).size() > 0)
					cssVar['seCornerRadius[]'] = TOPINNER.innerHeight();
			} else {
				cssVar['seCornerRadius[]'] = 0;
			}
			
			cssVar['seBGPosition[]'] = meta.ismenu == '1' ? $(NAMETAG+SLICED+' a:not(.hover):last').backgroundPosition() : INNER.backgroundPosition();
			cssVar['seBGRepeat[]'] = meta.ismenu == '1' ? $(NAMETAG+SLICED+' a:not(.hover):last').css('background-repeat') : INNER.css('background-repeat');
			
			var seBGColor, seGradHeight = '200';
			if (hasGrad()) {
				seBGColor = thisEl.data('grad1');
				seGradHeight = thisEl.data('gradheight');
			} else {
				seBGColor = INNER.css('background-color');
			}
			cssVar['seGradHeight[]'] = seGradHeight;
			
			cssVar['seBGColor[]'] = rgb2hex(seBGColor);
			
			if ( hasGrad() ) {
				cssVar['seGradient[]'] = '1';
				cssVar['seBGColor2[]'] = rgb2hex(INNER.css('background-color'));
			} else {
				cssVar['seGradient[]'] = '0';
				cssVar['seBGColor2[]'] = 'transparent';
			}
			
			cssVar['seColor[]'] = rgb2hex(INNER.css('color'));
			
			if (typeof $('a:not(.hover):last',INNER[0]).css('color') != 'undefined') {
				cssVar['seLinkColor[]'] = rgb2hex($('a:not(.hover):last',INNER[0]).css('color'));
			} else cssVar['seLinkColor[]'] = 'inherit';
				
			if (typeof $('a.hover:last',INNER[0]).css('color') != 'undefined') {
				cssVar['seLinkColorHover[]'] = rgb2hex($('a.hover:last',INNER[0]).css('color'));
			} else cssVar['seLinkColorHover[]'] = 'inherit';
			
			if (typeof $('a:not(.hover):last',INNER[0]).css('background-color') != 'undefined') {
				cssVar['seLinkBGColor[]'] = rgb2hex($('a:not(.hover):last',INNER[0]).css('background-color'));
			} else cssVar['seLinkBGColor[]'] = 'transparent';
			
			if (typeof $('a.hover:last',INNER[0]).css('background-color') != 'undefined') {
				cssVar['seLinkBGColorHover[]'] = rgb2hex($('a.hover:last',INNER[0]).css('background-color'));
			} else cssVar['seLinkBGColorHover[]'] = 'transparent';

			if (typeof INNER.css('border-left-color') != 'undefined') {
				cssVar['seBorderColor[]'] = rgb2hex(INNER.css('border-left-color'));
			} else cssVar['seBorderColor[]'] = '#CCCCCC';
			
			bside = new Array();
			if (meta.bordersides !== undefined && meta.bordersides != '0')
				bside = meta.bordersides.split('');
			
			if (bside.length > 0) {
				var side = '0';
				$.each(bside,function(){
					if (this == 'l') { side = INNER.border().left; return false; }
					if (this == 'r') { side = INNER.border().right; return false; }
					if (this == 'b') { side = INNER.border().bottom; return false; }
					if (this == 't') { side = INNER.border().top; return false; }
				});
				cssVar['seBorderWidth[]'] = side+'px';
			} else {
				cssVar['seBorderWidth[]'] = INNER.css('border-left-width');
			}
//			if (meta.tag=='#whole .agenda-table .track') {
//				alert(INNER.css('font-size'));
//			}
			//cssVar['sePadding[]'] = thisEl.css('padding-left');
			cssVar['sePaddingTop[]'] = INNER.padding().top+'px';
			cssVar['sePaddingBottom[]'] = INNER.padding().bottom+'px';
			cssVar['sePaddingLeft[]'] = INNER.padding().left+'px';
			cssVar['sePaddingRight[]'] = INNER.padding().right+'px';
			cssVar['seMarginTop[]'] = thisEl.margin().top+'px';
			cssVar['seMarginBottom[]'] = thisEl.margin().bottom+'px';
			cssVar['seMarginLeft[]'] = thisEl.margin().left+'px';
			cssVar['seMarginRight[]'] = thisEl.margin().right+'px';
			//cssVar['seMargin[]'] = thisEl.css('margin-left');
			cssVar['seFontSize[]'] = INNER.css('font-size');
			query += (inc++ > 0 ? '&':'')+$.param(cssVar);
		}	
	});
	
	//now get the universals
	query += '&sPageWidth='+$('#pagewidth').val();
	query += '&sPagePosition='+$('#pageposition').val();
	var unifont = $('#unifont').val().split('|');
	query += '&sUniFont='+unifont[0];
	//query += '&sColLeftWidth='+($('#col-left-outer').isVisible() ? $('#col-left-outer').outerWidth():'0');
	query += '&sColRightWidth='+($('#col-right-outer').isVisible() ? $('#col-right-outer').outerWidth():'0');
	query += '&sHeaderHeight='+(ISMSIE ? $('#header-inner').outerHeight() : $('#header-inner').height());
	query += '&sFacelift='+($('#sFacelift').is(':checked') ? '0':'1');
	query += '&sFlash='+($('#sFlash').is(':checked') ? '0':'1');
	query += '&sFlashTransparent='+($('#sFlashTransparent').is(':checked') ? '1':'0');
	query += '&sFlashPosition='+$('#sFlashPosition').val();
	query += '&sFlashWidth='+$('#sFlashWidth').val();
	query += '&sFlashHeight='+$('#sFlashHeight').val();
	query += '&sBGAttach='+$('#sBGAttach').val();
	query += '&sName='+$('#sName').val();
	query += '&sID='+$('body').data('universal').sID;
	query += preview ? '&do=previewpage' : '&do=savepage';
	//once collected then save and refresh.
	$.post(PHP_SELF,query,function(resp){ 
		if (preview && resp.length > 0 && resp > 0) {
			//generate popup from id
			<? $urlpath = str_replace('control/','',URLPATH);?>
			window.open(URLPATH+'/?thid='+resp,'Preview','width=1000,height=550,resizable=1,scrollbars=1,toolbar=0,location=0,directories=0,status=1,menubar=1') 
		} else {
//			UNLOAD = true;
//			location.href=PHP_SELF;
		}
	});
}

function rgb2hex(rgb) {
	if (typeof rgb != 'undefined' && rgb.match(new RegExp('^rgba?'))) {
		if (rgb.match(/^rgba\(0+,\s*(\d+),\s*(\d+),\s*(\d+)\)/i)) return 'transparent';
		rgb = rgb.match(/^rgba/i) ? rgb.match(/^rgba\(\d+,\s*(\d+),\s*(\d+),\s*(\d+)\)/i) : rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)/i);
		function hex(isX) {
			hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
			return isNaN(isX) ? "00" : hexDigits[Math.round((isX - isX % 16) / 16)].toString() + hexDigits[Math.round(isX % 16)].toString();
		}
		return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3])
	} else return rgb;
}

function colequal(){
	var j = $('#col-right, #content'), maxOH = 420, diffs = new Array(), i=0;
	j.each(function(){
		var thisEl = $(this);
		thisEl.parent().css('height','auto');
		var oH = thisEl.parent().outerHeight();
		diffs[diffs.length] = oH-($.support.boxModel ? thisEl.find('.body-inner div:first').height() : thisEl.find('.body-inner div:first').innerHeight());
		//maxOH = oH > maxOH ? oH : maxOH;
	}).each(function(){
		$(this).find('.body-inner div:first').height( maxOH-diffs[i++]);
	});
}

function hasRound(){
	return (typeof $(NAMETAG+' .top-left').css('background-image') != 'undefined' && $(NAMETAG+' .top-left').css('background-image').match('rounded.php')) ? true : false;
}

function hasShade(){
	return (typeof $(NAMETAG+' .top-left').css('background-image') != 'undefined' && $(NAMETAG+' .top-left').css('background-image').match('shading/')) ? true : false;
}

function getRoundHeight(){
	return (typeof $(NAMETAG+' .top-left').css('background-image') != 'undefined' && $(NAMETAG+' .top-left').css('background-image').match('rounded.php')) ? $(NAMETAG+' .top-inner').innerHeight() : '0';
}

function hasGrad() {
	var el = $(NAMETAG), meta = el.metadata(), thisEl = $(NAMETAG+SLICED+(meta.ismenu == '1'? ' a:not(.hover):last':''));
	return (typeof thisEl.css('background-image') != 'undefined' && thisEl.css('background-image').match('gradient.php')) ? true : false;
}

function colWrap() {
	var Total = 0;
	$(COLWRAP).each(function(){
		var meta = $(this).metadata(), thisEl = $(this), thisInner=$(meta.tag+'-inner');
		if (thisEl.isVisible()) {
			Total += meta.slice != '1' ? thisEl.padding().left+thisEl.padding().right : thisInner.padding().left+thisInner.padding().right;
			if (thisEl.find('.body-left:first').size() > 0) Total += thisEl.find('.body-left:first').padding().left;
			if (thisEl.find('.body-right:first').size() > 0) Total += thisEl.find('.body-right:first').padding().right;
			Total += thisEl.margin().left+thisEl.margin().right;
			Total += meta.slice != '1' ? thisEl.border().left+thisEl.border().right : thisInner.border().left+thisInner.border().right;
		}
	});
	$(ISCOL).each(function(){
		var els = $(this).metadata().outer != 1 ? $(this) : $(this).parent();
		if (els.isVisible()) Total += els.outerWidth();
	});
	//Total = $('body').data('universal').pagewidth - Total;
	Total = $('#main-outer').width() - Total;
	$('#content-col').css({'width':Total+'px'});
}

function gradCheck(toggle,gH) {
	var tEl = $(EL), first = '', second = '', hite = '', gradpos = '', hasG = hasGrad(EL), thisInner = $(NAMETAG+SLICED);
	if (typeof toggle != 'undefined') hasG = toggle;
	if (hasG) {
		$('.gradient')[0].checked = true;
		//hide repeat, background-color
		
		$('.colours-bg, .type-bgrepeat, .type-bgimage, .type-bgpos').hide();
		$('.grad1, .grad2, .type-gradheight').show();
		//change image position vals
		$('.bgpos').empty().append('<option value="0% 0%">top</option><option value="0% 100%">bottom</option>');
		//set the vals
		first = (typeof tEl.data('revert') != 'undefined' && typeof tEl.data('revert').grad1 != 'undefined') ? 
			tEl.data('revert').grad1 : (tEl.data('grad1').length > 0 ? tEl.data('grad1') : '#888888');
			
		second = (typeof tEl.data('revert') != 'undefined' && typeof tEl.data('revert').grad2 != 'undefined') ? 
			tEl.data('revert').grad2 : 
			(thisInner.size() == 0 || $.inArray(thisInner.css('background-color'),NOSETTING) > -1 ? '#EEEEEE' : 
				rgb2hex(thisInner.css('background-color'))
			);
		

		if (typeof gH != 'undefined') {
			hite = gH;
			tEl.data('gradheight',gH); 
		} else hite = typeof tEl.data('gradheight') != 'undefined' && tEl.data('gradheight') > 0 ? tEl.data('gradheight') : '200';
		
		gradpos = typeof tEl.data('revert') != 'undefined' &&  typeof tEl.data('revert').gradpos != 'undefined' ? tEl.data('revert').gradpos : '0% 0%';
		$('.gradheight').val(hite); $('.gradpos').val(gradpos);
		//and set the gradient
		
		thisInner.css({
			'background-position':gradpos, 'background-repeat':'repeat-x',
			'background-image':'url('+URLPATH+'/images/gradient.php?c1='+first.replace('#','')+'&c2='+second.replace('#','')+'&h='+$('.gradheight').val()+')',
			'background-color':'#'+second.replace('#','')
		});
		
		if (typeof $(NAMETAG).data('gradmem') == 'undefined') $(NAMETAG).data('gradmem',{});
		
		var gradmem = $(NAMETAG).data('gradmem');
		$(NAMETAG).data('gradmem').grad1 = typeof gradmem.grad1 == 'undefined' ?  first: gradmem.grad1;
		$(NAMETAG).data('grad1',(typeof $(NAMETAG).data('grad1') == 'undefined' || $(NAMETAG).data('grad1') == '' ?  first: gradmem.grad1));
		$(NAMETAG).data('gradmem').grad2 = typeof gradmem.grad2 == 'undefined' ? second: gradmem.grad2;
		$(NAMETAG).data('gradmem').gradpos = typeof gradmem.gradpos == '' ? gradpos : gradmem.gradpos;
		if (typeof gH != 'undefined') $(NAMETAG).data('gradmem').gradheight = gH;
		else $(NAMETAG).gradheight = typeof gradmem.gradheight == 'undefined' ? hite : gradmem.gradheight;
		if (tEl.metadata().rounded == '1' && hasRound()) {
			$('.corners').val( getRoundHeight() );
			roundEm($('.corners').val());
		}
	} else {
		if (tEl.metadata().rounded == '1' && hasRound()) {
			$('.corners').val( getRoundHeight() );
			roundEm($('.corners').val());
		}
		$('.gradient')[0].checked = false;
		if (typeof $(NAMETAG).data('gradmem') != 'undefined') $(NAMETAG+SLICED).css({'background-image':'none','background-position':'0% 0%'});
		$('.bgpos').empty().append(BGPOS);
		$('.colours-bg, .type-bgrepeat, .type-bgimage, .type-bgpos').show();
		$('.grad1, .grad2, .type-gradheight').hide();
	}
	//colequal();
}

function roundEm(rVal,fst){
	if (typeof fst == 'undefined') fst=false;
	var thisEl = $(NAMETAG), thisElSliced = $(NAMETAG+SLICED)
	var rBorderW = thisElSliced.border().left, rBGColor, rBrdrColor = '#444444', tEl = $(EL), rBGColor2, hasG = hasGrad(), origVal = rVal;
	rBGColor = rgb2hex(thisElSliced.css('background-color'));
	if (rBGColor == 'transparent') {
		rBGColor = '#FFFFFF';
		thisElSliced.css('background-color',rBGColor);
	}
	if (hasG) {
		rBGColor2 = 
			typeof thisEl.data('gradmem') != 'undefined' && typeof thisEl.data('gradmem').grad2 != 'undefined' ? 
			thisEl.data('gradmem').grad2 : (
				typeof thisEl.data('revert') != 'undefined' && typeof thisEl.data('revert').grad2 != 'undefined' ?
				thisEl.data('revert').grad2 : '#DDDDDD'
			);
		
		
		rBGColor =  typeof thisEl.data('grad1') != 'undefined' ? 
			thisEl.data('grad1') : (
				typeof thisEl.data('revert') != 'undefined' && typeof thisEl.data('revert').grad1 != 'undefined' ?
				thisEl.data('revert').grad1 : '#444444'
			);
	}
	if ($.inArray(thisElSliced.css('border-left-color'),NOSETTING) == -1)
		rBrdrColor = rgb2hex($(NAMETAG+SLICED).css('border-left-color'));
	var hite = rVal;
	rVal = parseInt(rBorderW)+parseInt(rVal);
	if (!$.support.boxModel) hite = rVal;
	if (origVal > 0) {
		tEl
			.find('.top-inner:first').css({
				'background-color': '#'+(rBGColor.replace('#',''))
			}).border({top:rBorderW,left:'0',right:'0',bottom:'0'});
		tEl
			.find('.bottom-inner:last').css({
				'background-color':'#'+((hasG ? rBGColor2 : rBGColor).replace('#',''))
			}).border({top:'0',left:'0',right:'0',bottom:rBorderW});
		tEl
			.find('.top-right:first, .top-left:first').css({
				'background-image':'url('+URLPATH+'/images/rounded.php?c='+rBGColor.replace('#','')+
				'&r='+rVal.toString()+'&bc='+rBrdrColor.replace('#','')+'&b='+rBorderW+')',
				'background-repeat':'no-repeat'
			});
		var c = (hasG ? rBGColor2.replace('#','') : rBGColor.replace('#',''));
		
		tEl
			.find('.bottom-left:last, .bottom-right:last').css({
				'background-image':'url('+URLPATH+'/images/rounded.php?c='+c+
				'&r='+rVal.toString()+'&bc='+rBrdrColor.replace('#','')+'&b='+rBorderW+')',
				'background-repeat':'no-repeat'
			});
		
		tEl.find('.top-left:first').css({'padding-right':'0px','padding-left':rVal+'px','background-position':'0% 0%'});
		tEl.find('.top-right:first').css({'padding-right':rVal+'px','padding-left':'0px','background-position':'100% 0%'});
		
		tEl.find('.top-inner:first').css({'height':hite+'px'});
		tEl.find('.bottom-left:last').css({'padding-right':'0px','padding-left':rVal+'px','background-position':'0% 100%'});
		tEl.find('.bottom-right:last').css({'padding-right':rVal+'px','padding-left':'0px','background-position':'100% 100%'});
		tEl.find('.bottom-inner:last').css({'height':hite+'px'});
		
		if (!hasG) $(NAMETAG+SLICED).css({'background-image':'none'});
	} else {
		tEl.find('.top-left:first, .top-right:first, .bottom-left:last, .bottom-right:last').css({'background-image':'none','padding':'0px'});
		tEl.find('.top-inner:first, .bottom-inner:last').css({'height':'0px'});
	}
	//colequal();
}

function triGen(el,clr){
	var ctx = el.getContext('2d');
	el.width = el.width;
	ctx.fillStyle = clr;
	ctx.beginPath();
	//console.log(clr);
	if ($(el).parent().hasClass('canvas-down')) {
		ctx.moveTo(0,0);
		ctx.lineTo(20,0);
		ctx.lineTo(10,20);
		ctx.lineTo(0,0);
	} else {
		ctx.moveTo(10,0);
		ctx.lineTo(20,20);
		ctx.lineTo(0,20);
		ctx.lineTo(10,0);
	}
	ctx.fill();
	ctx.closePath();
}

(function($) {
	var num = function (value) {
		return parseInt(value, 10) || 0;
	};
	$.each(['min', 'max'], function (i, name) {
		$.fn[name + 'Size'] = function (value) {
			var width, height;
			if (value) {
				if (value.width) {
					this.css(name + '-width', value.width);
				}
				if (value.height) {
					this.css(name + '-height', value.height);
				}
				return this;
			}
			else {
				width = this.css(name + '-width');
				height = this.css(name + '-height');
				// Apparently:
				//  * Opera returns -1px instead of none
				//  * IE6 returns undefined instead of none
				return {'width': (name === 'max' && (width === undefined || width === 'none' || num(width) === -1) && Number.MAX_VALUE) || num(width), 
						'height': (name === 'max' && (height === undefined || height === 'none' || num(height) === -1) && Number.MAX_VALUE) || num(height)};
			}
		};
	});
	$.fn.isVisible = function () {
		return this.css('visibility') !== 'hidden' && this.css('display') !== 'none';
	};
	$.each(['border', 'margin', 'padding'], function (i, name) {
		$.fn[name] = function (value) {
			if (value) {
				if (value.top) 
					this.css(name + '-top' + (name === 'border' ? '-width' : ''), value.top);
				if (value.bottom) 
					this.css(name + '-bottom' + (name === 'border' ? '-width' : ''), value.bottom);
				if (value.left) 
					this.css(name + '-left' + (name === 'border' ? '-width' : ''), value.left);
				if (value.right) 
					this.css(name + '-right' + (name === 'border' ? '-width' : ''), value.right);
				return this;
			}
			else {
				return {top: num(this.css(name + '-top' + (name === 'border' ? '-width' : ''))),
						bottom: num(this.css(name + '-bottom' + (name === 'border' ? '-width' : ''))),
						left: num(this.css(name + '-left' + (name === 'border' ? '-width' : ''))),
						right: num(this.css(name + '-right' + (name === 'border' ? '-width' : '')))};
			}
		};
	});

	
	$.fn.backgroundPosition = function() {
    var p = $(this).css('background-position');
    if (typeof(p) == 'undefined') {
    	var xV = $(this).css('background-position-x'), yV = $(this).css('background-position-y');
    	switch (xV){
    		case 'left': xV = '0%'; break;
    		case 'right': xV = '100%'; break;
    		default: case 'center': xV = '50%'; break;
    	}
    	switch (yV){
    		default: case 'top': yV = '0%'; break;
    		case 'center': yV = '50%'; break;
    		case 'bottom': yV = '100%'; break;
    	}
    	if (xV.match('px')) xV = (Math.round((xV.replace('px','')/$(this).outerWidth())*10)*10).toString()+'%';
    	if (yV.match('px')) yV = (Math.round((yV.replace('px','')/$(this).outerWidth())*10)*10).toString()+'%';
    	return xV+' '+yV;
    }
    else return p;
  };

	var tC;
	$.fn.colorize = function(cssType,linky) {
		return this.ColorPicker({
			onBeforeShow: function () {
				$('.colorpicker')
					.click(function(ev){ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;})
					.mouseover(function(ev){ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;});
				var el = linky.length > 0 ? $(linky,activeEl) : $(activeEl), hexVal;
				if ($.inArray(cssType,['grad1','grad2']) > -1) {
					hexVal = ($(activeEl.replace('-inner','')).data('gradmem')[cssType].match('#') ? '':'#')+$(activeEl.replace('-inner','')).data('gradmem')[cssType];
				} else {
					var thisEl = $(activeEl), meta = thisEl.metadata();
					if (cssType == 'border-color') {
						bside = new Array();
						if (meta.bordersides != undefined && meta.bordersides != '0') {
							bside = meta.bordersides.split('');
						}
						var side = '';
						if (bside.length > 0) {
							$.each(bside,function(){
								if (this == 'l') { side = thisEl.css('border-left-color'); return false; }
								if (this == 'r') { side = thisEl.css('border-right-color'); return false; }
								if (this == 'b') { side = thisEl.css('border-bottom-color'); return false; }
								if (this == 't') { side = thisEl.css('border-top-color'); return false; }
							});
						} else {
							side = thisEl.css('border-left-color');
						}
						hexVal = $.inArray(side,NOSETTING) > -1 ? '#FFFFFF' : side;
					} else {
						hexVal = $.inArray(el.css(cssType),NOSETTING) > -1 ? 'rgb(255,255,255)' : el.css(cssType);
					}
				}
				
				$(this).ColorPickerSetColor(rgb2hex(hexVal));
			},
			onChange: function (hsb, hex, rgb) {
				var el = linky.length > 0 ? $(linky+(linky=='a' ? ':not(.hover)':''),activeEl) : $(activeEl);
				if (cssType == 'border-color') el = $(activeEl+', '+activeEl.replace('-inner','')+' .bottom-inner:last,  '+activeEl.replace('-inner','')+' .top-inner:first');
				if ($.inArray(cssType,['grad1','grad2']) > -1) {
					clearTimeout(tC);
					tC = setTimeout(function(){
						switch(cssType) {
							case 'grad1':
								$(activeEl.replace('-inner','')+' .top-inner:first').css({'background-color':'#'+hex});
								$(activeEl).css({
									'background-image':'url('+URLPATH+'/images/gradient.php?c1='+hex+
									'&c2='+$(activeEl.replace('-inner','')).data('gradmem').grad2.replace('#','')+
									'&h='+$('.gradheight').val()+')'
								});
								$(activeEl.replace('-inner','')).data('grad1','#'+hex).data('gradmem').grad1 = hex;
								tC = setTimeout(function(){
									if (hasRound()) roundEm($('.corners').val());
								},500);
								break;
							case 'grad2':
								$(activeEl).css({
									'background-color':'#'+hex,
									'background-image':'url('+URLPATH+'/images/gradient.php?c2='+hex+
									'&c1='+$(activeEl.replace('-inner','')).data('gradmem').grad1.replace('#','')+
									'&h='+$('.gradheight').val()+')'
								});
								$(activeEl.replace('-inner','')).data('gradmem').grad2 = hex;
								tC = setTimeout(function(){
									if (hasRound()) roundEm($('.corners').val());
								},500);
								break;
						}
					},500);
				} else {
					switch(cssType){
						case 'background-color': 
							el.css({'background-color':'#'+hex}); 
							if (linky == 'a.hover' || linky == 'a') {
								var linkVis = $((linky=='a'?'a.hover':'a'),activeEl).css('background-color'), //other state's original 'color'
								linkc = $('a',activeEl).css('color'), linkcH = $('a.hover',activeEl).css('color'); 
								$('a:not(.hover)',activeEl)
									.unbind('mouseenter mouseleave')
									.hover(function(){ $(this).css({'background-color':(linky == 'a' ? linkVis : '#'+hex),'color':linkcH});},
									function(){ $(this).css({'background-color':(linky == 'a' ? '#'+hex: linkVis ),'color':linkc});});
							}
							//el.css({'border-color':'#'+hex}); 
							clearTimeout(tC);
							tC = setTimeout(function(){
								if (hasRound()) roundEm($('.corners').val());
							},500);
							break;
						case 'border-color': 
							el.css({'border-color':'#'+hex}); 
							clearTimeout(tC);
							tC = setTimeout(function(){
								if (hasRound()) roundEm($('.corners').val());
							},500);
							break;
						case 'color': 
							el.css({'color':'#'+hex}); 
							if (activeEl == '#body .teoti-points' && (linky == 'a.hover' || linky == 'a')) {
								var pel = $('#body .teoti-points .canvas'), cHover, cOut;
								if (linky != 'a.hover') {
									cOut = '#'+hex;
									cHover = rgb2hex(pel.parent().find('a.hover:first').css('color'));
									pel.find('canvas').each(function(){triGen(this,'#'+hex);});
								} else {
									cHover = '#'+hex;
									cOut = rgb2hex(pel.parent().find('a:first').css('color'));
								}
								pel.find('canvas')
									.unbind('mouseenter mouseleave')
									.hover(
										function(){ 
											triGen(this,cHover); 
										}
										,function(){ 
											triGen(this,cOut); 
										}
									);
							}
							
							if (linky == 'a.hover' || linky == 'a') {
								var linkVis = $((linky=='a'?'a.hover':'a'),activeEl).css('color'), //other state's original 'color'
								linkc = $('a',activeEl).css('background-color'), linkcH = $('a.hover',activeEl).css('background-color'); 
								$('a:not(.hover)',activeEl)
									.unbind('mouseenter mouseleave')
									.hover(function(){ $(this).css({'color':(linky == 'a' ? linkVis : '#'+hex),'background-color':linkcH});},
									function(){ $(this).css({'color':(linky == 'a' ? '#'+hex: linkVis ),'background-color':linkc});});
							}
							break;
					}
				}
			},
			onSubmit: function(hsb,hex,rgb,el){
				$(el).ColorPickerHide();
			},
			onHide: function(el){
				$(el).fadeOut(500);
				return false;
			}
		});
	};
	
	$.fn.styler = function() {
		// Default settings
//		var settings = $.extend({}, {
//		}, options);
		// Returns the jQuery object to allow for chainability.  
		var fT;
		function choosify(el,change) {
			//var box = $('#style-pop').isVisible() || change ? 'select':'choose',
			var 
				thisEl = $(el), meta = thisEl.metadata(), thisIW='0',thisIH='0', tI = thisEl.find('#'+thisEl.id+'-inner'),
				thisInner = tI.size() > 0 ? tI : thisEl;
			
			choose = $('#choose-left, #choose-right, #choose-top, #choose-bottom, #dims');
			if (!$('#style-pop').isVisible() || change) {
				choose.hide();
				thisIW = thisEl.outerWidth(), thisIH = thisInner.outerHeight();
				$('#dims')
					.find('span:eq(0)').each(function(){if (meta.friendly != 'undefined') $(this).text(meta.friendly) })
					.end().find('span:eq(1)').text(thisIW)
					.end().find('span:eq(2)').text(thisIH)
				;
				var 
					sP=5, L = 2, //sP is outer spacing, L is thickness of lines (determined in CSS)
					whl=$('#whole'), whlW = whl.outerWidth(), whlH = whl.outerHeight(), tbH = $('#toolbar-outer').outerHeight(),
					iW = meta.tag == '#whole' ? true : false, isOuter = meta.outer, 
					cW = isOuter == 1 ? thisEl.parent().outerWidth() : thisEl.outerWidth(),
					cH = isOuter== 1 ? thisEl.parent().outerHeight() : thisEl.outerHeight(),
					cPos = isOuter == 1 ? thisEl.parent().offset() : thisEl.offset(),
					tpbmW = ((cPos.left-sP < 0 ? -L:sP)+(cW+(sP*2) > whlW ? -L:sP)+cW);
				$('#dims').css({
					'top':((cPos.top-sP) < tbH ? cPos.top+L : cPos.top-sP)+'px',
					'left':(cPos.left-sP < 0 ? cPos.left+L : cPos.left-sP)+'px'
				});
				$('#choose-top').css({
					'width':tpbmW+'px',
					'top':((cPos.top-sP-L) < tbH ? cPos.top : cPos.top-sP-L)+'px',
					'left':(cPos.left-sP < 0 ? cPos.left+L : cPos.left-sP)+'px'
				});
				$('#choose-left').css({
					'height':((cH+((sP+L)*2) > whlH ? 0 : sP+L)+(cPos.top-sP-L < tbH ? 0 : sP+L)+cH-(iW ? tbH:0))+'px',
					'top':((cPos.top-sP-L) < tbH ? cPos.top : cPos.top-sP-L)+'px',
					'left':(cPos.left-sP-L < 0 ? '0': cPos.left-sP-L)+'px'
				});
				$('#choose-bottom').css({
					'width':tpbmW+'px',
					'top':((cPos.top+cH+sP) > tbH+whlH ? cPos.top+cH-(iW ? tbH : 0)-L : cPos.top+cH+sP)+'px',
					'left':(cPos.left-sP < 0 ? cPos.left+L : cPos.left-sP)+'px'
				});
				$('#choose-right').css({
					'height':((cH+((sP+L)*2) > whlH ? 0 : sP+L)+(cPos.top-sP-L < tbH ? 0 : sP+L)+cH-(iW ? tbH:0))+'px',
					'top':((cPos.top-sP-L) < tbH ? cPos.top : cPos.top-sP-L)+'px',
					'left':(cPos.left+cW+sP > whlW ? cPos.left+cW-L:cPos.left+cW+sP)+'px'
				});
			}
			choose.fadeIn('fast');
			clearTimeout(fT);
			fT = setTimeout(function(){$('#dims').fadeOut('slow');},1750)
		}
		
//		function choosify(el,change) {
//			//var box = $('#style-pop').isVisible() || change ? 'select':'choose',
//			var thisEl = $(el), sP=5, L = 3, bdy = $('body');
//			choose = $('#choose-left, #choose-right, #choose-top, #choose-bottom');
//			if (!$('#style-pop').isVisible() || change) {
//				choose.hide();
//				var isOuter = thisEl.metadata().outer;
//				var	cW = isOuter == 1 ? thisEl.parent().outerWidth() : thisEl.outerWidth(),
//						cH = isOuter== 1 ? thisEl.parent().outerHeight() : thisEl.outerHeight(),
//						cPos = isOuter == 1 ? thisEl.parent().offset() : thisEl.offset();
//				
//				$('#choose-top').css({'width':(cW+8)+'px','top':(cPos.top-3)+'px','left':(cPos.left-3)+'px'});
//				$('#choose-left').css({'height':(cH+6)+'px','top':(cPos.top-3)+'px','left':(cPos.left-3)+'px'});
//				$('#choose-bottom').css({'width':(cW+8)+'px','top':(cPos.top+cH+3)+'px','left':(cPos.left-3)+'px'});
//				$('#choose-right').css({'height':(cH+6)+'px','top':(cPos.top-3)+'px','left':(cPos.left+cW+3)+'px'});
//			}
//			choose.fadeIn('fast');
//		}
		
		function stylepopbox() {
			return $('<div class="style-pop-box" />').append(
				$('<h3><a /></h3>').click(function(){
					$('.style-pop-settings').hide();
					$('.bottom-image').removeClass('bottom-image')
					$('.style-pop-settings',$(this).parent()[0]).show();
					$(this).parent().find('h3 a').addClass('bottom-image');
				})
			);
		}
		
		function getSpectrumBox(classify,txt){
			return $('<div class="split floatleft '+classify+'" />').append(
				$('<a href="#" onclick="return false;" style="display:block" />')
					.append( $('<img class="iconify icon-edit-spectrum" src="images/blank.gif" alt="" />') )
					.append(txt)
					.append('<div class="clearboth"><!-- --></div>')
			);
		}
		
		function spectrumize(el,selector,cssType,linky){
			selector.colorize(cssType,linky);
			if (cssType == 'background-color' && linky == '') {
				selector.find('.icon-edit-remove').remove();
				selector.find('.icon-edit-spectrum').after(
					$('<img class="iconify icon-edit-remove floatleft" src="images/blank.gif" alt="" />').each(function(){
						$(this)
							.unbind('click')
							.click(function(ev){
								remBgcolor();
								if (ev.stopPropagation) { ev.stopPropagation();ev.preventDefault(); } else { ev.cancelBubble = true; ev.returnValue=false;}
							});
					})
				)
			}
		}
		
		function viewPos(pW,pH,pLeft,pTop) {
			var 
				winScrollLeft = $(window).scrollLeft(), winScrollTop = $(window).scrollTop(), 
				wholeX = $(window).width() + winScrollLeft,
				wholeY = (window.innerHeight ? window.innerHeight : $(window).height()) + winScrollTop;
			//reposition
			var 
				posTop  = (pTop + pH) < wholeY ? pTop : pTop - pH + winScrollTop,
				posLeft = (pLeft + pW)  < wholeX ? pLeft : pLeft - pW + winScrollLeft;

			posTop = posTop < 0 ? 0 : posTop;
			posLeft = posLeft < 0 ? 0 : posLeft;
			return {posLeft:posLeft,posTop:posTop};
		}
		
		var pT;
		function prevElements() {
			//if ($('#elemenu').size() == 0) $('body').append($('<div id="elemenu" />'))
			//var elemenu = $('#elemenu');
			var elemenu = $('.type-elements');
			pT = setTimeout( function(){ choosify(NAMETAG,true); },3000 ); 
			elemenu.empty();
			$(NAMETAG).parents().each(function(){
				var thisPar = this;
				if ($(thisPar).metadata() !== undefined && $(thisPar).metadata().tag !== undefined) {
					elemenu.append( 
						$('<div />')
							.html('<a>'+$(thisPar).metadata().friendly+'</a>')
							.mouseover(function(ev){ clearTimeout(pT); choosify(thisPar,true);if (ev.stopPropagation) { ev.stopPropagation();ev.preventDefault(); } else { ev.cancelBubble = true; ev.returnValue=false;} })
							.mouseout(function(){ pT = setTimeout( function(){  choosify(NAMETAG,true); },700 ); })
							.click(function(ev){ setStylerPopup(thisPar,ev); if (ev.stopPropagation) { ev.stopPropagation();ev.preventDefault(); } else { ev.cancelBubble = true; ev.returnValue=false;} })
					)
				} 
			});
//			var 
//				selectBox = $('#select-prev-element');
//			coord = viewPos(
//				selectBox.outerWidth(),
//				selectBox.outerHeight(),
//				selectBox.offset().left,
//				selectBox.offset().top+selectBox.outerHeight()
//			)
				
			//elemenu.css({top:(selectBox.offset().top+selectBox.outerHeight())+'px',left:selectBox.offset().left+'px'}).show();
		}
		
		function createStylerPopup(){
			var styler = $('<div id="style-pop" />')
				.append(
					$('<a href="#" style="position:relative;top:0;right:0;float:right;" />').append(
						$('<img src="images/blank.gif" alt="" class="iconify icon-edit-remove" alt="Close"/>')
					).click(function(ev){closeStyler(); if (ev.stopPropagation) { ev.stopPropagation();ev.preventDefault(); } else { ev.cancelBubble = true; ev.returnValue=false;} })
				)
				.append('<h3 id="style-handle">Edit <span id="style-head" /></h3>')
				.append( '<div class="style-pop-inner"><!-- --></div>')
				.append(
					$('<div class="style-pop-confirm"></div>')
//						.append( $('<div id="select-prev-element"><a>Underlying Elements</a></div>').click(function(){ prevElements(); })  )
						.append( $('<input type="button" value="Ok" />').click(function(){closeStyler();}) )
						.append( $('<input type="button" value="Revert" />').click(function(){revert();}) )
				)
				.click(function(ev){ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;}).mouseover(function(ev){ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;});
			var colour = stylepopbox(), layout = stylepopbox(), typography = stylepopbox(), underElement = stylepopbox();
			
			$('h3 a',layout).text('Layout');
			$('h3 a',colour).text('Colour');
			$('h3 a',typography).text('Text');
			$('h3 a',underElement).text('Underlying Elements');
			
			//create the colours layout
			var colours = $('<div class="style-pop-settings type-colours" />');
			colours
				.append( getSpectrumBox('colours-text','Text') )
				.append( getSpectrumBox('colours-border','Border') )
				.append( getSpectrumBox('colours-link','Link') )
				.append( getSpectrumBox('colours-link-hover','Link Hover') )
				.append( getSpectrumBox('colours-link-bg','Link Background') )
				.append( getSpectrumBox('colours-link-bg-hover','Link BG Hover') )
				.append( clearboth )
				.append( $('<div class="type-bgimage" />').append( 
					$('<form action="'+URLPATH+'/iframe.php" enctype="multipart/form-data" id="imgform" target="upload_iframe" method="post" />')
						.append( 'Background Image' ) 
						.append( 
							$('<input type="file" class="bgimage" name="lFileName" />').change(function(){
								$('#iframe_div').html('<iframe name="upload_iframe" id="upload_iframe" src="about:blank"></iframe>');
								$('#imgform').submit();
							})
						)
						.append( $('<input type="hidden" name="tag" id="bgimagetag" />') )
						.append( $('<input type="hidden" name="type" value="styler" />') )
						.append( $('<input type="hidden" name="do" value="update" />') )
					)
					.append(
						$('<div />')
							.append( $('<img class="iconify icon-edit-remove floatleft bgimage-remove" src="images/blank.gif" alt="" />') ) 
							.append( 'Remove Background Image')
							.append( clearboth )
					)
				)
				
				.append( $('<div class="split floatleft type-bgpos" />').text('Position') )
				.append( 
					$('<div class="split floatleft type-bgpos type-bgimage" />').append(
						$('<select class="fullwidth bgpos" />').append( BGPOS )
					) )
				.append( $('<div class="split floatleft type-bgrepeat type-bgimage" />').text('Repeat') )
				.append( 
					$('<div class="split floatleft type-bgrepeat" />').append(
						$('<select class="fullwidth bgrepeat" />').append( 
							'<option value="repeat">repeat</option>'+
							'<option value="no-repeat">no repeat</option>'+
							'<option value="repeat-x">repeat horiz.</option>'+
							'<option value="repeat-y">repeat vert.</option>' 
						)
					) 
				)
				.append( $('<div />').text('Background Colour') )
				.append( getSpectrumBox('colours-bg','Main Colour') )
				.append( getSpectrumBox('grad1','1st Colour') )
				.append( 
					$('<div class="split floatleft type-gradient" />').append( 
						$('<label />').append('Gradient: ').append(
							$('<input type="checkbox" class="gradient chkbox" />') 
						)
					).append( clearboth )
				)
				.append( clearboth )
				.append( getSpectrumBox('grad2','2nd Colour') )
				.append( 
					$('<div class="split floatleft type-gradheight type-gradient" />').append('Height: ').append(
						$('<select class="gradheight" />').each(function(){
							var inums = [3,5,10,20,30,40,50,75,100,150,200,300,400,500,600], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append(clearboth)
				)
				.append( clearboth )
			;
			
			var layouts = $('<div class="style-pop-settings type-layouts displaynone" />');
			layouts
				.append( 
					$('<div class="split floatleft type-borderwidth" />').append(
						$('<select class="borderwidth" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Border Width').append(clearboth)
				)
				.append( clearbothmargin )
				.append( $('<div class="split floatleft type-padding" />').append('<strong>Padding</strong>') )
				.append( 
					$('<div class="split floatleft type-padding" />').append(
						$('<input type="checkbox" class="padding-all" />')
					).append('All')
				)
				.append( clearboth )
				.append( 
					$('<div class="split floatleft type-padding" />').append(
						$('<select class="padding-top" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Top').append(clearboth)
				)
				.append( 
					$('<div class="split floatleft type-padding" />').append(
						$('<select class="padding-bottom" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Bottom').append(clearboth)
				)
				.append( clearboth )
				.append( 
					$('<div class="split floatleft type-padding" />').append(
						$('<select class="padding-left" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Left').append(clearboth)
				)
				.append( 
					$('<div class="split floatleft type-padding" />').append(
						$('<select class="padding-right" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Right').append(clearboth)
				)
				.append( clearbothmargin )
				.append( $('<div class="split floatleft type-margin" />').append('<strong>Margin</strong>') )
				.append( 
					$('<div class="split floatleft type-margin" />').append(
						$('<input type="checkbox" class="margin-all" />')
					).append('All')
				)
				.append( clearboth )
				.append( 
					$('<div class="split floatleft type-margin" />').append(
						$('<select class="margin-top" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Top').append(clearboth)
				)
				.append( 
					$('<div class="split floatleft type-margin" />').append(
						$('<select class="margin-bottom" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Bottom').append(clearboth)
				)
				.append( clearboth )
				.append( 
					$('<div class="split floatleft type-margin" />').append(
						$('<select class="margin-left" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Left').append(clearboth)
				)
				.append( 
					$('<div class="split floatleft type-margin" />').append(
						$('<select class="margin-right" />').each(function(){
							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Right').append(clearboth)
				)
				.append( clearbothmargin )
//				.append( 
//					$('<div class="split floatleft type-margin" />').append(
//						$('<select class="margin" />').each(function(){
//							var inums = [0,1,2,3,4,5,7,10,25], elN = $(this);
//							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
//						})
//					).append('Margin').append(clearboth)
//				)
				.append( 
					$('<div class="split floatleft type-rounded" />').append(
						$('<select class="corners" />').each(function(){
							var inums = [0,1,2,3,4,5,6,7,8,9,10,15,20,25], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
						})
					).append('Round Corners').append(clearboth)
				)
				.append( 
					$('<div class="split floatleft alignright type-shading" />').append( 
						$('<label />').append(
							$('<input type="checkbox" class="shading chkbox floatleft" />') 
						).append('Shadow')
					).append( clearboth )
				)
				.append( clearboth )
			;
			
			var typos = $('<div class="style-pop-settings type-typos displaynone" />');
			typos
				.append( 
					$('<div class="alignright type-fontsize" />').append(
						$('<select class="floatleft fontsize" />').each(function(){
							var inums = [8,9,10,11,12,13,14,16,18,20,24,28,32,36], elN = $(this);
							$.each(inums,function(){ elN.append( $('<option value="'+this+'px">'+this+'</option>') ) });
						})
					).append('Font size').append(clearboth)
				);
//				.append( 
//					$('<div class="alignright type-fontstyle" />').append(
//						$('<select class="floatleft fontstyle" />').each(function(){
//							var inums = ['none','bold','bold+underline','bold+italic','italic','italic+underline','bold+italic+underline'], elN = $(this);
//							$.each(inums,function(){ elN.append( $('<option>'+this+'</option>') ) });
//						})
//					).append('Font Style').append(clearboth)
//				)
			
			var underElements = $('<div class="style-pop-settings type-elements displaynone" />');
				
			$('.style-pop-inner',styler)
				.append( colour.append(colours) )
				.append( layout.append(layouts) )
				.append( typography.append(typos) )
				.append( underElement.append(underElements) )
			;
			colour.find('h3 a').addClass('bottom-image')
			
			return styler;
		}
		
		function setOrigVal(selector,cssType,side) { //this finds a value from a particular style attribute and sends it to the popup
			var sel = $(selector);
			if (side === undefined) side = '';
			switch (cssType) {
				case 'background-position':
					var isMenu = '';
					if ($(selector).metadata().ismenu == '1') isMenu = ' a:not(.hover):last'; 
					var thisSel = $(selector+isMenu);
					return thisSel.backgroundPosition();
					break;
				case 'background-repeat':
					var isMenu = '';
					if ($(selector).metadata().ismenu == '1') isMenu = ' a:not(.hover):last'; 
					var thisSel = $(selector+isMenu);
					return thisSel.css('background-repeat');
					break;
				case 'padding':
					if (side != '') return sel.padding()[side];
					else return sel.padding().left;
					break;
				case 'margin':
					sel = $(selector.replace('-inner',''));
					if (side != '') return sel.margin()[side];
					else return sel.margin().left;
					break;
				case 'border-width':
					var meta = $(NAMETAG).metadata(), bside = new Array();
					if (meta.bordersides !== undefined && meta.bordersides != '0') {
						bside = meta.bordersides.split('');
					}
					if (bside.length > 0) {
						var side = 0;
						$.each(bside,function(){
							if (this == 'l') { side = sel.border().left; return false; }
							if (this == 'r') { side = sel.border().right; return false; }
							if (this == 'b') { side = sel.border().bottom; return false; }
							if (this == 't') { side = sel.border().top; return false; }
						});
						return side;
					} else {
						return sel.border().left;
					}
					break;
				case 'font-size':
					return sel.css('font-size');
					break;
			}
		}
		
		
		function shadeEm(shaded){ 
			var tEl = $(EL),meta=tEl.metadata(), bgimage = URLPATH+'/../images/shading/shading', elInner = $(NAMETAG+SLICED), bW = 0, bC = '#888888';
			if (shaded) {
				tEl
					.find('.top-inner:first, .bottom-inner:last').css({ 
						'background-image':'url('+bgimage+'-tb.png)', 'background-color': 'transparent', 'background-repeat':'repeat-x'
					}).border({top:'0',left:'0',right:'0',bottom:'0'});
				tEl
					.find('.top-right:first, .bottom-right:last').css({
						'background-image':'url('+bgimage+'-trbr.png)', 'background-color': 'transparent', 'background-repeat':'no-repeat'
					});
				tEl
					.find('.top-left:first, .bottom-left:last').css({
						'background-image':'url('+bgimage+'-tlbl.png)', 'background-color': 'transparent', 'background-repeat':'no-repeat'
					});
				tEl
					.find('.body-left:first, .body-right:first').css({
						'background-image':'url('+bgimage+'-lr.png)', 'background-color': 'transparent', 'background-repeat':'repeat-y'
					});
				tEl.find('.body-left:first').css({'padding-right':'0px','padding-left':'10px','background-position':'0% 0%'});
				tEl.find('.body-right:first').css({'padding-right':'10px','padding-left':'0px','background-position':'100% 0%'});
				tEl.find('.top-left:first').css({'padding-right':'0px','padding-left':'10px','background-position':'0px 0px'});
				tEl.find('.top-right:first').css({'padding-right':'10px','padding-left':'0px','background-position':'100% 0%'});
				tEl.find('.top-inner:first').css({'height':'10px','background-position':'0px 0px','background-color':'transparent'});
				tEl.find('.bottom-inner:last').css({'height':'10px','background-position':'0px -10px','background-color':'transparent'});
				tEl.find('.bottom-left:last').css({'padding-right':'0px','padding-left':'10px','background-position':'0px -10px'});
				tEl.find('.bottom-right:last').css({'padding-right':'10px','padding-left':'0px','background-position':'100% -10px'});
				//fix the borders, set their colours
				elInner.css({'border-style':'solid'});
				bside = new Array();
				if (meta.bordersides != undefined && meta.bordersides != '0') {
					bside = meta.bordersides.split('');
				}
				var side = '';
				if (bside.length > 0) {
					$.each(bside,function(){
						if (this == 'l') { side = elInner.css('border-left-color'); return false; }
						if (this == 'r') { side = elInner.css('border-right-color'); return false; }
						if (this == 'b') { side = elInner.css('border-bottom-color'); return false; }
						if (this == 't') { side = elInner.css('border-top-color'); return false; }
					});
				} else {
					side = elInner.css('border-left-color');
				}
				if ($.inArray(side,NOSETTING) == -1) bC = side;
				bW = elInner.border().left;
				bW = bW == 0 ? bW.toString() : parseInt(bW);
				elInner.border({top:bW,right:bW,bottom:bW,left:bW}).css({'border-color':bC});
			} else {
				tEl.find('.top-left:first, .top-right:first, .body-left:first, .body-right:first, .bottom-left:last, .bottom-right:last')
					.css({'background-image':'none', 'padding':'0px'});
				tEl.find('.top-inner:first,.bottom-inner:last').css({'background-image':'none', 'height':'0px','background-color':'transparent'});
				//fix the borders, set their colours
				elInner.css({'border-style':'solid'});
				$(NAMETAG+' .top-inner:first, '+NAMETAG+' .bottom-inner:last').css({'border-style':'solid'});
				if ($.inArray(elInner.css('border-left-color'),NOSETTING) == -1)
					bC = elInner.css('border-left-color')
				bW = elInner.border().left;
				bW = bW == 0 ? bW.toString() : parseInt(bW);
				elInner.css({'border-color':bC}).border({top:'0',bottom:'0',right:bW,left:bW})
				$(NAMETAG+' .top-inner:first').border({top:bW,bottom:'0',right:'0',left:'0'}).css({'border-color':bC});
				$(NAMETAG+' .bottom-inner:last').border({bottom:bW,top:'0',right:'0',left:'0'}).css({'border-color':bC});
			}
			colWrap();
		}
		
		function setCSS(tag,cssType,cssVal,sliced,side) {
			if (side === undefined) side = '';
			switch (cssType) {
				case 'background-position':
					var isMenu = '';
					if ($(tag).metadata().ismenu == '1') isMenu = ' a:not(.hover):last'; 
					$(tag+sliced+isMenu).css({'background-position':cssVal});
					break;
				case 'background-repeat':
					var isMenu = '';
					if ($(tag).metadata().ismenu == '1') isMenu = ' a:not(.hover):last'; 
					$(tag+sliced+isMenu).css({'background-repeat':cssVal});
					break;
				case 'padding':
					cssVal = cssVal == 0 ? '0' : parseInt(cssVal);
					if (side != '') {
						switch (side) {
							case 'top': $(tag+sliced).padding({ top:cssVal }); break;
							case 'right': $(tag+sliced).padding({ right:cssVal }); break;
							case 'bottom': $(tag+sliced).padding({ bottom:cssVal }); break;
							case 'left': $(tag+sliced).padding({ left:cssVal }); break;
						}
					} else 
						$(tag+sliced).padding({ top:cssVal, right:cssVal, bottom:cssVal, left:cssVal});
					if ($(tag).metadata().colwrap == '1') colWrap();
					//colequal();
					break;
				case 'margin':
					cssVal = cssVal == 0 ? '0' : parseInt(cssVal);
					if (side != '') {
						switch (side) {
							case 'top': $(tag).margin({ top:cssVal }); break;
							case 'right': $(tag).margin({ right:cssVal }); break;
							case 'bottom': $(tag).margin({ bottom:cssVal }); break;
							case 'left': $(tag).margin({ left:cssVal }); break;
						}
					} else 
						$(tag).margin({ top:cssVal, right:cssVal, bottom:cssVal, left:cssVal});
					if ($(tag).metadata().colwrap == '1') colWrap();
					//colequal();
					break;
				case 'border-width':
					var tagslice = $(tag+sliced), meta = $(tag).metadata(), bside = new Array();
					cssVal = cssVal == 0 ? '0' : parseInt(cssVal);
					tagslice.css({'border-style':'solid'});
					
					if (meta.bordersides !== undefined && meta.bordersides != '0') {
						bside = meta.bordersides.split('');
					}
					var defaultSide = '';
					if (bside.length > 0) {
						$.each(bside,function(){
							if (this == 't' && $.inArray(defaultSide,['left','right','bottom']) == -1) defaultSide = 'top';
							if (this == 'b' && $.inArray(defaultSide,['left','right']) == -1) defaultSide = 'bottom';
							if (this == 'r' && $.inArray(defaultSide,['left']) == -1) defaultSide = 'right';
							if (this == 'l') defaultSide = 'left';
						});
					} else defaultSide = 'left';
					
					var bclr = $.inArray(tagslice.css('border-'+defaultSide+'-color'),NOSETTING) > -1 ? '#444444' : tagslice.css('border-'+defaultSide+'-color');
					if (sliced == '-inner' && !hasShade()) {
						if (bside.length > 0) {
							
							$.each(bside,function(){
								switch (this.toString()) {
									case 't': 
										if ($(tag+' .top-inner:first').css('border-style') != 'solid') $(tag+' .top-inner:first').css({'border-style':'solid'});
										$(tag+(sliced == '-inner' ? ' .top-inner:first':'')).css({'border-top-color':bclr}).border({top:cssVal,left:'0',right:'0',bottom:'0'});
										break;
									case 'r': 
										tagslice.border({ right:cssVal});
										break;
									case 'b': 
										if ($(tag+' .bottom-inner:last').css('border-style') != 'solid') $(tag+' .bottom-inner:last').css({'border-style':'solid'});
										$(tag+(sliced == '-inner' ? ' .bottom-inner:last':'')).css({'border-bottom-color':bclr}).border({bottom:cssVal,left:'0',right:'0',top:'0'});
										break;
									case 'l': 
										tagslice.border({ left:cssVal});
										break;
								}
							});
							tagslice.border({top:'0',bottom:'0'});
						} else {
							if ($(tag+' .top-inner:first').css('border-style') != 'solid') $(tag+' .top-inner:first').css({'border-style':'solid'});
							if ($(tag+' .bottom-inner:last').css('border-style') != 'solid') $(tag+' .bottom-inner:last').css({'border-style':'solid'});
							tagslice.border({ right:cssVal, left:cssVal,top:'0',bottom:'0'});
							$(tag+(sliced == '-inner' ? ' .top-inner:first':'')).css({'border-top-color':bclr}).border({top:cssVal,left:'0',right:'0',bottom:'0'});
							$(tag+(sliced == '-inner' ? ' .bottom-inner:last':'')).css({'border-bottom-color':bclr}).border({bottom:cssVal,left:'0',right:'0',top:'0'});
						}
					} else {
						if (bside.length > 0) {
							$.each(bside,function(){
								if (this == 't') tagslice.border({ top:cssVal });
								if (this == 'b') tagslice.border({ bottom:cssVal });
								if (this == 'r') tagslice.border({ right:cssVal });
								if (this == 'l') tagslice.border({ left:cssVal });
							});
							
							$.each(['t','l','b','r'],function(){
								if ($.inArray(this.toString(),bside) == -1) {
									switch (this.toString()) {
										case 't': tagslice.border({ top:'0' }); break;
										case 'b': tagslice.border({ bottom:'0' }); break;
										case 'r': tagslice.border({ right:'0' }); break;
										case 'l': tagslice.border({ left:'0' }); break;
									}
								}
							});
						} else {
							tagslice.border({ right:cssVal, left:cssVal,top:cssVal,bottom:cssVal});
						}
					}
					if (meta.colwrap == '1') colWrap();
					//colequal();
					break;
				case 'font-size':
					$(tag+sliced).css({'font-size':cssVal});
					break;
			}
		}
		
		
		function revertStore() {
			var 
				thisEl = $(NAMETAG), meta = thisEl.metadata(), thisInner = $(NAMETAG+SLICED), topInner = $(NAMETAG+' .top-inner'), 
				imageid = thisEl.find('input.imageid:first').val();
			if (typeof thisEl.data('revert') == 'undefined') thisEl.data('revert',{});
			if (typeof imageid != 'undefined' && imageid > 0 && !hasGrad() && !hasRound()) // if has imageid set and not rounded or gradient'd
				thisEl.data('revert').imageid = imageid;
			else 
				thisEl.data('revert').imageid = '';
			
			thisEl.data('revert').shading = hasShade() ? '1':'0';
			thisEl.data('revert').imagepath = meta.ismenu == '1' ? $(NAMETAG+SLICED+' a:not(.hover):last').css('background-image'): thisInner.css('background-image');
			thisEl.data('revert').bgpos = meta.ismenu == '1' ? $(NAMETAG+SLICED+' a:not(.hover):last').backgroundPosition(): thisInner.backgroundPosition();
			thisEl.data('revert').bgrepeat = meta.ismenu == '1' ? $(NAMETAG+SLICED+' a:not(.hover):last').css('background-repeat'): thisInner.css('background-repeat');
			
			if (meta.tag == '#header') 
				thisEl.data('revert').headheight = thisInner.height();
			
			if (hasRound()) {
				if ($('.top-inner:first',EL).size() > 0)
					thisEl.data('revert').rounded = topInner.innerHeight();
			} else {
				thisEl.data('revert').rounded = '0';
			}
			
			var seBGColor, seGradHeight = '200';
			if (hasGrad()) {
				seBGColor = thisEl.data('grad1');
				seGradHeight = thisEl.data('gradheight');
			} else {
				seBGColor = thisInner.css('background-color');
			}
				
			thisEl.data('revert').gradheight = seGradHeight;
			
			thisEl.data('revert').bgcolor = rgb2hex(seBGColor);
				
			if ( hasGrad() ) {
				thisEl.data('revert').gradient = '1';
				thisEl.data('revert').bgcolor2 = rgb2hex(thisInner.css('background-color'));
			} else {
				thisEl.data('revert').gradient = '0';
				thisEl.data('revert').bgcolor2 = 'transparent';
			}
			
			thisEl.data('revert').color = rgb2hex(thisInner.css('color'));
			
			if (typeof $('a:not(.hover):last',thisInner[0]).css('color') != 'undefined') {
				thisEl.data('revert').linkcolor = rgb2hex($('a:not(.hover):last',thisInner[0]).css('color'));
			} else thisEl.data('revert').linkcolor = 'inherit';
			
			if (typeof $('a.hover:last',thisInner[0]).css('color') != 'undefined') {
				thisEl.data('revert').linkcolorhover = rgb2hex($('a.hover:last',thisInner[0]).css('color'));
			} else thisEl.data('revert').linkcolorhover = 'inherit';
			
			if (typeof $('a:not(.hover):last',thisInner[0]).css('background-color') != 'undefined') {
				thisEl.data('revert').linkbgcolor = rgb2hex($('a:not(.hover):last',thisInner[0]).css('background-color'));
			} else thisEl.data('revert').linkbgcolor = 'transparent';

			if (typeof $('a.hover:last',thisInner[0]).css('background-color') != 'undefined') {
				thisEl.data('revert').linkbgcolorhover = rgb2hex($('a.hover:last',thisInner[0]).css('background-color'));
			} else thisEl.data('revert').linkbgcolorhover = 'transparent';
			
			if (typeof thisInner.css('border-left-color') != 'undefined') {
				thisEl.data('revert').bordercolor = rgb2hex(thisInner.css('border-left-color'));
			} else thisEl.data('revert').bordercolor = '#CCCCCC';
				
			var bside = new Array();
			if (meta.bordersides !== undefined && meta.bordersides != '0') {
				bside = meta.bordersides.split('');
			}
			if (bside.length > 0) {
				var side = '0';
				$.each(bside,function(){
					if (this == 'l') { side = thisInner.border().left; return false; }
					if (this == 'r') { side = thisInner.border().right; return false; }
					if (this == 'b') { side = thisInner.border().bottom; return false; }
					if (this == 't') { side = thisInner.border().top; return false; }
				});
				thisEl.data('revert').borderwidth = side;
			} else {
				thisEl.data('revert').borderwidth = thisInner.border().left;
			}
//			thisEl.data('revert').padding = thisInner.css('padding-left');
			thisEl.data('revert').paddingLeft = thisInner.padding().left;
			thisEl.data('revert').paddingRight = thisInner.padding().right;
			thisEl.data('revert').paddingTop = thisInner.padding().top;
			thisEl.data('revert').paddingBottom = thisInner.padding().bottom;
			thisEl.data('revert').marginLeft = thisEl.margin().left;
			thisEl.data('revert').marginRight = thisEl.margin().right;
			thisEl.data('revert').marginTop = thisEl.margin().top;
			thisEl.data('revert').marginBottom = thisEl.margin().bottom;
//			thisEl.data('revert').margin = thisEl.css('margin-left');
			thisEl.data('revert').fontsize= thisInner.css('font-size');
		}
		
		function revert() { 
			var 
				thisEl = $(NAMETAG), thisInner = $(NAMETAG+SLICED), topInner = $(NAMETAG+' .top-inner'), bottomInner = $(NAMETAG+' .bottom-inner');
					
			if (typeof thisEl.data('revert') != 'undefined') {
				var rev = thisEl.data('revert');
				
				if (thisEl.metadata().tag == '#header' && rev.headheight !== undefined) 
					thisInner.height(rev.headheight);
				
				thisInner.css('background-position',(rev.bgpos !== undefined ? rev.bgpos : '100% 100%'));
				thisInner.css('background-repeat',(rev.bgrepeat !== undefined ? rev.bgrepeat : 'repeat'));
				
				var seBGColor, seGradHeight = '200';
				if (rev.imagepath.match('gradient.php')) {
					thisEl.data('grad1',rev.bgcolor);
					thisEl.data('gradmem').grad1 = rev.bgcolor;
					seBGColor = rev.bgcolor;
					topInner.css('background-color',rev.bgcolor);
					seGradHeight = rev.gradheight;
					thisInner.css('background-color',rev.bgcolor2);
				} else {
					seBGColor = rev.bgcolor;
					//console.log(thisInner,seBGColor,typeof seBGColor) //use this with transparency revert bug
					thisInner.css('background-color',seBGColor);
				}
				
				thisEl.data('gradheight',seGradHeight);
				
				shadeEm(rev.shading !== undefined && rev.shading > 0 ? true : false);
				roundEm(rev.rounded != undefined && rev.rounded > 0 ? rev.rounded:'0');
				
				setCSS(NAMETAG,'border-width',rev.borderwidth,SLICED);
				if (SLICED == '-inner') {
					thisEl.css({'border-left-color':rev.bordercolor,'border-right-color':rev.bordercolor });
					topInner.css({'border-top-color':rev.bordercolor});
					bottomInner.css({'border-bottom-color':rev.bordercolor})
				} else {
					thisEl.css({'border-color':rev.bordercolor});
				}
				
				if (rev.imagepath !== undefined) {
					thisInner.css('background-image',rev.imagepath);
				} else {
					thisInner.css('background-image','none');
				}
				
				if (typeof rev.imageid != 'undefined' && rev.imageid > 0 && !hasGrad() && !hasRound()) {// if has imageid set and not rounded or gradient'd
					thisEl.find('input.imageid:first').val(rev.imageid);
				} else {
					thisEl.find('input.imageid:first').val('');
				}
				
				thisInner.css('color',rev.color);
				setCSS(NAMETAG,'padding',rev.paddingTop,SLICED,'top');	
				setCSS(NAMETAG,'padding',rev.paddingRight,SLICED,'right');	
				setCSS(NAMETAG,'padding',rev.paddingBottom,SLICED,'bottom');	
				setCSS(NAMETAG,'padding',rev.paddingLeft,SLICED,'left');	
				setCSS(NAMETAG,'margin',rev.marginTop,SLICED,'top');	
				setCSS(NAMETAG,'margin',rev.marginRight,SLICED,'right');	
				setCSS(NAMETAG,'margin',rev.marginBottom,SLICED,'bottom');	
				setCSS(NAMETAG,'margin',rev.marginLeft,SLICED,'left');	
//				setCSS(NAMETAG,'margin',rev.margin,SLICED);
				setCSS(NAMETAG,'font-size',rev.fontsize,SLICED);
				
				thisInner.find('a:not(.hover):last').css({'background-color':rev.linkbgcolor,'color':rev.linkcolor}); 
				thisInner.find('a.hover:last').css({'background-color':rev.linkbgcolorhover,'color':rev.linkcolorhover}); 
				
				linkc = thisInner.find('a').css('color'), linkcH = $('a.hover',activeEl).css('color'); 
				thisInner.find('a:not(.hover):last')
					.unbind('mouseenter mouseleave')
					.hover(
						function(){ $(this).css({ 'background-color':rev.linkbgcolorhover,'color':rev.linkcolorhover  });},
						function(){ $(this).css({ 'background-color':rev.linkbgcolor,'color':rev.linkcolor }); }
					);
					
				if (NAMETAG == '#body .teoti-points') {
					var cvsColOut = thisEl.find('a:first').css('color'), cvsColHover = thisEl.find('a.hover:first').css('color');
					thisEl.find('canvas')
						.each(function(){
							triGen(this,cvsColOut);
						})
						.unbind('mouseenter mouseleave').hover(
							function(){ 
								triGen(this,cvsColHover); 
							}
							,function(){ 
								triGen(this,cvsColOut); 
							}
						)
					;
				}
				
				$('#style-pop').hide();
				//$(TAGS).bind('mouseover',choosify);
				
			}
		}
		
		function remImg() {
			var elThis = $(NAMETAG), meta = elThis.metadata();
			$(NAMETAG+SLICED+(meta.ismenu == '1' ? ' a:not(.hover):last':'')).css('background-image','none');
			elThis.find('input.imageid').val('');
		}
		
		function remBgcolor() {
			elInner = $(NAMETAG+SLICED);
			elInner.css('background-color','transparent');
		}
		
		function closeStyler(){
			$('#style-pop').hide();
		}
		
		
		function setStylerPopup(el,ev) {
			//bind events
			var 
				meta = $(el).metadata(), styler = $('#style-pop'), sliced = meta.slice == 1 ? '-inner' : '', nameTag = meta.tag,
				textcolor=styler.find('.colours-text'),linkcolor=styler.find('.colours-link'),linkHcolor=styler.find('.colours-link-hover'),
				linkbgcolor=styler.find('.colours-link-bg'),linkHbgcolor=styler.find('.colours-link-bg-hover'),
				bordercolor=styler.find('.colours-border'),bgcolor=styler.find('.colours-bg'), gradi1=styler.find('.grad1'),gradi2=styler.find('.grad2'),
				colours = [
					[textcolor,'color',''],[linkcolor,'color','a'],[linkHcolor,'color','a.hover'],
					[linkHbgcolor,'background-color','a.hover'],[linkbgcolor,'background-color','a'],
					[bordercolor,'border-color',''],[bgcolor,'background-color',''],[gradi1,'grad1',''],[gradi2,'grad2','']
				];
			
			$('.bgpos').empty().append(BGPOS);
			
			if (meta.padding != '1') $('.type-padding').hide(); else $('.type-padding').show();
			if (meta.margin != '1') $('.type-margin').hide(); else $('.type-margin').show();
			if (meta.rounded != '1') $('.type-rounded').hide(); else $('.type-rounded').show();
			if (meta.shading != '1') $('.type-shading').hide(); else $('.type-shading').show();
			if (meta.gradient != '1') $('.type-gradient').hide(); else $('.type-gradient').show();
			if (meta.fontsize != '1') $('.type-fontsize').hide(); else $('.type-fontsize').show();
			if (meta.fontstyle != '1') $('.type-fontstyle').hide(); else $('.type-fontstyle').show();
			if (meta.borderwidth != '1') $('.type-borderwidth').hide(); else $('.type-borderwidth').show();
			if (meta.color != '1') textcolor.hide(); else textcolor.show(); 
			if (meta.linkcolor != '1') linkcolor.hide();  else linkcolor.show(); 
			if (meta.linkcolorhover != '1') linkHcolor.hide();  else linkHcolor.show(); 
			if (meta.linkbgcolor != '1') linkbgcolor.hide();  else linkbgcolor.show(); 
			if (meta.linkbgcolorhover != '1') linkHbgcolor.hide();  else linkHbgcolor.show(); 
			if (meta.bordercolor != '1') bordercolor.hide();  else bordercolor.show(); 
			
			
			var doRevert = true;
			if (NAMETAG == nameTag) doRevert = false;
			NAMETAG = nameTag; SLICED = sliced; EL = el;
			activeEl = nameTag+sliced;
			
			if (doRevert == true) revertStore();
			
			styler.find('#style-head').text(meta.friendly); 
			
			$('.bgimage[name=lFileName]').val('');
			$('.bgimage-remove').unbind('click').click(function(){ remImg();});

			//gradients
			$('.gradient').unbind('click').click(function(){ 
				gradCheck($(this).is(':checked'));
				if (hasShade()) shadeEm($('.shading').is(':checked'));
				if (hasRound()) roundEm($('.corners').val());
			});
			$('.gradheight').unbind('change').change(function(){ 
				gradCheck($('.gradient').is(':checked'),$(this).val());
				if (hasShade()) shadeEm($('.shading').is(':checked'));
				if (hasRound()) roundEm($('.corners').val());
			});
			//rounded corners
			$('.corners').unbind('change').change(function(){ 
				if (hasShade()) {
					shadeEm(false);
					$('.shading')[0].checked=false;
				}
				roundEm($(this).val());
				gradCheck($('.gradient').is(':checked'),$('.gradheight').val());
			});
			if (!hasGrad()) {
				if (meta.bgcolor != '1') bgcolor.hide();  else bgcolor.show();
				if (meta.bgimage != '1') $('.type-bgimage').hide(); else $('.type-bgimage').show();
				if (meta.bgrepeat != '1') $('.type-bgrepeat').hide(); else $('.type-bgrepeat').show();
				if (meta.bgpos != '1') $('.type-bgpos').hide(); else $('.type-bgpos').show();
				$('.grad1, .grad2, .type-gradheight').hide();
			}
			//shading
			$('.shading').unbind('click').click(function(){ 
				gradCheck($('.gradient').is(':checked'));
				if (!$('.corners').is(':checked')) {
					shadeEm($(this).is(':checked'));
					$('.corners').val('0');
				}
				if (!$('.shading').is(':checked')) {
					roundEm($('.corners').val());
					$('.shading')[0].checked = false;
				}
			});
			$('.corners').val( getRoundHeight() );
			$('.shading')[0].checked = hasShade() ? true : false;
//			if (!hasShade()) 
			if (meta.rounded == '1' && hasRound()){
				roundEm($('.corners').val());
			} else $('.corners').val('0');
			if (meta.gradient == '1' && hasGrad()) {
				gradCheck();
			} else $('.gradient')[0].checked = false;
//			if (!hasRound()) shadeEm($('.shading').is(':checked'));
			
			$.each(colours,function(){ spectrumize(el,this[0],this[1],this[2]); });
			
			//show/hide
			
			//select boxes
			var selStyle = [
				['.bgpos','background-position'],['.bgrepeat','background-repeat'],['.borderwidth','border-width'],
				['.padding-top','padding','top'],['.padding-right','padding','right'],
				['.padding-bottom','padding','bottom'],['.padding-left','padding','left'],
				['.margin-top','margin','top'],['.margin-right','margin','right'],
				['.margin-bottom','margin','bottom'],['.margin-left','margin','left'],
				['.fontsize','font-size']
			];
			
			$.each(selStyle,function(){
				var 
					arr = this,part = styler.find(arr[0]), 
					cssOrigVal = (arr[2]!==undefined ? setOrigVal(nameTag+sliced,arr[1],arr[2]):setOrigVal(nameTag+sliced,arr[1]));
				part.val(cssOrigVal);
				part
					.unbind('change')
					.change(function(){
						var allChecked=false;
						if (arr[1] == 'padding' && $('.padding-all').is(':checked')) allChecked = true;
						if (arr[1] == 'margin' && $('.margin-all').is(':checked')) allChecked = true;
						if (allChecked) $('.'+arr[1]+'-left, .'+arr[1]+'-right, .'+arr[1]+'-top, .'+arr[1]+'-bottom,').val($(this).val());
						arr[2] !== undefined && !allChecked ? setCSS(meta.tag,arr[1],$(this).val(),sliced,arr[2]):setCSS(meta.tag,arr[1],$(this).val(),sliced);
						if (hasShade()) shadeEm($('.shading').is(':checked'));
						if (hasRound()) roundEm($('.corners').val());
					});
			});
			
			$('#bgimagetag').val(meta.tag);
			/*
			var 
				wholeX = $(window).width() + $(window).scrollLeft(),
				wholeY = (window.innerHeight ? window.innerHeight : $(window).height()) + $(window).scrollTop(),
				winScrollTop = $(window).scrollTop(), winScrollLeft = $(window).scrollLeft();
			
			//reposition
			var 
				evPageY = ev.pageY, evPageX = ev.pageX, 
				posTop  = (evPageY +styler.outerHeight()) < (wholeY) ? evPageY : evPageY - styler.outerHeight()+winScrollTop,
				posLeft = (evPageX+styler.outerWidth())  < (wholeX) ? evPageX : evPageX - styler.outerWidth()+winScrollLeft;
				
			posTop = posTop < 0 ? 0 : posTop;
			posLeft = posLeft < 0 ? 0 : posLeft;*/
			
			prevElements();
			coord = viewPos(styler.outerWidth(),styler.outerHeight(),ev.pageX,ev.pageY);
			styler.hide().css({'top':coord.posTop+'px','left':coord.posLeft+'px'}).show();
		}
		
		if ($('#style-pop').size() == 0) {
			$('body').append(createStylerPopup());
			$('#style-pop').draggable({handle:'#style-handle',containment:'#whole'});
		}
		
		return this.each(function() {  
			$(this).unbind('click').click(function(ev){
				choosify(this,true);
				setStylerPopup(this,ev);
				ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;
			}).mouseover(function(ev){
				choosify(this);
				ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;
			});
		});
	};
})(jQuery);


$(function(){

	$('body').append( $('<div class="loading-box" />').append('<img src="images/loading.gif" alt="Loading" /> Loading ')
	).ajaxStart(function(){ 
			$('.loading-box').css({'top':($(window).scrollTop()+10)+'px','left':(($(window).width()/2)-($('.loading-box').width()/2))+'px'}).show();
	}).ajaxStop(function(){ 
		$('.loading-box').hide();
	}).ajaxError(function(ev, request, settings, thrown){
		$('.loading-box').text('Error!')
		alert("Error requesting: "+settings.url+"\n\nThere was an error with loading the request you made. It would be advisory to refresh the page as the last change you made may not have been instantiated; possibly due to internet connection fault. ");
		$('.loading-box').hide();
	}).append(
		'<div id="choose-left"><!-- --></div><div id="choose-right"><!-- --></div>'+
		'<div id="choose-top"><!-- --></div><div id="choose-bottom"><!-- --></div>'+
		'<div id="dims"><span><!-- --></span> (w:<span><!-- --></span>px, h:<span><!-- --></span>px)</div>'
	);
	$(window).unload(function(){
		$('.loading-box').css({'top':($(window).scrollTop()+10)+'px','left':(($(window).width()/2)-($('.loading-box').width()/2))+'px'}).show();
	});
	
	//$('a:not(.ignore)').hover(function() { $(this).addClass('hover');}, function() { $(this).removeClass('hover'); });
	
	var clwrparr = new Array(), iscolarr = new Array();
	
	<?
	$result = mysql_query('
		SELECT eStyleTags.*'.($session->styleid || $_GET['thid'] ? ', eStyleElements.*':'').'
		FROM '.($session->styleid || $_GET['thid'] ? 'eStyle
		LEFT JOIN eStyleElements ON (seStyle = sID)
		LEFT JOIN eStyleTags ON (stID = seTag)
		WHERE sID = \''.mysql_real_escape_string($_GET['thid'] > 0 ? $_GET['thid']:$session->styleid).'\'':'eStyleTags').'
		') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
	$tags = $grads = '['; $i = 0;
	while ($row = mysql_fetch_object($result)) {
		$tags .= $i ? ',' : '';
		$grads .= $i++ ? ',' : '';
		$grads .= '[\''.jsonparse($row->stName).'\',\''.jsonparse($row->seBGColor).'\',\''.jsonparse($row->seGradHeight).
							'\',\''.jsonparse($row->seBGImage).'\']';
	}
	$grads .= ']';
	$tags .= ']';
	?>
	var grads = <?= $grads ?>;
	<?/*var tagnames = <?= $tags ?>;*/?>
	$(TAGS).each(function(){ 
		var meta = $(this).metadata(), thisEl = $(this);
		$.each(grads,function(){
			if (this[0] == meta.tag) {
				thisEl.data('grad1',this[1]).data('gradheight',this[2]);
				thisEl.find('input.imageid').val(this[3]);
			}
		});
		if (meta.colwrap == 1) clwrparr[clwrparr.length] = meta.tag; 
		if (meta.iscol == 1) iscolarr[iscolarr.length] = meta.tag; 
	}).styler();
	COLWRAP = clwrparr.join(',');
	ISCOL = iscolarr.join(',');
	//set the universal data variables
	<? $uni = mysql_single('SELECT * FROM eStyle WHERE sID = \''.mysql_real_escape_string($_GET['thid'] > 0 ? $_GET['thid'] : $session->styleid).'\'',__LINE__.__FILE__); ?>
	$('body').data('universal',{
		'font':'<?= jsonparse($uni->sUniFont) ?>'
		,'facelift':'<?= jsonparse($uni->sFaceLift) ?>'
		,'flash':'<?= jsonparse($uni->sFlash) ?>'
		,'pagewidth':'<?= jsonparse($uni->sPageWidth) ?>'
		,'pageposition':'<?= jsonparse($uni->sPagePosition) ?>'
		,'colleftwidth':'<?= jsonparse($uni->sColLeftWidth) ?>'
		,'colrightwidth':'<?= jsonparse($uni->sColRightWidth) ?>'
		,'sID':'<?= jsonparse($uni->sID) ?>'
	});
	//colequal();
	$('#sFacelift')[0].checked = <?= $uni->sFacelift ? 'true':'false' ?>;
	$('#sFlash')[0].checked = <?= $uni->sFlash? 'false':'true' ?>;
	$('#sBGAttach').change(function(){
		$('#whole').css('background-attachment',($(this).val() == '1' ? 'fixed':'scroll'));
	}).val('<?= $uni->sBGAttach ?>');
	$('#pagewidth').change(function(){
	$('#main-outer').css({'width':$(this).val()+'px'});
		colWrap();
	}).val('<?= jsonparse($uni->sPageWidth) ?>');
	var colsize = function(){
		$(this).css({'left':'0','height':'auto'})
		$('#col-right-outer, #content-col, #content-inner').css({'height':'auto'});
		$('#content-col').width($('#body-inner').innerWidth()-( 
				//($('#col-left-outer').isVisible() ? $('#col-left-outer').outerWidth():0)+
				($('#col-right-outer').isVisible() ? $('#col-right-outer').outerWidth():0)
			) 
		)
	}
	//$('#col-left-inner').resizable({ resize:colsize, handles:'e', alsoResize:$('#col-left-outer'), autoHide:true, maxWidth:400, minWidth:100, stop:function(){$(this).css('width','auto'); } });
	$('#col-right-inner').resizable({ resize:colsize, handles:'w', alsoResize:$('#col-right-outer'), autoHide:true, maxWidth:400, minWidth:235, stop:function(){$(this).css('width','auto'); } });
	/*$('#col-left-inner .ui-resizable-handle, #col-right-inner .ui-resizable-handle').click(function(){return false;}).dblclick(function(){
		var elID = $(this).parent().attr('id'), side = (elID == 'col-left-inner') ? 'w': 'e';
		$('#'+elID.replace('inner','outer')).hide(); 
		colsize(); 
		$('#content-inner').append(
			$('<div class="ui-resizable-handle ui-resizable-'+side+'" />')
				.click(function(){return false;})
				.dblclick(function(){
					$('#'+elID.replace('inner','outer')).show();
					colsize();
					$(this).hide();
				})
		)
		
		return false;
	});*/
	$('#header-inner').resizable({ handles:'s', autoHide:true, minHeight:20, maxHeight:340, stop:function(){ $(this).css({'width':'auto'}); } });
	$('.flashheader').click(function(){
		$('<div id="flashheaderdialog" title="Upload Flash Header" />').append(
			$('<form action="'+URLPATH+'/iframe.php" enctype="multipart/form-data" id="flashform" target="upload_iframe" method="post" />')
				.submit(function(){
					$('#iframe_div').html('<iframe name="upload_iframe" id="upload_iframe" src="about:blank"></iframe>');
				})
				.append( 'Flash File' )
				.append( $('<input type="file" name="flashheader" />') )
				.append( $('<input type="hidden" name="type" value="flashheader" />') )
				.append( $('<input type="hidden" name="transparent" value="'+($('#sFlashTransparent').is(':checked') ? '1':'0')+'" />') )
				.append( $('<input type="hidden" name="do" value="insert" />') )
		).dialog({
			bgiframe: true, autoOpen: true, modal: true, resizable:false,
			start:function(){
				$(this)
					.click(function(ev,ui){ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;})
					.mouseover(function(ev,ui){ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;});
			},
			close:function(){$(this).dialog('destroy').remove();},
			buttons:{
				'Ok':function() { $('#flashform').submit(); $(this).dialog('close'); $(this).dialog('destroy').remove(); },
				'Cancel':function() { $(this).dialog('close'); $(this).dialog('destroy').remove(); }
			}
		});
	});
	
	$('.ownfont').click(function(){
		$('<div id="ownfontheaderdialog" title="Upload Own Font" />').append(
			$('<form action="'+URLPATH+'/iframe.php" enctype="multipart/form-data" id="fontform" target="upload_iframe" method="post" />')
				.submit(function(){
					$('#iframe_div').html('<iframe name="upload_iframe" id="upload_iframe" src="about:blank"></iframe>');
				})
				.append( 'Font File (must be .otf or .ttf' )
				.append( $('<input type="file" name="font[]" />') )
				.append( $('<input type="hidden" name="type" value="flir" />') )
				.append( $('<input type="hidden" name="do" value="insert" />') )
				.append('<br />')
				/*.append( 
					$('<a href="#">')
						.text('Remove Font File')
						.click(function(){
							$.get(PHP_SELF,{'do':'ajax','type':'removefontfile'});
							$(this).dialog('destroy').remove();
							alert('The font file has been removed successfully.');
							return false;
						}) 
				)*/
		).dialog({
			bgiframe: true, autoOpen: true, modal: true, resizable:false,
			start:function(){
				$(this)
					.click(function(ev,ui){ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;})
					.mouseover(function(ev,ui){ev.stopPropagation ? ev.stopPropagation() : ev.cancelBubble = true;});
			},
			close:function(){$(this).dialog('destroy').remove();},
			buttons:{
				'Ok':function() { $('#fontform').submit(); $(this).dialog('close'); $(this).dialog('destroy').remove(); },
				'Cancel':function() { $(this).dialog('close'); $(this).dialog('destroy').remove(); }
			}
		});
	});
	
	$('#sFlashWidth').val('<?= $uni->sFlashWidth > 0 ? $uni->sFlashWidth : 650 ?>');
	$('#sFlashHeight').val('<?= $uni->sFlashHeight > 0 ? $uni->sFlashHeight : (str_replace('px','',$uni->sHeaderHeight) > 0 ? str_replace('px','',$uni->sHeaderHeight) : 100) ?>');
	$('#sFlashPosition').change(function(){
		switch ($(this).val()) {
			case 'Left': 
				$('#header-inner').css({'text-align':'left'});
				$('#header-flash').css({'margin':'0px auto 0px 0px'}); 
				break;
			case 'Right': 
				$('#header-inner').css({'text-align':'right'});
				$('#header-flash').css({'margin':'0px 0px 0px auto'}); 
				break;
			default: case 'Center': 
				$('#header-inner').css({'text-align':'center'});
				$('#header-flash').css({'margin':'0px auto'}); 
				break;
		}
	}).val('<?=jsonparse(ucfirst($uni->sFlashPosition))?>');
	$('#sFlashTransparent')[0].checked = <?= $uni->sFlashTransparent ? 'true':'false' ?>;
	//flashfile:<?= PATH.'/lib/flash/'.$session->styleid.'.swf' ?>
	
	<? if (file_exists(PATH.'/lib/flash/'.($_GET['thid'] ? $_GET['thid'] : $session->styleid).'.swf')) { ?>
	$('#header-flash').flash({
		'swf':'<?= jsonparse(str_replace('/lib','',URLPATH).'/lib/flash/'.($_GET['thid'] ? $_GET['thid'] : $session->styleid).'.swf') ?>',
		'width':$('#sFlashWidth').val(),'height':$('#sFlashHeight').val(),
		'params':{'wmode':($('#sFlashTransparent').is(':checked') ? 'transparent':'opaque')}
	});
	<? }
	
	if (!$uni->sFlash) {?>
	$('#header-flash').hide();
	<? } ?>
	
	var cvsColOut = $('#body .teoti-points a:first').css('color'), cvsColHover = $('#body .teoti-points a.hover:first').css('color');
	$('#body .teoti-points canvas')
		.each(function(){
			triGen(this,cvsColOut);
		})
		.hover(
			function(){ 
				triGen(this,cvsColHover); 
			}
			,function(){ 
				triGen(this,cvsColOut); 
			}
		)
	;
	
	$('#pageposition').change(function(){
		switch ($(this).val()) {
			case 'Left': 
				$('#whole').css({'text-align':'left'});
				$('#main-outer').css({'margin':'0px auto 0px 0px'}); 
				break;
			case 'Right': 
				$('#whole').css({'text-align':'right'});
				$('#main-outer').css({'margin':'0px 0px 0px auto'}); 
				break;
			default: case 'Center': 
				$('#whole').css({'text-align':'center'});
				$('#main-outer').css({'margin':'0px auto'}); 
				break;
		}
	}).val('<?=jsonparse(ucfirst($uni->sPagePosition))?>');
	<? $row = mysql_single('SELECT * FROM eStyleUniFonts WHERE sufID = \''.mysql_real_escape_string($uni->sUniFont).'\'',__LINE__.__FILE__); 
?>$('#unifont').change(function(){
		var ff = $(this).val().split('|');
		$('#whole').css({'font-family':ff[1]});
	}).val('<?= jsonparse(($uni->sUniFont).'|'.$row->sufFamily) ?>');
	
	window.onbeforeunload = function(){ if (!UNLOAD) { return 'Recent changes may be lost if you haven\'t saved them.'; } };
});
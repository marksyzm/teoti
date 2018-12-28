// draggable plugin 
(function(d){d.widget("ui.draggable",d.ui.mouse,{widgetEventPrefix:"drag",options:{addClasses:true,appendTo:"parent",axis:false,connectToSortable:false,containment:false,cursor:"auto",cursorAt:false,grid:false,handle:false,helper:"original",iframeFix:false,opacity:false,refreshPositions:false,revert:false,revertDuration:500,scope:"default",scroll:true,scrollSensitivity:20,scrollSpeed:20,snap:false,snapMode:"both",snapTolerance:20,stack:false,zIndex:false},_create:function(){if(this.options.helper==
"original"&&!/^(?:r|a|f)/.test(this.element.css("position")))this.element[0].style.position="relative";this.options.addClasses&&this.element.addClass("ui-draggable");this.options.disabled&&this.element.addClass("ui-draggable-disabled");this._mouseInit()},destroy:function(){if(this.element.data("draggable")){this.element.removeData("draggable").unbind(".draggable").removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled");this._mouseDestroy();return this}},_mouseCapture:function(a){var b=
this.options;if(this.helper||b.disabled||d(a.target).is(".ui-resizable-handle"))return false;this.handle=this._getHandle(a);if(!this.handle)return false;return true},_mouseStart:function(a){var b=this.options;this.helper=this._createHelper(a);this._cacheHelperProportions();if(d.ui.ddmanager)d.ui.ddmanager.current=this;this._cacheMargins();this.cssPosition=this.helper.css("position");this.scrollParent=this.helper.scrollParent();this.offset=this.positionAbs=this.element.offset();this.offset={top:this.offset.top-
this.margins.top,left:this.offset.left-this.margins.left};d.extend(this.offset,{click:{left:a.pageX-this.offset.left,top:a.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()});this.originalPosition=this.position=this._generatePosition(a);this.originalPageX=a.pageX;this.originalPageY=a.pageY;b.cursorAt&&this._adjustOffsetFromHelper(b.cursorAt);b.containment&&this._setContainment();if(this._trigger("start",a)===false){this._clear();return false}this._cacheHelperProportions();
d.ui.ddmanager&&!b.dropBehaviour&&d.ui.ddmanager.prepareOffsets(this,a);this.helper.addClass("ui-draggable-dragging");this._mouseDrag(a,true);return true},_mouseDrag:function(a,b){this.position=this._generatePosition(a);this.positionAbs=this._convertPositionTo("absolute");if(!b){b=this._uiHash();if(this._trigger("drag",a,b)===false){this._mouseUp({});return false}this.position=b.position}if(!this.options.axis||this.options.axis!="y")this.helper[0].style.left=this.position.left+"px";if(!this.options.axis||
this.options.axis!="x")this.helper[0].style.top=this.position.top+"px";d.ui.ddmanager&&d.ui.ddmanager.drag(this,a);return false},_mouseStop:function(a){var b=false;if(d.ui.ddmanager&&!this.options.dropBehaviour)b=d.ui.ddmanager.drop(this,a);if(this.dropped){b=this.dropped;this.dropped=false}if(!this.element[0]||!this.element[0].parentNode)return false;if(this.options.revert=="invalid"&&!b||this.options.revert=="valid"&&b||this.options.revert===true||d.isFunction(this.options.revert)&&this.options.revert.call(this.element,
b)){var c=this;d(this.helper).animate(this.originalPosition,parseInt(this.options.revertDuration,10),function(){c._trigger("stop",a)!==false&&c._clear()})}else this._trigger("stop",a)!==false&&this._clear();return false},cancel:function(){this.helper.is(".ui-draggable-dragging")?this._mouseUp({}):this._clear();return this},_getHandle:function(a){var b=!this.options.handle||!d(this.options.handle,this.element).length?true:false;d(this.options.handle,this.element).find("*").andSelf().each(function(){if(this==
a.target)b=true});return b},_createHelper:function(a){var b=this.options;a=d.isFunction(b.helper)?d(b.helper.apply(this.element[0],[a])):b.helper=="clone"?this.element.clone():this.element;a.parents("body").length||a.appendTo(b.appendTo=="parent"?this.element[0].parentNode:b.appendTo);a[0]!=this.element[0]&&!/(fixed|absolute)/.test(a.css("position"))&&a.css("position","absolute");return a},_adjustOffsetFromHelper:function(a){if(typeof a=="string")a=a.split(" ");if(d.isArray(a))a={left:+a[0],top:+a[1]||
0};if("left"in a)this.offset.click.left=a.left+this.margins.left;if("right"in a)this.offset.click.left=this.helperProportions.width-a.right+this.margins.left;if("top"in a)this.offset.click.top=a.top+this.margins.top;if("bottom"in a)this.offset.click.top=this.helperProportions.height-a.bottom+this.margins.top},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var a=this.offsetParent.offset();if(this.cssPosition=="absolute"&&this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],
this.offsetParent[0])){a.left+=this.scrollParent.scrollLeft();a.top+=this.scrollParent.scrollTop()}if(this.offsetParent[0]==document.body||this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&d.browser.msie)a={top:0,left:0};return{top:a.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:a.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if(this.cssPosition=="relative"){var a=this.element.position();return{top:a.top-
(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:a.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}else return{top:0,left:0}},_cacheMargins:function(){this.margins={left:parseInt(this.element.css("marginLeft"),10)||0,top:parseInt(this.element.css("marginTop"),10)||0}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var a=this.options;if(a.containment==
"parent")a.containment=this.helper[0].parentNode;if(a.containment=="document"||a.containment=="window")this.containment=[(a.containment=="document"?0:d(window).scrollLeft())-this.offset.relative.left-this.offset.parent.left,(a.containment=="document"?0:d(window).scrollTop())-this.offset.relative.top-this.offset.parent.top,(a.containment=="document"?0:d(window).scrollLeft())+d(a.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(a.containment=="document"?
0:d(window).scrollTop())+(d(a.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top];if(!/^(document|window|parent)$/.test(a.containment)&&a.containment.constructor!=Array){var b=d(a.containment)[0];if(b){a=d(a.containment).offset();var c=d(b).css("overflow")!="hidden";this.containment=[a.left+(parseInt(d(b).css("borderLeftWidth"),10)||0)+(parseInt(d(b).css("paddingLeft"),10)||0)-this.margins.left,a.top+(parseInt(d(b).css("borderTopWidth"),
10)||0)+(parseInt(d(b).css("paddingTop"),10)||0)-this.margins.top,a.left+(c?Math.max(b.scrollWidth,b.offsetWidth):b.offsetWidth)-(parseInt(d(b).css("borderLeftWidth"),10)||0)-(parseInt(d(b).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left,a.top+(c?Math.max(b.scrollHeight,b.offsetHeight):b.offsetHeight)-(parseInt(d(b).css("borderTopWidth"),10)||0)-(parseInt(d(b).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top]}}else if(a.containment.constructor==
Array)this.containment=a.containment},_convertPositionTo:function(a,b){if(!b)b=this.position;a=a=="absolute"?1:-1;var c=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,f=/(html|body)/i.test(c[0].tagName);return{top:b.top+this.offset.relative.top*a+this.offset.parent.top*a-(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():
f?0:c.scrollTop())*a),left:b.left+this.offset.relative.left*a+this.offset.parent.left*a-(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():f?0:c.scrollLeft())*a)}},_generatePosition:function(a){var b=this.options,c=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,f=/(html|body)/i.test(c[0].tagName),e=a.pageX,g=a.pageY;
if(this.originalPosition){if(this.containment){if(a.pageX-this.offset.click.left<this.containment[0])e=this.containment[0]+this.offset.click.left;if(a.pageY-this.offset.click.top<this.containment[1])g=this.containment[1]+this.offset.click.top;if(a.pageX-this.offset.click.left>this.containment[2])e=this.containment[2]+this.offset.click.left;if(a.pageY-this.offset.click.top>this.containment[3])g=this.containment[3]+this.offset.click.top}if(b.grid){g=this.originalPageY+Math.round((g-this.originalPageY)/
b.grid[1])*b.grid[1];g=this.containment?!(g-this.offset.click.top<this.containment[1]||g-this.offset.click.top>this.containment[3])?g:!(g-this.offset.click.top<this.containment[1])?g-b.grid[1]:g+b.grid[1]:g;e=this.originalPageX+Math.round((e-this.originalPageX)/b.grid[0])*b.grid[0];e=this.containment?!(e-this.offset.click.left<this.containment[0]||e-this.offset.click.left>this.containment[2])?e:!(e-this.offset.click.left<this.containment[0])?e-b.grid[0]:e+b.grid[0]:e}}return{top:g-this.offset.click.top-
this.offset.relative.top-this.offset.parent.top+(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollTop():f?0:c.scrollTop()),left:e-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():f?0:c.scrollLeft())}},_clear:function(){this.helper.removeClass("ui-draggable-dragging");this.helper[0]!=
this.element[0]&&!this.cancelHelperRemoval&&this.helper.remove();this.helper=null;this.cancelHelperRemoval=false},_trigger:function(a,b,c){c=c||this._uiHash();d.ui.plugin.call(this,a,[b,c]);if(a=="drag")this.positionAbs=this._convertPositionTo("absolute");return d.Widget.prototype._trigger.call(this,a,b,c)},plugins:{},_uiHash:function(){return{helper:this.helper,position:this.position,originalPosition:this.originalPosition,offset:this.positionAbs}}});d.extend(d.ui.draggable,{version:"1.8.8"});
d.ui.plugin.add("draggable","connectToSortable",{start:function(a,b){var c=d(this).data("draggable"),f=c.options,e=d.extend({},b,{item:c.element});c.sortables=[];d(f.connectToSortable).each(function(){var g=d.data(this,"sortable");if(g&&!g.options.disabled){c.sortables.push({instance:g,shouldRevert:g.options.revert});g._refreshItems();g._trigger("activate",a,e)}})},stop:function(a,b){var c=d(this).data("draggable"),f=d.extend({},b,{item:c.element});d.each(c.sortables,function(){if(this.instance.isOver){this.instance.isOver=
0;c.cancelHelperRemoval=true;this.instance.cancelHelperRemoval=false;if(this.shouldRevert)this.instance.options.revert=true;this.instance._mouseStop(a);this.instance.options.helper=this.instance.options._helper;c.options.helper=="original"&&this.instance.currentItem.css({top:"auto",left:"auto"})}else{this.instance.cancelHelperRemoval=false;this.instance._trigger("deactivate",a,f)}})},drag:function(a,b){var c=d(this).data("draggable"),f=this;d.each(c.sortables,function(){this.instance.positionAbs=
c.positionAbs;this.instance.helperProportions=c.helperProportions;this.instance.offset.click=c.offset.click;if(this.instance._intersectsWith(this.instance.containerCache)){if(!this.instance.isOver){this.instance.isOver=1;this.instance.currentItem=d(f).clone().appendTo(this.instance.element).data("sortable-item",true);this.instance.options._helper=this.instance.options.helper;this.instance.options.helper=function(){return b.helper[0]};a.target=this.instance.currentItem[0];this.instance._mouseCapture(a,
true);this.instance._mouseStart(a,true,true);this.instance.offset.click.top=c.offset.click.top;this.instance.offset.click.left=c.offset.click.left;this.instance.offset.parent.left-=c.offset.parent.left-this.instance.offset.parent.left;this.instance.offset.parent.top-=c.offset.parent.top-this.instance.offset.parent.top;c._trigger("toSortable",a);c.dropped=this.instance.element;c.currentItem=c.element;this.instance.fromOutside=c}this.instance.currentItem&&this.instance._mouseDrag(a)}else if(this.instance.isOver){this.instance.isOver=
0;this.instance.cancelHelperRemoval=true;this.instance.options.revert=false;this.instance._trigger("out",a,this.instance._uiHash(this.instance));this.instance._mouseStop(a,true);this.instance.options.helper=this.instance.options._helper;this.instance.currentItem.remove();this.instance.placeholder&&this.instance.placeholder.remove();c._trigger("fromSortable",a);c.dropped=false}})}});d.ui.plugin.add("draggable","cursor",{start:function(){var a=d("body"),b=d(this).data("draggable").options;if(a.css("cursor"))b._cursor=
a.css("cursor");a.css("cursor",b.cursor)},stop:function(){var a=d(this).data("draggable").options;a._cursor&&d("body").css("cursor",a._cursor)}});d.ui.plugin.add("draggable","iframeFix",{start:function(){var a=d(this).data("draggable").options;d(a.iframeFix===true?"iframe":a.iframeFix).each(function(){d('<div class="ui-draggable-iframeFix" style="background: #fff;"></div>').css({width:this.offsetWidth+"px",height:this.offsetHeight+"px",position:"absolute",opacity:"0.001",zIndex:1E3}).css(d(this).offset()).appendTo("body")})},
stop:function(){d("div.ui-draggable-iframeFix").each(function(){this.parentNode.removeChild(this)})}});d.ui.plugin.add("draggable","opacity",{start:function(a,b){a=d(b.helper);b=d(this).data("draggable").options;if(a.css("opacity"))b._opacity=a.css("opacity");a.css("opacity",b.opacity)},stop:function(a,b){a=d(this).data("draggable").options;a._opacity&&d(b.helper).css("opacity",a._opacity)}});d.ui.plugin.add("draggable","scroll",{start:function(){var a=d(this).data("draggable");if(a.scrollParent[0]!=
document&&a.scrollParent[0].tagName!="HTML")a.overflowOffset=a.scrollParent.offset()},drag:function(a){var b=d(this).data("draggable"),c=b.options,f=false;if(b.scrollParent[0]!=document&&b.scrollParent[0].tagName!="HTML"){if(!c.axis||c.axis!="x")if(b.overflowOffset.top+b.scrollParent[0].offsetHeight-a.pageY<c.scrollSensitivity)b.scrollParent[0].scrollTop=f=b.scrollParent[0].scrollTop+c.scrollSpeed;else if(a.pageY-b.overflowOffset.top<c.scrollSensitivity)b.scrollParent[0].scrollTop=f=b.scrollParent[0].scrollTop-
c.scrollSpeed;if(!c.axis||c.axis!="y")if(b.overflowOffset.left+b.scrollParent[0].offsetWidth-a.pageX<c.scrollSensitivity)b.scrollParent[0].scrollLeft=f=b.scrollParent[0].scrollLeft+c.scrollSpeed;else if(a.pageX-b.overflowOffset.left<c.scrollSensitivity)b.scrollParent[0].scrollLeft=f=b.scrollParent[0].scrollLeft-c.scrollSpeed}else{if(!c.axis||c.axis!="x")if(a.pageY-d(document).scrollTop()<c.scrollSensitivity)f=d(document).scrollTop(d(document).scrollTop()-c.scrollSpeed);else if(d(window).height()-
(a.pageY-d(document).scrollTop())<c.scrollSensitivity)f=d(document).scrollTop(d(document).scrollTop()+c.scrollSpeed);if(!c.axis||c.axis!="y")if(a.pageX-d(document).scrollLeft()<c.scrollSensitivity)f=d(document).scrollLeft(d(document).scrollLeft()-c.scrollSpeed);else if(d(window).width()-(a.pageX-d(document).scrollLeft())<c.scrollSensitivity)f=d(document).scrollLeft(d(document).scrollLeft()+c.scrollSpeed)}f!==false&&d.ui.ddmanager&&!c.dropBehaviour&&d.ui.ddmanager.prepareOffsets(b,a)}});d.ui.plugin.add("draggable",
"snap",{start:function(){var a=d(this).data("draggable"),b=a.options;a.snapElements=[];d(b.snap.constructor!=String?b.snap.items||":data(draggable)":b.snap).each(function(){var c=d(this),f=c.offset();this!=a.element[0]&&a.snapElements.push({item:this,width:c.outerWidth(),height:c.outerHeight(),top:f.top,left:f.left})})},drag:function(a,b){for(var c=d(this).data("draggable"),f=c.options,e=f.snapTolerance,g=b.offset.left,n=g+c.helperProportions.width,m=b.offset.top,o=m+c.helperProportions.height,h=
c.snapElements.length-1;h>=0;h--){var i=c.snapElements[h].left,k=i+c.snapElements[h].width,j=c.snapElements[h].top,l=j+c.snapElements[h].height;if(i-e<g&&g<k+e&&j-e<m&&m<l+e||i-e<g&&g<k+e&&j-e<o&&o<l+e||i-e<n&&n<k+e&&j-e<m&&m<l+e||i-e<n&&n<k+e&&j-e<o&&o<l+e){if(f.snapMode!="inner"){var p=Math.abs(j-o)<=e,q=Math.abs(l-m)<=e,r=Math.abs(i-n)<=e,s=Math.abs(k-g)<=e;if(p)b.position.top=c._convertPositionTo("relative",{top:j-c.helperProportions.height,left:0}).top-c.margins.top;if(q)b.position.top=c._convertPositionTo("relative",
{top:l,left:0}).top-c.margins.top;if(r)b.position.left=c._convertPositionTo("relative",{top:0,left:i-c.helperProportions.width}).left-c.margins.left;if(s)b.position.left=c._convertPositionTo("relative",{top:0,left:k}).left-c.margins.left}var t=p||q||r||s;if(f.snapMode!="outer"){p=Math.abs(j-m)<=e;q=Math.abs(l-o)<=e;r=Math.abs(i-g)<=e;s=Math.abs(k-n)<=e;if(p)b.position.top=c._convertPositionTo("relative",{top:j,left:0}).top-c.margins.top;if(q)b.position.top=c._convertPositionTo("relative",{top:l-c.helperProportions.height,
left:0}).top-c.margins.top;if(r)b.position.left=c._convertPositionTo("relative",{top:0,left:i}).left-c.margins.left;if(s)b.position.left=c._convertPositionTo("relative",{top:0,left:k-c.helperProportions.width}).left-c.margins.left}if(!c.snapElements[h].snapping&&(p||q||r||s||t))c.options.snap.snap&&c.options.snap.snap.call(c.element,a,d.extend(c._uiHash(),{snapItem:c.snapElements[h].item}));c.snapElements[h].snapping=p||q||r||s||t}else{c.snapElements[h].snapping&&c.options.snap.release&&c.options.snap.release.call(c.element,
a,d.extend(c._uiHash(),{snapItem:c.snapElements[h].item}));c.snapElements[h].snapping=false}}}});d.ui.plugin.add("draggable","stack",{start:function(){var a=d(this).data("draggable").options;a=d.makeArray(d(a.stack)).sort(function(c,f){return(parseInt(d(c).css("zIndex"),10)||0)-(parseInt(d(f).css("zIndex"),10)||0)});if(a.length){var b=parseInt(a[0].style.zIndex)||0;d(a).each(function(c){this.style.zIndex=b+c});this[0].style.zIndex=b+a.length}}});d.ui.plugin.add("draggable","zIndex",{start:function(a,
b){a=d(b.helper);b=d(this).data("draggable").options;if(a.css("zIndex"))b._zIndex=a.css("zIndex");a.css("zIndex",b.zIndex)},stop:function(a,b){a=d(this).data("draggable").options;a._zIndex&&d(b.helper).css("zIndex",a._zIndex)}})})(jQuery);
;


//slider plugin
(function(d){d.widget("ui.slider",d.ui.mouse,{widgetEventPrefix:"slide",options:{animate:false,distance:0,max:100,min:0,orientation:"horizontal",range:false,step:1,value:0,values:null},_create:function(){var b=this,a=this.options;this._mouseSliding=this._keySliding=false;this._animateOff=true;this._handleIndex=null;this._detectOrientation();this._mouseInit();this.element.addClass("ui-slider ui-slider-"+this.orientation+" ui-widget ui-widget-content ui-corner-all");a.disabled&&this.element.addClass("ui-slider-disabled ui-disabled");
this.range=d([]);if(a.range){if(a.range===true){this.range=d("<div></div>");if(!a.values)a.values=[this._valueMin(),this._valueMin()];if(a.values.length&&a.values.length!==2)a.values=[a.values[0],a.values[0]]}else this.range=d("<div></div>");this.range.appendTo(this.element).addClass("ui-slider-range");if(a.range==="min"||a.range==="max")this.range.addClass("ui-slider-range-"+a.range);this.range.addClass("ui-widget-header")}d(".ui-slider-handle",this.element).length===0&&d("<a href='#'></a>").appendTo(this.element).addClass("ui-slider-handle");
if(a.values&&a.values.length)for(;d(".ui-slider-handle",this.element).length<a.values.length;)d("<a href='#'></a>").appendTo(this.element).addClass("ui-slider-handle");this.handles=d(".ui-slider-handle",this.element).addClass("ui-state-default ui-corner-all");this.handle=this.handles.eq(0);this.handles.add(this.range).filter("a").click(function(c){c.preventDefault()}).hover(function(){a.disabled||d(this).addClass("ui-state-hover")},function(){d(this).removeClass("ui-state-hover")}).focus(function(){if(a.disabled)d(this).blur();
else{d(".ui-slider .ui-state-focus").removeClass("ui-state-focus");d(this).addClass("ui-state-focus")}}).blur(function(){d(this).removeClass("ui-state-focus")});this.handles.each(function(c){d(this).data("index.ui-slider-handle",c)});this.handles.keydown(function(c){var e=true,f=d(this).data("index.ui-slider-handle"),h,g,i;if(!b.options.disabled){switch(c.keyCode){case d.ui.keyCode.HOME:case d.ui.keyCode.END:case d.ui.keyCode.PAGE_UP:case d.ui.keyCode.PAGE_DOWN:case d.ui.keyCode.UP:case d.ui.keyCode.RIGHT:case d.ui.keyCode.DOWN:case d.ui.keyCode.LEFT:e=
false;if(!b._keySliding){b._keySliding=true;d(this).addClass("ui-state-active");h=b._start(c,f);if(h===false)return}break}i=b.options.step;h=b.options.values&&b.options.values.length?(g=b.values(f)):(g=b.value());switch(c.keyCode){case d.ui.keyCode.HOME:g=b._valueMin();break;case d.ui.keyCode.END:g=b._valueMax();break;case d.ui.keyCode.PAGE_UP:g=b._trimAlignValue(h+(b._valueMax()-b._valueMin())/5);break;case d.ui.keyCode.PAGE_DOWN:g=b._trimAlignValue(h-(b._valueMax()-b._valueMin())/5);break;case d.ui.keyCode.UP:case d.ui.keyCode.RIGHT:if(h===
b._valueMax())return;g=b._trimAlignValue(h+i);break;case d.ui.keyCode.DOWN:case d.ui.keyCode.LEFT:if(h===b._valueMin())return;g=b._trimAlignValue(h-i);break}b._slide(c,f,g);return e}}).keyup(function(c){var e=d(this).data("index.ui-slider-handle");if(b._keySliding){b._keySliding=false;b._stop(c,e);b._change(c,e);d(this).removeClass("ui-state-active")}});this._refreshValue();this._animateOff=false},destroy:function(){this.handles.remove();this.range.remove();this.element.removeClass("ui-slider ui-slider-horizontal ui-slider-vertical ui-slider-disabled ui-widget ui-widget-content ui-corner-all").removeData("slider").unbind(".slider");
this._mouseDestroy();return this},_mouseCapture:function(b){var a=this.options,c,e,f,h,g;if(a.disabled)return false;this.elementSize={width:this.element.outerWidth(),height:this.element.outerHeight()};this.elementOffset=this.element.offset();c=this._normValueFromMouse({x:b.pageX,y:b.pageY});e=this._valueMax()-this._valueMin()+1;h=this;this.handles.each(function(i){var j=Math.abs(c-h.values(i));if(e>j){e=j;f=d(this);g=i}});if(a.range===true&&this.values(1)===a.min){g+=1;f=d(this.handles[g])}if(this._start(b,
g)===false)return false;this._mouseSliding=true;h._handleIndex=g;f.addClass("ui-state-active").focus();a=f.offset();this._clickOffset=!d(b.target).parents().andSelf().is(".ui-slider-handle")?{left:0,top:0}:{left:b.pageX-a.left-f.width()/2,top:b.pageY-a.top-f.height()/2-(parseInt(f.css("borderTopWidth"),10)||0)-(parseInt(f.css("borderBottomWidth"),10)||0)+(parseInt(f.css("marginTop"),10)||0)};this.handles.hasClass("ui-state-hover")||this._slide(b,g,c);return this._animateOff=true},_mouseStart:function(){return true},
_mouseDrag:function(b){var a=this._normValueFromMouse({x:b.pageX,y:b.pageY});this._slide(b,this._handleIndex,a);return false},_mouseStop:function(b){this.handles.removeClass("ui-state-active");this._mouseSliding=false;this._stop(b,this._handleIndex);this._change(b,this._handleIndex);this._clickOffset=this._handleIndex=null;return this._animateOff=false},_detectOrientation:function(){this.orientation=this.options.orientation==="vertical"?"vertical":"horizontal"},_normValueFromMouse:function(b){var a;
if(this.orientation==="horizontal"){a=this.elementSize.width;b=b.x-this.elementOffset.left-(this._clickOffset?this._clickOffset.left:0)}else{a=this.elementSize.height;b=b.y-this.elementOffset.top-(this._clickOffset?this._clickOffset.top:0)}a=b/a;if(a>1)a=1;if(a<0)a=0;if(this.orientation==="vertical")a=1-a;b=this._valueMax()-this._valueMin();return this._trimAlignValue(this._valueMin()+a*b)},_start:function(b,a){var c={handle:this.handles[a],value:this.value()};if(this.options.values&&this.options.values.length){c.value=
this.values(a);c.values=this.values()}return this._trigger("start",b,c)},_slide:function(b,a,c){var e;if(this.options.values&&this.options.values.length){e=this.values(a?0:1);if(this.options.values.length===2&&this.options.range===true&&(a===0&&c>e||a===1&&c<e))c=e;if(c!==this.values(a)){e=this.values();e[a]=c;b=this._trigger("slide",b,{handle:this.handles[a],value:c,values:e});this.values(a?0:1);b!==false&&this.values(a,c,true)}}else if(c!==this.value()){b=this._trigger("slide",b,{handle:this.handles[a],
value:c});b!==false&&this.value(c)}},_stop:function(b,a){var c={handle:this.handles[a],value:this.value()};if(this.options.values&&this.options.values.length){c.value=this.values(a);c.values=this.values()}this._trigger("stop",b,c)},_change:function(b,a){if(!this._keySliding&&!this._mouseSliding){var c={handle:this.handles[a],value:this.value()};if(this.options.values&&this.options.values.length){c.value=this.values(a);c.values=this.values()}this._trigger("change",b,c)}},value:function(b){if(arguments.length){this.options.value=
this._trimAlignValue(b);this._refreshValue();this._change(null,0)}return this._value()},values:function(b,a){var c,e,f;if(arguments.length>1){this.options.values[b]=this._trimAlignValue(a);this._refreshValue();this._change(null,b)}if(arguments.length)if(d.isArray(arguments[0])){c=this.options.values;e=arguments[0];for(f=0;f<c.length;f+=1){c[f]=this._trimAlignValue(e[f]);this._change(null,f)}this._refreshValue()}else return this.options.values&&this.options.values.length?this._values(b):this.value();
else return this._values()},_setOption:function(b,a){var c,e=0;if(d.isArray(this.options.values))e=this.options.values.length;d.Widget.prototype._setOption.apply(this,arguments);switch(b){case "disabled":if(a){this.handles.filter(".ui-state-focus").blur();this.handles.removeClass("ui-state-hover");this.handles.attr("disabled","disabled");this.element.addClass("ui-disabled")}else{this.handles.removeAttr("disabled");this.element.removeClass("ui-disabled")}break;case "orientation":this._detectOrientation();
this.element.removeClass("ui-slider-horizontal ui-slider-vertical").addClass("ui-slider-"+this.orientation);this._refreshValue();break;case "value":this._animateOff=true;this._refreshValue();this._change(null,0);this._animateOff=false;break;case "values":this._animateOff=true;this._refreshValue();for(c=0;c<e;c+=1)this._change(null,c);this._animateOff=false;break}},_value:function(){var b=this.options.value;return b=this._trimAlignValue(b)},_values:function(b){var a,c;if(arguments.length){a=this.options.values[b];
return a=this._trimAlignValue(a)}else{a=this.options.values.slice();for(c=0;c<a.length;c+=1)a[c]=this._trimAlignValue(a[c]);return a}},_trimAlignValue:function(b){if(b<=this._valueMin())return this._valueMin();if(b>=this._valueMax())return this._valueMax();var a=this.options.step>0?this.options.step:1,c=(b-this._valueMin())%a;alignValue=b-c;if(Math.abs(c)*2>=a)alignValue+=c>0?a:-a;return parseFloat(alignValue.toFixed(5))},_valueMin:function(){return this.options.min},_valueMax:function(){return this.options.max},
_refreshValue:function(){var b=this.options.range,a=this.options,c=this,e=!this._animateOff?a.animate:false,f,h={},g,i,j,l;if(this.options.values&&this.options.values.length)this.handles.each(function(k){f=(c.values(k)-c._valueMin())/(c._valueMax()-c._valueMin())*100;h[c.orientation==="horizontal"?"left":"bottom"]=f+"%";d(this).stop(1,1)[e?"animate":"css"](h,a.animate);if(c.options.range===true)if(c.orientation==="horizontal"){if(k===0)c.range.stop(1,1)[e?"animate":"css"]({left:f+"%"},a.animate);
if(k===1)c.range[e?"animate":"css"]({width:f-g+"%"},{queue:false,duration:a.animate})}else{if(k===0)c.range.stop(1,1)[e?"animate":"css"]({bottom:f+"%"},a.animate);if(k===1)c.range[e?"animate":"css"]({height:f-g+"%"},{queue:false,duration:a.animate})}g=f});else{i=this.value();j=this._valueMin();l=this._valueMax();f=l!==j?(i-j)/(l-j)*100:0;h[c.orientation==="horizontal"?"left":"bottom"]=f+"%";this.handle.stop(1,1)[e?"animate":"css"](h,a.animate);if(b==="min"&&this.orientation==="horizontal")this.range.stop(1,
1)[e?"animate":"css"]({width:f+"%"},a.animate);if(b==="max"&&this.orientation==="horizontal")this.range[e?"animate":"css"]({width:100-f+"%"},{queue:false,duration:a.animate});if(b==="min"&&this.orientation==="vertical")this.range.stop(1,1)[e?"animate":"css"]({height:f+"%"},a.animate);if(b==="max"&&this.orientation==="vertical")this.range[e?"animate":"css"]({height:100-f+"%"},{queue:false,duration:a.animate})}}});d.extend(d.ui.slider,{version:"1.8.8"})})(jQuery);
;

//jquery-json

(function($){$.toJSON=function(o)
{if(typeof(JSON)=='object'&&JSON.stringify)
return JSON.stringify(o);var type=typeof(o);if(o===null)
return"null";if(type=="undefined")
return undefined;if(type=="number"||type=="boolean")
return o+"";if(type=="string")
return $.quoteString(o);if(type=='object')
{if(typeof o.toJSON=="function")
return $.toJSON(o.toJSON());if(o.constructor===Date)
{var month=o.getUTCMonth()+1;if(month<10)month='0'+month;var day=o.getUTCDate();if(day<10)day='0'+day;var year=o.getUTCFullYear();var hours=o.getUTCHours();if(hours<10)hours='0'+hours;var minutes=o.getUTCMinutes();if(minutes<10)minutes='0'+minutes;var seconds=o.getUTCSeconds();if(seconds<10)seconds='0'+seconds;var milli=o.getUTCMilliseconds();if(milli<100)milli='0'+milli;if(milli<10)milli='0'+milli;return'"'+year+'-'+month+'-'+day+'T'+
hours+':'+minutes+':'+seconds+'.'+milli+'Z"';}
if(o.constructor===Array)
{var ret=[];for(var i=0;i<o.length;i++)
ret.push($.toJSON(o[i])||"null");return"["+ret.join(",")+"]";}
var pairs=[];for(var k in o){var name;var type=typeof k;if(type=="number")
name='"'+k+'"';else if(type=="string")
name=$.quoteString(k);else
continue;if(typeof o[k]=="function")
continue;var val=$.toJSON(o[k]);pairs.push(name+":"+val);}
return"{"+pairs.join(", ")+"}";}};$.evalJSON=function(src)
{if(typeof(JSON)=='object'&&JSON.parse)
return JSON.parse(src);return eval("("+src+")");};$.secureEvalJSON=function(src)
{if(typeof(JSON)=='object'&&JSON.parse)
return JSON.parse(src);var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,'@');filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']');filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*$/.test(filtered))
return eval("("+src+")");else
throw new SyntaxError("Error parsing JSON, source is not valid.");};$.quoteString=function(string)
{if(string.match(_escapeable))
{return'"'+string.replace(_escapeable,function(a)
{var c=_meta[a];if(typeof c==='string')return c;c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+string+'"';};var _escapeable=/["\\\x00-\x1f\x7f-\x9f]/g;var _meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};})(jQuery);


//colorpicker
(function(b){var a=function(){var S={},c,N=65,t,P='<div class="colorpicker"><div class="colorpicker_color"><div><div></div></div></div><div class="colorpicker_hue"><div></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_submit"></div></div>',B={eventName:"click",onShow:function(){},onBeforeShow:function(){},onHide:function(){},onChange:function(){},onSubmit:function(){},color:"ff0000",livePreview:true,flat:false},J=function(T,V){var U=j(T);b(V).data("colorpicker").fields.eq(1).val(U.r).end().eq(2).val(U.g).end().eq(3).val(U.b).end();},u=function(T,U){b(U).data("colorpicker").fields.eq(4).val(T.h).end().eq(5).val(T.s).end().eq(6).val(T.b).end();},g=function(T,U){b(U).data("colorpicker").fields.eq(0).val(R(T)).end();},l=function(T,U){b(U).data("colorpicker").selector.css("backgroundColor","#"+R({h:T.h,s:100,b:100}));b(U).data("colorpicker").selectorIndic.css({left:parseInt(150*T.s/100,10),top:parseInt(150*(100-T.b)/100,10)});},G=function(T,U){b(U).data("colorpicker").hue.css("top",parseInt(150-150*T.h/360,10));},h=function(T,U){b(U).data("colorpicker").currentColor.css("backgroundColor","#"+R(T));},E=function(T,U){b(U).data("colorpicker").newColor.css("backgroundColor","#"+R(T));},n=function(T){var V=T.charCode||T.keyCode||-1;if((V>N&&V<=90)||V==32){return false;}var U=b(this).parent().parent();if(U.data("colorpicker").livePreview===true){e.apply(this);}},e=function(U){var V=b(this).parent().parent(),T;if(this.parentNode.className.indexOf("_hex")>0){V.data("colorpicker").color=T=m(y(this.value));}else{if(this.parentNode.className.indexOf("_hsb")>0){V.data("colorpicker").color=T=f({h:parseInt(V.data("colorpicker").fields.eq(4).val(),10),s:parseInt(V.data("colorpicker").fields.eq(5).val(),10),b:parseInt(V.data("colorpicker").fields.eq(6).val(),10)});}else{V.data("colorpicker").color=T=i(M({r:parseInt(V.data("colorpicker").fields.eq(1).val(),10),g:parseInt(V.data("colorpicker").fields.eq(2).val(),10),b:parseInt(V.data("colorpicker").fields.eq(3).val(),10)}));}}if(U){J(T,V.get(0));g(T,V.get(0));u(T,V.get(0));}l(T,V.get(0));G(T,V.get(0));E(T,V.get(0));V.data("colorpicker").onChange.apply(V,[T,R(T),j(T)]);},o=function(T){var U=b(this).parent().parent();U.data("colorpicker").fields.parent().removeClass("colorpicker_focus");},K=function(){N=this.parentNode.className.indexOf("_hex")>0?70:65;b(this).parent().parent().data("colorpicker").fields.parent().removeClass("colorpicker_focus");b(this).parent().addClass("colorpicker_focus");},I=function(T){var V=b(this).parent().find("input").focus();var U={el:b(this).parent().addClass("colorpicker_slider"),max:this.parentNode.className.indexOf("_hsb_h")>0?360:(this.parentNode.className.indexOf("_hsb")>0?100:255),y:T.pageY,field:V,val:parseInt(V.val(),10),preview:b(this).parent().parent().data("colorpicker").livePreview};b(document).bind("mouseup",U,s);b(document).bind("mousemove",U,L);},L=function(T){T.data.field.val(Math.max(0,Math.min(T.data.max,parseInt(T.data.val+T.pageY-T.data.y,10))));if(T.data.preview){e.apply(T.data.field.get(0),[true]);}return false;},s=function(T){e.apply(T.data.field.get(0),[true]);T.data.el.removeClass("colorpicker_slider").find("input").focus();b(document).unbind("mouseup",s);b(document).unbind("mousemove",L);return false;},w=function(T){var U={cal:b(this).parent(),y:b(this).offset().top};U.preview=U.cal.data("colorpicker").livePreview;b(document).bind("mouseup",U,r);b(document).bind("mousemove",U,k);},k=function(T){e.apply(T.data.cal.data("colorpicker").fields.eq(4).val(parseInt(360*(150-Math.max(0,Math.min(150,(T.pageY-T.data.y))))/150,10)).get(0),[T.data.preview]);return false;},r=function(T){J(T.data.cal.data("colorpicker").color,T.data.cal.get(0));g(T.data.cal.data("colorpicker").color,T.data.cal.get(0));b(document).unbind("mouseup",r);b(document).unbind("mousemove",k);return false;},x=function(T){var U={cal:b(this).parent(),pos:b(this).offset()};U.preview=U.cal.data("colorpicker").livePreview;b(document).bind("mouseup",U,A);b(document).bind("mousemove",U,q);},q=function(T){e.apply(T.data.cal.data("colorpicker").fields.eq(6).val(parseInt(100*(150-Math.max(0,Math.min(150,(T.pageY-T.data.pos.top))))/150,10)).end().eq(5).val(parseInt(100*(Math.max(0,Math.min(150,(T.pageX-T.data.pos.left))))/150,10)).get(0),[T.data.preview]);return false;},A=function(T){J(T.data.cal.data("colorpicker").color,T.data.cal.get(0));g(T.data.cal.data("colorpicker").color,T.data.cal.get(0));b(document).unbind("mouseup",A);b(document).unbind("mousemove",q);return false;},v=function(T){b(this).addClass("colorpicker_focus");},Q=function(T){b(this).removeClass("colorpicker_focus");},p=function(U){var V=b(this).parent();var T=V.data("colorpicker").color;V.data("colorpicker").origColor=T;h(T,V.get(0));V.data("colorpicker").onSubmit(T,R(T),j(T),V.data("colorpicker").el);},D=function(T){var X=b("#"+b(this).data("colorpickerId"));X.data("colorpicker").onBeforeShow.apply(this,[X.get(0)]);var Y=b(this).offset();var W=z();var V=Y.top+this.offsetHeight;var U=Y.left;if(V+176>W.t+W.h){V-=this.offsetHeight+176;}if(U+356>W.l+W.w){U-=356;}X.css({left:U+"px",top:V+"px"});if(X.data("colorpicker").onShow.apply(this,[X.get(0)])!=false){X.show();}b(document).bind("mousedown",{cal:X},O);return false;},O=function(T){if(!H(T.data.cal.get(0),T.target,T.data.cal.get(0))){if(T.data.cal.data("colorpicker").onHide.apply(this,[T.data.cal.get(0)])!=false){T.data.cal.hide();}b(document).unbind("mousedown",O);}},H=function(V,U,T){if(V==U){return true;}if(V.contains){return V.contains(U);}if(V.compareDocumentPosition){return !!(V.compareDocumentPosition(U)&16);}var W=U.parentNode;while(W&&W!=T){if(W==V){return true;}W=W.parentNode;}return false;},z=function(){var T=document.compatMode=="CSS1Compat";return{l:window.pageXOffset||(T?document.documentElement.scrollLeft:document.body.scrollLeft),t:window.pageYOffset||(T?document.documentElement.scrollTop:document.body.scrollTop),w:window.innerWidth||(T?document.documentElement.clientWidth:document.body.clientWidth),h:window.innerHeight||(T?document.documentElement.clientHeight:document.body.clientHeight)};},f=function(T){return{h:Math.min(360,Math.max(0,T.h)),s:Math.min(100,Math.max(0,T.s)),b:Math.min(100,Math.max(0,T.b))};},M=function(T){return{r:Math.min(255,Math.max(0,T.r)),g:Math.min(255,Math.max(0,T.g)),b:Math.min(255,Math.max(0,T.b))};},y=function(V){var T=6-V.length;if(T>0){var W=[];for(var U=0;U<T;U++){W.push("0");}W.push(V);V=W.join("");}return V;},d=function(T){var T=parseInt(((T.indexOf("#")>-1)?T.substring(1):T),16);return{r:T>>16,g:(T&65280)>>8,b:(T&255)};},m=function(T){return i(d(T));},i=function(V){var U={h:0,s:0,b:0};var W=Math.min(V.r,V.g,V.b);var T=Math.max(V.r,V.g,V.b);var X=T-W;U.b=T;if(T!=0){}U.s=T!=0?255*X/T:0;if(U.s!=0){if(V.r==T){U.h=(V.g-V.b)/X;}else{if(V.g==T){U.h=2+(V.b-V.r)/X;}else{U.h=4+(V.r-V.g)/X;}}}else{U.h=-1;}U.h*=60;if(U.h<0){U.h+=360;}U.s*=100/255;U.b*=100/255;return U;},j=function(T){var V={};var Z=Math.round(T.h);var Y=Math.round(T.s*255/100);var U=Math.round(T.b*255/100);if(Y==0){V.r=V.g=V.b=U;}else{var aa=U;var X=(255-Y)*U/255;var W=(aa-X)*(Z%60)/60;if(Z==360){Z=0;}if(Z<60){V.r=aa;V.b=X;V.g=X+W;}else{if(Z<120){V.g=aa;V.b=X;V.r=aa-W;}else{if(Z<180){V.g=aa;V.r=X;V.b=X+W;}else{if(Z<240){V.b=aa;V.r=X;V.g=aa-W;}else{if(Z<300){V.b=aa;V.g=X;V.r=X+W;}else{if(Z<360){V.r=aa;V.g=X;V.b=aa-W;}else{V.r=0;V.g=0;V.b=0;}}}}}}}return{r:Math.round(V.r),g:Math.round(V.g),b:Math.round(V.b)};},C=function(T){var U=[T.r.toString(16),T.g.toString(16),T.b.toString(16)];b.each(U,function(V,W){if(W.length==1){U[V]="0"+W;}});return U.join("");},R=function(T){return C(j(T));},F=function(){var U=b(this).parent();var T=U.data("colorpicker").origColor;U.data("colorpicker").color=T;J(T,U.get(0));g(T,U.get(0));u(T,U.get(0));l(T,U.get(0));G(T,U.get(0));E(T,U.get(0));};return{init:function(T){T=b.extend({},B,T||{});if(typeof T.color=="string"){T.color=m(T.color);}else{if(T.color.r!=undefined&&T.color.g!=undefined&&T.color.b!=undefined){T.color=i(T.color);}else{if(T.color.h!=undefined&&T.color.s!=undefined&&T.color.b!=undefined){T.color=f(T.color);}else{return this;}}}return this.each(function(){if(!b(this).data("colorpickerId")){var U=b.extend({},T);U.origColor=T.color;var W="collorpicker_"+parseInt(Math.random()*1000);b(this).data("colorpickerId",W);var V=b(P).attr("id",W);if(U.flat){V.appendTo(this).show();}else{V.appendTo(document.body);}U.fields=V.find("input").bind("keyup",n).bind("change",e).bind("blur",o).bind("focus",K);V.find("span").bind("mousedown",I).end().find(">div.colorpicker_current_color").bind("click",F);U.selector=V.find("div.colorpicker_color").bind("mousedown",x);U.selectorIndic=U.selector.find("div div");U.el=this;U.hue=V.find("div.colorpicker_hue div");V.find("div.colorpicker_hue").bind("mousedown",w);U.newColor=V.find("div.colorpicker_new_color");U.currentColor=V.find("div.colorpicker_current_color");V.data("colorpicker",U);V.find("div.colorpicker_submit").bind("mouseenter",v).bind("mouseleave",Q).bind("click",p);J(U.color,V.get(0));u(U.color,V.get(0));g(U.color,V.get(0));G(U.color,V.get(0));l(U.color,V.get(0));h(U.color,V.get(0));E(U.color,V.get(0));if(U.flat){V.css({position:"relative",display:"block"});}else{b(this).bind(U.eventName,D);}}});},showPicker:function(){return this.each(function(){if(b(this).data("colorpickerId")){D.apply(this);}});},hidePicker:function(){return this.each(function(){if(b(this).data("colorpickerId")){b("#"+b(this).data("colorpickerId")).hide();}});},setColor:function(T){if(typeof T=="string"){T=m(T);}else{if(T.r!=undefined&&T.g!=undefined&&T.b!=undefined){T=i(T);}else{if(T.h!=undefined&&T.s!=undefined&&T.b!=undefined){T=f(T);}else{return this;}}}return this.each(function(){if(b(this).data("colorpickerId")){var U=b("#"+b(this).data("colorpickerId"));U.data("colorpicker").color=T;U.data("colorpicker").origColor=T;J(T,U.get(0));u(T,U.get(0));g(T,U.get(0));G(T,U.get(0));l(T,U.get(0));h(T,U.get(0));E(T,U.get(0));}});}};}();b.fn.extend({ColorPicker:a.init,ColorPickerHide:a.hidePicker,ColorPickerShow:a.showPicker,ColorPickerSetColor:a.setColor});})(jQuery);

//progress bar plugin (for flash/uploads)
/*(function(b,d){b.widget("ui.progressbar",{options:{value:0,max:100},min:0,_create:function(){this.element.addClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").attr({role:"progressbar","aria-valuemin":this.min,"aria-valuemax":this.options.max,"aria-valuenow":this._value()});this.valueDiv=b("<div class='ui-progressbar-value ui-widget-header ui-corner-left'></div>").appendTo(this.element);this.oldValue=this._value();this._refreshValue()},destroy:function(){this.element.removeClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow");
this.valueDiv.remove();b.Widget.prototype.destroy.apply(this,arguments)},value:function(a){if(a===d)return this._value();this._setOption("value",a);return this},_setOption:function(a,c){if(a==="value"){this.options.value=c;this._refreshValue();this._value()===this.options.max&&this._trigger("complete")}b.Widget.prototype._setOption.apply(this,arguments)},_value:function(){var a=this.options.value;if(typeof a!=="number")a=0;return Math.min(this.options.max,Math.max(this.min,a))},_percentage:function(){return 100*
this._value()/this.options.max},_refreshValue:function(){var a=this.value(),c=this._percentage();if(this.oldValue!==a){this.oldValue=a;this._trigger("change")}this.valueDiv.toggleClass("ui-corner-right",a===this.options.max).width(c.toFixed(0)+"%");this.element.attr("aria-valuenow",a)}});b.extend(b.ui.progressbar,{version:"1.8.8"})})(jQuery);
;*/

function parseUNum(n){ return n < 0 ? 0 : n; };

//(function($){

	skinSet = {
		'tools': {}, 'active' : false, 'wholePage' : null, 'chooseBox':null, 'canvasActive': false, 'canvas':null, 'canvasArray':[]
		, 'canvasMeta':null, 'canvasObject':null, 'lastCanvasObject':null, 'palette':null, 'menu':null, 'applied':true
		,'styleElement': null, 'styleElementsWithout':null, 'isie6' : $.browser.msie && $.browser.version < 7, 'enabler':null 
		,'colorPicker':null, 'activeColorSkinTag':null, 'activeColorBox':null, 'tC':null, 'tCP':null, 'chooserEnabled':true
	};
	
	function Skin(enabler){
		var $class = this;
		init(); //prep the skinner tools
		
		//private methods
		function init(){
			//set base variables with precursors
			skinSet.wholePage = $('#whole');
			skinSet.active = true;
			skinSet.enabler = enabler;
			
			$('.status-box').text('Initialising').show('fast');
			$.getJSON('ajax/gather-data.php',function(json){ 
				if ($('style').size() == 0) $('head').append(json.styleElements); 
				$('.skin-this').chooser(); //apply skinner to elements
				$('#skin-menu .skin-name').val(json['name']);
				$('.status-box').hide('fast');
			});
			
			$('body') //add the choose box
				.append($('<div class="choose-box choose-top"><!-- --></div>').css({'background':'#000000','width':'100%'}))
				.append($('<div class="choose-box choose-left"><!-- --></div>').css({'background':'#000000'}))
				.append($('<div class="choose-box choose-right"><!-- --></div>').css({'background':'#000000'}))
				.append($('<div class="choose-box choose-bottom"><!-- --></div>').css({'background':'#000000','width':'100%'}))
			;
			
			skinSet
				.chooseBox = $('.choose-box').css({'opacity':0.3,'display':'none','position':'absolute','top':'0px','left':'0px','z-index':700})
				.click($class.hidePalette)
			;
			
			//basic menu
			skinSet.menu = $('<div id="skin-menu" />')
				.append(
					$('<div id="palette-buttons" class="palette-buttons floatright alignright last-right" />')
						.append(  $('<a href="#Save" class="marginright save-button">Save</a>').bind('click',{'thisClass':$class},saveYourSkin) )
						.append( 
							$('<a href="#New" class="marginright new-button">New</a>').toggle(function(){$(this).addClass('active')},function(){$(this).removeClass('active')}) 
						) 
						.append(  $('<a href="#Delete" class="marginright delete-button">Delete</a>').bind('click',deleteSkin) )
						.append(  $('<a href="#Quit" class="quit-button marginright">Quit</a>').click(quitSkin) )
						.append(  $('<a href="#Choose" class="choose-button active">Choose</a>').bind('click',{'thisClass':$class},enableChooser) )
				)
				.append( 
					$('<div class="move-this" />').append('Name: ').append( 
						$('<input type="text" class="skin-name" />').keypress(function(ev){ if(ev.which >= 33 && this.value.length >= 30) ev.preventDefault(); })
					) )
				.appendTo('body')
				.draggable({ 'handle':'.move-this', 'containment': skinSet.wholePage, 'opacity':.8 }) 
				.fadeIn('fast')
			;
			
			skinSet.colorPicker = $('<div id="palette-colorpicker" />')
				.ColorPicker({ 
					'flat':true
					,'onChange':function (hsb, hex, rgb) {
						skinSet.activeColorBox.css('background-color','#'+hex);
						clearTimeout(skinSet.tCP);
						skinSet.tCP = setTimeout(function(){
							$class.applyStyleElement(skinSet.activeColorSkinTag,'#'+hex);
						},25);
					}
				});
				
			//palette
			skinSet.palette = $('<div id="palette" />')
				.append(  $('<div class="half" />').append( $('<h3 class="move-this marginbottom">Title</h3>') ) )
				.append(
					$('<div id="palette-tabs" class="palette-buttons half alignright last-left" />')//navigation tabs
						.append( $('<a class="palette-icon icon-palette-color active" title="Color" />').append( $('<img src="images/blank.gif" alt="" />') ).data('section','color') )
						.append( $('<a class="marginleft palette-icon icon-palette-font" title="Font" />').append( $('<img src="images/blank.gif" alt="" />') ).data('section','font') )
						.append( $('<a class="marginleft palette-icon icon-palette-image" title="Image" />').append( $('<img src="images/blank.gif" alt="" />') ).data('section','image') )
						.append( $('<a class="marginleft palette-icon icon-palette-format" title="Format" />').append( $('<img src="images/blank.gif" alt="" />') ).data('section','format') )
						.click( switchTabs )
				)
				.append( '<div class="clearboth"><!-- --></div>' )
				.append(//palette sections
					$('<div id="palette-sections" />')
						//color
						.append( 
							$('<div class="palette-section palette-section-color active" />') 
								.append( buildColorPickerTool('Link','link-color','half') )
								.append( buildColorPickerTool('Link on Hover','link-color-hover','half last-left') )
								.append( '<div class="clearboth"><!-- --></div>' )
								.append( buildColorPickerTool('Link Background','link-background-color','half') )
								.append( buildColorPickerTool('Link Background on Hover','link-background-color-hover', 'half last-left') )
								.append( '<div class="clearboth"><!-- --></div>' )
								.append( buildColorPickerTool('Link Border','link-border-color','half') )
								.append( buildColorPickerTool('Link Border on Hover','link-border-color-hover', 'half last-left') )
								.append( '<div class="clearboth"><!-- --></div>' )
								.append( buildColorPickerTool('Text','text-color','half') )
								.append( buildColorPickerTool('Border','border-color','half last-left') )
								.append( '<div class="clearboth"><!-- --></div>' )
								.append( buildColorPickerTool('Background','background-color','half') )
								.append( buildColorPickerTool('Background on Hover','background-color-hover','half last-left') ) //ie7+, ff*, chrome*, safari* only
								.append( '<div class="clearboth"><!-- --></div>' )
								.append( skinSet.colorPicker ) //colorpicker
						)
						//font
						.append(
							$('<div class="palette-section palette-section-font" />') 
								.append(
									$('<div class="half palette-tool palette-section-underline-link" />')
										.append( $('<div class="half" />').append( $('<p />').text('Underline Links') ) )
										.append( 
											$( '<div class="palette-buttons half last-left alignright" />' ) 
												.append( 
													$('<a href="#underline-link" class="marginright" />')
														.data('setting','none,underline').text('Off').click({'skintag':'underline-link'},setSimpleSkinTag)
												)
										)
										.append( '<div class="clearboth"><!-- --></div>' )
								)
								.append(
									$('<div class="half last-left palette-tool palette-section-underline-link-hover" />')
										.append( $('<div class="half" />').append( $('<p />').text('Underline Links on Hover') ) )
										.append( 
											$( '<div class="palette-buttons half last-left alignright" />' ) 
												.append( 
													$('<a href="#underline-link-hover" />').data('setting','none,underline').text('Off').click({'skintag':'underline-link-hover'},setSimpleSkinTag)
												)
										)
										.append( '<div class="clearboth"><!-- --></div>' )
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(
									$('<div class="half last-left palette-tool palette-section-bold-links" />')
										.append( $('<div class="half" />').append( $('<p />').text('Bold Links') ) )
										.append( 
											$( '<div class="palette-buttons alignright" />' ) 
												.append( 
													$('<a href="#bold-links" class="marginright" />').data('setting','normal,bold').text('Off').click({'skintag':'bold-links'},setSimpleSkinTag)
												)
										)
										.append( '<div class="clearboth"><!-- --></div>' )
								)
								.append(
									$('<div class="half last-left palette-tool palette-section-font-family" />')
										.append(
											$('<select class="floatright" />')
												.change({'skintag':'font-family'},setSelectValue)
												.append('<option value="Arial, Tahoma, Verdana, sans serif">Arial</option>')
												.append('<option value="Georgia, \'Times New Roman\', \'Times New\', Times,serif">Georgia</option>')
												.append('<option value="\'Lucida Grande\', \'Lucida Sans Unicode\', \'Lucida\', Arial, Verdana, sans serif">Lucida</option>')
												.append('<option value="Tahoma, Arial, Verdana, sans serif">Tahoma</option>')
												.append('<option value="\'Times New Roman\', \'Times New\', Times,serif">Times New Roman</option>')
												.append('<option value="Trebuchet, \'Trebuchet MS\', Arial, sans-serif">Trebuchet</option>')
												.append('<option value="Verdana, Tahoma, Arial, sans serif">Verdana</option>')
										)
										.append( $('<p />').text('Font Family: ') )
										.append('<div class="clearboth"><!-- --></div>')
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(  
									$('<div class="half palette-tool palette-section-font-size" />')
										.append( $('<p class="aligncenter" />').text('Font Size: ').append( $('<strong />').text( '11px' ) ) )
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'font-size','range':[],'unit':'px'})
												.slider({ 'value':11, 'min':8, 'max':72, 'slide': setSliderSkinTag })
										)
								)
								.append('<div class="clearboth"><!-- --></div>')
						)
						
						//image
						.append(
							$('<div class="palette-section palette-section-image" />') 
								.append(
									$('<div class="floatleft palette-tool palette-section-background-image" />')
										.append('<p><strong>Background Image</strong></p>')
										.append('<div class="floatleft alignright"><p class="marginright">Upload:</p></div>')
										.append( 
											$('<div class="floatleft marginright palette-background-image-tool" />')
												.append( 
													$('<form action="ajax/upload-image.php" enctype="multipart/form-data" target="image-upload" method="post" />')
														.append('<input type="hidden" name="skintag" value="background-image" />')
														.append(
															$('<input type="file" name="background-image" />').change(function(){
																$('.status-box').text('Uploading').show('fast');
																var $this = $(this);
																$('#image-upload-container').html('<iframe name="image-upload" id="image-upload" src="about:blank"></iframe>');
																$this.closest('form').submit();
																$this.val('');
															})
														)
														
												)
										)
										.append( $('<div class="palette-buttons floatleft"/>').append(
											$('<a href="#remove-background-image">Remove</a>').bind('click', function(ev){ 
												$class.applyStyleElement('background-image','none');
												ev.preventDefault(); 
											}) 
										) )
										.append('<div class="clearboth"><!-- --></div>')
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(
									$('<div class="half palette-tool palette-section-background-position" />')
										.append(
											$('<div class="select-background-position background-position-background" />')
												.append('<a class="{\'setting\':\'left top\'} active"><!-- --></a><a class="{\'setting\':\'center top\'}"><!-- --></a><a class="{\'setting\':\'right top\'}"><!-- --></a>')
												.append('<a class="{\'setting\':\'left center\'}"><!-- --></a><a class="{\'setting\':\'center center\'}"><!-- --></a><a class="{\'setting\':\'right center\'}"><!-- --></a>')
												.append('<a class="{\'setting\':\'left bottom\'}"><!-- --></a><a class="{\'setting\':\'center bottom\'}"><!-- --></a><a class="{\'setting\':\'right bottom\'}"><!-- --></a>')
												.click({'skintag':'background-position'},setBackgroundPosition)
										)
										.append( '<p>Position: <br /><strong>top left</strong></p>' )
										.append('<div class="clearboth"><!-- --></div>')
								)
								.append(  
									$('<div class="half last-left palette-tool palette-section-background-tile" />')
										.append(
											$('<select class="floatright" />')
												.change({'skintag':'background-tile'},setSelectValue)
												.append('<option value="repeat">All Directions</option><option value="repeat-x">Horizontally</option>')
												.append('<option value="repeat-y">Vertically</option><option value="no-repeat">None</option>')
										)
										.append( $('<p />').text('Tile: ') )
										.append('<div class="clearboth"><!-- --></div>')
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(
									$('<div class="half palette-tool palette-section-background-position-hover" />')
										.append(
											$('<div class="select-background-position background-position-background-hover" />')
												.append('<a class="{\'setting\':\'left top\'} active"><!-- --></a><a class="{\'setting\':\'center top\'}"><!-- --></a><a class="{\'setting\':\'right top\'}"><!-- --></a>')
												.append('<a class="{\'setting\':\'left center\'}"><!-- --></a><a class="{\'setting\':\'center center\'}"><!-- --></a><a class="{\'setting\':\'right center\'}"><!-- --></a>')
												.append('<a class="{\'setting\':\'left bottom\'}"><!-- --></a><a class="{\'setting\':\'center bottom\'}"><!-- --></a><a class="{\'setting\':\'right bottom\'}"><!-- --></a>')
												.click({'skintag':'background-position-hover'},setBackgroundPosition)
										)
										.append( '<p>Position on Hover: <br /><strong>top left</strong></p>' )
										.append('<div class="clearboth"><!-- --></div>')
								)
								.append(
									$('<div class="half last-left palette-tool palette-section-background-attachment" />')
										.append( $('<div class="half" />').append( $('<p />').text('Fixed background:') ) )
										.append( 
											$( '<div class="palette-buttons half last-left alignright" />' ) 
												.append( 
													$('<a href="#background-attachment" class="margin-right" />')
														.data('setting','scroll,fixed').text('Off').click({'skintag':'background-attachment'},setSimpleSkinTag)
												)
										)
										.append( '<div class="clearboth"><!-- --></div>' )
								)
								/*.append( 
									$('<div class="background-image-preview background-image-preview-background half last-left" />')
										.append('<div class="preview-box"><!-- --></div>') 
										.append('<p>Preview: </p>')
										.append('<div class="clearboth"><!-- --></div>')
								)*/
								
								.append(
									$('<div class="floatleft palette-tool palette-section-link-background-image" />')
										.append('<p><strong>Link Background Image</strong></p>')
										.append('<div class="floatleft alignright"><p class="marginright">Upload:</p></div>')
										.append( 
											$('<div class="floatleft marginright palette-link-background-image-tool" />')
												.append( 
													$('<form action="ajax/upload-image.php" enctype="multipart/form-data" target="image-upload" method="post" />')
														.append('<input type="hidden" name="skintag" value="link-background-image" />')
														.append(
															$('<input type="file" name="background-image" />').change(function(){
																$('.status-box').text('Uploading').show('fast');
																var $this = $(this);
																$('#image-upload-container').html('<iframe name="image-upload" id="image-upload" src="about:blank"></iframe>');
																$this.closest('form').submit();
																$this.val('');
															})
														)
												)
										)
										.append( $('<div class="palette-buttons floatleft"/>').append(
											$('<a href="#remove-background-image">Remove</a>').bind('click', function(ev){ 
												$class.applyStyleElement('link-background-image','none');
												ev.preventDefault(); 
											}) 
										) )
										.append('<div class="clearboth"><!-- --></div>')
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(
									$('<div class="half palette-tool palette-section-link-background-position" />')
										.append(
											$('<div class="select-background-position background-position-link-background" />')
												.append('<a class="{\'setting\':\'left top\'} active"><!-- --></a><a class="{\'setting\':\'center top\'}"><!-- --></a><a class="{\'setting\':\'right top\'}"><!-- --></a>')
												.append('<a class="{\'setting\':\'left center\'}"><!-- --></a><a class="{\'setting\':\'center center\'}"><!-- --></a><a class="{\'setting\':\'right center\'}"><!-- --></a>')
												.append('<a class="{\'setting\':\'left bottom\'}"><!-- --></a><a class="{\'setting\':\'center bottom\'}"><!-- --></a><a class="{\'setting\':\'right bottom\'}"><!-- --></a>')
												.click({'skintag':'link-background-position'},setBackgroundPosition)
										)
										.append( '<p>Position: <br /><strong>top left</strong></p>' )
										.append('<div class="clearboth"><!-- --></div>')
								)
								.append(  
									$('<div class="half last-left palette-tool palette-section-link-background-tile">')
										.append(
											$('<select class="floatright" />')
												.change({'skintag':'link-background-tile'},setSelectValue)
												.append('<option value="repeat">All Directions</option><option value="repeat-x">Horizontally</option>')
												.append('<option value="repeat-y">Vertically</option><option value="no-repeat">None</option>')
										)
										.append( $('<p />').text('Tile: ') )
										.append( '<div class="clearboth"><!-- --></div>' )
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(
									$('<div class="half palette-tool palette-section-link-background-position-hover" />')
										.append(
											$('<div class="select-background-position background-position-link-background-hover" />')
												.append('<a class="{\'setting\':\'left top\'} active"><!-- --></a><a class="{\'setting\':\'center top\'}"><!-- --></a><a class="{\'setting\':\'right top\'}"><!-- --></a>')
												.append('<a class="{\'setting\':\'left center\'}"><!-- --></a><a class="{\'setting\':\'center center\'}"><!-- --></a><a class="{\'setting\':\'right center\'}"><!-- --></a>')
												.append('<a class="{\'setting\':\'left bottom\'}"><!-- --></a><a class="{\'setting\':\'center bottom\'}"><!-- --></a><a class="{\'setting\':\'right bottom\'}"><!-- --></a>')
												.click({'skintag':'link-background-position-hover'},setBackgroundPosition)
										)
										.append( '<p>Position on Hover: <br /><strong>top left</strong></p>' )
										.append('<div class="clearboth"><!-- --></div>')
								)
								.append(
									$('<div class="half last-left palette-tool palette-section-link-background-attachment" />')
										.append( $('<div class="half" />').append( $('<p />').text('Fixed background:') ) )
										.append( 
											$( '<div class="palette-buttons last-left alignright" />' ) 
												.append( 
													$('<a href="#link-background-attachment" />')
														.data('setting','scroll,fixed').text('Off').click({'skintag':'link-background-attachment'},setSimpleSkinTag)
												)
										)
										.append('<div class="clearboth"><!-- --></div>')
								)
								/*.append( 
									$('<div class="background-image-preview background-image-preview-link-background half last-left" />')
										.append('<div class="preview-box"><!-- --></div>') 
										.append('<p>Preview: </p>')
										.append('<div class="clearboth"><!-- --></div>')
								)*/
								.append('<div class="clearboth"><!-- --></div>')
						)
						
						//format
						.append(
							$('<div class="palette-section palette-section-format" />') 
								.append(  
									$('<div class="half palette-tool palette-section-border-width" />')
										.append( $('<p>').text('Border Width: ').append( $('<strong />').text( '0px' ) ) )
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'border-width','range':[],'unit':'px'})
												.slider({ 'value':0, 'min':0, 'max':25, 'slide': setSliderSkinTag })
										)
								)
								.append(  
									$('<div class="half last-left palette-tool palette-section-link-border-width" />')
										.append( $('<p>').text('Link Border Width: ').append( $('<strong />').text( '0px' ) ) )
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'link-border-width','range':[],'unit':'px'})
												.slider({ 'value':0, 'min':0, 'max':25, 'slide': setSliderSkinTag })
										)
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(
									$('<div class="half palette-tool palette-section-align" />')
									.append( $('<p>').text('Align: ').append( $('<strong />').text( '0px' ) ) )
									.append( 
										$('<div class="palette-slider-tool" />') 
											.data('settings',{'skintag':'align','range':[],'unit':'px'})
											.slider({ 'value':0, 'min':0, 'max':2, 'slide': setSliderSkinTag })
									)
								)
								.append(
									$('<div class="half last-left palette-tool palette-section-rounded-corners" />')
									.append( $('<p>').text('Rounded Corners: ').append( $('<strong />').text( '0px' ) ) )
									.append( 
										$('<div class="palette-slider-tool" />') 
											.data('settings',{'skintag':'rounded-corners','range':[],'unit':'px'})
											.slider({ 'value':0, 'min':0, 'max':25, 'slide': setSliderSkinTag })
									)
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(  
									$('<div class="half palette-tool palette-section-margin palette-section-margin-left" />')
										.append( 
											$('<p>')
												.html('Margin<span class="show-margin"> left</span>: ')
												.append( $('<strong />').text( '0px' ) ) 
												//.append(' <span class="light">All: </span>' ).append( $('<input type="checkbox" value="1" />') )
										)
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'margin-left','range':[],'unit':'px'})
												.slider({ 'value':0, 'min':0, 'max':100, 'slide': setSliderSkinTag })
										)
								)
								.append(
									$('<div class="half last-left show-margin palette-tool palette-section-margin palette-section-margin-right" />')
									.append( $('<p>').text('Margin Right: ').append( $('<strong />').text( '0px' ) ) )
									.append( 
										$('<div class="palette-slider-tool" />') 
											.data('settings',{'skintag':'margin-right','range':[],'unit':'px'})
											.slider({ 'value':0, 'min':0, 'max':100, 'slide': setSliderSkinTag })
									)
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(  
									$('<div class="half show-margin palette-tool palette-section-margin palette-section-margin-top" />')
										.append( $('<p>').html('Margin top: ').append( $('<strong />').text( '0px' ) ) )
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'margin-top','range':[],'unit':'px'})
												.slider({ 'value':0, 'min':0, 'max':100, 'slide': setSliderSkinTag })
										)
								)
								.append(
									$('<div class="half last-left show-margin palette-tool palette-section-margin palette-section-margin-bottom" />')
									.append( $('<p>').text('Margin Bottom: ').append( $('<strong />').text( '0px' ) ) )
									.append( 
										$('<div class="palette-slider-tool" />') 
											.data('settings',{'skintag':'margin-bottom','range':[],'unit':'px'})
											.slider({ 'value':0, 'min':0, 'max':100, 'slide': setSliderSkinTag })
									)
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(  
									$('<div class="half palette-tool palette-section-padding palette-section-padding-left" />')
										.append( 
											$('<p>')
												.html('Padding <span class="show-margin"> left</span>: ')
												.append( $('<strong />').text( '0px' ) ) 
												//.append('  <span class="light">All:</span> ' ).append( $('<input type="checkbox" value="1"  />') )
										)
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'padding-left','range':[],'unit':'px'})
												.slider({ 'value':0, 'min':0, 'max':100, 'slide': setSliderSkinTag })
										)
								)
								.append(
									$('<div class="half last-left show-padding palette-tool palette-section-padding palette-section-padding-right" />')
									.append( $('<p>').text('Padding Right: ').append( $('<strong />').text( '0px' ) ) )
									.append( 
										$('<div class="palette-slider-tool" />') 
											.data('settings',{'skintag':'padding-right','range':[],'unit':'px'})
											.slider({ 'value':0, 'min':0, 'max':100, 'slide': setSliderSkinTag })
									)
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(  
									$('<div class="half show-padding palette-tool palette-section-padding palette-section-padding-top" />')
										.append( $('<p>').html('Padding top: ').append( $('<strong />').text( '0px' ) ) )
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'padding-top','range':[],'unit':'px'})
												.slider({ 'value':0, 'min':0, 'max':100, 'slide': setSliderSkinTag })
										)
								)
								.append(
									$('<div class="half last-left show-padding palette-tool palette-section-padding palette-section-padding-bottom" />')
									.append( $('<p>').text('Padding Bottom: ').append( $('<strong />').text( '0px' ) ) )
									.append( 
										$('<div class="palette-slider-tool" />') 
											.data('settings',{'skintag':'padding-bottom','range':[],'unit':'px'})
											.slider({ 'value':0, 'min':0, 'max':100, 'slide': setSliderSkinTag })
									)
								)
								.append('<div class="clearboth"><!-- --></div>')
								.append(  
									$('<div class="half palette-tool palette-section-width" />')
										.append( $('<p>').text('Width: ').append( $('<strong />').text( '0px' ) ) )
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'width','range':[],'unit':'px'})
												.slider({ 'value':240, 'min':150, 'max':360, 'slide': setSliderSkinTag })
										)
								)
								.append(  
									$('<div class="half palette-tool palette-section-height" />')
										.append( $('<p>').text('Height: ').append( $('<strong />').text( '0px' ) ) )
										.append( 
											$('<div class="palette-slider-tool" />') 
												.data('settings',{'skintag':'height','range':[],'unit':'px'})
												.slider({ 'value':60, 'min':0, 'max':250, 'slide': setSliderSkinTag })
										)
								)
								.append('<div class="clearboth"><!-- --></div>')
						)
				)
				.append( $('<div id="palette-depth" class="twothirds margintop"><strong>Width:</strong> <span class="palette-canvas-width">&nbsp;</span>px <strong>Height:</strong> <span class="palette-canvas-height">&nbsp;</span>px <br /><strong>Parents:</strong> <span class="palette-canvas-parents">&nbsp;</span></div>') )
				.append( 
					$('<div id="palette-buttons" class="palette-buttons third alignright floatright last-right margintop" />') 
						.append(  $('<a href="#Cancel" class="marginright cancel-button">Cancel</a>').click(cancelCanvas) )
						.append( $('<a href="#Apply" class="apply-button">Apply</a>').bind('click',{'thisClass':$class},$class.applyCanvas) )
				)
				.append( '<div class="clearboth"><!-- --></div>' )
				.bind( 'mouseenter mouseleave click',function(ev){ ev.stopPropagation(); } )
				.bind( 'showPalette', showPalette )
				.appendTo('body') 
				.draggable({ 'handle':'.move-this:first', 'containment': skinSet.wholePage, 'opacity':.8 }) 
			;
			
			skinSet.palette.css({ //position it just below the navigation menu
				'top':$('#header-menu').offset().top+$('#header-menu').height()
				,'left':($('#header-menu').offset().left+$('#header-menu').width())-skinSet.palette.outerWidth()
			});
			
			skinSet.activeColorSkinTag = 'linkcolor';
			skinSet.activeColorBox = $('.palette-section-'+skinSet.activeColorSkinTag+' .palette-colorpicker-box');
			$('.palette-section-'+skinSet.activeColorSkinTag).addClass('active');//show the tool as active
		};
		
		function setPalette() {
			var activeSetter, slideRange, slideRangeInt, styleElements = $('style.style-element');
			if (styleElements.size() == 0) styleElements = $('style');
			
			$class.showCanvasDimensions();
			
			skinSet.styleElement = $('#style-element-'+skinSet.canvasObject['id']);
			
			skinSet.styleElementsWithout = styleElements.not(skinSet.styleElement[0]); 
			skinSet.styleElementIndex = styleElements.index(skinSet.styleElement); //get the index of the style element
			skinSet.lastCanvasObject = $.extend(true,{},skinSet.canvasObject);//save original style settings in object for cancellation
			
			skinSet.palette.find('.palette-tool').hide();//hide all palette tools
			skinSet.palette.find('h3:first').text(skinSet.canvasObject['name']);
			
			//show relevant tools and set values for palette via 'skinlist' array
			for (var i in skinSet.canvasObject.skinlist) {
				$('.palette-section-'+skinSet.canvasObject.skinlist[i]['skintag']).show();
				
				switch(true) {
					case $.inArray(skinSet.canvasObject.skinlist[i]['csstag'],['border-color','color','background-color']) > -1:
						$('.palette-section-'+skinSet.canvasObject.skinlist[i]['skintag']+' .palette-colorpicker-box')
							.css({'background-color':skinSet.canvasObject.skinlist[i]['value']});
						$('.palette-colorpicker-tool').removeClass('active')//make all inactive, hide colorpicker
						skinSet.colorPicker.hide();
						break;
					case $.inArray(skinSet.canvasObject.skinlist[i]['skintag'],['underline-link','underline-link-hover','bold-links','background-attachment','link-background-attachment']) > -1:
						activeSetter = skinSet.palette.find('.palette-section-'+skinSet.canvasObject.skinlist[i]['skintag']+' a').removeClass('active').text('Off');
						if (activeSetter.data('setting').split(',')[1] == skinSet.canvasObject.skinlist[i]['value']) activeSetter.addClass('active').text('On');
						break;
					case $.inArray(skinSet.canvasObject.skinlist[i]['skintag'],['font-family','background-tile','link-background-tile']) > -1:
						skinSet.palette.find('.palette-section-'+skinSet.canvasObject.skinlist[i]['skintag']+' select').val(skinSet.canvasObject.skinlist[i]['value'])
						break;
					case $.inArray(skinSet.canvasObject.skinlist[i]['skintag'],['background-position','background-position-hover','link-background-position','link-background-position-hover']) > -1:
						skinSet.palette.find('.palette-section-'+skinSet.canvasObject.skinlist[i]['skintag'])
							.find('a').removeClass('active').each(function(){
								if ($(this).metadata().setting == skinSet.canvasObject.skinlist[i]['value']) $(this).addClass('active');
							})
							.end().find('strong').text(skinSet.canvasObject.skinlist[i]['value'].split(' ').reverse().join(' '))
						;
						break;
					case $.inArray(
						skinSet.canvasObject.skinlist[i]['skintag']
						,[
							'width','height','margin-left','margin-right','margin-top','margin-bottom','padding-left','padding-right','padding-top','padding-bottom'
							,'font-size','link-border-width','border-width','align','rounded-corners'
						]) > -1:
						activeSetter = skinSet.palette.find('.palette-section-'+skinSet.canvasObject.skinlist[i]['skintag']+' .palette-slider-tool');
						activeSetter.data('settings')['range'] = [];
						activeSetter.data('settings')['unit'] = skinSet.canvasObject.skinlist[i]['unit'];
						if (skinSet.canvasObject.skinlist[i]['range'] && skinSet.canvasObject.skinlist[i]['range'].length > 0) {
							slideRange = skinSet.canvasObject.skinlist[i]['range'].split(',');
							if (slideRange.length > 1) {
								activeSetter.data('settings')['range'] = slideRange;
								activeSetter.slider('option','min',parseInt(slideRange.length > 2 ? 0 : slideRange[0]));
								activeSetter.slider('option','max',parseInt(slideRange.length > 2 ? slideRange.length-1 : slideRange[1]));
							};
						};
						if ($.isArray(slideRange)) slideRangeInt = $.inArray(skinSet.canvasObject.skinlist[i]['value'],slideRange);
						if (skinSet.canvasObject.skinlist[i]['value'] != 'inherit') {
							activeSetter
								.slider('option','value',(
									slideRange && slideRange.length > 2 && slideRangeInt && slideRangeInt > -1 ? slideRangeInt : skinSet.canvasObject.skinlist[i]['value'])
								)
								.prev('p').find('strong').text(
									skinSet.canvasObject.skinlist[i]['value']+(activeSetter.data('settings')['unit'] ? activeSetter.data('settings')['unit'] : '')
								);
						};
						break;
				};
			};
			
			if (skinSet.isie6) {
				skinSet.palette.css({'top':$(window).scrollTop()+120},500).show()
			} else skinSet.palette.fadeIn('fast'); //display palette
		};
		
		function cancelCanvas(ev){
			skinSet.canvasObject = skinSet.lastCanvasObject;
			for (var i in skinSet.canvasArray) {
				if (skinSet.canvasArray[i]['selector'] == skinSet.canvasObject['selector']) {
					skinSet.canvasArray[i] = skinSet.canvasObject;
					break;
				};
			};
			$class.applyStyleElement(), $class.hidePalette(ev), ev.preventDefault();
		};
		
		function saveYourSkin(ev) {
			if (skinSet.palette.is(':visible')) skinSet.palette.find('.apply-button').trigger('click',{'thisClass':ev.data.thisClass});
			
			if (skinSet.canvasArray.length > 0) {
				//build multidimensional array in stringified (seriously) json to be saved - better to build array front end than serialise giant json object in backend
				var jsonSend = [];
				setTimeout(function(){$('.status-box').text('Saving').show('fast');},100);
				for (var i in skinSet.canvasArray) {
					if (skinSet.canvasArray) {
						jsonSend[i] = { 'id':skinSet.canvasArray[i]['id'] };
						for (var j in skinSet.canvasArray[i].skinlist)
							jsonSend[i][ skinSet.canvasArray[i].skinlist[j]['skintag'] ] = skinSet.canvasArray[i].skinlist[j]['value'];
					};
				};
				
				$.post(
					'ajax/save-skin.php'
					,{'json':$.toJSON(jsonSend),'name':skinSet.menu.find('.skin-name').val(),'new':skinSet.menu.find('.new-button').hasClass('active') ? '1':'0'}
					,function(resp){
						//if (resp == '1') {
							skinSet.menu.find('.new-button').removeClass('active');
							skinSet.canvasArray = [];//empty the array
							//switch the linked style to the one stored
							if ($.trim(resp).length > 0) $('#generated-stylesheet').attr('href','lib/'+resp);
							$('.status-box').text('Saved');
							setTimeout(function(){$('.status-box').hide('slow');},3000);
						//} else alert('There was a problem saving your skin! ');
					}
				);
			} else alert('You have made no changes since you last saved your skin.');
			ev.preventDefault();
		};
		
		function deleteSkin(ev) {
			if (confirm('Are you sure you want to delete this skin?')) {
				$('.status-box').text('Deleting').show('fast');
				$.get('ajax/delete-skin.php',{},function(resp){
					$.trim(resp).length > 0 ? alert(resp) : location.reload(true);
					$('.status-box').hide('fast');
				});
			};
			ev.preventDefault(ev);
		};
		
		function quitSkin(ev) {
			var thisCanvas = $(this);
			$('.skin-this').unbind('mouseenter.choose-canvas mouseleave.choose-canvas click.choose-canvas refreshchoosebox');
			skinSet.active = false;
			skinSet.palette.fadeOut('fast',function(){$(this).remove()});
			skinSet.menu.fadeOut('fast',function(){$(this).remove()});
			skinSet.enabler.removeClass('active');
			skinSet.chooseBox.remove();
			$('style, style.style-element').remove();
			ev.preventDefault();
		};
		
		function enableChooser(ev){
			var $this = $(this);
			if ($this.hasClass('active')) {
				if (skinSet.palette.is(':visible')) skinSet.palette.find('.apply-button').trigger('click',{'thisClass':ev.data.thisClass});
				$this.removeClass('active');
				skinSet.chooserEnabled = false;
			} else {
				$this.addClass('active');
				skinSet.chooserEnabled = true;
			}
			ev.preventDefault();
		};
		
		function rgbToHex(rgb) {
			if (typeof rgb != 'undefined' && rgb.match(new RegExp('^rgba?'))) {
				if (rgb.match(/^rgba\(0+,\s*(\d+),\s*(\d+),\s*(\d+)\)/i)) return 'transparent';
				rgb = rgb.match(/^rgba/i) ? rgb.match(/^rgba\(\d+,\s*(\d+),\s*(\d+),\s*(\d+)\)/i) : rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)/i);
				function hex(isX) {
					hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
					return isNaN(isX) ? "00" : hexDigits[Math.round((isX - isX % 16) / 16)].toString() + hexDigits[Math.round(isX % 16)].toString();
				}
				return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
			} else return rgb;
		};
		
		function showPalette(ev) {
			var thisSelector = skinSet.canvasMeta.selector;
			if (!thisSelector || $.trim(thisSelector).length == 0) thisSelector = skinSet.canvasMeta.altselector; //for toggle tags
			if (thisSelector) {
				//if style data object doesn't exist then assign data from database to canvas and use that object
				skinSet.canvasObject = null;
				for (var i in skinSet.canvasArray) {
					if (skinSet.canvasArray[i]['selector'] == thisSelector) {
						skinSet.canvasObject = skinSet.canvasArray[i];
						break;
					};
				};
				
				if (!skinSet.canvasObject) {
					$('.status-box').text('Loading').show('fast');
					var getSend = { 'selector':thisSelector };
					$.getJSON('ajax/selector-data.php',getSend,function(json){
						skinSet.canvasArray[skinSet.canvasArray.length] = json;
						skinSet.canvasObject = skinSet.canvasArray[skinSet.canvasArray.length-1];
						setPalette();
						$('.status-box').hide('fast');
					});
				} else {
					setPalette();
				};
			} else $class.hidePalette();
		};
		
		function buildColorPickerTool(palName,skintag,palClass){
			return $('<div class="palette-colorpicker-tool palette-tool palette-section-'+skintag+' '+palClass+'" />')
				.append( //set color to transparent, enable picker
					$('<div class="palette-colorpicker-clear palette-icon icon-palette-close" />') 
						.append( $('<img src="images/blank.gif" alt="" />"')
						.bind('click', { 'skintag':skintag }, prepColorPicker ) )
				)
				.append( //set color to colorpicker-box, enable picker
					$('<div class="palette-colorpicker-choose" />')
						.append( 
							$('<a  />')
								.text(palName)
								.prepend( $('<span class="palette-colorpicker-box"><!-- --></span>') )
								.bind('click',{ 'skintag':skintag }, prepColorPicker )  
						)
				)
				.append( '<div class="clearboth"><!-- --></div>' )
			;
		};
		
		function prepColorPicker(ev) {
			var hex = 'transparent', $this = $(this);
			skinSet.colorPicker.show();
			if ($this.find('span').size() > 0 && $this.find('span').css('background-color')) hex = rgbToHex($this.find('span').css('background-color'));
			skinSet.colorPicker.ColorPickerSetColor(hex); //set the colorpicker color
			skinSet.activeColorSkinTag = ev.data.skintag; //set current active color tag
			skinSet.activeColorBox = $('.palette-section-'+skinSet.activeColorSkinTag+' .palette-colorpicker-box'); //create reference to active color box preview
			skinSet.activeColorBox.css('background-color',hex);
			
			$('.palette-colorpicker-tool').removeClass('active'); //remove highlight for inactive tools
			$('.palette-section-'+skinSet.activeColorSkinTag).addClass('active');//show the tool as active
			
			$class.applyStyleElement(skinSet.activeColorSkinTag, hex);
		};
		
		function switchTabs(ev){
			var $this = $(ev.target);
			if ($this.is('img')) $this = $this.parent();
			if ($this.hasClass('palette-icon')) {
				skinSet.palette
					.find('.palette-icon').removeClass('active').end()
					.find('.palette-section').removeClass('active').end()
					.find('.palette-section-'+$this.data('section')).addClass('active')
				;
				$this.addClass('active');
			};
			ev.preventDefault();
		};
		
		function setSimpleSkinTag(ev){
			var $this = $(this), skinTagSetting = $this.data('setting').split(',');
			if (!$this.hasClass('active')) {
				$this.text('On').addClass('active');
				$class.applyStyleElement(ev.data.skintag,skinTagSetting[1]);
			} else {
				$this.text('Off').removeClass('active');
				$class.applyStyleElement(ev.data.skintag,skinTagSetting[0]);
			};
			ev.preventDefault();
		};
		
		function setSliderSkinTag(ev,ui){
			var $this = $(this), settings = $this.data('settings');
			if ($this.parent().find('input:checkbox').is(':checked')) {//synchronise other 3 sliders
				$('.palette-section-'+settings.skintag.replace(/-\w+$/,''))
					.slider('option','value',ui.value)
					.prev('p').find('strong').text(ui.value.toString()+settings.unit)
				;
			} else $this.prev('p').find('strong').text((settings.range.length > 2 ? settings.range[ui.value] : ui.value.toString())+(settings.unit ? settings.unit : ''));
			clearTimeout(skinSet.tCP);
			
			skinSet.tCP = setTimeout(function(){
				$class.applyStyleElement(settings.skintag,(settings.range.length > 2 ? settings.range[ui.value] : ui.value.toString()));
			},25);
		};
		
		function setBackgroundPosition(ev){
			var $target = $(ev.target), meta = $target.metadata();
			if ($target.is('a')) { 
				$(this).find('a').removeClass('active').end().next('p').find('strong').text(meta.setting.split(' ').reverse().join(' '));
				$target.addClass('active');
				$class.applyStyleElement(ev.data.skintag,meta.setting);
			};
			ev.preventDefault();
		};
		
		function setSelectValue(ev){ $class.applyStyleElement(ev.data.skintag,$(this).val()); };
	};
	
	//public methods
	Skin.prototype.applyStyleElement = function(skinTag,setting){
		var 
			$class = this, css = '', lastSkinListName = '', affects = [], triangleSetting = [], roundedSetting = []
			,groupnames = [], extras = [], splitr = [], roundedItems = ['.top-left','.top-right','.bottom-left','.bottom-right']
			,i, k, n, rvs, triangle, selectorGroup = skinSet.canvasObject, skinlist
		;
		//build style element with each style tag and setting - replace previous setting with new one
		//alter Math.floor(Math.random()*16777215).toString(16)
		//cssTags += '\n#whole .feed-node { font-size:'+setting+'; } \n';
		css += "\n/*"+selectorGroup['name']+"*/";
		triangle = skinSet.canvasObject['selector'] == '#whole .teoti-points' ? true : false;
		
		//get all group names first
		for (k in selectorGroup['skinlist']) {
			skinlist = selectorGroup['skinlist'][k];
			if (skinTag && skinlist['skintag'] == skinTag) { //if skintag is this then edit object value
				skinlist['value'] = setting; //wow, that was almost easy wasn't it? Not quite there yet though :(
			};
			skinlist['groupname'] = skinlist['groupname'] ? skinlist['groupname'] : '';
			if (k == 0 || (skinlist['groupname'] != lastSkinListName)) {
				if (skinlist['groupname'] != lastSkinListName && k > 0) css += "}";
				groupnames = skinlist['groupname'] ? skinlist['groupname'].split(','): [''];
				css += "\n";
				for (n in groupnames) css += (n > 0 ? ", ":"")+selectorGroup['selector']+groupnames[n];
				if (selectorGroup['extras'] && selectorGroup['extras'].length > 0) { //add the extras
					extras = selectorGroup['extras'].split("\n");
					for (n in extras) {
						splitr = extras[n].split("|"); //splitr[0] = extra selector + values, splitr[1] = group name that extra belongs to
						if (splitr[1] && splitr[1] == skinlist['groupname']) css += ', '+splitr[0];
					};
				};
				css += " { ";
			};
			
			if ($.inArray('rounded-corners',selectorGroup['variables']) > -1
				&& $.inArray(skinlist['skintag'],['border-color','border-width','rounded-corners','background-color']) > -1
				&& !roundedSetting[skinlist['skintag']]) {
				roundedSetting[skinlist['skintag']] = skinlist['value']; //get background color, border color, border width and rounded corner radius. 
			} else css += $class.formatCSSRule(skinlist); //now build the css tags
			
			if (triangle && $.inArray(skinlist['skintag'],['link-color-hover','link-color']) > -1) triangleSetting[skinlist['skintag']] = skinlist['value'];
			
			
			
			//get affected items
			if (skinlist['affects'] && skinlist['affects'].length > 0) affects[affects.length] = { 'data' : skinlist['affects'], 'value':skinlist['value'],'range' : skinlist['range'] };
			
			lastSkinListName = skinlist['groupname'];//send group name to next looped item
		};
		css += "}"; //close the last css tag
		
		if (affects.length > 0) {//apply what this element also affects
			var affect, compareval;
			for (n in affects) {
				affect = affects[n];
				affsel = comparestring = '';
				compareval = affect['value'];
				aff = affect['data'].split("="); //affected selector [0], affected css stuff [1]
				comp = aff[1].split(":"); //csstag[0],comparestring[1]
				range = affect['range'].split(",");
				if (comp[1]) compares = comp[1].split(',');
				if (comp[1] && range.length > 0 && compares.length > 0) {
					for (k in range) {
						if (range[k] == affect['value'] && $.trim(compares[k])) {
							compareval = compares[k];
							break;
						};
					};
				};
				css += "\n/*affected selector*/\n"+aff[0]+' { '+comp[0]+': '+compareval+($class.is_numeric(compareval) && compareval > 0 ? 'px':'')+'; }';
			};
		};
		
		if ($.inArray('rounded-corners',selectorGroup['variables']) > -1 && roundedSetting && roundedSetting['rounded-corners']) {
			for (k in roundedItems) {//generate rounded corners css
				css += "\n"+selectorGroup['selector']+' '+roundedItems[k]+'  {';
				css += ' padding: 0; background-color:transparent; background-image:none; background-repeat: no-repeat;';
				if (roundedSetting['rounded-corners'] > 0) {
					css += ' background-image: url(images/rounded.php?c='+roundedSetting['background-color'].replace('#','');
					css += '&r='+(parseInt(roundedSetting['rounded-corners'])+parseInt(roundedSetting['border-width']))+'&bc='+roundedSetting['border-color'].replace('#','');
					css += '&b='+roundedSetting['border-width']+'); ';
					css += 'padding'+roundedItems[k].replace('.top','').replace('.bottom','')+': ';
					css += (parseInt(roundedSetting['rounded-corners'])+parseInt(roundedSetting['border-width']))+'px; ';
					css += ' background-position: '+$.trim(roundedItems[k]).replace('.','').split('-').reverse().join(' ')+'; '; //wowzas!
				};
				css += '}';
			};
			rvs = roundedSetting['border-width']+'px solid '+roundedSetting['border-color']+'; background-color:'+roundedSetting['background-color']+'; ';
			rvs += 'height: '+roundedSetting['rounded-corners'].toString()+'px';
			//rvs += skinSet.isie6 && roundedSetting['border-width'] > 0 && roundedSetting['rounded-corners'] > 0 ? 
			//	'!important; height: '+(parseInt(roundedSetting['rounded-corners']) + parseInt(roundedSetting['border-width']))+'px ':'';
			css += "\n"+selectorGroup['selector']+' .top-inner { border-top: '+rvs+'; }';
			css += "\n"+selectorGroup['selector']+' .bottom-inner { border-bottom: '+rvs+' }';
			css += "\n"+selectorGroup['selector']+' .body-inner { background-color: '+roundedSetting['background-color']+'; }';
			css += "\n"+selectorGroup['selector']+' .body-left { border-left:'+roundedSetting['border-width']+'px solid '+roundedSetting['border-color']+'; }';
			css += "\n"+selectorGroup['selector']+' .body-right { border-right:'+roundedSetting['border-width']+'px solid '+roundedSetting['border-color']+'; }';
		};
		
		if (triangle && triangleSetting && triangleSetting['link-color'] && triangleSetting['link-color-hover']) {
			var pturl = location.protocol+'//'+location.hostname+URLPATH+'/images/shapes/triangle_';
			pturl += (triangleSetting['link-color'].match(new RegExp('[A-Fa-f0-9]{6}')) ? triangleSetting['link-color'].replace('#','') : '585e9c');
			pturl += '_'+(triangleSetting['link-color-hover'].match(new RegExp('[A-Fa-f0-9]{6}')) ? triangleSetting['link-color-hover'].replace('#','') : '010767')+'.png';
			css += "\n/*triangle*/\n"+selectorGroup['selector']+' a { background-image: url(images/phpThumb.php?src='+escape(pturl)+'&w=36&h=36&f=png); }';
		};
		
		if (selectorGroup['custom']) css += selectorGroup['custom']; //extra custom css to be appended
		//remove the style element
		skinSet.styleElement.remove();
		//create the element
		skinSet.styleElement = $('<style type="text/css" id="style-element-'+selectorGroup['id']+'" class="style-element"> '+css+' </style>'); 
		//add style element to header again - this is the only way to change the style for some reason. 
		//The +1 below is because the element is still a reference in the array stored
		if (skinSet.styleElementIndex < skinSet.styleElementsWithout.size()) 
			$(skinSet.styleElementsWithout.get(skinSet.styleElementIndex)).before(skinSet.styleElement);
		else $('head').append(skinSet.styleElement);
		//reset size of choose box and change dimensions
		skinSet.canvas.trigger('refreshchoosebox');
		$class.showCanvasDimensions();
		//awesome.
	};
	
	Skin.prototype.formatCSSRule = function (skinlist) {
		var $class = this;
		switch (true) {//then build the css settings
			case skinlist['csstag'] == 'rounded-corners': return '';
			case skinlist['csstag'] == 'width': return skinlist['csstag']+": "+($class.is_numeric(skinlist['value']) && skinlist['value'] > 0 ? skinlist['value']+skinlist['unit'] :'auto')+"; ";
			case skinlist['skintag'] == 'align': return skinlist['csstag']+": "+(skinlist['value'] ? skinlist['value'] :'0 auto')+"; ";
			case skinlist['unit'] == 'px': return skinlist['csstag']+": "+skinlist['value']+( $class.is_numeric(skinlist['value']) ? skinlist['unit'] : '' )+"; ";
			case skinlist['unit'] == 'url': return skinlist['csstag']+": "+(skinlist['value'] && skinlist['value'] != 'none' ? 'url('+skinlist['value']+')':'none')+"; ";
			case 
				$.inArray(skinlist['skintag'],['text-color','link-color','link-color-hover','link-background-color','link-background-color-hover']) > -1 
				&& skinlist['value'] == 'transparent': 
				return skinlist['csstag']+": inherit; ";
			default: return skinlist['csstag']+": "+skinlist['value']+"; ";
		}
	};
	
	Skin.prototype.is_numeric = function (input) { 
		return (input - 0) == input && input.length > 0; 
	};
	
	Skin.prototype.showCanvasDimensions = function  () {
		var parents = $(skinSet.canvasObject['parents']), $class = this;
		skinSet.palette
			.find('.palette-canvas-width').text(skinSet.canvas.outerWidth().toString())
			.end().find('.palette-canvas-height').text(skinSet.canvas.outerHeight().toString())
			.end().find('.palette-canvas-parents').empty().each(function(){
				if (skinSet.canvasObject['parents']&& skinSet.canvasObject['parents'].length > 0) {
					for (var i in skinSet.canvasObject['parents']) {
						if (i > 0) $(this).append(' < ')
						$(this).append( $(skinSet.canvasObject['parents'][i]).bind('click',{'thisClass':$class},$class.changeCanvasSelector) );
					};
				} else $(this).append('None');
			})
		;
	};
	
	Skin.prototype.changeCanvasSelector = function (ev) {
		ev.data.thisClass.hidePalette();
		$($(this).metadata().selector).trigger('click.choose-canvas');
		ev.preventDefault();
	};
	
	Skin.prototype.hidePalette = function (ev){
		//have they applied it?
		//if (skinSet.applied == false && confirm('Do you wish to apply the changes made?')) return this.applyCanvas(); //if so, apply it
		skinSet.canvas.removeClass('skin-active'); //make canvas inactive
		skinSet.isie6 ? skinSet.palette.hide() : skinSet.palette.fadeOut('fast'); //hide the palette
		skinSet.canvasActive = false; 
		skinSet.isie6 ? skinSet.chooseBox.hide() : skinSet.chooseBox.fadeOut('fast');//hide the choose box
		if (ev != undefined) ev.preventDefault();
	};
	
	Skin.prototype.applyCanvas = function(ev) {
		ev.data.thisClass.hidePalette();
		if (ev != undefined) ev.preventDefault();
		return false;
	};
	
	//highlighting/choosing tool
	$.fn.chooser = function(){
		this.each(function(){
			var thisCanvas = $(this), styleObject = {};
			init();
			
			function init(){
				thisCanvas.bind('mouseenter.choose-canvas mouseleave.choose-canvas click.choose-canvas refreshchoosebox',canvasSelector);
			};
			
			function canvasSelector(ev) {
				clearTimeout(skinSet.tC);
				
					if (ev.type == 'mouseleave' && !skinSet.canvasActive) {
						skinSet.isie6 ? skinSet.chooseBox.hide() : skinSet.chooseBox.fadeOut('fast');
						return false;
					};
				
				if (skinSet.chooserEnabled) {
					if (!skinSet.canvasActive) skinSet.tC = setTimeout(displayChooseBox,600);
					
					if (ev.type == 'refreshchoosebox') displayChooseBox();
					
					if (ev.type == 'click') {
						if (!skinSet.canvasActive) {
							thisCanvas.addClass('skin-active');
							skinSet.canvas = thisCanvas;
							skinSet.canvasMeta = thisCanvas.metadata();
							displayChooseBox();
							clearTimeout(skinSet.tC);
							//set chosen element
							skinSet.canvasActive = true;
							skinSet.palette.trigger('showPalette');
						};
					};
					ev.stopPropagation();
				};
				ev.preventDefault();
				
			};
			
			
			function displayChooseBox(){
				var 
					chTop = skinSet.chooseBox.filter('.choose-top'), chRight = skinSet.chooseBox.filter('.choose-right')
					,chBottom = skinSet.chooseBox.filter('.choose-bottom'), chLeft = skinSet.chooseBox.filter('.choose-left')
					,targetElPos = thisCanvas.offset(), targetElWidth = thisCanvas.outerWidth(), targetElHeight = thisCanvas.outerHeight()
				;
				
				chTop.height(targetElPos.top);
				chRight.width(parseUNum(skinSet.wholePage.width()-(targetElPos.left+targetElWidth))).height(parseUNum(targetElHeight)).css({'top':targetElPos.top,'left':targetElWidth+targetElPos.left });
				chBottom.height(parseUNum(skinSet.wholePage.height()-(targetElPos.top+targetElHeight))).css({'top':targetElPos.top+targetElHeight});
				chLeft.width(parseUNum(targetElPos.left)).height(parseUNum(targetElHeight)).css({'top':targetElPos.top});
				skinSet.isie6 ? skinSet.chooseBox.show() : skinSet.chooseBox.fadeIn('fast');
			};
		});	
	};
	

//})(jQuery);

$(function(){
	//alert('activated');
	skinSet.tools = new Skin($('#design-button')); //initialize the skinner class
	
	//design button toggle
	$('#design-button')
		.addClass('active') 
		.click(function(ev){
			var $this = $(this)
			if ($this.not('.active').size() > 0) {
				skinSet.tools = new Skin($this);
				$this.addClass('active');
				ev.preventDefault();
			};
		})
	;
});
//indexof fix
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (searchElement /*, fromIndex */ ) {
        "use strict";
        if (this == null) {
            throw new TypeError();
        }
        var t = Object(this);
        var len = t.length >>> 0;
        if (len === 0) {
            return -1;
        }
        var n = 0;
        if (arguments.length > 0) {
            n = Number(arguments[1]);
            if (n != n) { // shortcut for verifying if it's NaN
                n = 0;
            } else if (n != 0 && n != Infinity && n != -Infinity) {
                n = (n > 0 || -1) * Math.floor(Math.abs(n));
            }
        }
        if (n >= len) {
            return -1;
        }
        var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
        for (; k < len; k++) {
            if (k in t && t[k] === searchElement) {
                return k;
            }
        }
        return -1;
    }
}
// // JavaScript Document
//TODO: make setCalendar easier to re-implement
function setCalendar(elID) {
    var curDay, daysInMonthArray, day, i;
    
	curDay = $('#get'+elID+' .day').val();
	daysInMonthArray= new Array(-1,31,($('#get'+elID+' .year').val() % 4 ? 28:29),31,30,31,30,31,31,30,31,30,31);
	day=month=new Array();
    
	for (i=1;i<=31;i++) day[i-1] = i > 9 ? i : "0"+i.toString();
	for (i=1;i<=12;i++) month[i-1] = i > 9 ? i : "0"+i.toString();
    
	if ($('#get'+elID+' .month').val() > 0) {
		$('#get'+elID+' .day').html("<option value=\"-1\">Day:</option>");
		if (curDay > daysInMonthArray[$('#get'+elID+' .month').val()]) {
			for (i=1;i <= daysInMonthArray[$('#get'+elID+' .month').val()];i++) $('#get'+elID+' .day').append("<option value=\""+i+"\">"+i+"</option>");
			$('#get'+elID+' .day').val(daysInMonthArray[$('#get'+elID+' .month').val()]);
		} else {
			for (i=1;i <= daysInMonthArray[$('#get'+elID+' .month').val()];i++) $('#get'+elID+' .day').append("<option value=\""+i+"\">"+i+"</option>");
			$('#get'+elID+' .day').val(curDay);
		}
	}
    
	if ($('#get'+elID+' .day').val() > 0 && $('#get'+elID+' .month').val() > 0 && $('#get'+elID+' .year').val() > 0) {
		$('#reg'+elID).val(
            $('#get'+elID+' .year').val()+'-'+month[$('#get'+elID+' .month').val()-1]+"-"+day[$('#get'+elID+' .day').val()-1]
        );
    } else {
        $('#reg'+elID).val('');
    }
}

function addslashes(str) {
	return str.replace(/\\/g,'\\\\').replace(/\'/g,'\\\'').replace(/\"/g,'\\"').replace(/\0/g,'\\0').replace(/\//g,'\\/');
}

function addZero(num) {return num.toString().length > 1 ? num.toString() : '0'+num.toString();}

function activateSkinner(ev){
	$.getScript('lib/skinner.js');
	ev.preventDefault();
}

function popupUserinfo(ev) {
	var $this = $(this), username = $.trim($this.text());
	//if (username.length == 0) username = $this.metadata().username;
	$('.popup').hide();
	$.getJSON('ajax/getuserinfo.php',{'username': username},function(userinfo){
		if (!userinfo.error) {
			$('#userinfo-box')
				.empty()
				.append( $('<a href="'+userinfo.profile+'" />').text('View '+username+'\'s profile') )
				.each(function(){ 
                    if (userinfo.threadtitle) {
                        $(this)
                            .append( $('<a href="'+userinfo.threadlink+'" />')
                            .text('Last viewing '+userinfo.threadtitle) ) 
                        ;
                    }
                })
				.append( $('<a href="'+userinfo.conversation+'" />').text('Have a conversation with '+username) )
				.append( $('<a href="'+userinfo.threads+'" />').text('View threads by '+username) )
				.append( $('<a href="'+userinfo.posts+'" />').text('View posts by '+username) )
				.append( $('<a href="'+userinfo.points+'" />').text('View liked/disliked posts by '+username) )
				.css({'top':ev.pageY,'left':ev.pageX})
				.show('fast');
		}
	});
	
	ev.preventDefault();
}

function capitaliseFirstLetter(str) {
	return str.charAt(0).toUpperCase() + str.slice(1);
}

function setPages() {
    var thisEl, pageNumHtml;
	
    thisEl = $('.pagination');
	thisEl.find('.prev-button').html( 
		constants.curpage > 1 ? 
			$('<a href="'+constants.stripRequestUri+'?page='+(constants.curpage-1)+'" />').text('Prev')
			: '&nbsp;' 
	);
	thisEl.find('.next-button').html( 
		constants.curpage < constants.curpagetotal && constants.curpagetotal > 1 ? 
			$('<a href="'+constants.stripRequestUri+'?page='+(constants.curpage+1)+'" />').text('Next')
			: '&nbsp;' 
	);
	
    pageNumHtml = '';
	for (var i = 1; i <= constants.curpagetotal; i++) {
		pageNumHtml += '<a href="'+constants.stripRequestUri+'?page='+i+'">'+(i == constants.curpage ? '<strong>['+i+']</strong>' : '['+i+']')+'</a>';
	}
	thisEl.find('.per-page').html( pageNumHtml );
	if (constants.curpagetotal > 1 && thisEl.is(':hidden')) {
		thisEl.slideDown()
	} else if (thisEl.is(':visible')) {
		thisEl.slideUp();
	}
}

function ago(metaTime,dateOnly) {
    var curTime = Math.round((new Date()).getTime() / 1000) - parseInt(metaTime,10), timeFormatted;
    if (typeof dateOnly !== 'boolean') dateOnly = false;
    switch (true) {
        case !dateOnly && curTime < 60 && curTime > 0:
            timeFormatted = curTime+' second'+(curTime == 1 ? '':'s')+' ago'; 
            break;
        /*case curTime < (60*60) && curTime > 0: 
            var mins = Math.floor(curTime/60);
            thisEl.text( mins+' minute'+(mins == 1 ? 's':'')+' ago' );
            break;*/
        case !dateOnly && curTime < (60*60*24) && curTime > 0:
            var hrs = Math.floor(curTime/(60*60)), mins = Math.floor((curTime - (hrs*60*60))/60);
            timeFormatted = (hrs > 0 ? hrs+' hour'+(hrs == 1 ? '':'s')+' ' : '')
                +(mins > 0 ? mins+' minute'+(mins == 1 ? '':'s')+' ' : '')+' ago';
            break;
        default:
            var 
                thisDate = new Date( (parseInt(metaTime,10)+parseInt(constants.timezoneOffset,10))*1000 )
                ,ap = "am", hr = thisDate.getUTCHours()
            ;
            if (hr > 11) ap = "pm"; 
            if (hr > 12) hr = hr - 12; 
            if (hr == 0) hr = 12;
            timeFormatted = hr+':'+addZero(thisDate.getUTCMinutes())+' '+ap+' '
                +addZero(thisDate.getUTCDate())+'/'+addZero(thisDate.getUTCMonth()+1)+'/'+thisDate.getUTCFullYear();
    }
    return timeFormatted;
}



(function($){
	$.fn.points = function() {
		this.each(function(){
			//get post id
			var postid = this.id.match(/[^\-]*$/).toString(), pel = $('.points-bit span',this), thisEl = $(this), gvpts;
			$('a',this)
				.click(function(){
					var 
                        el = $(this), pts, data = {
                            'do': 'update', 'ajax':1, 'postid': postid, 'type': 'likedislike'
                            ,'plusminus': (el.hasClass('points-dec') ? '0':'1')
                        }
                    ;
                    if (constants.sessUserId > 0){
                        if (!el.hasClass('inactive')) { //add el.hasClass('isset') to be unable to clear pts
                            if (!el.hasClass('isset')) {
                                var elp = el.parent().find('.isset');
                                // if the other link is not set then pts are +/-1 else +/-2. gvpts is always +/-1
                                pts = elp.size() > 0 ? el.hasClass('points-inc') ? '2' : '-2' : el.hasClass('points-inc') ? '1' : '-1';
                                if (elp.size() > 0) elp.each(function(){$(this).removeClass('isset');});
                                gvpts = el.hasClass('points-inc') ? '1' : '-1';
                                el.addClass('isset');
                            } else {
                                pts = el.hasClass('points-inc') ? '-1' : '1';
                                gvpts = '0';
                                el.removeClass('isset');
                            }
                            
                            pel.text(parseInt(pel.text())+parseInt(pts));
                            data.give = gvpts;
                            data.sessionid = constants.usess;
                            $('.popup').hide();
                            if (socket.socket && !socket.socket.connected && !socket.socket.connecting && socket.socket.connect) {
                                socket.socket.connect();
                            }
                            socket.emit('submit',data);
                        }
                        
                    } else {
                        location.href='./register'; 
                    }
		
					return false;
				})
			;
		});
		return this;
	};
	
	$.fn.pointhover = function(){
		this.each(function(){
			//get post id
			var postid = this.id.match(/[^\-]*$/).toString(), thisEl = $(this);
			$('a',this)
				.mouseenter(function(){
					clearTimeout(tpt);
					var el = $(this), elOs;
					tpt = setTimeout(function(){
						$('.popup').hide();
						$.post('points.php',{'postid':postid,'plusminus':(el.hasClass('points-dec') ? '0':'1'),'ajax':'1','do':'update'},function(r){
							if (r.length > 0) {
								elOs = el.position();
								thisEl
									.find('.popup').html(r)
									.css({'top':elOs.top, 'left':elOs.left+el.width()+10})
									.show(100)
								;
							}
						});
					},400);
					return false;
				})
				.mouseleave(function(){ 
					clearTimeout(tpt); 
					thisEl.find('.popup:visible').hide(100);				
				})
			;
		});
		return this;
	};
	
	$.fn.boxGet = function () {
		return this.click(function (ev) {
			var $this = $(this), meta = $this.metadata(), pos, popup, dataSend, boxFilter;
			$('.popup').hide();
			switch(true){
				case $this.hasClass('box-type-select'):
					//show box type drop down
					pos = $this.position();
					$('.popup-boxtype').css({'left':pos.left,'top': pos.top+$this.outerHeight()}).show();
					return false;
				case $this.hasClass('box-filter-select'):
					//show box filter drop down
					pos = $this.position(); 
                    popup = $('.popup-'+meta.boxtype);
					if (popup.size()) {
						popup.css({'left':pos.left-popup.outerWidth()+$this.outerWidth(),'top': pos.top+$this.outerHeight()}).show();
						return false;
					}
					break;
				case $this.hasClass('box-type-button'):
					$('.box-type-select').text( $this.text() );
					//set filter on box type meta data
					boxFilter = $('.box-filter-select');
					if (meta.filtername.length > 0) {
						boxFilter.text( meta.filtername );
						boxFilter.metadata().boxtype = meta.boxtype;
						boxFilter.parent().removeClass('hidethis');
					} else {
						boxFilter.parent().addClass('hidethis');
					}
					break;
				case $this.hasClass('box-filter-button'):
					//set filter on box filter meta data
					$('.box-filter-select').text( $this.text() );
					break;
			}
			
			dataSend = {
				'boxtype':meta.boxtype
				,'forumid':meta.forumid
				,'filter':(meta.filter != undefined && meta.filter.length > 0 ? meta.filter:'')
			};
			
			if (meta.page != undefined && meta.page.length) {
				dataSend['page'] = meta.page;
			}
			
			$.get('ajax/boxes.php',dataSend,function(r){
				$('.box .col-head-links').html(r).find('.paginate').boxGet();
			});
			
			ev.preventDefault();
			ev.stopPropagation();
		});
	};
	
	
	$.fn.imageResize = function(block){
		if (block == undefined) block = 'div'
		return this.each(function(){
			var 
				thisEl = $(this)
				,meta = thisEl.metadata()
				,oW = thisEl.width()
				,oH = thisEl.height()
				,cW = thisEl.closest(block).innerWidth() //set max width to nearest selector
				,cH = 0
			;
			
			if (meta.w && meta.h){
				oW = meta.w;
				oH = meta.h;
			}
			
			cH = Math.round(oH*(cW/oW));
			
			if (oW > cW) {
				thisEl
					.data('scales',{'owidth':oW, 'oheight':oH, 'cwidth': cW, 'cheight':cH})
					.before(
						$('<div class="teoti-button image-resize skin-this {\'selector\':\'#whole .teoti-button\'}"><a href="'+thisEl.attr('src')+'" target="_blank">Full Size: '+oW+'x'+oH+'</a></div>')
					)
					.each(function(){
						if ('maxWidth' in document.body.style) {
							$(this).css({'max-width':'100%','height':'auto'});
						} else {
							$(this).css({'width':cW, 'height':cH});
						}
					})
				;
			}
		});
	};
	
	$.fn.whosonline = function(selector){
		if (selector == undefined) selector = '#whosonline-outer';
		var parentEl = $(selector), popup = parentEl.find('.popup');
		this
			.mouseenter(function(){
				var thisEl = $(this), thisOffset = thisEl.position();
				popup.text(thisEl.attr('rel')).css({'left':thisOffset.left-(popup.width()/2-5),'top':thisOffset.top+thisEl.height()}).show();
			})
			.mouseleave(function(){
				popup.hide();
			})
		;
		return this;
	};
	
	$.fn.timeago = function(){
		return this.each(function(){
			var 
                wrapper = $(this), meta = wrapper.metadata(), thisEl = wrapper.find('.time-ago')
                ,metaTime = meta.hasOwnProperty('created') ? meta.created : meta.datetime
            ;
			
			if (metaTime !== undefined) {
				metaTime = parseInt(metaTime,10);
                thisEl.text( ago(metaTime) );
			}
		});
	};
	

	$.fn.noteNudge = function () {
		return this.click(function(ev){
            if ($('#notification-box:visible').size() == 0 && $(this).find('span').text() != '0') {
				if (constants.isLoggedIn) {
					socket.emit('notification', {'force': true});
				}
				ev.preventDefault();
				ev.stopPropagation();
			}
		});
	}

    $.fn.textEditor = function (opts) {
        var settings = {
                textarea: "textarea",
                buttonToggle: ".toggle-wysiwyg",
                ckeditorSettings: {
                    extraPlugins : 'bbcode',
                    // Remove unused plugins.
                    removePlugins : 'bidi,button,dialogadvtab,div,filebrowser,flash,format,forms,horizontalrule,iframe,indent,justify,liststyle,pagebreak,showborders,stylescombo,table,tabletools,templates',
                    // Width and height are not supported in the BBCode format, so object resizing is disabled.
                    disableObjectResizing : true,
                    // Define font sizes in percent values.
                    fontSize_sizes : "X Small/6px;Small/8px;Normal/11px;Large/14px;X Large/22px;XX Large/32px;XXX Large/48px",
                    'resize_dir' : 'vertical',
                    toolbar : [
                        ['Source', '-', 'Save','NewPage','-','Undo','Redo'],
                        ['Find','Replace','-','SelectAll','RemoveFormat'],
                        ['Link', 'Unlink', 'Image', ,'SpecialChar'],
                        ['Bold', 'Italic','Underline'],
                        ['FontSize'],
                        ['TextColor'],
                        ['NumberedList','BulletedList','-','Blockquote'], ['Maximize']
                    ]
                }
            };


        function loadTextEditor($this, wysiwyg, firstLoad) {
            if (wysiwyg) {
                if (!firstLoad) {
                    $this.markItUpRemove();
                }
                $this.ckeditor(function(){}, settings.ckeditorSettings);
            } else {
                if (!firstLoad) {
                    $this.ckeditor(function(){
                        this.destroy();
                    });
                }
                $this.markItUp( mySettings );
            }
        };

        if (typeof opts === 'object') {
            $.extend(settings,opts);
        }

        this.find(settings.textarea).each(function () {
            loadTextEditor( $(this), constants.wysiwyg, true );
        });

        this.on("click", settings.buttonToggle, function (ev) {
            var $this = $( ev.currentTarget),
                $textarea = $(ev.delegateTarget).find( settings.textarea );

            ev.preventDefault();

            $this.text( 'Use ' + ( constants.wysiwyg ? 'WYSIWYG':'BBCode' ) );

            //toggle editor type
            constants.wysiwyg = !constants.wysiwyg;
            $.get('ajax/usersettings.php',{'type':'wysiwyg','setting': constants.wysiwyg ? '1':'0'});

            loadTextEditor( $textarea, constants.wysiwyg, false );
        });

        return this;
    };
	
	$.fn.pause = function(duration,func) {
		if (func === undefined) func = function(){}
	  $(this).animate({dummy: 1}, duration, func);
	  return this;
	}
	
	$.fn.autocompleteMore = function(opts){
		var settings = {
			'path':'ajax/user.php',
			'chars':3
		}, self = this;
		
		if (typeof opts == 'object') {
            $.extend(settings,opts);
        }
		
		self.autocomplete(settings.path,{minChars:settings.chars,max:15,selectFirst:false,cacheLength:1})
			.result(function(ev,data,fmt){ 
				$(this).prev('input').val(data[1]);
				if (data[2]) $(this).parent().find('input:first').val(data[2]);
				$(this).replaceWith( 
					$('<a href="#click-to-remove" rel="'+$(this).attr('class')+'" class="displayblock" />')
						.text( data[0] )
						.click(function(ev){
							var inputField = $('<input type="text"/>')
								.attr('value',$(this).text())
								.attr('class',$(this).attr('rel'))
								.autocompleteMore(settings) 
								.blur().focus() //fix caching bug
							;
							$(this).replaceWith( inputField );
							inputField.select();
							inputField.prev('input').val('');
							if (ev && ev.preventDefault) ev.preventDefault();
						})
				);
			})
		;
		return self;
	}
	
	$.fn.searchFilter = function(){
		var settings = {
			'delay':400
			,'chars':2
			,'mainWrapper':$('#main-outer')
			,'autocompleteBox':$('<div id="search-list" class="teoti-button"><!-- --></div>').width(this.width())
			,'timer':null
			,'searchListActive':false
		}, self = this;
		
		self.attr('autocomplete','off').one('focus',function(){
			$(this).next('a').show();
			$(this).next('a').one('click',function(ev){
				$(this).remove();
				$('.search-advanced').show();
				self.parent().find('.search-username').autocompleteMore({'path': 'ajax/user.php', 'chars': 2});
				self.parent().find('.search-category').autocompleteMore({'path': 'ajax/forums.php', 'chars': 2});
				if (ev && ev.preventDefault) ev.preventDefault();
			});
		}).keyup(function(ev){
			var $this = $(this);
			switch(ev.keyCode) {
				case 40://down arrow
					if (settings.autocompleteBox.find('a').size() > 0) {
						$this.blur();
						//settings.autocompleteBox.find('a:first').addClass('active');
						settings.searchListActive = true;
					}
					break;
				case 13: //default action on return keys
                    $this.closest('form').submit();
                    break;
				default:// search on all other keypresses
					clearTimeout(settings.timer);
					if ($this.val().length >= settings.chars) {
						settings.timer = setTimeout(function(){
							var 
								forumid = self.parent().find('input[name=forumid]').val()
								,userid = self.parent().find('input[name=userid]').val()
								,data = { 
                                    'q':self.val()
                                    ,'forumid':forumid.length > 0 ? forumid : constants.forumId
                                    ,'userid' :userid.length > 0 ? userid : ''
                                    ,'which':self.parent().find('input[name=which]:checked').val()
                                }
                            ;
							
							$.getJSON('ajax/search.php',data,function(results){
								//populate and show search box if there are results
								if (results.users.length > 0 || results.threads.length) {
									var i, user, thread, pos = self.position();
									settings.autocompleteBox
										.empty()
										.css({'left':pos.left+'px', 'top':(pos.top+self.height()+4).toString()+'px'})
										.show()
									;
									
									if (results.users.length > 0){
										for (i in results.users){
											user = results.users[i];
											if (typeof user != 'function'){
												settings.autocompleteBox.append(
													$('<a href="'+user['link']+'" class="displayblock clearfix" />')
														.append( $('<img src="images/phpThumb.php?zc=1&w=22&h=22&src='+user['avatar']+'" style="width:22px;height:22px;" alt="" />') )
														.append( $('<span />').text(user.username) )
												);
											}
										}
									}
									
									if (results.threads.length > 0){
										for (i in results.threads){
											thread = results.threads[i];
											if (typeof thread != 'function'){
												settings.autocompleteBox.append(
													$('<a href="'+thread['link']+'" title="By '+thread.username+'" class="displayblock clearfix" />')
														.append( $('<img src="images/phpThumb.php?zc=1&w=22&h=22&src='+thread.thumbnail+'" style="width:22px;height:22px;" alt="" />') )
														.append( $('<span />').text(thread['title']) )
												);
											}
										}
									}
								} else {
									settings.autocompleteBox.empty().hide();
								}
								
								
							});
						},settings.delay);
					} else {
						settings.autocompleteBox.hide();
					}
					break;
			}
		});
		
		self.closest('form').submit(function(ev){
			var $this = $(this), forumUrl = $this.find('input[name="forumurl"]').val();
			if ($this.find('input[name=filter]').val().length == 0) {
				ev.preventDefault();
			} else {
				if (forumUrl.length > 0) $this.attr('action',forumUrl+'/');
				$this.find('input[name=forumid]').remove();
				$this.find('input[name=forumurl]').remove();
			}
		});
		
		$(window).keypress(function(ev){
			if (settings.searchListActive && settings.autocompleteBox.is(':visible')) {
				var 
					searchLinks = settings.autocompleteBox.find('a')
					,activeLink = searchLinks.filter('.active').get()
					,indx = searchLinks.index($(activeLink))
				;
				switch(ev.keyCode){
					case 38://up arrow
						if (indx > 0) {//select previous item
							searchLinks.removeClass('active').eq(indx-1).addClass('active');
						} else {
							searchLinks.removeClass('active');
							settings.searchListActive = false;
							self.focus();
						}
						if (ev && ev.preventDefault) ev.preventDefault();
						break;
					case 40://down arrow
						if (indx+1 < searchLinks.size()) {
							searchLinks.removeClass('active').eq(indx+1).addClass('active');//select next item
						}
						if (ev && ev.preventDefault) ev.preventDefault();
						break;
					case 13: //return key
						location.href = $(activeLink).attr('href');
						break;
				}
			}
		});
		
		settings.mainWrapper.append(settings.autocompleteBox);
		
		return this;
    }
    
    $.fn.serializeObject = function() {
        var arrayData, objectData;
        arrayData = this.serializeArray();
        objectData = {};

        $.each(arrayData, function() {
            var value;

            if (this.value != null) {
                value = this.value;
            } else {
                value = '';
            }

            if (objectData[this.name] != null) {
                if (!objectData[this.name].push) {
                    objectData[this.name] = [objectData[this.name]];
                }

                objectData[this.name].push(value);
            } else {
                objectData[this.name] = value;
            }
        });

        return objectData;
    };
    
    $.fn.deletenode = function(){
        return this.click(function(ev){
            var $this = $(this), meta = $(this).metadata();
            meta.ajax = 1; //failsafe
            if (confirm('Are you sure you want to '+(meta['do'] ? meta['do'] : 'delete')+' this?')) {
                $this.closest('.feed-node-outer').slideUp(function(){
                    $(this).remove();
                }); 
                if ($this.hasClass('delete-nocallback') || $this.hasClass('forget')) {
                    $.get($this.attr('href'));
                } else {
                    meta.sessionid = constants.usess;
                    if (socket.socket && !socket.socket.connected && !socket.socket.connecting && socket.socket.connect) {
                        socket.socket.connect();
                    }
                    socket.emit('submit',meta);
                }
            }
            ev.preventDefault();
        });  
    }

})(jQuery);

function onFacebookConnect(ev){
    ev.preventDefault();

    if (window.FB) {
        FB.login(
            function(response) {
                if (response.session) {
                    if (response.perms) {
                        // user is logged in and granted some permissions.
                        // perms is a comma separated list of granted permissions
                        // redirect to home page?
                        location.href=constants.stripRequestUri+'?merge=1';
                    } else {
                        // user is logged in, but did not grant any permissions
                        // log them out again
                        FB.logout(function(response) {
                            // user is now logged out
                            alert('Sorry, you must have permissions applied in order to use Facebook Connect on this application.');
                        });
                    };
                } else {
                    // user is not logged in
                };
            }
            ,{perms:'email,publish_stream'}
        );
    }
};

function getHelp (ev) {
    var url = $( ev.currentTarget ).attr("href");

    ev.preventDefault();

    $.get( url, function(r){
        $('#help-box').html(r);
    });
}

function closeHelp (ev) {
    ev.preventDefault();
    $(ev.delegateTarget).empty();
}

function openMergeThreadsView (ev) {
    var $this = $(ev.currentTarget)

    ev.preventDefault();

    $( $this.data("selector") )
        .removeClass( "hidden" )
        .slideDown('fast',function(){
            $(this).find('input:first').focus();
        });

    $this.slideUp('fast',function(){
        $this.parent().remove();
    });
}

function onButtonLoginThread (ev) {
    ev.preventDefault();

    $(document.documentElement).add(document.body).animate({
        'scrollTop': 0
    },500);

    $('input[name=username]').focus();
    $('#login-inner').stop().effect('highlight',{},5000);
}

function onConfirm(ev) {
    var $this = $(ev.currentTarget),
        message = $this.data("message") || "Are you sure you wish to do this?",
        url = $this.data("url");

    if (confirm( message )) {
        if (url) {
            location.href = url;
            ev.preventDefault();
        }
        //else allow default behaviour
    } else {
        ev.preventDefault();
    }
}

function onSelectChangeRedirect (ev) {
    location.href= constants.stripRequestUri+'?changestyle=' + $(ev.currentTarget).val();
}

function onSelectChangeCalendar (ev) {
    setCalendar( $(ev.delegateTarget).data("idSuffix") );
}

function onClickButtonCallRemoveClosest (ev) {
    var $this = $(ev.currentTarget),
        closestSelector = $this.data("closestSelector"),
        call = $this.data("call");

    if (call) {
        $.get( $this.attr("href")+'&amp;ajax=1' );
    }

    ev.preventDefault();

    if (closestSelector) {
        $this.closest(closestSelector).remove();
    }
}

function onButtonStandardAjax(ev) {
    var $this = $(this),
        data = {
            type: $this.data("type"),
            name: $this.data("name")
        };

    if ($this.data("value")) {
        data.value = $this.data("value");
    }

    ev.preventDefault();

    $.get(
        constants.stripRequestUri,
        data,
        function(res){
            $( $this.data("selector") ).html(res);
        }
    );
}

function onButtonNotificationDeleteAll (ev) {
    var $this = $(ev.currentTarget);
    ev.preventDefault();

    if (confirm('Are you sure you want to delete all your notifications?')) {

        $.get($this.attr('href'));

        $('.feed-node-outer').slideUp(function(){
            $(this).remove();
        });
    }
}

function onBookmark (ev) {
    var ttl = document.title,
        url = location.href;

    ev.preventDefault();

    if (document.all) {
        window.external.AddFavorite( url, ttl);
    } else if (window.sidebar) {
        window.sidebar.addPanel(ttl, url, "");
    } else if (window.opera && window.print) {
        var elem = document.createElement('a');
        elem.setAttribute('href',url);
        elem.setAttribute('title', ttl);
        elem.setAttribute('rel','sidebar');
        elem.click(); // this.title=document.title;
    } else {
        alert('Press ' + (navigator.userAgent.toLowerCase().indexOf('mac') !== - 1 ? 'Command/Cmd' : 'CTRL') + ' + D to bookmark this page.');
    }
}

function onButtonForgotPassword (ev) {
    ev.preventDefault();

    $(".form-forgot-password")
        .slideToggle()
        .find('input:first')
        .focus();
}

//SOCKETS

function emitNotification () {
    var data = {'lastnote': constants.lastnote};
    initData.dataNotification = data;
}

function onNotification(data){
    var noteBox = $('#notification-box'), noteLink, noteDelete, loginBox = $('#login'), n;
	if (typeof data === 'object') {
		if (typeof data.notifications == 'object' && data.notifications.length > 0) {
            if (data.lastnote > constants.lastnote) {
                constants.lastnote = data.lastnote;
            }
			if (!noteBox.find('a').hasClass('header-link')) 
				noteBox.append( $('<a href="notifications" class="header-link"><strong>Notifications and history</strong></a>') ); 
			for (n in data.notifications) {
                if (noteBox.find('#note-popup-'+data.notifications[n].noteid).size()) {
                    noteBox.find('#note-popup-'+data.notifications[n].noteid).slideUp(function(){$(this).remove();});
                }
				noteLink = $('<a id="note-popup-'+data.notifications[n].noteid+'" href="'+data.notifications[n]['link']+'" />')
					.html(data.notifications[n].message).hide().html(data.notifications[n].message).hide().click(function(){
						$(this).slideUp(function(){
                            $(this).remove();
                        });
					})
				;
                
                noteDelete = $('<a href="'+data.notifications[n]['link']+'" class="absolute" style="width:auto !important; right:0; padding:0px 3px;">X</a>')
                    .click(function(ev){ 
                        var $this = $(this); 
                        if (parseInt(data.notifications[n].noteid,10)) {
                            $.get($this.attr('href')); 
                            $('#session-notifications').text(parseInt($('#session-notifications').text(),10)-1);
                        }
                        $this.next().slideUp(function(){$(this).remove();}); 
                        $this.remove(); 
                        ev.preventDefault();
                    })
                ;
                noteBox.append( noteDelete );
				
				noteBox.append( noteLink );
				noteLink.slideDown();
			}
			clearTimeout(nT);
			
			noteBox.stop();
			noteBox.css({'opacity':1,'display':'block'});
			nT = setTimeout(function(){
				noteBox.fadeOut(2000, function(){noteBox.find('a').remove();});
			},5000);
            
            if (typeof data.multiple === 'boolean' && data.multiple) {
                /*if (typeof data.type === 'string') {
                    if (['conversation','message'].indexOf(data.type) > -1) {
                        loginBox.find('#session-conversations').text(parseInt(loginBox.find('#session-conversations').text(),10)+1);
                    }
                }*/
                
                
                //increment if noteid exists... for use when notication is for a new post or similar
                if (parseInt(data.notifications[0].noteid,10) > 0) {
                    loginBox.find('#session-notifications').text(data.count);
                }
            }
		}
		
		if (typeof data.userData === 'object') {
			if (data.userData.individual && data.userData.type) {
                switch(data.userData.type) {
                /*case 'likedislike':
                    loginBox.find('#session-points').text(
                        parseInt(loginBox.find('#session-points').text(),10)+(data.userData.extra === 'like' ? 1:-1)
                    );
                    break;*/
                case 'extrapoints':
                    loginBox.find('#session-points').text(
                        parseInt(loginBox.find('#session-points').text(),10)+parseInt(data.userData.extra,10)
                    );
                    break;
                case 'message':
                case 'conversation':
                    loginBox.find('#session-conversations').text(data.userData.extra);
                }
            } else {
                loginBox.find('#session-notifications').text(data.userData.notifications);
                loginBox.find('#session-conversations').text(data.userData.pmunread);
                loginBox.find('#session-points').text(data.userData.points);
                loginBox.find('#session-limit').text(data.userData.limit_points);
                loginBox.find('#session-threads').text(data.userData.threads);
                loginBox.find('#session-posts').text(data.userData.posts);
            }
		}
	}
}

function emitActivity (init) {
	var data = {'allmethods': false, 'lastnote': parseInt(constants.lastactivity,10), 'forumid': parseInt(constants.forumId,10)};
    if (typeof init !== 'boolean') init = true;
	if (constants.allmethods) data.allmethods = true;
	//socket.emit('activity',data);
    
    if (init) {
        initData.dataActivity = data;
    } else {
        if (nodeJS.indexOf('activity') === -1) {
            nodeJS.push('activity');
        }
        socket.emit('join activity',data);
    }
}

function onActivity(data){
	if (typeof data == 'object') {
		if (typeof data.activities == 'object' && data.activities.length > 0) {
			var 
				activity, i, message, theLink, theMethod
				,firstLoad = false, firstLoadPara = activityObj.wrapper.find('p.remove-this')
			;
			if (!data.old) constants.lastactivity = data.lastnote;
			if (firstLoadPara.size() > 0) {
				firstLoad = true;
				//firstLoadPara.remove();
				if (Modernizr.touch && !Modernizr.overflowscrolling) {
					$.getScript('lib/iscroll.js',function () {
						activityObj.wrapper.data('scroller',new iScroll(activityObj.wrapper.attr('id'),{snap: true, hScroll: false}));
					});
				}
			}
			activityObj.wrapper.find('.remove-this').remove();
			
			for (i in data.activities){
				activity = data.activities[i];
				if (typeof activity != 'function' && activity['type'] && activity['type'].length > 0 && activity.userid != constants.sessUserId) {
					
					switch(activity.method) {
						case 'merge':theMethod = 'merged';break;
						case 'update':theMethod = 'edited';break;
						case 'delete':theMethod = 'deleted';break;
						case 'undelete':theMethod = 'undeleted';break;
						default:theMethod = 'created';
					}
					
					switch (activity['type']) {
						case 'newpost':
							message = '<strong>'+activity.username+'</strong> has ';
							message += theMethod+' a '+(activity.extra.length > 0 ? activity.extra : '');
							message += ' in <strong>'+activity.title+'</strong>';
							break;
						case 'likedislike':
							message = '<strong>'+activity.username+'</strong> ';
							message += (activity.extra.length > 0 ? activity.extra : '');
							message += ' a post in <strong>'+activity.title+'</strong>';
							break;
						case 'extrapoints':
							message = '<strong>'+activity.username+'</strong> ';
							message += ' gave '+(activity.extra.length > 0 ? activity.extra : '');
							message += ' points for <strong>'+activity.title+'</strong>';
							break;
					}
					
					theLink = $('<a class="clearfix" />')
						.html('<img src="images/phpThumb.php?zc=1&w=22&h=22&src='+activity.thumbnail+'" alt="" /><span>'+message+'</span>')
						.attr('href',activity.link)
						.attr('id','activity-'+activity['id'])
						.data('mindateline',activity.mindateline)
					;
					if (activity.ip != '') theLink.attr('title',activity.ip);
					
					$('#activity-'+activity['id']).slideUp(function(){$(this).remove();});
					activityObj.box[ (data.old ? 'ap':'pre')+'pend' ]( theLink );
					if (!firstLoad) theLink.hide().slideDown()
				}
			}
			
			activityObj.box.append( $('<a href="#" class="remove-this">Show more</a>').bind('click',{'isClick':true},onActivityForce) );
						
			if (activityObj.pending) activityObj.pending = false;
			
			if (!firstLoad && Modernizr.touch && !Modernizr.overflowscrolling) {
				setTimeout(function(){activityObj.wrapper.data('scroller').refresh();},0);
			}
		}
	}
}

function onActivityForce(ev) {
	if (!activityObj.pending) {
		
		if (ev.type == 'touchstart' && !ev.data.isClick) {
			$(window).bind('touchend touchcancel',onActivityForce);
		}
		
		if (ev.type == 'touchend' || ev.type == 'touchcancel' || ev.type == 'scroll' || (ev.type == 'click' && ev.data.isClick)) {
			if (ev.type == 'touchend' || ev.type == 'touchcancel') {
				$(window).unbind('touchend touchcancel',onActivityForce);
			}
			var forceActivity = false;
			if (Modernizr.touch && Modernizr.csstransforms) {
				var amount = activityObj.box
					.css((activityObj.cssPrefix.length > 0 ? '-'+activityObj.cssPrefix+'-':'')+'transform')
					.replace(/matrix\(\s*\d+,\s*\d+,\s*\d+,\s*\d+,\s*-?\d+(px)?,\s*-?(\d+)(px)?\)/,'$2');
				amount = parseInt(amount,10);
				if (amount+10 >= activityObj.box.height()) forceActivity = true;
			} else {
				//native overflow or no transforms supported
				if (activityObj.wrapper.scrollTop()+activityObj.wrapper.height()+30 > activityObj.box.height()) {
					forceActivity = true;
				}
			}
			if (forceActivity) {
				activityObj.pending = true;
				var data = { 
					'allmethods' : false
					,'lastnote' : activityObj.box.find('a:not(.remove-this):last').data('mindateline')
					,'old' : true 
                    ,'forumid': parseInt(constants.forumId,10)
				};
				if (constants.allmethods) data.allmethods = true;
				socket.emit('activity',data);
			}
		}
	}
	if (ev.type == 'click' && ev.data.isClick) {
		ev.preventDefault();
	}
}

function onLikeDislike (data) {
    var $thisEl, $thisElPos, $ptEl, $thisLinkEl;
    
    if (typeof data !== 'object') return;
    if ([!data.message,!data.extra].indexOf(true) > -1) return;
    
    $thisEl = $('#teoti-points-'+data.id);

    $thisLinkEl = $thisEl.find( 'a.points-'+(data.extra && data.extra == 'like' ? 'inc' : 'dec') );
    $thisElPos = $thisLinkEl.position();

    $thisEl
        .find('.popup').html(data.message)
        .css({'top': $thisElPos.top, 'left': $thisElPos.left+$thisEl.width()+10})
        .show('fast')
    ;
}

function toggleBoxes (ev) {
	var $boxes = $('.navigation-mobile');
	
    if ($boxes.is(':visible')) {
        socket.emit('leave activity', {forumid: parseInt(constants.forumId,10)});
        socket.removeListener('activity',onActivity);
        if (nodeJS.indexOf('activity') > -1) {
            nodeJS.splice(nodeJS.indexOf('activity'),1);
        }
    } else {
        //emitActivity(false); //resume activity feed
        addNodeJsListener('activity', true);
    }
	
	$boxes.slideToggle(); 
	ev.preventDefault();
}

//END SOCKETS

//NODE JS FUNCTIONS
function initialiseNodeJs(){
    var i;
    if (!window.socket) {
        socket = io.connect('/');
        socket.on('connect',function(){
            initData = {'rooms': nodeJS, 'sessionid': constants.usess };

            for (i in nodeJS) {
                if (typeof nodeJS[i] !== 'function') {
                    addNodeJsListener(nodeJS[i]);
                }
            }
            
            if (firstConnect) {
                firstConnect = false;
            }
            
            socket.emit('init',initData);
        });
        
        socket.on('redirect',function (data) {
            if (typeof data !== 'object') data = {};
            if (typeof data.message === 'string') alert(data.message);
            location.href = data.url;
        });
        
        socket.on('errors',function (data) {
            if (typeof data.type === 'string' && ['thread','post'].indexOf(data.type) > -1) {
                $('#submit-post input[type=submit], #submit-form input[type=submit]').removeAttr('disabled');
            }
            alert(data.errors.join("\n\n"));
        });
        
        socket.on('node delete',function(data){
            if (typeof data !== 'object') data = {};
            if (typeof data.id === 'number' && data.id > 0) {
                $('#feed-node-'+data.id.toString()).closest('.feed-node-outer').slideUp(function(){
                    $(this).remove();
                }); 
            }
        });
        
        socket.on('likedislike',onLikeDislike);
        
        //show notifications if logged in
        if (constants.isLoggedIn && !($.browser.msie && $.browser.version < 7)) {
            socket.on('notification',onNotification);
        }
    } else if (socket.socket && !socket.socket.connected && !socket.socket.connecting ) {
        socket.socket.connect();
    }
    
    $(window).unbind('focus',initialiseNodeJs);
	$('body').unbind(Modernizr.touch ? 'touchstart':'mousemove',initialiseNodeJs);
}

function addNodeJsListener (nodeJsCall, addToArray) {
	if (typeof addToArray !== 'boolean') addToArray = false;
    if (addToArray && nodeJS.indexOf(nodeJsCall) === -1 || firstConnect) {
        addNodeJsReceiver(nodeJsCall);
    }
    addNodeJsEmitter(nodeJsCall,!addToArray);
    if (addToArray && nodeJS.indexOf(nodeJsCall) === -1) nodeJS.push(nodeJsCall);
}

function addNodeJsEmitter (nodeJsCall,init) {
    if (typeof init !== 'boolean') init = true;
	window['emit'+capitaliseFirstLetter(nodeJsCall)](init);
	//eval('emit'+capitaliseFirstLetter(nodeJsCall)+'();');
}

function addNodeJsReceiver (nodeJsCall) {
	socket.on(nodeJsCall, window['on'+capitaliseFirstLetter(nodeJsCall)]);
}

function disconnectNodeJS () {
    if (socket.socket.connected) {
        $(window).bind('focus',initialiseNodeJs);
        $('body').bind(Modernizr.touch ? 'touchstart':'mousemove',initialiseNodeJs);
        socket.disconnect();
    }
}

//END NODE JS FUNCTIONS

var timeFrom = 0, nT, tpt, stillAlive, skinSet, constants, socket, firstConnect = true, nodeJS = [], initData, activityObj = { 
	pending : false, box : null, wrapper : null
	,cssPrefix : /webkit/i.test(navigator.appVersion)?"webkit":/firefox/i.test(navigator.userAgent)?"moz":"opera"in window?"O":"" 
}; 

$(function() {
	constants = $('#usess').metadata();
	constants.lastactivity = 0;

    initialiseNodeJs();
	
	//update time display on nodes
	if ($('.feed-node-outer .time-ago:first').size() > 0) {
		setInterval(function(){$('.feed-node-outer').timeago();},10000); //update node time display every 1 seconds
	}
	
	//generate auto image resizer for loaded/cached images
	$('#content-col img').each(function(){
		var loaded = false;
		$(this).load(function(){ //resize unloaded images once loaded
			$(this).imageResize(); 
			loaded = true;
		});
		if (!loaded) $(this).imageResize(); //resize cached images (all other)
	});
    
    //add delete node behaviour
    $('.feed-node .delete, .feed-node .undelete, #thread-tools .delete, #thread-tools .undelete, #conversations .delete, #notification-feed .delete, #notification-feed .forget').deletenode();
	
	//enable sliding boxes
	$('.box a').boxGet();
	//hide anything with popup class on page click if visible
	$('body').click(function(){ 
		if ($('.popup').is(':visible')) $('.popup').hide(); 
		if ($('#search-list').is(':visible')) $('#search-list').hide(); 
	});
	//enable points buttons and displaying data on hover
	$('.teoti-points').points().pointhover();
	//turn whosonline links into mouseover tooltips and change title tag data to rel data
	$('#whosonline a.who')
		.each(function(){
			var thisEl = $(this);
			thisEl.attr('rel',thisEl.attr('title'));
			thisEl.attr('title','');
		})
		.whosonline()
	;
	
	//enable nudging of notifications on click
	if ($(window).width() > 650) {
		$('.notification-latest').noteNudge();
	}
	
	//notification pop up show/hide timeouts
	if (!($.browser.msie && $.browser.version < 7)) {
		$('#notification-box')
			.mouseenter(function(){
				var thisEl = $(this);
				clearTimeout(nT);
				thisEl.stop();
				thisEl.css({'opacity':1,'display':'block'});
			})
			.mouseleave(function(){
				var thisEl = $(this);
				nT = setTimeout(function(){
					thisEl.fadeOut(2000, function(){thisEl.find('a').remove();});
				},5000);
			})
		;		
	}
	
	//top navigation drop downs
	$('#header-menu .distinct')
		.mouseenter(function(){
			$(this).find('a:first').toggleClass('active').end().find('.menu-dropdown-button').show();
		})
		.mouseleave(function(){
			$(this).find('a:first').toggleClass('active').end().find('.menu-dropdown-button').hide();
		})
	;
    
	//display user drop down per user link
	$('.userlink').live('click',popupUserinfo);
    
	//load skinner script and activate skinner on first click
	$('#design-button').one('click.activate-skinner',activateSkinner);
    
	//add text editor of choice to text boxes
	$('.text-editor').textEditor();
    //add text editor toggle to buttons

	$('#search-filter').searchFilter();
	
	$('.boxes-showhide').click(toggleBoxes);
	
	//pause all feeds
	$(window).blur(disconnectNodeJS);
    
	//show notifications if logged in
	/*if (ISLOGGEDIN && !($.browser.msie && $.browser.version < 7)) {
		nodeJS.push( 'notification' );
	}*/
	
	if ($(window).width() > 650) {
		nodeJS.push( 'activity' );
	} 
	
	activityObj.wrapper = $('#activity');
	activityObj.box = activityObj.wrapper.find('#activity-box');
	
	activityObj.wrapper.bind(Modernizr.touch ? 'touchstart':'scroll',onActivityForce);
	//Modernizr.touch = true;
	if (!Modernizr.touch) {
		activityObj.wrapper.parent().bind('mousewheel',function(ev){ev.preventDefault();});
		activityObj.wrapper.slimScroll({height: '180px'});
	}

    if ($("#fb-root").size()) {
        $.getScript("http://connect.facebook.net/en_US/all.js", function () {
            FB.init({
                appId:'189974681025019',
                cookie:true,
                status:true,
                xfbml:false
            });
            $(".facebook-connect").on("click", onFacebookConnect );
        });
    }



    //user stuff
    $('#usersearch')
        .autocomplete('ajax/user.php',{
            minChars: 2,
            max: 10,
            selectFirst: false
        })
        .result(function(ev, data){
            location.href='manage-user?itemid='+data[1]
        })
    ;
    //end user stuff

    $(".bbcode-help,.smilie-help").on( "click", getHelp )
    $( "#help-box" ).on( "click", "a.help-close", closeHelp );

    $(".button-merge-threads").on( "click", openMergeThreadsView );

    $(".button-login-thread").on("click", onButtonLoginThread);

    $(".button-confirm").on("click", onConfirm);

    $(".select-redirect").on("change", onSelectChangeRedirect);

    $(".hasCalendar").on("change", "select", onSelectChangeCalendar);

    $(".button-remove-closest-call").on("click", onClickButtonCallRemoveClosest);

    $(".button-standard-ajax").on("click", onButtonStandardAjax);

    $(".button-notification-delete-all").on("click", onButtonNotificationDeleteAll);

    $(".button-bookmark").on("click", onBookmark);

    $(".button-forgot-password").on("click", onButtonForgotPassword);

    var isApple = !!navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i); 
    $(document.documentElement).on('keydown', 'textarea', function (ev) {
        var metaKey = isApple ? ev.metaKey : ev.ctrlKey;
        if (ev.keyCode === 13 && metaKey) {
            $(ev.target.form).submit();
        }
    });
});

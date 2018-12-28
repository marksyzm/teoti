var quote='', username='', wasQuoted = false, quoteButton, nT, livePosts = true;

function emitThread(){
	var lastEl = $('.feed-node-outer:last'), data = {}, meta = {}, datetime = 0;
        
	if (lastEl.size() > 0) {
		meta = lastEl.metadata();
		if (meta.datetime != undefined && meta.datetime > 0) datetime = meta.datetime;
	}
	
	data.datetime = parseInt(datetime,10);
	//disable just the thread feed if you're on the last page
	//data.liveposts = true;
	//if (constants.curpage != constants.curpagetotal) data.liveposts = false;
	
	data.update = [];
	$('.feed-node-outer').each(function(){
		var fnoMeta = $(this).metadata();
		if (typeof fnoMeta.updated !== 'undefined' && typeof fnoMeta.postid !== 'undefined') {
			data.update[data.update.length] = { 'id': fnoMeta.postid, 'time': fnoMeta.updated };
		}
	});
	
	if (typeof constants.threadid !== 'undefined') data.threadid = parseInt(constants.threadid,10);
	//don't have to send this anymore
	//if (typeof meta.postid != 'undefined') data.postid = parseInt(meta.postid,10);
	
	initData.dataThread = data;
}

function onThread(data){
	var fn = $('#post-nodes'), r, u, w, loginBox = $('#login'), submitForm = $('#submit-post');
	if (typeof data === 'object') {
		//post feed
		if (typeof data.posts === 'object' && data.posts.length > 0 && fn.size() > 0) {
            if (livePosts) {
                if (fn.find('.remove-this').size() > 0) {
                    fn.find('.remove-this').slideUp('fast',function(){ 
                        $(this).remove(); 
                    });
                }
                for (r in data.posts) {
                    //hide the existing node - works with old posts too as items with matching times are removed and re-displayed.
                    $('#feed-node-'+data.posts[r].postid).slideUp('fast',function(){$(this).remove(); }); 
                    fn.each(function(){
                        var newNode = buildPost( data.posts[r] )
                        $(this).append( newNode.hide() );
                    }); 
                    $('.feed-node-outer:last').slideDown('fast').find('img').imageResize();//show the new node
                }
                if (data.pages != undefined && constants.curpagetotal != data.pages) {
                    //rebuild page numbers, next/prev buttons
                    if (constants.curpagetotal == constants.curpage) {
                        constants.curpage = data.pages; //if already on last page, set current page to new page total
                    }
                    constants.curpagetotal = data.pages;
                    setPages();
                }
                if (typeof data.multiple == 'boolean' && data.multiple && data.posts[0].userid == constants.sessUserId) {
                    submitForm.find('input[type=submit]:first').val('Submit');
                    loginBox.find('#session-posts,#session-points').each(function(){
                        $(this).text(parseInt($(this).text(),10)+1);
                    });
                    submitForm.find('input[type=submit]').prop('disabled',false);

                    //clear the field
                    $('#textarea_pagetext').val('');
                    $('#textarea_pagetext').focus();
                }
            } else if (data.multiple && data.posts[0].userid == constants.sessUserId) {
                location.href=constants.stripRequestUri;
            }
		}
		
		//post updates
		if (typeof data.updates == 'object' && data.updates.length > 0) {
			for (u in data.updates) {
				var upd = data.updates[u], uEl = $('#feed-node-'+upd.postid), $nodeSnippet;
				if (upd.postid && upd.html && uEl.size() > 0) {
                    $nodeSnippet = uEl.find('.node-snippet');
                    switch (data.type) {
                        case 'likedislike':
                            $('#teoti-points-'+upd.postid+' .points-bit span').text(upd.points);
                            break;
                        case 'post':
                        default:
                            if ($nodeSnippet.html() != upd.html) {
                                $nodeSnippet
                                    .html(upd.html)
                                    .addClass('red-text')
                                    .removeClass('red-text',7000)
                                    .find('img')
                                    .imageResize()
                                ;
                            }
                    }
					
					uEl.metadata().updated = upd.updated;
				}
			}
            
            if (data.multiple) {
                if (data.editor == constants.sessUserId && submitForm.find('input[name=do]').val() == 'update') {
                    submitForm.find('input[name=do]').val('insert').end().find('input[type=submit]:first').val('Submit');
                    var postidInput = submitForm.find('input[name=postid]'), postId = postidInput.val();
                    //unset vals,go to relevant post whether in this page or a different page
                    //make sure it is set to insert mode
                    submitForm.find('input[type=submit]').prop('disabled',false);

                    //clear the field
                    $('#textarea_pagetext').val('');
                    $('#textarea_pagetext').focus();

                    submitForm.find('.remove-this').remove();


                    $('#comment-add-edit').text('Add Comment');
                    //if (typeof Cufon != 'undefined') Cufon.refresh();
                    postidInput.val(''); //unset postid

                    if ($('#feed-node-'+postId).size() > 0) {
                        //scroll up to this post
                        $('html,body').animate({'scrollTop': $('#feed-node-'+postId).position().top},500);
                    }
                }
            }
		}
		
		//watchers
		if (typeof data.watchers == 'object' && data.watchers.length > 0) {
			var watchLink, watchBox = $('#whos-watching-watchers'), watcher;
			watchBox.find('a.watcher').remove();
			for (w in data.watchers) {
				watcher = data.watchers[w];
				if (typeof watcher !== 'function') {
					if (!watcher.avatar) watcher.avatar = '';
					watchLink = $('<a class="watcher" />')
						.attr('href','members/'+watcher.usernameurl+'.html')
						.attr('rel',watcher.username)
						.append( 
							$('<img alt="" />')
								.attr(
									'src' 
									,'images/phpThumb.php?src='
									+(escape(watcher.avatar && watcher.avatar.length ? 'avatar/'+watcher.avatar : 'error.png'))
									+'&zc=1&w=22&h=22&f=png'
								)
						)
						.whosonline('#whos-watching')
					;
					watchBox.append( watchLink );
				}
			}
		}
	}
}

function buildPost(obj) {
	var	node = $('<div id="feed-node-'+obj.postid+'" />')
			,inc = 'points.php?postid='+obj.postid+'&give=1'
			,dec = 'points.php?postid='+obj.postid+'&give=-1'
	;
	node
		.addClass('feed-node-outer feed-node {\'datetime\':\''+obj.datetime+'\',\'postid\':\''+obj.postid+'\',\'updated\':\''+obj.updated+'\',\'username\':\''+obj.usernametxt+'\'}')
		.append(
			$('<div class="feed-node skin-this #content-col .feed-node" />')
				.each(function(){ if (window.skinSet && skinSet.active) $(this).chooser(); })
				.append('<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>')
				.append(
					$('<div class="body-left" />').append( $('<div class="body-right" />').append( $('<div class="body-inner" />').append(
						$('<div class="feed-node-inner" />')
							.append(
								$('<div class="teoti-points'+(obj.pointsactive ? '':' inactive')+'" id="teoti-points-'+obj.postid+'" />')
									.each(function(){ if (window.skinSet && skinSet.active) $(this).chooser(); })
									.append( $('<a href="'+inc+'" class="points-inc'+(obj.scored > 0 ? ' isset':'')+(obj.pointsactive ? '':' inactive')+'"><!-- --></a>') )
									.append( '<div class="points-bit"><span>'+obj.post_thanks_amount+'</span></div>' )
									.append( $('<a href="'+dec+'" class="points-dec'+(obj.scored < 0 ? ' isset':'')+(obj.pointsactive ? '':' inactive')+'"><!-- --></a>') )
									.append( $('<div class="popup hidethis"><!-- --></div>') )
									.each(function(){ 
                                        if (obj.pointsactive) $(this).points().pointhover(); 
                                        else $(this).click(function(ev){return false;}) 
                                    })
									.pointhover()
							)
							.each(function(){ 
                                if (obj.image) {
                                    $(this).append( 
                                        $(obj.image).each(function(){ 
                                            if (window.skinSet && skinSet.active) {
                                                $(this).chooser(); 
                                            }
                                        }) 
                                    ); 
                                }
                            })	
							.append(
								$('<div class="node-content'+(obj.image != '' ? ' node-content-hasimage':'')+'" />')
									.append( 
										$('<small class="lighter time-ago floatright skin-this {\'selector\':\'#whole .lighter\'}" />')
											.text(ago(obj.datetime)).each(function(){ if (window.skinSet && skinSet.active) $(this).chooser(); })
									)
									.append( 
										$('<h3 class="skin-this {\'selector\':\'#whole #main h3\'}" />').html( obj.username )
											.each(function(){ if (window.skinSet && skinSet.active) $(this).chooser(); })
									)
									.append( 
                                        $('<div class="node-snippet clearfix" />')
                                            .bind('textselect',function(e){
                                                quote = e.text;
                                                username = $(this).closest('.feed-node-outer').metadata().username;
                                                quoteButton.css({'left':e.pageX+10,'top':e.pageY+10}).show('fast');		
                                            }) 
                                            .append( obj.html ) 
                                    )
									.append( 
										$('<div />')
											.append(
												$('<div class="teoti-button alignright skin-this {\'selector\':\'#whole .teoti-button\'}" />')
													.each(function(){ if (window.skinSet && skinSet.active) $(this).chooser(); })
													.append('<!-- edit/delete buttons -->')
													.each(function(){
														var thisEl = $(this);
														if (constants.isLoggedIn) {
															thisEl.append( $('<a href="conversation?do=new&amp;report='+obj.postid+'" title="Report this post ('+(obj.ipaddress.length > 0 ? obj.ipaddress : '')+')">!</a>') );
															thisEl.append(' ').append( $('<a href="#quote-button" class="quote-button {\'p\':\''+obj.postid+'\'}">Quote</a>') );
															if (constants.sessUserId == obj.userid || constants.staff) {
																thisEl.append(' ').append( $('<a href="submit?p='+obj.postid+'" class="edit">Edit</a>').editpost() );
                                                            }
															if (constants.sessUserId == obj.userid || constants.staff) {
																thisEl.append(' ').append( 
																	$('<a href="#delete" class="delete {\'p\':\''+obj.postid+'\',\'type\':\'post\',\'do\':\'delete\'}">Delete</a>').deletenode()
																);
                                                            }
														}
													})
													.css({'visibility':'hidden'})
											)
											.append('<div class="clearboth"><!-- --></div>')
									)
							)
							.append( '<div class="clearboth"><!-- --></div>' )
					)))
				)
				.append('<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>')
		)
	;
	
	return node;
}


function quoteFullText(ev){
	var $this = $(this), meta = $this.metadata();
	$('html,body').animate({'scrollTop':$('#submit-post').position().top },500);
	$.getJSON('ajax/getpostfulltext.php',{'p': meta['p'] },function(postfulltext){
		if (!postfulltext.error) $('#textarea_pagetext').val($('#textarea_pagetext').val()+'[quote='+postfulltext.username+']'+postfulltext.quote+'[/quote] \n ');
	});
	ev.preventDefault();
}

function previewPost(ev){
	var $this = $(this), meta = $this.metadata();
	if ($('#preview-box').size() == 0) {
		$.getJSON('ajax/getpostfulltext.php',{'p': meta['p'] },function(postfulltext){
			if (!postfulltext.error) {
				$('#whole').append( 
					$('<div id="preview-box" />')
						.css({
							'padding':'10px','border':'1px solid #010767','width':'400px','height':'300px','overflow':'auto','position':'absolute','z-index':25
							,'top':(($(window).height()/2)-150+$(window).scrollTop()).toString()+'px','left':(($(window).width()/2)-200).toString()+'px','background-color':'white'
						})
						.append( $('<h2 />').text( postfulltext.username+' wrote:') )
						.append( postfulltext.quote )
						.append( '<p><small>Click to remove</small></p>' )
						.click(function(){$(this).remove();})
				);
			}
		});
	}
	ev.preventDefault();
}

function extraPoints(ev){
	var $this = $(this), meta = $this.metadata();
	meta.ajax = 1;
	$.post(
        $this.attr('href'), meta
        ,function(r){
        
            if (r.error) {
                alert(r.error)
            } else {
                $('#teoti-points-'+r.postid+' .points-bit span').text( parseInt($('#teoti-points-'+r.postid+' .points-bit span').text())+parseInt(r.amount) );
                $('#session-limit').text( r.limit );

                if (r.update) {
                    $('#extra-points-'+r.userid).text(r.totalamount);
                } else {
                    var extraPointsBox = $('#extra-points-box');
                    if (!extraPointsBox.size()) {
                        extraPointsBox = $('<div id="extra-points-box" />');
                        $('#extra-points-box-outer').append( extraPointsBox );
                    }

                    extraPointsBox
                        .each(function(){ if ($(this).find('a').size()) $(this).append(', '); })
                        .append( r.userlink )
                        .append( ' (').append( $('<span id="extra-points-'+r.userid+'" />').text( r.totalamount ) ).append(')')
                }

                $('.extra-points').each(function(){
                    if (parseInt($(this).text()) > r['max']) $(this).remove();
                });

                socket.emit(
                    'extrapoints'
                    ,{id: r.postid, activityid: r.activityid, forumid: r.forumid, extra: r.amount }
                );
            }
        }
        ,"json"
    );
	
	ev.preventDefault();
}

(function($){
	$.fn.editpost = function(){
		this.click(function(){
			var 
				thisEl = $(this), meta = thisEl.closest('.feed-node-outer').metadata()
				,submitForm = $('#submit-post')
				//,wysiwyg = $('#pagetext')[0].contentWindow
			;
			
			$.getJSON(constants.stripRequestUri,{'postid': meta.postid, 'type': 'getPost', 'do': 'ajax'},function(json){
				if (json.pagetext) {
					$('html,body').animate({'scrollTop':$('#submit-post').position().top },500);
					$('#comment-add-edit').text('Edit Comment');
					//if (typeof Cufon != 'undefined') Cufon.refresh();
					submitForm.find('.remove-this').remove();
					submitForm
						.find('input[name=postid]').val(meta.postid)
						.end().find('input[name=do]')
							.before(
								$('<input type="button" value="Cancel" class="remove-this" />')
									.click(function(){
										var postidInput = submitForm.find('input[name=postid]'), postId = postidInput.val();
										$('html,body').animate({'scrollTop':$('#feed-node-'+postId).position().top},500);
										$('#textarea_pagetext').val('');
										
										//unset vals,go to relevant post whether in this page or a different page
										//make sure it is set to insert mode
										submitForm.find('input[name=do]').val('insert').end().find('input[type=submit]:first').val('Submit'); 
										$('#comment-add-edit').text('Add Comment');
										if (typeof Cufon != 'undefined') Cufon.refresh();
										postidInput.val(''); //unset postid
										$(this).remove();
										
									})
							)
							.val('update')
						.end().find('input[type=submit]:first').val('Update')
						.end().find('.markItUpContainer').effect('highlight',{},5000)
					;
					$('#textarea_pagetext').val(json.pagetext);
					$('#textarea_pagetext').focus();
				} else {
					if (json.error) alert(json.error);
				}
			});
			
			return false;
		});
		return this;
	};
	
	$.fn.submitpost = function(){
		this.submit(function(){
			var $submitForm = $('#submit-post'), i;
			
			if (constants.wysiwyg) {
				for (i in CKEDITOR.instances) {
                    if (typeof (CKEDITOR.instances[i] !== 'function')) {
                        CKEDITOR.instances[i].updateElement();
                    }
                }
			}
			
			var thisEl = $(this), data = thisEl.serializeObject();
            
			data.ajax = 1;
            
            if ($submitForm.find('input[name=do]',thisEl[0]).val() !== 'update') {
                $submitForm.find('input[type=submit]').prop('disabled', true).val('Submitting...');
            } else {
                $submitForm.find('input[type=submit]').val('Updating...');
            }
            
            data.sessionid = constants.usess;
            
            if (socket.socket && !socket.socket.connected && !socket.socket.connecting && socket.socket.connect) {
                socket.socket.connect();
            }
            
            socket.emit('submit',data);
            
			return false;
		});
		return this;
	};

	/* jQuery plugin textselect
	 * version: 0.9
	 * tested on jQuery 1.3.2
	 * author: Josef Moravec, josef.moravec@gmail.com
	 * 
	 * usage:
	 * $(function() {
	 *    $(document).bind('textselect', function(e) {
	 *      Do stuff with e.text
	 *    });    
	 *   });
	 *    
	 */

	$.event.special.textselect = {
	  setup: function(data, namespaces) {
	    $(this).data("textselected",false);
	    $(this).bind('mouseup', $.event.special.textselect.handler);
	  },
	  teardown: function(data) {
	    $(this).unbind('mouseup', $.event.special.textselect.handler);
	  },
	  handler: function(event) { 
	    var text = $.event.special.textselect.getSelectedText().toString(); 
	    if(text!='') {
            $(this).data("textselected",true);
            event.type = "textselect";
            event.text = text;
            $.event.handle.apply(this, arguments);
	    }
	  },
	  getSelectedText: function() {
	    var txt = '';
        if (window.getSelection) {
            txt = window.getSelection();
        } else if (document.getSelection) {
            txt = document.getSelection();
        } else if (document.selection) {
            txt = document.selection.createRange().text;
        }
	    return txt;
	  }
	};
})(jQuery);

$(function(){
	$.extend(true, constants, $('#constants').metadata());
	if (constants.curpage != constants.curpagetotal) livePosts = false;
	//ajaxify the submit post forms
	$('#submit-post').submitpost(); 
	//edit individual posts
	$('.feed-node .edit').editpost();
    
	//reset previous comment settings - fixes caching issues
	$('#submit-post input[name=do]').val('insert');
	$('#submit-post input[name=postid], #textarea_pagetext').val('');
	
	//display quote button on text select
	if (constants.isLoggedIn) {
		$('body').click(function(){ 
			if ($('#quote-button').is(':visible') && $('#quote-button').is(':not(:animated)')) 
				$('#quote-button').each(function(){
					if (wasQuoted) {
						wasQuoted = false;
						$(this).delay(500).hide('slow',function(){$(this).find('a').text('Quote')});
					} else $(this).hide('slow');
				}); 
		});
		
		quoteButton = $('#quote-button')
			.click(function(e){
				$(this).find('a').text('Quoted.');
				var bbcode = '[quote='+username+']'+quote+'[/quote]';
				wasQuoted = true;
				var txt = $('#textarea_pagetext').val()+bbcode+' \n ';
				$('#textarea_pagetext').val(txt);
				e.preventDefault();
			})
		;
		$('.node-snippet').bind('textselect',function(e){
			quote = e.text;
			username = $(this).closest('.feed-node-outer').metadata().username;
			quoteButton.css({'left':e.pageX+10,'top':e.pageY+10}).show('fast');		
		});
	}
	
	//display tool bar for nodes on hover, hide on mouse out
	$('.feed-node-outer')
		.live('mouseenter',function(){
			$(this).find('.teoti-button:not(.related-link)').css({'visibility':'visible'});
		})
		.live('mouseleave',function(){
			$(this).find('.teoti-button:not(.related-link)').css({'visibility':'hidden'});
		})
		.find('.teoti-button:not(.related-link)').css({'visibility':'hidden'})
	;
	
	$('#whos-watching-watchers a.watcher')
		.each(function(){
			var thisEl = $(this);
			thisEl.attr('rel',thisEl.attr('title'));
			thisEl.attr('title','');
		})
		.whosonline('#whos-watching')
	;
	
	$('.preview-post').live('click',previewPost)
	
	$('.quote-button').live('click',quoteFullText);
	
	$('.extra-points').live('click',extraPoints);
	
	//auto scroll to post
	if (constants.posthighlight != undefined && constants.posthighlight > 0) {
		var postHighlight = $('#feed-node-'+constants.posthighlight);
		if (postHighlight.size() > 0) {
			postHighlight.find('.node-snippet').addClass('red-text').removeClass('red-text',7000);
			$('html,body').animate({'scrollTop':postHighlight.position().top },500);
		}
	}
	
	if (constants.threadid > 0) {
		nodeJS.push( 'thread' );
	}
});
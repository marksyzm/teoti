function emitConversation(init){
	var firstEl = $('.feed-node-outer:first'), data =  {}, meta = {}, datetime = 0, dataType = 'conversation';
	if (typeof init !== 'boolean') init = true;
	if (firstEl.size() > 0) {
		meta = firstEl.metadata();
		datetime = meta.datetime;
	}
	
	data.datetime = parseInt(datetime,10);
	data.pm = parseInt(constants.pm,10);
	
    if (init) {
    	initData.dataConversation = data;
    } else {
        if (nodeJS.indexOf(dataType) === -1) {
            nodeJS.push(dataType);
        }
        socket.emit('join '+dataType,data);
    }
}

function onConversation(data){
	var r, fn = $('#post-nodes'), i = 0, participant, $thisEl = $('#submit-post');
	if (typeof data == 'object') {
		if (typeof data.pmnodes == 'object' && data.pmnodes.length && fn.size()) {
			if (fn.find('.remove-this').size() > 0) fn.find('.remove-this').slideUp(function(){ $(this).remove(); });
			for (r in data.pmnodes) {
				//hide the existing node - works with old pmnodes too as items with matching times are removed and re-displayed.
				$('#feed-node-'+data.pmnodes[r].postid).slideUp(function(){$(this).remove(); }); 
				fn.prepend( buildPost( data.pmnodes[r] ).hide()  ); 
				$('.feed-node-outer:first').slideDown();//show the new node
				i++;
			}
			if (data.hasOwnProperty('pages') && constants.curpagetotal != data.pages) {
				//rebuild page numbers, next/prev buttons
				if (constants.curpagetotal == constants.curpage) constants.curpage = data.pages; //if already on last page, set current page to new page total
				constants.curpagetotal = data.pages;
				setPages();
			} 
			
            //trim posts
			$('.feed-node-outer:gt('+(constants.nodelimit-1)+')').slideUp(function(){
                $(this).remove(); 
            }); 
            
            if (data.multiple) {
                //clear the field
				if (data.pmnodes[0].userid == constants.sessUserId) {
					$('#textarea_message').val('');
					$('#textarea_message').focus();
				}
				//check if it's a new conversation
				if (data.type == 'conversation') {
					$('#conversation-type').val('message'); //set this form to send messages now
					constants.pm = data.pm;
					$thisEl.find('input[name=pm]').val(data.pm); //get the insert id of the conversation
					$thisEl.find('input[name=title]').replaceWith($('<span />').text($thisEl.find('input[name=title]').val()));
				}
            }
		}
        
        if (typeof data.participants === 'object' && data.participants.length && fn.size()) {
            for (i in data.participants) {
                participant = data.participants[i];
                if (typeof participant !== 'function') {
                    $('#participants')
                        .append( 
                            $('<a href="#remove-participant" class="remove-me {\'userid\':\''+participant.userid+'\'}">'+participant.username+'</a>')
                                .deleteparticipant(participant.userid)
                                .fadeIn() 
                        )
                        .find('.hidden-inputs').append(' ').append( $('<input type="hidden" name="participants[]" value="'+participant.userid+'" />') )
                    ;
                }
            }
        }
	}
}

function buildPost(obj) {
	var	node = $('<div id="feed-node-'+obj.pmnodeid+'" />');
	node
		.addClass('feed-node-outer feed-node {\'datetime\':\''+obj.datetime+'\',\'pmnodeid\':\''+obj.pmnodeid+'\'}')
		.append(
			$('<div class="feed-node skin-this {\'selector\':\'#content-col .feed-node\'}" />')
				.each(function(){ if (window.skinSet && skinSet.active) $(this).chooser(); })
				.append('<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>')
				.append(
					$('<div class="body-left" />').append( $('<div class="body-right" />').append( $('<div class="body-inner" />').append(
						$('<div class="feed-node-inner" />')
							.each(function(){ 
                                if (obj.image) {
                                    $(this).append( 
                                        $(obj.image).each(function(){ 
                                            if (window.skinSet && skinSet.active) $(this).chooser(); 
                                        }) 
                                    ); 
                                }
                            })	
							.append(
								$('<div class="node-content node-content-nopoints '+(obj.image ? ' node-content-hasimage':'')+'" />')
									.append( 
										$('<span class="lighter time-ago floatright skin-this {\'selector\':\'#whole .lighter\'}" />')
											.text(ago(obj.datetime)).each(function(){ 
                                                if (window.skinSet && skinSet.active) $(this).chooser(); 
                                            })
									)
									.append( 
										$('<h3 class="skin-this {\'selector\':\'#whole #main h3\'}"/>')
											.each(function(){ 
                                                if (window.skinSet && skinSet.active) $(this).chooser(); 
                                            }).html( obj.username )
									)
									.append( $('<div class="node-snippet" />').html( obj.message ) )
									.append( 
										$('<div class="teoti-button alignright skin-this {\'selector\':\'#whole .teoti-button\'}" />')
											.each(function(){ if (skinSet != undefined && skinSet.active) $(this).chooser(); })
											.append('<!-- edit/delete buttons -->')
											.each(function(){
												if (constants.sessUserId == obj.userid || constants.staff) {
													$(this).append(' ').append( 
														$('<a href="#delete-message" class="delete {\'do\':\'delete\',\'type\':\'message\',\'pmnodeid\':\''+obj.pmnodeid+'\'}">Delete</a>').deletenode() 
													);
                                                }
											})
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


$.fn.submitconversation = function(){
	this.submit(function(ev){
		var thisEl = $(this), data = thisEl.serializeObject();
        data.ajax = 1;
        
        if (nodeJS.indexOf('conversation') === -1) {
            nodeJS.push('conversation');
            socket.on('conversation',onConversation);
        }
        data.sessionid = constants.usess;
		//count participants, return false if none added
        if (socket.socket && !socket.socket.connected && !socket.socket.connecting && socket.socket.connect) {
            socket.socket.connect();
        }
        socket.emit('submit',data);
		ev.preventDefault();
	});
	return this;
};

$.fn.deleteparticipant = function(){
	return this.click(function(){
		var thisEl = $(this), meta = thisEl.metadata();
		if (confirm('Are you sure you want to remove this participant?')) {
			$.get(constants.stripRequestUri
				,{'do': 'delete', 'type': 'participant', 'ajax': '1', 'pm': constants.pm, 'participant': meta.userid}
				,function(resp){
					//if (resp.match(new RegExp('^error:'))) {
					//	alert('There were mistakes in your submission: \n\n'+resp.replace(new RegExp('^error:'),'').replace('|','\n'));
					//} else {
						thisEl.parent().find('input[value='+meta.userid+']:first').remove();
						thisEl.fadeOut(function(){thisEl.remove()});
					//}
				}
			);
		}
		return false;
	});
}

$(function(){
	if ($('#post-nodes').size()){
		$.extend(true, constants, $('#constants').metadata());
		
		$('#submit-post').submitconversation(); //ajaxify the submit post forms
		//reset previous comment settings
		$('#submit-post input[name=do]').val('insert');
		$('#submit-post input[name=postid], #message').val('');
		
		$('#invite-participant')
			.autocomplete('ajax/user.php',{minChars: 2, max: 10, selectFirst: false})
			.result(function(ev,dat,fmt){ 
                if ($('#conversation-type').val() !== 'conversation') {
                    if (socket.socket && !socket.socket.connected && !socket.socket.connecting && socket.socket.connect) {
                        socket.socket.connect();
                    }
                    socket.emit('submit',{
                        'pm': parseInt(constants.pm,10), 'participant': parseInt(dat[1],10), 'username': dat[0]
                        ,'type': 'message', 'addparticipant': true, 'do': 'insert', 'ajax': 1, 'sessionid': constants.usess
                    });
                } else if (constants.sessUserId != dat[1]) {
                    $('#participants')
                        .append( 
                            $('<a href="#remove-participant" class="remove-me {\'userid\':\''+dat[1]+'\'}">'+dat[0]+'</a>')
                                .deleteparticipant(dat[1]).fadeIn() 
                        )
                        .find('.hidden-inputs').append(' ').append( $('<input type="hidden" name="participants[]" value="'+dat[1]+'" />') )
                    ;
                }
				$(this).focus().val('');
			})
		;
        
		$('#participants a.remove-me').deleteparticipant();
		
		//start live feed
		if ($('#conversation-type').val() !== 'conversation' && constants.curpage == constants.curpagetotal) {
			nodeJS.push( 'conversation' );
		}
	}
});

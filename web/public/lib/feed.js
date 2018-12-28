$.fn.toggleFeed = function() {
	this.each(function(){
		var thisEl = $(this)
		thisEl.find('.teoti-button a:not(.skip)').click(function(){
			var el = $(this), meta = el.metadata();
			$('.popup').hide();
			var elOff = el.position();
			thisEl
				.find('.filter-'+($.inArray(el.text(),['Updated','Latest']) == -1 ? ($.inArray(el.text(),['NSFW','SFW','Both']) == -1 ? 'layout':'rating'):'threads'))
				.css({'top':elOff.top+el.outerHeight(),'left':elOff.left})
				.show('fast')
			; 
			//el.text(meta['name']);
			return false;
		});
	});	
};

function emitHome(){
	var data = {}, fn = $('#feed-nodes'), meta = {}, firstEl;
	firstEl = fn.find('.feed-node-outer:first');
	
	if (firstEl.size() > 0) meta = firstEl.metadata();
	
	//data to send to node.js
	data.dateline = firstEl.size() > 0 ? parseInt(meta.datetime,10) : 0; 
	/*data['filter-rating'] = constants['filter-rating'];
	data['filter-threads'] = constants['filter-threads']; */
	data.forumid = parseInt(constants.forumId,10); //must be a 'number'
	
	initData.dataHome = data;
}

function onHome (data) {
	var fn = $('#feed-nodes'), nodelimit, thisNode, i;
	
	if (typeof data.nodes == 'object' && data.nodes.length > 0 && fn.size() > 0) {
		nodelimit = $('.feed-node-outer').size();
		for (i in data.nodes) {
			//hide the existing node(s) first
			$('#feed-node-'+data.nodes[i].threadid).slideUp('fast',function(){$(this).remove(); }); 
			thisNode = buildNode(data.nodes[i]).hide();
			fn.prepend( thisNode ); 
			thisNode.slideDown('fast');//show the new node
		}
		$('.feed-node-outer:gt('+nodelimit+')').slideUp(function(){ $(this).remove(); }); //trim nodes
	}
}

function buildNode(obj) {
	var	node = $('<div id="feed-node-'+obj.threadid+'" />')
			,inc = 'points.php?postid='+obj.postid+'&give=1'
			,dec = 'points.php?postid='+obj.postid+'&give=-1'
	;
	node
		.addClass('feed-node-outer feed-node {\'datetime\':\''+obj.datetime+'\',\'threadid\':\''+obj.threadid+'\'}')
		.append(
			$('<div class="feed-node style-this" />')
				.each(function(){ if (skinSet != undefined && skinSet.active) $(this).chooser(); })
				.append('<div class="top-left"><div class="top-right"><div class="top-inner"><!-- --></div></div></div>')
				.append(
					$('<div class="body-left" />').append( $('<div class="body-right" />').append( $('<div class="body-inner" />').append(
						$('<div class="feed-node-inner clearfix" />')
							.append(
								$('<div class="teoti-points skin-this {\'selector\':\'#whole .teoti-points\'}'+(obj.pointsactive ? '':' inactive')+'" id="teoti-points-'+obj.postid+'" />')
									.each(function(){ if (skinSet != undefined && skinSet.active) $(this).chooser(); })
									.append( $('<a href="'+inc+'" class="points-inc'+(obj.scored > 0 ? ' isset':'')+(obj.pointsactive ? '':' inactive')+'"><!-- --></a>') )
									.append( '<div class="points-bit"><span>'+obj.post_thanks_amount+'</span></div>' )
									.append( $('<a href="'+dec+'" class="points-dec'+(obj.scored < 0 ? ' isset':'')+(obj.pointsactive ? '':' inactive')+'"><!-- --></a>') )
									.append( $('<div class="popup hidethis"><!-- --></div>') )
									.each(function(){ if (obj.pointsactive) $(this).points().pointhover(); else $(this).click(function(ev){return false;}) })
									.pointhover()
							)
							.each(function(){ if (obj.image != '') $(this).append( $(obj.image).each(function(){ if (skinSet != undefined && skinSet.active) $(this).chooser(); }) ); })	
							.append(
								$('<div class="node-content'+(obj.image != '' ? ' node-content-hasimage':'')+'" />')
									.append( 
										$('<small class="node-time time-ago lighter skin-this {\'selector\':\'#whole .lighter\'}">'+ago(obj.datetime)+'</small>')
											.each(function(){ if (skinSet != undefined && skinSet.active) $(this).chooser(); }) 
										)
									.append( '<div class="node-icon"><a href="'+obj.threadlink+'" class="icon-'+obj.forumlink+'"><img src="images/blank.gif" alt="'+obj.forumtitle+'" /></a></div>' )
									.append( 
										$('<h3 class="node-title skin-this {\'selector\':\'#whole #main h3\'}" />')
											.each(function(){ if (skinSet != undefined && skinSet.active) $(this).chooser(); }) 
											.append( $('<a href="'+obj.threadlink+'" />').text(obj.title) ) 
										)
									.append( 
										$('<div class="node-snippet light skin-this {\'selector\':\'#whole .light\'}" />')
											.html( obj.pagetext ).each(function(){ if (skinSet != undefined && skinSet.active) $(this).chooser(); })
									)
							)
					)))
				)
				.append('<div class="bottom-left"><div class="bottom-right"><div class="bottom-inner"><!-- --></div></div></div>')
		)
	;
	
	return node;
}

function emitShout (init) {
    var 
        data = { 'forumid': parseInt(constants.forumId,10), 'datetime': constants.lastshout }
        ,dataType = 'shout'
    ;
        
    if (typeof init !== 'boolean') init = true;
    if (init) {
    	initData.dataShout = data;
    } else {
        if (nodeJS.indexOf(dataType) === -1) {
            nodeJS.push(dataType);
        }
        socket.emit('join '+dataType,data);
    }
}

function onShout (data) {
	var shoutContainer = $('#feed-shouts'), shoutPanel = $('#shoutbox-panel'), i, firstShouts, rvShouts, allShouts, bodyHtml;
	
	if (typeof data.shouts == 'object' && data.shouts.length > 0 && shoutContainer.size() > 0 && shoutPanel.is(':visible')) {
		constants.lastshout = data.lastshout;
		if (shoutContainer.find('p.remove-me').size() > 0) shoutContainer.find('p.remove-me').remove();
		
		firstShouts = $('.feed-shout');
        rvShouts = data.shouts.reverse();
		if (!(firstShouts.size() == 30 && rvShouts.length == 30)) {
			for (i in rvShouts) {
				if (typeof rvShouts[i] !== 'function' && $('#feed-shout-'+rvShouts[i].shoutid).size() == 0) {
					shoutContainer.append(buildShout(rvShouts[i]));
                }
            }
        }
			
		//remove old shouts
		allShouts = shoutContainer.find('.feed-shout');
		if (allShouts.size() > 30) {
            $('.feed-shout:eq('+(allShouts.size()-31).toString()+')').slideUp(function(){ $(this).remove() });
        }
        
		//scroll to bottom if near or there are no nodes
		if ( shoutContainer.scrollTop()+shoutContainer.height() > shoutContainer.prop('scrollHeight')-shoutContainer.height()
            || firstShouts.size() == 0 ) {
			shoutContainer.animate({ 'scrollTop': shoutContainer.prop('scrollHeight') - shoutContainer.height() }, 500);
            bodyHtml = $('body,html');
            bodyHtml.animate({ 'scrollTop': bodyHtml.prop('scrollHeight') },500);
            if (constants.sessUserId > 0) {
                $('#shout-input').focus();
            }
        }
	}
}

function toggleShout(ev) {
	var 
		$sbPanel = $('#shoutbox-panel'), $shoutContainer = $sbPanel.find('#feed-shouts')
		,allShouts = $sbPanel.find('.feed-shout'), bodyHtml
	;
    
	if ($sbPanel.is(':visible')) {
		$sbPanel.slideUp(); 
        if (nodeJS.indexOf('shout') > -1) {
            nodeJS.splice(nodeJS.indexOf('shout'),1);
        }
        socket.emit('leave shout',{ forumid: constants.forumId }); //stop individual shout feed
        socket.removeListener('shout',onShout);
	} else {
		$sbPanel.slideDown(function(){ $('#shout-input').focus(); });
		//emitShout(false); //resume individual shout feed
        addNodeJsListener('shout',true);
        
		if (allShouts.size() > 0) { 
            $shoutContainer.animate({ 'scrollTop': $shoutContainer.prop('scrollHeight') - $shoutContainer.height() }, 500);
            bodyHtml = $('body,html');
            bodyHtml.animate({ 'scrollTop': bodyHtml.prop('scrollHeight') },500);
            if (constants.sessUserId > 0) {
                $('#shout-input').focus();
            }
		}
	}
	ev.preventDefault();
	ev.stopPropagation();
}

function linkify(inputText) {
	var replacedText, replacePattern1, replacePattern2, replacePattern3;

	//URLs starting with http://, https://, or ftp://
	replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
	replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');

	//URLs starting with "www." (without // before it, or it'd re-link the ones done above).
	replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
	replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');

	//Change email addresses to mailto:: links.
	replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
	replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');

	return replacedText;
}

function buildShout(obj) {
	obj.text = obj.text ? obj.text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'):'' ;
	obj.text = linkify(obj.text);

	var shout = $('<div class="feed-shout clearfix {datetime:'+obj.time+'}" id="feed-shout-'+obj.shoutid+'" />');
	shout
		.append( 
			$('<div class="quarter" />')
				.append(obj.username).append('<br />') 
				.append(
					$('<small class="light skin-this {\'selector\':\'#whole .light\'}">'+ago(obj.time,true)+'</small>')
						.each(function(){ if (window.skinSet && skinSet.active) $(this).chooser(); })
				)
		)
		.append( $('<div class="threequarter" />').append(obj.text) )

	return shout;
}

function sendShout(){
	var el = $('#shout-input');
	if (el.val() != '') {
        if (socket.socket && !socket.socket.connected && !socket.socket.connecting && socket.socket.connect) {
            socket.socket.connect();
        }
		socket.emit('submit',{'do':'insert','type':'shout','shout':el.val(),'forumid':constants.forumId, 'sessionid': constants.usess });
	}
	el.focus().val('');
	return false;
}


$(function(){
	$.extend(true, constants, $('#constants').metadata());
	constants.lastshout = 0;
	
	$('#shout-input').keyup(function(ev){ if (ev.keyCode == 13) sendShout(); }).val('');
	$('#filter-box').toggleFeed();
	$('#shout-toggle').click(toggleShout);
	$('#shout-send').click(sendShout);

	if ($.trim($('#search-filter').val()) && $('#paginated').size() === 0 && constants.which.length === 0) {
		nodeJS.push( 'home' );
	}
});

$(function(){
	var isPreview = false;
    
	$('.showhide-category').filter(':not(.showthis)').hide();
    
	$('.choose-category')
		.css({'cursor':'pointer'})
		.click(function(){
			var thisEl = $(this), elID = this.id.replace('category-',''), catBox = $('#category-box-'+elID);
			if (!thisEl.hasClass('active')) {
				$('.auto-exists').remove();
				$('.choose-category').removeClass('active'); 
				thisEl.addClass('active');
				if (!catBox.is(':visible')) { 
					$('.showhide-category:not(:hidden)').slideUp();
					catBox.slideDown();
				}
			}
			return false;
		});
		
	$('.category-chosen')
		.click(function(){
			var catChosen = $('#category-chosen'), thisEl = $(this);
			if (catChosen.is(':hidden')) catChosen.slideDown();
			catChosen.find('span').text(thisEl.parent().text());
		})
	;

	$('#submit-form')
		.submit(function(ev){
			if (isPreview) return true;
			$('#submit-form input[type=submit]').attr('disabled', 'disabled');
			if ($('.switch-threadtype-auto').is(':checked')) {
				$('.auto-exists').remove();
				$(this).append('<input type="hidden" name="auto" value="1" class="auto-exists" />');
			}

            sendData();

			ev.preventDefault();
			return false;
		})
	;
	
	function sendData(){
		var pagetext = $('.pagetext'), data;
		pagetext.unbind('setData.ckeditor');
		if (constants.wysiwyg) {
			var editor = pagetext.ckeditorGet();
			editor.updateElement();
		}
        
        data = $('#submit-form').serializeObject();
        data.ajax = 1;
        data.sessionid = constants.usess;
        
        if (socket.socket && !socket.socket.connected && !socket.socket.connecting && socket.socket.connect) {
            socket.socket.connect();
        }
        
        socket.emit('submit',data);
	}
	
	function scrapeData(ev){
		if (ev && (ev.type == 'click' || (ev.type == 'keyup' && ev.keyCode == 13))) {
			$.getJSON('ajax/scraper.php',{ 'url':$('.input-related').val() },function(scraper){
                var i, thumb, autoThumbs = $('.auto-thumbnails'), thumbImg;
				if (scraper && scraper.title) {
					$('#submit-form input[name=title]').val(scraper.title);
					$('.pagetext').val(scraper.description);
					if (constants.wysiwyg) {
						var editor = $('.pagetext').ckeditorGet();
						editor.updateElement(); 
					}
					$('.switch-threadtype-autoshowhide').show();
					//populate the thumbnails
					
					autoThumbs.empty();
					if (scraper.imgs && scraper.imgs.length > 0) {
						$('.switch-threadtype-autohideshow').find('h3').show();
						for (i in scraper.imgs){
							thumb = scraper.imgs[i];
							if (typeof thumb != 'function'){
								thumbImg = $('<img alt="" class="thumbnail-chooser" />')
									.attr('src', 'images/phpThumb.php?w=80&h=60&zc=1&src='+encodeURIComponent(thumb) )
									.data('src',thumb)
									.click(function(){
										var $this = $(this);
										$('input[name=thumbnail]').val( $this.data('src') ).data('value',$this.data('src'));
										$this.parent().find('img').not(this).css({ 'opacity':0.5 });
										$this.css({ 'opacity': 1 });
									})
								;
								autoThumbs.append( thumbImg );
								//if (thumbImg.width() < 64 || thumbImg.height() < 48) thumbImg.remove();
							}
						}
					}
				} else {
					alert('Sorry, the URL you provided did not return any data.')
				}
			});
		
			ev.preventDefault();
			ev.stopPropagation();
		}
	}
	
    $('.retrieve-auto').bind('click',scrapeData);
	$('.input-related').bind('keyup',scrapeData);
	
	function switchThreadType(){
		var $this = $(this), inputThumb = $('input[name=thumbnail]');
		$('.submit-info').text($this.attr('rel'));
		if ($this.hasClass('switch-threadtype-auto')){
			
			//if (inputThumb.data('value') && inputThumb.data('value').length > 0) inputThumb.val(inputThumb.data('value'));
			if ($('input[name=title]').val('').length != 0) {
				$('.switch-threadtype-autoshowhide').hide();
			}
			$('.switch-threadtype-autohideshow').show();
			$('.input-related').focus();
		} else {
			//inputThumb.val('');
			$('.switch-threadtype-autoshowhide').show();
			$('.switch-threadtype-autohideshow').hide();
			
		}
	}
	
	$('.switch-threadtype').click(switchThreadType).filter(':checked').each(switchThreadType);
});
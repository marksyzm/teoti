$(function(){
	$('#content-col img').each(function(){
		var loaded = false;
		$(this).load(function(){ //resize unloaded images once loaded
			$(this).imageResize(); 
			loaded = true;
		});
		if (!loaded) $(this).imageResize(); //resize cached images (all other)
	});
});
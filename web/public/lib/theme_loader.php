<?

function getFontSelectorItems(){
	$fonts = array(
		'Arial'
		,'Comic Sans MS'
		,'Georgia'
		,'Helvetica'
		,'Lucida'
		,'Impact'
		,'Tahoma'
		,'Times New Roman'
		,'Trebuchet MS'
		,'Verdana'
	);
	echo '<option value="" selected>Font Family</option>';
	foreach($fonts as $font)
		echo '<option value="',$font,'" style="font-family:\'',$font,'\',sans-serif">',$font,'</option>';
}

function getFontSizeItems(){
	$sizes = array('X-Small'=>1,'Small'=>2,'Regular'=>3,'Large'=>4,'X-Large'=>5,'XX-Large'=>6,'Jumbo Text'=>7);
	
	echo '<option value="" selected>Font Size</option>';
	$i = 0;
	foreach($sizes as $name=>$size){
		echo '<option value="',$size,'" style="font-size:',($size*3+4+$i),'px">',$name,'</option>';
		$i += 2;
	}
}

function getColorPickerItems(){
	/* Might as well stick to the W3C/CSS Standards */
	$colors = array(
		'#00FFFF'=>'aqua'
		,'#000000'=>'black'
		,'#0000FF'=>'blue'
		,'#FF00FF'=>'fuchsia'
		,'#808080'=>'gray'
		,'#008000'=>'green'
		,'#00FF00'=>'lime'
		,'#800000'=>'maroon'
		,'#000080'=>'navy'
		,'#808000'=>'olive'
		,'#800080'=>'purple'
		,'#000000'=>'red'
		,'#000000'=>'silver'
		,'#000000'=>'teal'
		,'#000000'=>'white'
		,'#000000'=>'yellow'
	);
	
	echo '<option value="" selected>Font Color</option>';
	foreach($colors as $key=>$color)
		echo '<option value=\'',$key,'\''.($color == 'white' ? '' : ' style=\'color:'.$key.';\'').'>',ucfirst($color),'</option>';
}

if(isset($_POST['theme'])){
	//$basepath = "http://".$_SERVER['SERVER_NAME'];
	$path = pathinfo($_SERVER['PHP_SELF'],PATHINFO_DIRNAME);
	define('THEMEPATH',$path.'/themes/default/');
	
	echo '<div class="osimo-editor">';
	include 'themes/default/template.php';
	echo '</div>';
}



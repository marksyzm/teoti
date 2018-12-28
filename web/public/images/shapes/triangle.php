<? #phpinfo();
// Setup an anti-aliased image and a normal image
define('MAXSIZE',(isset($_GET['maxsize']) && is_numeric($_GET['maxsize']) ? $_GET['maxsize']: 36));
function triangle($first=false,$color=array(0,0,255),$upsidedown = false) {
	if ($upsidedown) {
		$values = array(
      2,  2,  // Point 3 (x, y)
      MAXSIZE-3,  2,  // Point 4 (x, y)
      MAXSIZE/2,  MAXSIZE-3, // Point 2 (x, y)
      2,  2  // Point 3 (x, y)
      );
  } else {
  	$values = array(
      MAXSIZE/2,  2,  // Point 1 (x, y)
      MAXSIZE-3,  MAXSIZE-3, // Point 2 (x, y)
      2,  MAXSIZE-3,  // Point 3 (x, y)
      MAXSIZE/2,  2  // Point 4 (x, y)
    );
  }

	// create image
	$image = imagecreatetruecolor(($first ? MAXSIZE*2 : MAXSIZE), ($first ? MAXSIZE*2 : MAXSIZE));
	
	// allocate colors
	$bg   = imagecolorallocate($image, 0, 0, 0);
	$blue = imagecolorallocate($image, $color[0], $color[1], $color[2]);
	imagecolortransparent($image, $bg);
	// fill the background
	imagefilledrectangle($image, 0, 0, MAXSIZE-1, MAXSIZE-1, $bg);
	// draw a polygon
	imagefilledpolygon($image, $values, (count($values)/2), $blue);
	
	return $image;
}

function hexsplit($hex) {
	//split into three parts by 2 characters each
	return array_map('hexdec',str_split($hex,2));
}

if (!isset($_GET['color1'])) $_GET['color1'] = '';
if (!isset($_GET['color2'])) $_GET['color2'] = '';

$color1 = hexsplit(preg_match('/[a-fA-F0-9]{6}/i',$_GET['color1']) ? $_GET['color1'] : '010767');
$color2 = hexsplit(preg_match('/[a-fA-F0-9]{6}/i',$_GET['color2']) ? $_GET['color2'] : '585E9C');

$image = triangle(true,$color1); //tl
$image2 = triangle(true,$color2); //bl
$image3 = triangle(true,$color1,true); //tr
$image4 = triangle(true,$color2,true); //br

//second is below
imagecopymerge($image, $image2, 0, MAXSIZE, 0, 0, MAXSIZE, MAXSIZE, 100);
//third is to the right
imagecopymerge($image, $image3, MAXSIZE, 0, 0, 0, MAXSIZE, MAXSIZE, 100);
//fourth is bottom right
imagecopymerge($image, $image4, MAXSIZE, MAXSIZE, 0, 0, MAXSIZE, MAXSIZE, 100);
// flush image
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
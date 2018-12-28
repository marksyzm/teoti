<?
//
function hex2rgb($color) {
    if ($color[0] == '#')
        $color = substr($color, 1);
    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;
    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
    return array($r, $g, $b);
}

include('../classes/GDMagic.php');

$_GET['h'] = str_replace('px','',$_GET['h']);
if (!$_GET['c1'] || !$_GET['c2']) {
	if ($_GET['flip']) {
		$_GET['c1'] = 'FFFFFF'; $_GET['c2'] = '000000';
	} else {
		$_GET['c1'] = '000000'; $_GET['c2'] = 'FFFFFF';
	}
}
header('Content-Type: image/png');
$img = imagecreatetruecolor(1,($_GET['h'] ? $_GET['h'] : 10));
$gdmagic = new GDMagic($img);
$gdmagic->gradient(hex2rgb($_GET['c1']),hex2rgb($_GET['c2']),'vertical');
imagepng($img);

//imagedestroy($img); 
?>
<?php
include '../includes/dbconnect.php';
include PATH.'/classes/simple_html_dom.php';

function utfshim($input){
	return html_entity_decode(preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $input));
}

$data = new stdClass();
$data->error = "";
$data->title = "";
$data->description = "";
$data->imgs = array();
if ($session->userid > 0) {
    if ($_GET['url']) {
        $url = trim($_GET['url']);
    } elseif ($_POST['url']) {
        $url = trim($_POST['url']);
    }
	
	if (!preg_match('/^https?:\/\//i',$url)) $url = 'http://'.$url;
	$parsedUrl = parse_url($url);
	if ($parsedUrl['host']){
		$html = new simple_html_dom(file_get_contents( $url ));
		
		
		
		$data->title = utfshim($html->find('head meta[property=og:title]')->content);
		if (!$data->title) $data->title = utfshim($html->find('head title',0)->innertext);
		
		$data->description = utfshim($html->find('meta[property=og:description]',0)->content);
		if (!$data->description) {
			$descs = $html->find('meta[name]');
			foreach($descs as $desc){
				if (strtolower($desc->name) == 'description'){
					$data->description = utfshim($desc->content);
					break;
				}
			}
		}
		
		$base = $parsedUrl['scheme'].'://'.$parsedUrl['host'];
		$abspath = $html->find('base',0)->href;
		if (!$abspath) {
			$abspath = $base.dirname($parsedUrl['path']).'/';
		}
		
		$getimages = $html->find('img');
		$i = 0;
		foreach ($getimages as $img) {
			if (preg_match('/\.(jpe?g|png)$/i',$img->src)){
				$imgUrl = parse_url($img->src);
				if ($imgUrl['host']) {
					$data->imgs[] = $img->src;
				} else {
					$data->imgs[] = ($img->src{0} != '/' ? $abspath : $base ).$img->src;
				}				
				if ($i++ == 9) break;
			}
		}
        if (!$data->title && !$data->description && !$data->images) {
            $data->error = "Nothing could be scraped from this URL!";
        }
	} else {
        $data->error = "The URL you gave could not be parsed!";
    }
} else {
    $data->error = "You are not logged in! Please log out and log in again.";
}
echo json_encode($data);
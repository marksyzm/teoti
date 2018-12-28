<?
switch (true) {
	case preg_match('/^\/([A-Za-z0-9-_]+)\/(\d+)-.+\.html/', $_SERVER['REQUEST_URI'], $matches):
		$_GET['t'] = $matches[2];
		include './thread.php';
		break;
	default:
		include './home.php';
}


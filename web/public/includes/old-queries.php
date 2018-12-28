<?

switch (true) {
	case strtolower($_GET['name']) == 'sticky':
		threadLoader($boxestop[0],$session);
		break;
	case strtolower($_GET['name']) == 'updated':
		threadLoader($boxestop[1],$session);
		break;
	case strtolower($_GET['name']) == 'scores':
		userLoader($_GET['page']);
		break;
	case strtolower($_GET['name']) == 'hot':
		threadLoader($boxesleft[0],$session);
		break;
	case strtolower($_GET['name']) == 'news':
		threadLoader($boxesleft[1],$session);
		break;
	case strtolower($_GET['name']) == 'random':
		threadLoader($boxesleft[2],$session);
		break;
	case strtolower($_GET['name']) == 'latest':
		threadLoader($boxright,$session);
		break;
}
exit;
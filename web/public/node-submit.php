<?php

if (!$_SERVER['argc']) {
	echo "You shouldn't be here!";
} else {
    header('Content-type: application/json');
	list($script,$postval) = $_SERVER['argv'];
	$_POST = (array)json_decode($postval);
    
    if (!$_POST['sessionid']) {
        $json = new stdClass();
        $json->errors = array('Invalid session. Please inform the administrator with a screenshot of this alert. '.json_encode($_POST));
        $json->type = $_POST['type'];
        echo json_encode($json);
        exit;
    }
    
	session_id($_POST['sessionid']);
    
	include 'includes/dbconnect.php';
    error_reporting(0);
    if ($session->userid) {
        switch($_POST['type']) {
            case 'post':
            case 'thread':
                if (in_array($_POST['do'], array('insert','update'))) {
                    include PATH.'/includes/submit-queries.php';
                } elseif (in_array($_POST['do'], array('delete','undelete'))) {
                    include PATH.'/includes/thread-queries.inc.php';
                }
                break;
            case 'shout':
                include PATH.'/includes/index-queries.php';
                break;
            case 'likedislike':
                include PATH.'/points.php';
                break;
            case 'conversation':
            case 'message':
                include PATH.'/includes/conversation-queries.inc.php';
                break;
            default:
                $json = new stdClass();
                $json->errors = array('Invalid submission, sir/madam.');
                $json->type = $_POST['type'];
                echo json_encode($json);
        }
    } else {
        $json = new stdClass();
        $json->errors = array('You are not logged in.');
        $json->type = $_POST['type'];
        echo json_encode($json);
    }
}
?>

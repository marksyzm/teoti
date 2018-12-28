<?php 
if (isset($_POST)  && isset($_POST['sessionid'])) {
    session_id($_POST['sessionid']);
}
require '../includes/dbconnect.php';
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
include PATH.'/includes/recaptchalib.php';
include PATH.'/includes/register-queries.php';

$json = new stdClass();
$json->session = new stdClass();
$json->session->userid = (int)$_SESSION['uid'];
//$json->session->sessionid = session_id();
$json->session->gcmid = "";
$json->errors = $merrors;
echo json_encode($json);
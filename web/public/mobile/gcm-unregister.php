<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
if (isset($_POST)  && isset($_POST['sessionid'])) {
    session_id($_POST['sessionid']);
}
include '../includes/dbconnect.php';
trigger_error("Unregistering user on GCM ".json_encode($_POST), E_USER_NOTICE);
 
if ($session->userid && $_POST && $_POST['regId']) {
    
    $time = time();
    $gcmuser = mysql_single('
        SELECT * FROM gcmuser 
        WHERE gcmid = \''.mysql_real_escape_string((string)$_POST['regId']).'\'
        AND userid = \''.mysql_real_escape_string((string)$session->userid).'\'
        ', __LINE__.__FILE__);
    
    if ($gcmuser->userid) {
        mysql_query('
            DELETE FROM gcmuser WHERE gcmid = \''.mysql_real_escape_string((string)$_POST['regId']).'\'
            ') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
    }
    
    //tidy up for JSON
    include_once '../classes/gcm.class.php';
    $gcm = new GCM();
    
    $registration_ids = array($gcmuser->gcmid);
    $message = array("register" => false);
 
    $result = $gcm->send_notification($registration_ids, $message);
 
    echo $result;
}

?>
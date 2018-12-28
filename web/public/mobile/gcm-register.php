<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_USER_NOTICE);
if (isset($_POST)  && isset($_POST['sessionid'])) {
    session_id($_POST['sessionid']);
}
include '../includes/dbconnect.php';
//trigger_error("Registering user on GCM".json_encode($_POST), E_USER_NOTICE);
if ($session->userid && $_POST && $_POST['regId']) {
    
    $time = time();
    //tidy up
    
    mysql_query(
        'DELETE FROM gcmuser WHERE dateline < '.mysql_real_escape_string($time-604800)
        ) or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
    
    $gcmuser = mysql_single('
        SELECT * FROM gcmuser 
        WHERE userid = \''.mysql_real_escape_string((int)$session->userid).'\'
        AND gcmid = \''.mysql_real_escape_string((string)$_POST['regId']).'\'
        ', __LINE__.__FILE__);
    
    //trigger_error("GCM USERID: ".$gcm->userid, E_USER_NOTICE);
    if ($gcmuser->userid) {
        //trigger_error("THIS DOES HAPPEN!", E_USER_NOTICE);
        mysql_query('
            UPDATE gcmuser SET 
            dateline = \''.$time.'\' 
            WHERE userid = \''.mysql_real_escape_string((int)$session->userid).'\'
            AND gcmid = \''.mysql_real_escape_string((string)$_POST['regId']).'\'
            ') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
        $gcmuser->dateline = time();
    } else {
        mysql_query('
            INSERT INTO gcmuser SET 
            gcmid = \''.mysql_real_escape_string((string)$_POST['regId']).'\',
            userid = \''.mysql_real_escape_string((int)$session->userid).'\',
            dateline = \''.$time.'\'
            ') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);
        
        $gcmuser = mysql_single('
            SELECT * FROM gcmuser 
            WHERE userid = \''.mysql_real_escape_string((int)$session->userid).'\'
            AND gcmid = \''.mysql_real_escape_string((string)$_POST['regId']).'\'
            ', __LINE__.__FILE__);
    }
    
    //tidy up for JSON
    $gcmuser->userid = (int)$gcmuser->userid;
    
    include_once '../classes/gcm.class.php';
    $gcm = new GCM();
    
    $registration_ids = array($gcm->gcmid);
    $message = array("register" => true);
 
    $result = $gcm->send_notification($registration_ids, $message);
 
    echo $result;
}
 
?>
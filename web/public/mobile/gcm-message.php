<?php
//if (isset($_GET["regId"])) {
    
 
    include_once '../includes/dbconnect.php';
    include_once '../classes/gcm.class.php';
    $regId = $_GET["regId"];
    $gcm = new GCM();
    
    //for testing
    if (!$regId) {
        $gcmuser = mysql_single('
            SELECT * FROM gcmuser WHERE userid = 2 AND dateline > '.(time()-604800*8).' ORDER BY dateline DESC LIMIT 1
            ', __LINE__.__FILE__);
        $regId = $gcmuser->gcmid;
        if (!$regId) {
            echo json_encode(array('error' => 'No GCM keys in database'));
            exit;
        }
    }
    
    if (!$regId) {
        echo json_encode(array('error' => 'Invalid GCM key sent'));
        exit;
    }
    
    
 
    $registration_ids = array($regId);
    $message = array(
        "url" => "personal-threads/120542-fall-colours-in-my-backyard.html",
        "noteid" => 123,
        "groupid" => 120437,
        "title" => 'Bart should not be in jail...',
        "message" => "mynameis created a new post in Bart should not be in jail"
     );
    //var_dump($registration_ids);var_dump($message);exit;
    //$fields = array('registration_ids' => $registration_ids,'data' => $message);
    //echo json_encode($fields);exit;
 
    $result = $gcm->send_notification($registration_ids, $message);
 
    echo $result;
//}
?>
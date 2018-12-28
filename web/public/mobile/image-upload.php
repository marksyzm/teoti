<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
if (isset($_POST)  && isset($_POST['sessionid'])) {
    session_id($_POST['sessionid']);
}
include '../includes/dbconnect.php';

function createName($amount) {
    $chars = array_merge(range('a','z'),range('A','Z'));
    $len = count($chars);
    $name = '';
    $complete = false;
    
    while (!$complete) {
        //get the amount left to determine the index in character array to be used
        $left = $amount % $len;
        
        //subtract the modulo amount from the original amount to get a neat dividable number for the next place value
        $amount = $amount - $left;
        
        // if the amount is less than the modulo comparison then the amount left will be 0
        if ($amount == 0) {
            //stop if there are no further left over numbers
            $complete = true;
        } else {
            //send the new number with the correct amount of place value 
            //(as this is treated as having a variable base and the base is being incremented)
            //the minus 1 is so you definitely start from the first character for each place value
            $amount = $amount / $len - 1;
        }
        
        //add the character to the name builder
        $name = $chars[$left].$name;
    }
    
    return $name;
}

$filetag = 'image';

$json = new stdClass();
$json->error = "You are not logged in!";
$json->uri = "";


//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_USER_NOTICE);
//trigger_error(print_r($_FILES, true), E_USER_NOTICE);
//trigger_error(print_r($_POST, true), E_USER_NOTICE);
//trigger_error($session->userid, E_USER_NOTICE);
//
//trigger_error($session->userid." ".session_id(), E_USER_NOTICE);
//trigger_error(print_r(!!$_FILES[ $filetag ]['name'],true), E_USER_NOTICE);
//trigger_error(print_r(!!$_POST,true), E_USER_NOTICE);

if ($session->userid > 0 && !!$_FILES[ $filetag ]['name']) {
    require_once PATH.'/images/phpthumb.class.php';
    
    //trigger_error("THIS HERE", E_USER_NOTICE);
    
    $json->error = "";
    //get extension
    $extension = pathinfo($_FILES[ $filetag ]['name'], PATHINFO_EXTENSION);
    
    $imageArr = send_image_to_file($filetag, $session->userid);
    

    if (is_string($imageArr)) {
        $json->error = $imageArr;
    } else {
        $time = time();

        mysql_query('
            INSERT INTO images SET
            '.$imageArr[0].',
            userid = \''.mysql_real_escape_string($session->userid).'\',
            created = \''.mysql_real_escape_string($time).'\',
            updated = \''.mysql_real_escape_string($time).'\',
            viewed = \''.mysql_real_escape_string($time).'\',
            extension = \''.mysql_real_escape_string($extension).'\'
            ') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);

        $insertid = mysql_insert_id();

        $name = createName($insertid-1); //as 0 = a, 1 = b etc.

        mysql_query('
            UPDATE images SET
            name = \''.mysql_real_escape_string($name).'\'
            WHERE id = \''.  mysql_real_escape_string($insertid).'\'
            ') or trigger_error(__LINE__.mysql_error(),E_USER_ERROR);

        if (strstr($_SERVER['HTTP_HOST'],OURDOMAIN)) {
            $path = PROTOCOL.'i.'.OURDOMAIN.'/'; //live
        } else {
            $path = PROTOCOL.$_SERVER['HTTP_HOST'].URLPATH.'/images/'.$filetag.'/'; //testing
        }
        
        $json->uri = $path.$name.'.'.$extension;
        //trigger_error($json->uri, E_USER_NOTICE);
    }
}

echo json_encode($json);

?>
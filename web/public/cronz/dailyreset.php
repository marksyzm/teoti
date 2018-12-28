<?php 

$session = new stdClass();
$session->staff = true;
$session->userid = 2;
$session->username = 'marksyzm';
$_POST = array('daily' => 1,'cron' => 1);

include '../sitelog.php';

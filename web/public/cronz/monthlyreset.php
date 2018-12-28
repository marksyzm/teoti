<?php 

$session = new stdClass();
$session->admin = true;
$session->userid = 5905;
$session->username = 'REALITY';
$_POST = array('monthly' => 1,'cron' => 1);

include '../sitelog.php';

<?php
require_once 'includes/dbconnect.php';
$phpsession->delete();
/*$_SESSION = array();// Unset all of the session variables.
if (session_id() != "" && isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
session_destroy(); */
header('Location:'.URLPATH.'/') and exit();

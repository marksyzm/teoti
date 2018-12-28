<?php
if (!$_GET['urn']) {
    // die(dirname('./'));
	include './home.php';
} else die($_GET['urn']);
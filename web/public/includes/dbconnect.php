<?php
// error_reporting(0);

// $_SERVER['PHP_SELF'] = str_replace('//','/',$_SERVER['PHP_SELF']);

// mb_internal_encoding("utf-8");
// $debug = true;
// if ($debug==true) 
$starttime = array_sum(explode(' ',microtime()));
//### defines ###
//if (!in_array($_SERVER['HTTP_HOST'],array('192.168.1.252','atlas','localhost:20000'))) $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
define('PATH',realpath(dirname(dirname(__FILE__))));
// $ignore = array(basename($_SERVER['PHP_SELF']),'/includes','/ajax','/lib','/mobile');
// define('URLPATH',str_replace($ignore,'',(in_array(dirname($_SERVER['PHP_SELF']),array('/','\\')) ? '': dirname($_SERVER['PHP_SELF']))));
//define('URLPATH',substr(str_replace($ignore,'',$_SERVER['PHP_SELF']),0,-1));
define('URLPATH', '');
// unset($ignore);

define ('HOST',$_ENV['MYSQL_HOST']);
define ('DBUSERNAME',$_ENV['MYSQL_USER']);
define ('DBPASSWORD',$_ENV['MYSQL_PASSWORD']);
define ('DBNAME',$_ENV['MYSQL_DATABASE']);
define ('OURCOMPANY','TEOTI');
define ('COMPANYTITLE','The End Of The Internet');
define ('VERSION','9.0.0');
define ('CLIENTNAME',OURCOMPANY);
define ('COMPANYNAME',OURCOMPANY);
define ('OURDOMAIN','teoti.com');
define ('CLIENTEMAIL','noreply@'.OURDOMAIN);
define ('TWITTER','teoticommunity');
define ('EMAILTEMPLATE','<html><head><title>%s</title></head><body>%s</body></html>');
define ('MAXIMGFILESIZE',31457280);
define ('MAXFILESIZE',MAXIMGFILESIZE);
define ('MAXIMGWIDTH',15000);
define ('MAXIMGHEIGHT',15000);
define ('FRONTBOXMAX',4);
define ('MAXLIMIT',99999);
define ('DATEFORMAT','d/m/Y');
define ('TIMEFORMAT','g:i a');
define ('DATETIMEFORMAT',TIMEFORMAT.' '.DATEFORMAT);
define ('MYSQLDATE','Y-m-d');
define ('MYSQLTIME','H:i:s');
define ('MYSQLDATETIME',MYSQLDATE.' '.MYSQLTIME);
define ('FEEDLIMIT',40); 
define ('POSTFEEDLIMIT',25); 

define ('BLOGFEEDLIMIT',15); 
define ('PMFEEDLIMIT',50); 
define ('MAXPARTICIPANTS',30);
define ('SHOUTLIMIT',30);
define ('COLHEADLIMIT',6);
define ('MAXOLD',10);
define ('MAXKEYWORDS',20);
define ('CLEARBOTH','<div class="clearboth"><!-- --></div>');
define ('THUMBW',64);
define ('THUMBH',48);
define ('GALLERYW',100);
define ('GALLERYH',100);
define ('DESCLENGTH',18);
define ('SHORTDESCLENGTH',12);
define ('DEFAULTKW','teoti, the end of the internet, interwebs, top links, hot topics, political, discussion');
define ('DEFAULTDESC','Teoti is a news feed going beyond what FB/Digg have to offer. Teoti is a top source for hot topics, political debate, internet memes, ranting into the void, videos, humour; welcome to The End Of The Internet.');
define ('ADMINGROUPS','6');
define ('MODGROUPS','5');
define ('GODGROUPS','10,12,7');
define ('REGULARGROUPS','2,11');
define ('BANNEDGROUPS','1,3,4,8');
define ('PROTOCOL','http'.($_SERVER['SERVER_PORT'] == 443 ? 's':'').'://');
define ('LIBRARYFILE','/clientfiles/%d/file/%s/%s'); //userid,filedir,filename
define ('MINTITLELENGTH',3);
define ('MINPOSTLENGTH',MINTITLELENGTH);
define ('MAXTITLELENGTH',80);
define ('MAXPOSTLENGTH',65530);
define ('MAXPOINTLISTSIZE',30);
define ('MAXNOTIFYUSERS',5);
define ('NEWTHREADPOINTS',5);
define ('NEWPOSTPOINTS',1);
define ('POSTINTERVAL',15);
define ('MAXSTYLETITLE',30);
define ('MAXIMAGENUM',300);
define ('AUDIOHEIGHT',24);
define ('VIDEOHEIGHT',370);
define ('FLASHHEIGHT',480);
define ('DEFAULTICONID',93);
define ('VIEWTIME',90);
define ('HISTORYLIMIT',20);
define ('FACEBOOKAPPID','189974681025019');
define ('FACEBOOKAPIKEY','a989ab85cee490fabb680058e21e9cc3');
define ('FACEBOOKAPPSECRET','732d1a2544fb7ee432f711b959309e8c');
//define ('GOOGLE_API_KEY_GCM','AIzaSyA6yEGHxdR91DcvFmsovYSIa2VX4YaDcRs');
define ('GOOGLE_API_KEY_GCM','AIzaSyCa-yB39aInHhnT4hQyqXJIYS5sSXm_duA');
define ('STRIP_REQUEST_URI',(strpos($_SERVER['REQUEST_URI'],'?') ? substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?')) : $_SERVER['REQUEST_URI']));
define ('STRIP_LINK',PROTOCOL.$_SERVER['HTTP_HOST'].STRIP_REQUEST_URI);
$keywords = array('the','of','and','a','to','in','is','you','that','it','he','was','for','on','are','as','with','his','they','i','at','be','this','have','from','or','one','had','by','word','but','not','what','all','were','we','when','your','can','said','there','use','an','each','which','she','do','how','their','if','will','up','other','about','out','many','then','them','these','so','some','her','would','make','like','him','into','time','has','look','two','more','go','see','no','way','could','my','than','first','been','call','who','its','now','long','down','did','get','come','made','may');
define ('COMMONWORDS',@implode(',',$keywords));
unset($keywords);
$BOXTYPES = array('hot','random','sticky','scores','profiles');

// $ISIE = strstr($_SERVER['HTTP_USER_AGENT'],$br='MSIE');
// $IEVERSION = $ISIE{5};

//### dbconnect ###
$mysqli = new mysqli(HOST, DBUSERNAME, DBPASSWORD, DBNAME);
// define('DBLINK', mysql_connect(HOST, DBUSERNAME, DBPASSWORD));
if ($mysqli->connect_errno) {
    echo "Sorry, this website is experiencing problems. <br>";
    echo "Error: Failed to make a MySQL connection, here is why: \n<br>";
    echo "Errno: " . $mysqli->connect_errno . "\n<br> Error: " . $mysqli->connect_error . "\n<br>";
    exit;
}

//### includes ###
require 'functions.php';
require 'session.php';

define('POINTSLIMIT',$session->god ? '25':'10');

if ($usesbbcode) {
	require 'classes/stringparser_bbcode.class.php';
	require 'includes/bbcode-functions.php';	
}

//### mysql defines ###

//	$f = mysql_single('SELECT GROUP_CONCAT(title SEPARATOR \'|\') as titles,  GROUP_CONCAT(forumid SEPARATOR \'|\') as ids FROM forum',__LINE__.__FILE__);
//	$fids = explode('|',$f->ids);
//	$ftitles = explode('|',$f->titles);
//	$forumtitles = array();
//	foreach($fids as $k => $v) $forumtitles[$v] = $ftitles[$k];
//	define ('FORUMDETAILS',serialize($forumtitles));
//	unset($fids,$ftitles,$forumdetails);
// mysql_query('SET CHARACTER SET utf8');
//mysql_query('SET NAMES utf8');
// mysql_query('SET NAMES utf8'); 

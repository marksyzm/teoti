<? ob_start ('ob_gzhandler'); header('Content-type: text/css; charset: UTF-8'); header('Cache-Control: max-age=604800, public'); header('Expires: ' .gmdate('D, d M Y H:i:s', time() + (60*60*24*6)) . ' GMT'); ?>@charset "utf-8";
<? include 'reset.css'; ?>
<? 
$isstylesheet = true;
$ISIE = strstr($_SERVER['HTTP_USER_AGENT'],$br='MSIE');
$IEVERSION = $ISIE{5};
//$ISFF = str_replace('Firefox/','',strrchr($_SERVER['HTTP_USER_AGENT'],'Firefox/'));
//$FFVERSION = $ISFF{0};
?>
<? include 'styles.css' ?>
<? include 'icons.css' ?>
<? 
/*
if ($ISFF) include 'ff-hacks.css';
if ($ISFF && $FFVERSION <= 2) include 'ff2-hacks.css';
if ($ISIE) include 'ie-hacks.css';
if ($ISIE && $IEVERSION <= 6) include 'ie-6-only-hacks.css';
if ($ISIE && $IEVERSION <= 7) include 'ie-7-hacks.css';  
if ($ISIE && $IEVERSION >= 7) include 'ie-7-only-hacks.css';  
*/
?>
<? include 'default.css' ?>

<? include 'autocomplete.css' ?>

<? include 'colorpicker.css' ?>

<? include 'style-generated.php' ?>

<? if ($_GET['submit']) include '../hoteditor/styles/office2007/style.css';

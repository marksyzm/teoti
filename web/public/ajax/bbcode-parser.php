<?
$usesbbcode = true;
include '../includes/dbconnect.php';
$bbcode = bbcode();
if (strlen((string)$_POST['data']) > MAXPOSTLENGTH) $_POST['data'] = substr((string)$_POST['data'],0,MAXPOSTLENGTH);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>markItUp! preview template</title>
<link rel="stylesheet" type="text/css" href="<?= URLPATH ?>/ajax/preview.css" />
</head>
<body>
	
<?= $bbcode->parse((string)$_POST['data']) ?>

</body>
</html>
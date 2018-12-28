<?

include '../includes/dbconnect.php';
include PATH.'/classes/class.skingen.php';

$skingen = new SkinGen();

if ($_GET['gather']) $skingen->gatherStyleElements = true;

if ($session->styleid) $skingen->skinid = $session->styleid;

//death($skingen->getJSON($_GET['selector']));
echo $skingen->getJSON($_GET['selector']);
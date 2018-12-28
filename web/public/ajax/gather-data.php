<?

include '../includes/dbconnect.php';
include PATH.'/classes/class.skingen.php';

$skingen = new SkinGen();

$skingen->gatherStyleElements = true;

if ($session->styleid) $skingen->skinid = $session->styleid;

$skingen->session = $session;

echo $skingen->getJSON();
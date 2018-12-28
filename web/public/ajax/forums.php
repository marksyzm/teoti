<?
require '../includes/dbconnect.php';

$forumids = okcats();

$result = mysql_query('
	SELECT forumid,title FROM forum
	WHERE title LIKE \''.mysql_real_escape_string($_GET['q']).'%\'
	AND forumid IN ('.implode(',',$forumids).')
	ORDER BY title
	LIMIT 25
	') or die(__LINE__.__FILE__.mysql_error());
while ($forum = mysql_fetch_object($result)) echo $forum->title,'|',$forum->forumid,'|',urlify($forum->title),"\n";
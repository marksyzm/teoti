<?php

require 'includes/dbconnect.php';

/**
 * Function to ping Google Sitemaps.
 * 
 * Function to ping Google Sitemaps. Returns an integer, e.g. 200 or 404,
 * 0 on error.
 *
 * @author     J de Silva                           <giddomains@gmail.com>
 * @copyright  Copyright &copy; 2005, J de Silva
 * @link       http://www.gidnetwork.com/b-54.html  PHP function to ping Google Sitemaps
 * @param      string   $url_xml  The sitemap url, e.g. http://www.example.com/google-sitemap-index.xml
 * @return     integer            Status code, e.g. 200|404|302 or 0 on error
 */
function pingSitemaps( $url_xml,$domain,$request_uri )
{
   $status = 0;
   //$google = 'www.google.com';
   if( $fp=@fsockopen($domain, 80) )
   {
      $req =  'GET '. $request_uri .
              urlencode( $url_xml ) . " HTTP/1.1\r\n" .
              "Host: $domain\r\n" .
              "User-Agent: Mozilla/5.0 (compatible; " .
              PHP_OS . ") PHP/" . PHP_VERSION . "\r\n" .
              "Connection: Close\r\n\r\n";
      fwrite( $fp, $req );
      while( !feof($fp) )
      {
         if( @preg_match('~^HTTP/\d\.\d (\d+)~i', fgets($fp, 128), $m) )
         {
            $status = intval( $m[1] );
            break;
         }
      }
      fclose( $fp );
   }
   return( $status );
}

$time = time(); //page initiation time
$basepath = PROTOCOL.$_SERVER['HTTP_HOST'].URLPATH;
$forums = implode(',',forumChildren(15,true));

if (!$_GET['row']) {
	mysql_query('
		TRUNCATE TABLE sitemap
		') or die(__LINE__.__FILE__.mysql_error());


	//generate each link and redirect after a certain length of time
	//front page
	mysql_query('
		INSERT INTO sitemap SET
		link = \''.mysql_real_escape_string($basepath.'/').'\'
		,dateline = \''.mysql_real_escape_string($time).'\'
		,priority = \'1\'
		,changefreq = \'always\'
		') or die(__LINE__.__FILE__.mysql_error());
	
	//forums + 20 pages each
	
	$result = mysql_query('
		SELECT * FROM forum WHERE forumid IN ('.$forums.')
		') or die(__LINE__.__FILE__.mysql_error());
	while ($f = mysql_fetch_object($result)) {
		for ($i=1; $i<=20; $i++) {
			mysql_query('
				INSERT INTO sitemap SET 
				link = \''.mysql_real_escape_string($basepath.'/'.urlify($f->title).'/'.($i == 1 ? '':'?page='.$i)).'\'
				,dateline = \''.mysql_real_escape_string($f->lastpost ? $f->lastpost : $time).'\'
				,priority = \'0.8\'
				,changefreq = \'hourly\'
				') or die(__LINE__.__FILE__.mysql_error());
		}
	}
	
	//user profiles
	$result = mysql_query('
		SELECT usernameurl FROM user
		') or die(__LINE__.__FILE__.mysql_error());
	while ($u = mysql_fetch_object($result)) {
		mysql_query('
			INSERT INTO sitemap SET
			link = \''.mysql_real_escape_string($basepath.'/members/'.$u->usernameurl.'.html').'\'
			,dateline = \''.mysql_real_escape_string($u->lastpost ? $u->lastpost : $time-(86400*365)).'\'
			,priority = \'0.6\'
			,changefreq = \'weekly\'
			') or die(__LINE__.__FILE__.mysql_error());
	}
}

//threads
$result = mysql_query('
	SELECT thread.title, thread.threadid, thread.lastpost, thread.forumid
	FROM thread
	WHERE visible = 1
	AND thread.forumid IN ('.$forums.')
	LIMIT '.mysql_real_escape_string((int)$_GET['row'] > 0 ? (int)$_GET['row'] : '0').',9999999
	') or die(__LINE__.__FILE__.mysql_error());
$i = (int)$_GET['row'] > 0 ? (int)$_GET['row'] : 0;
while ($t = mysql_fetch_object($result)) {
	mysql_query('
		INSERT INTO sitemap SET
		link = \''.mysql_real_escape_string($basepath.'/'.urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$t->threadid).'.html').'\'
		,dateline = \''.mysql_real_escape_string($t->lastpost).'\'
		,priority = \'0.5\'
		,changefreq = \'weekly\'
		') or die(__LINE__.__FILE__.mysql_error());
	$i++;
	
	if (time()-$time > 20) {
		//reload if past loading time
		header('Location: '.$_SERVER['PHP_SELF'].'?row='.$i);
		exit();
	}
}

if (!is_dir(PATH.'/sitemap/')) mkdir(PATH.'/sitemap/',0777,true);

$sitemap = mysql_single('
	SELECT COUNT(id) as `num` FROM sitemap
	',__LINE__.__FILE__);
	
$sitemaptotal = ceil($sitemap->num/50000);

$urls = $indexes = array();

//which page am I on?
$page = 0;

for ($i = 0; $i < $sitemaptotal; $i++) {
	$result = mysql_query('
		SELECT * FROM sitemap LIMIT '.((string)($i*50000)).',50000
		') or die(__LINE__.__FILE__.mysql_error());
	$urls = array();
	while ($sitemap = mysql_fetch_object($result)) {
		$urls[] = '<url>
  <loc>'.$sitemap->link.'</loc>
  <priority>'.$sitemap->priority.'</priority>
  <lastmod>'.date('c',$sitemap->dateline).'</lastmod>
  <changefreq>'.$sitemap->changefreq.'</changefreq>
</url>';
	}

	$indexes[] = '
<sitemap>
	<loc>'.$basepath.'/sitemap/sitemap_'.($i+1).'.xml.gz</loc>
	<lastmod>'.date('c',$time).'</lastmod>
</sitemap>
	';
	
	@unlink($file = PATH.'/sitemap/sitemap_'.($i+1).'.xml.gz');
	//exec('touch '.$file);
	
	echo !($fp = gzopen($file,'wb9')) ? $file." not written!\n<br />" : $file." written!\n<br />";
	chmod($file,0777);
	if ($fp) {
		gzwrite(
			$fp
			,'<?xml version="1.0" encoding="UTF-8"?>
<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="
            http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/09/sitemap.xsd">
'
			.implode("\n",$urls)
			.'
</urlset>'
		);
		gzclose ( $fp );
	}
}

//create the sitemap index file
@unlink($file = PATH.'/sitemap/sitemap_index.xml.gz');

//exec('touch '.$file);

echo !($fp = gzopen($file,'wb9')) ? $file." not written!\n<br />" : $file." written!\n<br />";
chmod($file,0777);
if ($fp) {
	gzwrite(
		$fp
		,'<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="
            http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/09/siteindex.xsd">'
		.implode("\n",$indexes)
		.'
</sitemapindex>'
	);
	gzclose ( $fp );
}

//ping sitemaps

if ($_SERVER['HTTP_HOST'] != 'localhost') {
	$sitemapurl = $basepath.'/sitemap/sitemap_index.xml.gz';
	echo '<br />Google pinged with status: ',pingSitemaps($sitemapurl,'www.google.com','/webmasters/sitemaps/ping?sitemap='),"\n";
	echo '<br />Yahoo pinged with status: ',pingSitemaps($sitemapurl,'search.yahooapis.com','/SiteExplorerService/V1/updateNotification?appid=YahooDemo&url='),"\n";
	echo '<br />Bing pinged with status: ',pingSitemaps($sitemapurl,'www.bing.com','/webmaster/ping.aspx?siteMap='),"\n";
	echo '<br />Ask pinged with status: ',pingSitemaps($sitemapurl,'submissions.ask.com','/ping?sitemap='),"\n";
}

//that's it!

<?php

include 'includes/dbconnect.php';

$tweet = mysql_single('SELECT * FROM tweets LIMIT 1', $linefile);
if ($tweet->tweetid) {
    //$bitlycall = 'http://api.bit.ly/v3/shorten?longUrl='.urlencode(PROTOCOL.$_SERVER['HTTP_HOST'].'/'.$tweet->url).'&login=teoti&apiKey=R_839ce28aa7990d45cc63c3882ecaedba&format=json';
    //get the url
    //could also use cURL here
    //$response = file_get_contents($bitlycall);
    //$json = @json_decode($response,true);
    //$bitly = $json['data']['url'];
    $f = mysql_single('
        SELECT title,hashtags FROM forum WHERE forumid = \''.mysql_real_escape_string($tweet->forumid).'\'
        ',__LINE__.__FILE__);
    //$snip = trim($tweet->status).' '.$f->hashtags.' '.$bitly;
    $snip = trim($tweet->status).' '.$f->hashtags.' '.PROTOCOL.$_SERVER['HTTP_HOST'].'/'.$tweet->url;

    //if ($bitly && strlen($snip) <= 140) { //if post isn't too long
        //update twitter
        include PATH.'/classes/OAuth.php';
        include PATH.'/classes/twitteroauth.php';

        $consumer_key = 'fIazxLTn7Gtq3F4LV2s6w';
        $consumer_secret = 'ZxilcnOvJ8FZ3PqONAacehdvSj9DpvRiekT7ZItcho';
        $token = '244264064-5ftyRch3FDKoZ4VFegSitkwCU0EZU9pUoWU9Dym2';
        $secret= 't8XewIZw5d4DUCQuP4greDIM979eTh0ZlyAtmlgkeHI';

        $connection = new TwitterOAuth(
            $consumer_key,
            $consumer_secret,
            $token,
            $secret
        );

        //$temporary_credentials = $connection->getRequestToken('http://www.teoti.com/twitterauth'); // Use config.php callback URL.
        //$redirect_url = $connection->getAuthorizeURL($temporary_credentials);

        //$account = $connection->get('account/verify_credentials');

        $post = $connection->post('/statuses/update', array('status' => $snip));

        //update facebook group


        //farking sweet
    //}

    mysql_single('
        DELETE FROM tweets WHERE tweetid = \''.mysql_real_escape_string($tweet->tweetid).'\'
        ',__LINE__.__FILE__);
    
    echo 'Tweet: ',$snip,"\n\n<br /><br />...delivered";
}
?>

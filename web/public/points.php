<?
require_once 'includes/dbconnect.php';

//check post and thread exists and post doesn't belong to user

if ($_POST['postid'] > 0) {
    $post = mysql_single('
        SELECT postid, threadid, userid FROM post 
        WHERE postid = \''.mysql_real_escape_string((int)$_POST['postid']).'\'
        '.($session->userid ? 'AND userid != \''.mysql_real_escape_string($session->userid).'\'': '')
        ,__LINE__.__FILE__);
    $thread = mysql_single('
        SELECT threadid,firstpostid,forumid,title FROM thread WHERE threadid = \''.mysql_real_escape_string($post->threadid).'\'
        ',__LINE__.__FILE__);
    if ($post->postid > 0 && $thread->threadid > 0 && $session->userid && isset($_POST['give'])) {
        
        $_POST['give'] = (int)$_POST['give'];
        //check if this is the first post
        $threadpost = $thread->firstpostid == $post->postid ? true : false;

        //check if user has already pointed this post and get value
        $points = mysql_single('
            SELECT id,scored 
            FROM post_thanks 
            WHERE userid = \''.mysql_real_escape_string($session->userid).'\' 
            AND postid = \''.mysql_real_escape_string((int)$_POST['postid']).'\'
            ',__LINE__.__FILE__);

        //failsafe give var
        $tmp = intval($_POST['give']);
        $give = $tmp > 0 ? 1 : ($tmp < 0 ? -1 : 0);
        unset($tmp);

        //fix legacy issue with 'scored' if not equal to '0' - also failsafe
    #	echo($points->scored);
        if (isset($points->scored) && $points->scored != 0) {
            $points->scored = $points->scored > 0 ? '1' : '-1';
        } else {
            $points->scored = '0';
        }
        #die($points->scored);
        //if value sent is not already set or the same as the score sent then apply points
        if ($give != $points->scored) {

            $updatepoints = '0';
            switch ($give){
                case 0:
                    switch ($points->scored) {
                        case '-1': $updatepoints = '1'; break;
                        case '1': $updatepoints = '-1'; break;
                    }
                    break;
                case -1:
                    switch ($points->scored) {
                        case '0': $updatepoints = '-1'; break;
                        case '1': $updatepoints = '-2'; break;//turn plus points into minus points
                    }
                    break;
                case 1:
                    switch ($points->scored) {
                        case '-1': $updatepoints = '2'; break; //turn minus points into plus points
                        case '0': $updatepoints = '1'; break;
                    }
                    break;
            }
            $time = time();
            $insertupdate = 'insert';
            if ($points->id > 0) {
                //update points
                mysql_query('
                    UPDATE post_thanks SET 
                    scored = scored + '.$updatepoints.'
                    ,date = \''.mysql_real_escape_string($time).'\'
                    WHERE id = \''.mysql_real_escape_string($points->id).'\'
                    ') or die(__LINE__.__FILE__.mysql_error());
                $insertupdate = 'update';
            } else {
                //insert points
                mysql_query('
                    INSERT INTO post_thanks SET
                    userid = \''.mysql_real_escape_string($session->userid).'\'
                    ,username = \''.mysql_real_escape_string($session->username).'\'
                    ,date =  \''.mysql_real_escape_string($time).'\'
                    ,postid = \''.mysql_real_escape_string($post->postid).'\'
                    ,scored = \''.mysql_real_escape_string($give).'\'
                    ') or die(__LINE__.__FILE__.mysql_error());
            }

            mysql_query('
                UPDATE post SET 
                post_thanks_amount = post_thanks_amount + '.$updatepoints.'
                ,updated =  \''.mysql_real_escape_string($time).'\'
                WHERE postid = \''.mysql_real_escape_string($post->postid).'\'
                ') or die(__LINE__.__FILE__.mysql_error());

            if ($post->userid) {
                mysql_query('
                    UPDATE user SET 
                    post_thanks_thanked_times = post_thanks_thanked_times + '.$updatepoints.'
                    ,user_total_score = user_total_score + '.$updatepoints.'
                    WHERE userid = \''.mysql_real_escape_string($post->userid).'\'
                    ') or die(__LINE__.__FILE__.mysql_error());
            }

            //give points to self
            /*mysql_query('
                UPDATE user SET 
                user_score = user_score + '.$updatepoints.'
                ,user_total_score = user_total_score + '.$updatepoints.'
                WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
                ') or die(__LINE__.__FILE__.mysql_error());*/

            if ($threadpost) {
                mysql_query('
                    UPDATE thread SET thread_score = thread_score + '.$updatepoints.'
                    WHERE threadid = \''.mysql_real_escape_string($thread->threadid).'\'
                    ') or die(__LINE__.__FILE__.mysql_error());
                //if user isn't already a subscriber...
                if (!mysql_single('
                    SELECT subscribethreadid FROM subscribethread WHERE threadid = \''.mysql_real_escape_string($thread->threadid).'\' 
                    AND userid = \''.mysql_real_escape_string($session->userid).'\'
                    ',__LINE__.__FILE__)) {
                    //put the user who created the post onto the subscription list
                    mysql_query('
                        INSERT INTO subscribethread SET
                        userid = \''.mysql_real_escape_string($session->userid).'\'
                        ,threadid = \''.mysql_real_escape_string($thread->threadid).'\'
                        ,emailupdate = 0
                        ,folderid = 0
                        ,canview = 1
                        ') or die(__LINE__.__FILE__.mysql_error());
                }

                updateHistory($thread->threadid);
            }

            if ($give != 0) {
                $activityid = activity('likedislike',$insertupdate,$post->postid,$thread->forumid,__LINE__,$give == 1 ? 'liked':'disliked');
                notify('likedislike',(int)$_POST['postid'],$give == 1 ? 'liked':'disliked');
            }
        }
    }
    
    if (!$_POST['ajax'] && !$_SERVER['argc']) {
        //redirect to thread
        $forum = mysql_single('
            SELECT title FROM forum WHERE forumid = \''.mysql_real_escape_string($thread->forumid).'\'
            ',__LINE__.__FILE__);
        header( 'HTTP/1.1 301 Moved Permanently' );
        header('Location: '.($_POST['referrer'] ? $_POST['referrer'] : urlify($forum->title).'/'.urlify($thread->title,$thread->threadid).'.html')) AND exit();
    } else {
        //echo out point shizzle
        if (strlen($_POST['postid']) > 50) $_POST['postid'] = '0'; 
        
        $result = mysql_query('
            SELECT userid,username FROM post_thanks 
            WHERE postid = \''.mysql_real_escape_string((int)$_POST['postid']).'\'
            AND scored '.((int)$_POST['plusminus'] > 0 ? '>' : '<').' 0
            ') or die(__LINE__.__FILE__.mysql_error());
        $message = '';
        if (mysql_num_rows($result) <= MAXPOINTLISTSIZE && mysql_num_rows($result)) {
            $i=0;
            $message = 'The following people '.($_POST['plusminus'] ? '' : 'dis').'like this:<br />';
            while ($pt = mysql_fetch_object($result)){
                $message .= ($i++ ? ', ':'').PHP_EOL.userlink($pt->username,$pt->userid,false);
            }
        } else {
            $message = (string)mysql_num_rows($result).' people '.($_POST['plusminus'] ? '' : 'dis').'like this.';
        }
        
        if ($_SERVER['argc'] && $session->userid) {
            $points = mysql_single('
                SELECT id,scored 
                FROM post_thanks 
                WHERE userid = \''.mysql_real_escape_string($session->userid).'\' 
                AND postid = \''.mysql_real_escape_string((int)$_POST['postid']).'\'
                ',__LINE__.__FILE__);
            
            $json = new stdClass();
            $json->id = (int)$_POST['postid'];
            $json->threadid = (int)$post->threadid;
            $json->type = 'likedislike';
            $json->do = $_POST['do'];
            $json->extra = $_POST['plusminus'] ? 'like':'dislike';
            $json->errors = array();
            $json->update = array();
            $json->activityid = (int)$activityid;
            $json->forumid = (int)$thread->forumid;
            $json->editor = $session->userid;
            $json->message = $message;

            echo json_encode($json);
        } else {
            echo $message;
        }
    }
}

?>
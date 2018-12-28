<?


if ($_POST['do'] == 'delete' && $_POST['p'] > 0 && $session->userid) {
    
	$p = mysql_single('
        SELECT postid,threadid FROM post WHERE postid = \''.mysql_real_escape_string($_POST['p']).'\'
        ',__LINE__.__FILE__);
	
	mysql_query('
		UPDATE post SET visible = 2 WHERE postid = \''.mysql_real_escape_string($p->postid).'\'
		'.($session->staff ? '': 'AND userid = \''.mysql_real_escape_string($session->userid).'\'').'
		') or die(__LINE__.__FILE__.mysql_error());
    
	$t = mysql_single('
        SELECT forumid FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'
        ',__LINE__.__FILE__);
    
	$forumid = $t->forumid;
		
	//if this post is the thread starter content then delete thread too
	$t = mysql_single('
		SELECT threadid FROM thread WHERE firstpostid = \''.mysql_real_escape_string($p->postid).'\'
		',__LINE__.__FILE__);
    
    $location = STRIP_REQUEST_URI;
    $isthread = false;
    
	if ($t->threadid) {
        $isthread = true;
        $location = URLPATH.'/';
        
		mysql_query('
			DELETE FROM notification 
			WHERE type IN (1,2,6) 
			AND itemid IN (SELECT postid FROM post WHERE threadid = \''.mysql_real_escape_string($t->threadid).'\')
			') or die(__LINE__.__FILE__.mysql_error());
		
		mysql_query('
			UPDATE thread SET visible = 2 WHERE threadid = \''.mysql_real_escape_string($t->threadid).'\'
			'.($session->staff ? '': 'AND postuserid = \''.mysql_real_escape_string($session->userid).'\'').'
			') or die(__LINE__.__FILE__.mysql_error());
		$activityid = activity('newpost','delete',$p->postid,$forumid,__LINE__,'thread');
	} else {
		$activityid = activity('newpost','delete',$p->postid,$forumid,__LINE__,'post');
        //if this post is the thread starter content then delete thread too
	}
    
    
	
	//header('Location: '.$location);
    $json = new stdClass();
    $json->errors = array();
    $json->forumid = (int)$forumid;
    $json->activityid = $activityid;
    $json->id = (int)$p->postid;
    $json->threadid = (int)$p->threadid;
    $json->type = $isthread ? 'thread' : 'post';
    $json->do = 'delete';
    echo json_encode($json);
	exit();
}

if ($session->userid && $_POST['do'] == 'points' && $_POST['p'] > 0 && $_POST['amount'] > 0) {
	$error = '';
	$amount = (int)$_POST['amount'];
	
	$p = mysql_single('SELECT userid,postid,threadid FROM post WHERE postid = \''.mysql_real_escape_string((int)$_POST['p']).'\'',__LINE__.__FILE__);
	$t = mysql_single('SELECT firstpostid,forumid FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'',__LINE__.__FILE__);
	
	if (!$p->postid) $error = 'The post you are trying to give points to doesn\'t exist.';
	
	if ($p->userid == $session->userid) $error = 'You can\'t give points to your own post.';
	
	$pt = mysql_single('
		SELECT * FROM points 
		WHERE postid = \''.mysql_real_escape_string($p->postid).'\'
		AND userid = \''.mysql_real_escape_string($session->userid).'\'
		',__LINE__.__FILE__);
	
	if (!$pt->amount) $pt->amount = 0;
	
	
	//if the points being given are greater than the amount the user has left per day then don't allow this.
	if ($amount > $session->limit_points) $amount = $session->limit_points;
	
	//finally if the amount now given is too low then deny
	if ($amount <= 0) $error = 'Sorry, you have run out of points to give!';
	
	if ($amount) {
		//if the points being given are greater than the user's limit then limit the number of points to the maximum amount by only giving the difference
		if (($pt->amount+$amount) > POINTSLIMIT) $amount = POINTSLIMIT-$pt->amount; 
		
		//finally if the amount now given is too low then deny
		if ($amount <= 0) $error = 'Sorry, you can\'t give any more points to this thread.';
	}
	
	
	if (!$error) {
		//update user score
		mysql_query('
			UPDATE user SET
			limit_points = limit_points - \''.mysql_real_escape_string($amount).'\'
			WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		$session->limit_points = $session->limit_points-$amount;
		//update receiver score		
		mysql_query('
			UPDATE user SET 
			post_thanks_thanked_times = post_thanks_thanked_times + \''.mysql_real_escape_string($amount).'\'
			,user_total_score = user_total_score + \''.mysql_real_escape_string($amount).'\'
			WHERE userid = \''.mysql_real_escape_string($p->userid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		//update post score
		mysql_query('
			UPDATE post SET
			post_thanks_amount = post_thanks_amount + \''.mysql_real_escape_string($amount).'\'
			WHERE postid = \''.mysql_real_escape_string($p->postid).'\'
			') or die(__LINE__.__FILE__.mysql_error());
		//update thread score if thread starter (should be anyway)
		if ($p->postid == $t->firstpostid) {
			mysql_query('
				UPDATE thread SET
				thread_score = thread_score + \''.mysql_real_escape_string($amount).'\'
				WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
		}
		
		if ($pt->pointid) {
			mysql_query('
				UPDATE points SET 
				amount = amount + \''.mysql_real_escape_string($amount).'\'
				,dateline = \''.mysql_real_escape_string(time()).'\'
				WHERE pointid = \''.mysql_real_escape_string($pt->pointid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			$activityid = activity('extrapoints','update',$p->postid,$t->forumid,__LINE__,$amount);
		} else {
			mysql_query('
				INSERT INTO points SET 
				amount = \''.mysql_real_escape_string($amount).'\'
				,dateline = \''.mysql_real_escape_string(time()).'\'
				,userid = \''.mysql_real_escape_string($session->userid).'\'
				,username = \''.mysql_real_escape_string($session->username).'\'
				,postid = \''.mysql_real_escape_string($p->postid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
				
			$activityid = activity('extrapoints','insert',$p->postid,$t->forumid,__LINE__,$amount);
		}
		
		//if user isn't already a subscriber...
		if (!mysql_single('
			SELECT subscribethreadid FROM subscribethread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\' 
			AND userid = \''.mysql_real_escape_string($session->userid).'\'
			',__LINE__.__FILE__) && $p->postid == $t->firstpostid) {
			//put the user who created the post onto the subscription list
			mysql_query('
				INSERT INTO subscribethread SET
				userid = \''.mysql_real_escape_string($session->userid).'\'
				,threadid = \''.mysql_real_escape_string($p->threadid).'\'
				,emailupdate = 0, folderid = 0, canview = 1
				') or die(__LINE__.__FILE__.mysql_error());
		}
		
		notify('extrapoints',$p->postid,(int)$pt->amount+(int)$amount);
	}
	
	if ($_POST['ajax']) {
		$json = new stdClass();
		$json->error = $error;
		$json->amount = (int)$amount;
		$json->totalamount = (int)$pt->amount+(int)$amount;
		$json->userlink = userlink($session->username,$session->userid);
		$json->userid = (int)$session->userid;
		$json->limit = (int)$session->limit_points;
		$json->postid = (int)$p->postid;
		$json->update = $pt->pointid ? true : false;
		$json->max = POINTSLIMIT - $pt->amount;
		$json->max = (int)($json->max > $session->limit_points ? $session->limit_points : $json->max);
        $json->activityid = $activityid;
        $json->forumid = (int)$t->forumid;
        
		echo json_encode($json);
	} else {
		header('Location: '.STRIP_REQUEST_URI.($error ? '?error='.$error : ''));
	}
	exit();
}

if ($session->staff) {
	if ($_POST['do'] == 'undelete' && $_POST['p'] > 0) {
        
		$p = mysql_single('
			SELECT postid,threadid FROM post WHERE postid = \''.mysql_real_escape_string((int)$_POST['p']).'\'
			',__LINE__.__FILE__);
        
		$errors = array();
        $activityid = 0;
        
        
        
		if ($p->postid > 0){
			$type = 'post';
			mysql_query('
				UPDATE post SET visible = 1 WHERE postid = \''.mysql_real_escape_string($p->postid).'\'
				'.($session->staff ? '': 'AND userid = \''.mysql_real_escape_string($session->userid).'\'').'
				') or die(__LINE__.__FILE__.mysql_error());
			//if this post is the thread starter content then undelete thread too
			
			$forumid = $t->forumid;
			
			$t = mysql_single('
				SELECT threadid,title FROM thread WHERE firstpostid = \''.mysql_real_escape_string($p->postid).'\'
				',__LINE__.__FILE__);
			if ($t->threadid) {
				mysql_query('
					UPDATE thread SET visible = 1 WHERE threadid = \''.mysql_real_escape_string($t->threadid).'\'
					'.($session->staff ? '': 'AND postuserid = \''.mysql_real_escape_string($session->userid).'\'').'
					') or die(__LINE__.__FILE__.mysql_error());
				$type = 'thread';
			} else {
                $t = mysql_single('
                    SELECT forumid, title FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'
                    ',__LINE__.__FILE__);
            }
			
			$activityid = activity('newpost','undelete',(int)$_POST['p'],$forumid,__LINE__,$type);
			
		} else $errors[] = 'You can\'t undelete this.';
		
        $json = new stdClass();
        $json->forumid = $forumid;
        $json->activityid = $activityid;
        $json->errors = $errors;
        $json->type = $t->threadid ? 'thread' : 'post';
        $json->url = $p->threadid ? urlify(forumtitle($t->forumid)).'/'.urlify($t->title, $p->threadid).'.html?p='.$p->postid : './';
        $json->do = $_POST['do'];
        echo json_encode($json);
		//header('Location: '.STRIP_REQUEST_URI);
		exit();
	}
	
	if ($_GET['do'] == 'merge') {
		
		//get id from value sent
		
		if (!is_numeric(trim($_GET['merge']))) {
			$items = explode('-',(strstr($_GET['merge'],'/') ? str_replace('/','',strrchr($_GET['merge'],'/')):$_GET['merge']));
			$_GET['merge'] = (int)$items[0];
		}
		
		$t = mysql_single('SELECT threadid,postuserid,title,firstpostid,forumid FROM thread WHERE threadid = \''.mysql_real_escape_string($_GET['t']).'\'',__LINE__.__FILE__);
		$merge = mysql_single('SELECT threadid, title, forumid FROM thread WHERE threadid = \''.mysql_real_escape_string($_GET['merge']).'\'',__LINE__.__FILE__);
		$f = mysql_single('SELECT title FROM forum WHERE forumid = \''.mysql_real_escape_string($merge->forumid).'\'',__LINE__.__FILE__);
		
		//if both original and new threads exist and the thread being moved to isn't the same as the one currently active...
		if ($t->threadid && $merge->threadid && $merge->threadid != $t->threadid) {
			//move all posts in thread to the original
			mysql_query('
				UPDATE post SET threadid = \''.mysql_real_escape_string($merge->threadid).'\' WHERE threadid = \''.mysql_real_escape_string($t->threadid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
				
			//remove the old thread
			mysql_query('DELETE FROM thread WHERE threadid = \''.mysql_real_escape_string($t->threadid).'\'') or die(__LINE__.__FILE__.mysql_error());
			
			$url = urlify($f->title).'/'.urlify($merge->title,$merge->threadid).'.html';
			
			//auto pm the geezer if it's not your own thread
			//if ($t->postuserid != $session->userid) {
				include PATH.'/includes/conversation-functions.inc.php';
                if (!class_exists('StringParser_BBCode')) {
                    require PATH.'/classes/stringparser_bbcode.class.php';
                    require PATH.'/includes/bbcode-functions.php';	
                    $bbcode = bbcode();
                }
                
				$message = '
				Your thread: '.$t->title.' was merged into [url='
				.PROTOCOL.$_SERVER['HTTP_HOST'].$url.']'.$merge->title
				.'[/url] for the following reason:
				
				[b]'.(trim($_GET['reason']) ? trim($_GET['reason']):'Repost').'[/b]
				
				Apologies for any inconvenience. If you have any issues with this, please contact myself or another member of staff. 
				
				Thanks.
				'.$session->username;
				
				mysql_query('
					INSERT INTO pmtext SET
					fromuserid = \''.mysql_real_escape_string($session->userid).'\'
					,fromusername = \''.mysql_real_escape_string($session->username).'\'
					,title = \''.mysql_real_escape_string(trim('Thread: '.$t->title.' merged')).'\'
					,message = \''.mysql_real_escape_string($message).'\'
					,touserarray = \''.mysql_real_escape_string(serialize(array())).'\'
					,iconid = \''.mysql_real_escape_string(DEFAULTICONID).'\'
					,dateline = \''.mysql_real_escape_string($time = time()).'\'
					,lastpm = \''.mysql_real_escape_string($time).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				
				$pmtextid = mysql_insert_id();
				//create node
				mysql_query('
					INSERT INTO pmnode SET
					pmtextid = \''.mysql_real_escape_string($pmtextid).'\'
					,userid = \''.mysql_real_escape_string($session->userid).'\'
					,message = \''.mysql_real_escape_string($message).'\'
					,html = \''.mysql_real_escape_string($bbcode->parse($message)).'\'
					,dateline = \''.mysql_real_escape_string($time).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				
				$participants = array($session->userid,$t->postuserid);
				foreach ($participants as $participant) {
					mysql_query('
						INSERT INTO pm SET
						pmtextid = \''.mysql_real_escape_string($pmtextid).'\'
						,userid = \''.mysql_real_escape_string($participant).'\'
						,messageread = \''.mysql_real_escape_string($participant == $session->userid ? '1':'0').'\'
						,unread = \''.mysql_real_escape_string($participant == $session->userid ? '1':'0').'\'
						') or die(__LINE__.__FILE__.mysql_error());
				}
				
				updateConversationCount($participants);
			//}
			
			activity('newpost','merge',$t->firstpostid,$t->forumid,__LINE__,'thread');
			
			//and go to the merged thread page. 
			header('Location: '.$url);
			exit();
		}
	}
}
if ($_GET['do'] == 'ajax') {
	if ($_GET['type'] == 'getPost' && $_GET['postid']) {
		$p = mysql_single('
			SELECT postid,pagetext FROM post 
			WHERE postid = \''.mysql_real_escape_string((int)$_GET['postid']).'\'
			'.($session->staff ? '':'AND userid = \''.mysql_real_escape_string($session->userid).'\'').'
			',__LINE__.__FILE__);
			
		$json = new stdClass();
		if ($p->postid) {
			$json->pagetext = $p->pagetext;
		} else {
			//echo the error
			$json->error = 'Sorry, either the post does not exist or you do not have permission to edit this post.';
		}
		echo json_encode($json);
	}
	exit();
}
if ($_SERVER['argc']) {
    exit;
}

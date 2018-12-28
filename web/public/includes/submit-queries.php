<?
$check = array();
if (in_array($_POST['do'],array('insert','update')) && $session->userid) {
    
	if ($_POST['type'] == 'thread') {
	//validate title
		if (strlen(trim($_POST['title'])) <= MINTITLELENGTH) $check[] = 'Title must be more than '.MINTITLELENGTH.' characters';
		if (strlen(trim($_POST['title'])) > MAXTITLELENGTH) $check[] = 'Title must be less than '.MAXTITLELENGTH.' characters ('.strlen(trim($_POST['title'])).' characters used.)';
		
		//validate category (is set, allowance)
		if (!($_POST['forumid'] > 0)) $check[] = 'You must choose a category!';
		elseif (!okcats((int)$_POST['forumid'])) $check[] = 'You are not allowed to post in the category chosen';
	}
    
	//validate page body
	if (strlen(trim($_POST['textarea_pagetext'])) <= MINPOSTLENGTH) $check[] = 'Page body must be more than '.MINPOSTLENGTH.' characters';
	if (strlen(trim($_POST['textarea_pagetext'])) >= MAXPOSTLENGTH) $check[] = 'Page body must be less than '.MAXPOSTLENGTH.' characters ('.strlen(trim($_POST['textarea_pagetext'])).' characters used.)';
	
	if ($_POST['type'] == 'post') {
		$t = mysql_single('SELECT threadid FROM thread WHERE threadid = \''.mysql_real_escape_string($_POST['threadid']).'\'',__LINE__.__FILE__);
		if ($_POST['do'] == 'update') {
			$p = mysql_single('
				SELECT threadid FROM post 
				WHERE postid = \''.mysql_real_escape_string($_POST['postid']).'\'
				'.($session->staff ? '':'AND userid = \''.mysql_real_escape_string($session->userid).'\'').'
				',__LINE__.__FILE__);
			if (!$p->threadid) $check[] = 'In order to create a post it must belong to a thread (or yourself)!';
		} elseif (!$t->threadid) $check[] = 'In order to create a post it must belong to a thread!';
	}
	
	if ($_POST['update'] && $_POST['type'] == 'thread') {
		$t = mysql_single('
			SELECT threadid
			FROM thread
			WHERE threadid = \''.mysql_real_escape_string($_POST['threadid']).'\'
			'.($session->staff ? 'AND postuserid = \''.mysql_real_escape_string($session->userid).'\'' : '').'
			',__LINE__.__FILE__);
		if (!$t->threadid) $check[] = 'The thread you are trying to update either doesn\'t doesn\'t exist or belong to you.';
	}
	
    
    
	if ($_POST['do'] == 'insert' && !$session->staff) {
		$lastpostdiff = time()-$session->lastpost;
		if ($lastpostdiff < POSTINTERVAL) $check[] = 'You can\'t create a new '.$_POST['type'].' within '.POSTINTERVAL.' seconds of the last. '.(POSTINTERVAL-$lastpostdiff).' seconds left until you can post again.';
	}
	//validate ip?
	
	//count img tags!
	if (!$check) {
        if (!class_exists('StringParser_BBCode')) {
            require PATH.'/classes/stringparser_bbcode.class.php';
            require PATH.'/includes/bbcode-functions.php';	
        }
        $bbcode = bbcode();
    }
}



if (!$check && in_array($_POST['do'],array('insert','update'))) {
	if ($_POST['do'] == 'insert') {
		if ($_POST['type'] == 'thread') {
			
            
            
			$sreplace = array();
            $ssearch = array();
            
			$result = mysql_query('
				SELECT smilietext,smiliepath FROM smilie
				') or die(__LINE__.__FILE__.mysql_error());
			while ($smilie = mysql_fetch_object($result)) $ssearch[] = $smilie->smilietext;
			
			if ($_POST['auto'] && $_POST['thumbnail'] && !strstr($_POST['textarea_pagetext'],$_POST['thumbnail'])) {
				$_POST['textarea_pagetext'] = '[img]'.$_POST['thumbnail']."[/img]\n\n".$_POST['textarea_pagetext'];
			}
			
			mysql_query('
				INSERT INTO thread SET 
				title = \''.mysql_real_escape_string(longword((string)$_POST['title'])).'\'
				,threadtype = \''.mysql_real_escape_string((int)$_POST['threadtype']).'\'
				,lastpost = \''.mysql_real_escape_string(time()).'\'
				,lastposter = \''.mysql_real_escape_string($session->username).'\'
				,forumid = \''.mysql_real_escape_string((int)$_POST['forumid']).'\'
				,related = \''.mysql_real_escape_string($_POST['related']).'\'
				,open = \''.mysql_real_escape_string($session->staff ? $_POST['open'] : '1').'\'
				'.($session->staff ? '
				,sticky = \''.mysql_real_escape_string((int)$_POST['sticky']).'\'
				,styleid = \''.mysql_real_escape_string((int)$_POST['styleid']).'\'
				':'').'
				,visible = 1
				,postusername = \''.mysql_real_escape_string($session->username).'\'
				,postuserid = \''.mysql_real_escape_string($session->userid).'\'
				,dateline = \''.mysql_real_escape_string(time()).'\'
				,iconid = '.DEFAULTICONID.'
				,description = \''.mysql_real_escape_string(showBrief(cleanNodePara(str_replace($ssearch,'',$_POST['textarea_pagetext'])),50)).'\'
				,thumbnail = \''.mysql_real_escape_string($_POST['thumbnail'] ? $_POST['thumbnail'] : getFirstImage($_POST['textarea_pagetext'])).'\'
				') or die(__LINE__.__FILE__.mysql_error());
				
			$threadinsert = mysql_insert_id();
	
			mysql_query('
				INSERT INTO post SET
				threadid = \''.mysql_real_escape_string($threadinsert).'\'
				,username = \''.mysql_real_escape_string($session->username).'\'
				,userid = \''.mysql_real_escape_string($session->userid).'\'
				,title = \''.mysql_real_escape_string(longword((string)$_POST['title'])).'\'
				,dateline = \''.mysql_real_escape_string($time = time()).'\'
				,updated = \''.mysql_real_escape_string($time).'\'
				,pagetext = \''.mysql_real_escape_string($_POST['textarea_pagetext']).'\'
				,html = \''.mysql_real_escape_string($bbcode->parse((string)$_POST['textarea_pagetext'])).'\'
				,allowsmilie = 1
				,showsignature = 0
				,ipaddress = \''.mysql_real_escape_string(userip()).'\'
				,iconid = '.DEFAULTICONID.'
				,visible = \''.mysql_real_escape_string($session->staff && $_POST['visible'] == 2 ? '2' : '1').'\'
				,point_lock = \''.mysql_real_escape_string($_POST['point_lock']).'\'
				,starter_only = 1
				') or die(__LINE__.__FILE__.mysql_error());
			
			$postinsert = mysql_insert_id();
				
			mysql_query('
				UPDATE thread SET 
				firstpostid = \''.mysql_real_escape_string($postinsert).'\' 
				WHERE threadid = \''.mysql_real_escape_string($threadinsert).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			$type = getThreadType((int)$_POST['threadtype']);
			
			$activityid = activity('newpost','insert',$postinsert,(int)$_POST['forumid'],__LINE__,$type);
	
			$t = mysql_single('SELECT COUNT(threadid) as `num` FROM thread WHERE postuserid = \''.mysql_real_escape_string($session->userid).'\' AND visible = 1',__LINE__.__FILE__);
			$p = mysql_single('SELECT COUNT(postid) as `num` FROM post WHERE userid = \''.mysql_real_escape_string($session->userid).'\' AND visible = 1',__LINE__.__FILE__);
	
			//distribute points for creating a thread and update other user vars (last post etc)	
			mysql_query('
				UPDATE user SET
				lastactivity = \''.mysql_real_escape_string($time).'\'
				,lastpost = \''.mysql_real_escape_string($time).'\'
				,posts = \''.mysql_real_escape_string($p->num).'\'
				,threads = \''.mysql_real_escape_string($t->num).'\'
				#,post_thanks_thanked_times = post_thanks_thanked_times + '.mysql_real_escape_string(NEWTHREADPOINTS).'
				#,user_total_score = user_total_score + '.mysql_real_escape_string(NEWTHREADPOINTS).'
				WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			//todo: add threads counter for users
			
			mysql_query('
				UPDATE forum SET lastpost = \''.mysql_real_escape_string($time).'\' WHERE forumid = \''.mysql_real_escape_string((int)$_POST['forumid']).'\'
				') or die(__LINE__.__FILE__.mysql_error());
						
			
			//put the user who created the thread onto the subscription list
			mysql_query('
				INSERT INTO subscribethread SET
				userid = \''.mysql_real_escape_string($session->userid).'\'
				,threadid = \''.mysql_real_escape_string($threadinsert).'\'
				,emailupdate = 0
				,folderid = 0
				,canview = 1
				') or die(__LINE__.__FILE__.mysql_error());
				
			//fixed subscriptions for mods/admins
			if (in_array($_POST['forumid'],forumChildren(2))) {
				$result = mysql_query('
					SELECT userid FROM user WHERE usergroupid IN ('.mysql_real_escape_string(MODGROUPS.','.ADMINGROUPS).')
					') or die(__LINE__.__FILE__.mysql_error());
				while ($u = mysql_fetch_object($result)) {
					//notify all other mods of new thread!
					if ($session->userid != $u->userid)
						mysql_query('
							INSERT INTO subscribethread SET
							userid = \''.mysql_real_escape_string($u->userid).'\'
							,threadid = \''.mysql_real_escape_string($threadinsert).'\'
							,emailupdate = 0
							,folderid = 0
							,canview = 1
							') or die(__LINE__.__FILE__.mysql_error());
				}
				
				
			} else {
				//check choice subscriptions - remember to remove users that are already subscribed
				$result = mysql_query('
					SELECT * FROM subscribeforum WHERE forumid = \''.mysql_real_escape_string((int)$_POST['forumid']).'\'
					') or die(__LINE__.__FILE__.mysql_error());
				while ($subscribe = mysql_fetch_object($result)) {
					if ($session->userid != $subscribe->userid) {
						mysql_query('
							INSERT INTO subscribethread SET
							userid = \''.mysql_real_escape_string($subscribe->userid).'\'
							,threadid = \''.mysql_real_escape_string($threadinsert).'\'
							,emailupdate = 0
							,folderid = 0
							,canview = 1
							') or die(__LINE__.__FILE__.mysql_error());
					}
				}
			}
			
			//now notify subscribed users of the update
			notify('newpost',$postinsert);
			
			updateHistory($threadinsert);
            
            
			$url = urlify(forumtitle($_POST['forumid'])).'/'.urlify($_POST['title'],$threadinsert).'.html';
            //these are submitted and removed via cronjob (tweet.php)
			if (in_array($_POST['forumid'],forumChildren(15))) {
				mysql_query('
                    INSERT INTO tweets SET
                    status = \''.mysql_real_escape_string(trim($_POST['title'])).'\'
                    ,forumid = \''.mysql_real_escape_string((int)$_POST['forumid']).'\'
                    ,url = \''.mysql_real_escape_string($url).'\'
                    ') or die(__LINE__.__FILE__.mysql_error());
			}
			
			//redirect to thread created (uncomment later)
			if ($_POST['ajax']) {
				
				//echo 'success:'.$url;
				$json = new stdClass();
				$json->url = $url;
				$json->id = $threadinsert;
				$json->type = $_POST['type'];
				$json->do = $_POST['do'];
				$json->errors = array();
                $json->activityid = $activityid;
                $json->forumid = (int)$_POST['forumid'];
				echo json_encode($json);
			} else header('Location: '.$url);
			exit();
		}
		
		if ($_POST['type'] == 'post') {
			
			mysql_query('
				INSERT INTO post SET
				threadid = \''.mysql_real_escape_string((int)$_POST['threadid']).'\'
				,username = \''.mysql_real_escape_string((string)$session->username).'\'
				,userid = \''.mysql_real_escape_string((int)$session->userid).'\'
				,title = \''.mysql_real_escape_string(longword((string)$_POST['title'])).'\'
				,dateline = \''.mysql_real_escape_string($time = time()).'\'
				,updated = \''.mysql_real_escape_string($time).'\'
				,pagetext = \''.mysql_real_escape_string((string)$_POST['textarea_pagetext']).'\'
				,html = \''.mysql_real_escape_string($bbcode->parse((string)$_POST['textarea_pagetext'])).'\'
				,allowsmilie = 1
				,showsignature = 0
				,ipaddress = \''.mysql_real_escape_string(userip()).'\'
				,iconid = '.DEFAULTICONID.'
				,visible = \''.mysql_real_escape_string($session->staff && $_POST['visible'] == 2 ? '2' : '1').'\'
				,point_lock = \''.mysql_real_escape_string((int)$_POST['point_lock']).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			$postinsert = mysql_insert_id();
			
			
			
			$p = mysql_single('SELECT COUNT(postid) as `num` FROM post WHERE userid = \''.mysql_real_escape_string($session->userid).'\' AND visible = 1',__LINE__.__FILE__);
			//distribute points for creating a thread and update other user vars (last post etc)
			mysql_query('
				UPDATE user SET
				lastactivity = \''.mysql_real_escape_string(time()).'\'
				,lastpost = \''.mysql_real_escape_string(time()).'\'
				,posts = \''.mysql_real_escape_string($p->num).'\'
				#,post_thanks_thanked_times = post_thanks_thanked_times + '.mysql_real_escape_string(NEWPOSTPOINTS).'
				#,user_total_score = user_total_score + '.mysql_real_escape_string(NEWPOSTPOINTS).'
				WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
				
			$threadid = (int)$_POST['threadid'];
				
			$t = mysql_single('
				SELECT * FROM thread WHERE threadid = \''.mysql_real_escape_string($threadid).'\'
				',__LINE__.__FILE__);	
				
			$activityid = activity('newpost','insert',$postinsert,$t->forumid,__LINE__,'post');
				
			$pc = mysql_single('
				SELECT COUNT(postid) as `num` FROM post WHERE threadid = \''.mysql_real_escape_string($threadid).'\' AND visible = 1
				',__LINE__.__FILE__);
				
			mysql_query('
				UPDATE thread SET 
				lastpost = \''.mysql_real_escape_string(time()).'\' 
				,lastposter = \''.mysql_real_escape_string($session->username).'\' 
				,replycount = \''.mysql_real_escape_string($pc->num > 1 ? $pc->num-1 : '0').'\'
				,thread_score = thread_score + 1
				WHERE threadid = \''.mysql_real_escape_string($threadid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			

			mysql_query('
				UPDATE user SET
				post_thanks_thanked_times = post_thanks_thanked_times + '.mysql_real_escape_string(NEWPOSTPOINTS).'
				,user_total_score = user_total_score + '.mysql_real_escape_string(NEWPOSTPOINTS).'
				WHERE userid = \''.mysql_real_escape_string($t->postuserid).'\'
				AND userid != \''.mysql_real_escape_string($session->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			//todo: add threads counter for users
			
			mysql_query('
				UPDATE forum SET lastpost = \''.mysql_real_escape_string($time).'\' WHERE forumid = \''.mysql_real_escape_string((int)$t->forumid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
						
			
			//if user isn't already a subscriber...
			if (!mysql_single('
				SELECT subscribethreadid FROM subscribethread WHERE threadid = \''.mysql_real_escape_string($threadid).'\' 
				AND userid = \''.mysql_real_escape_string($session->userid).'\'
				',__LINE__.__FILE__)) {
				//put the user who created the post onto the subscription list
				mysql_query('
					INSERT INTO subscribethread SET
					userid = \''.mysql_real_escape_string($session->userid).'\'
					,threadid = \''.mysql_real_escape_string($threadid).'\'
					,emailupdate = 0, folderid = 0, canview = 1
					') or die(__LINE__.__FILE__.mysql_error());
			}
			
			//now notify subscribed users of the update
			notify('newpost',$postinsert);
			
			updateHistory($threadid);
			
			//perhaps add postid to url later in order to highlight new post?
			$url = urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$t->threadid).'.html';
			if ($_POST['ajax']) {
				//echo 'success:'.$url;
				$json = new stdClass();
				$json->id = (int)$postinsert;
                $json->forumid = (int)$t->forumid;
				$json->threadid = (int)$threadid;
				$json->type = $_POST['type'];
				$json->do = $_POST['do'];
				$json->errors = array();
                $json->activityid = (int)$activityid;
				echo json_encode($json);
			} else header('Location: '.$url);
			exit();
		}
		
		//return to submit page
	}
	
	if ($_POST['do'] == 'update') {
		if ($_POST['type'] == 'thread') {
			$t = mysql_single('
				SELECT firstpostid,thumbnail FROM thread WHERE threadid = \''.mysql_real_escape_string($_POST['threadid']).'\'
				',__LINE__.__FILE__);
			if ($_POST['auto'] && $_POST['thumbnail'] && $_POST['thumbnail'] != $t->thumbnail && !strstr($_POST['textarea_pagetext'],$_POST['thumbnail'])) {
				$_POST['textarea_pagetext'] = '[img]'.$_POST['thumbnail']."[/img]\n\n".$_POST['textarea_pagetext'];
			}
			
			$sreplace=$ssearch=array();
			$result = mysql_query('
				SELECT smilietext,smiliepath FROM smilie
				') or die(__LINE__.__FILE__.mysql_error());
			while ($smilie = mysql_fetch_object($result)) 
				$ssearch[] = $smilie->smilietext;
				
			
			mysql_query('
				UPDATE thread SET 
				title = \''.mysql_real_escape_string(longword($_POST['title'])).'\'
				,threadtype = \''.mysql_real_escape_string((int)$_POST['threadtype']).'\'
				,forumid = \''.mysql_real_escape_string((int)$_POST['forumid']).'\'
				,related = \''.mysql_real_escape_string($_POST['related']).'\'
				,open = \''.mysql_real_escape_string($session->staff ? (int)$_POST['open'] : '1').'\'
				,description = \''.mysql_real_escape_string(showBrief(cleanNodePara(str_replace($ssearch,'',$_POST['textarea_pagetext'])),50)).'\'
				,thumbnail = \''.mysql_real_escape_string($_POST['auto'] && $_POST['thumbnail'] ? $_POST['thumbnail'] : getFirstImage($_POST['textarea_pagetext'])).'\'
				'.($session->staff ? '
				,sticky = \''.mysql_real_escape_string((int)$_POST['sticky']).'\'
				,styleid = \''.mysql_real_escape_string((int)$_POST['styleid']).'\'
				':'').'
				WHERE threadid = \''.mysql_real_escape_string((int)$_POST['threadid']).'\'
				') or die(__LINE__.__FILE__.mysql_error());
	
			mysql_query('
				UPDATE post SET
				title = \''.mysql_real_escape_string(longword((string)$_POST['title'])).'\'
				,pagetext = \''.mysql_real_escape_string($_POST['textarea_pagetext']).'\'
				,html = \''.mysql_real_escape_string($bbcode->parse((string)$_POST['textarea_pagetext'])).'\'
				'.($session->staff ? '':',ipaddress = \''.mysql_real_escape_string(userip()).'\'').'
				,visible = \''.mysql_real_escape_string($session->staff && $_POST['visible'] == 2 ? '2' : '1').'\'
				,point_lock = \''.mysql_real_escape_string((int)$_POST['point_lock']).'\'
				,updated = \''.mysql_real_escape_string(time()).'\'
				WHERE postid = \''.mysql_real_escape_string($t->firstpostid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
					
			$threads = mysql_single('SELECT COUNT(threadid) as `num` FROM thread WHERE postuserid = \''.mysql_real_escape_string($session->userid).'\'',__LINE__.__FILE__);
			//distribute points for creating a thread and update other user vars (last post etc)
			mysql_query('
				UPDATE user SET
				lastactivity = \''.mysql_real_escape_string($time = time()).'\'
				,lastpost = \''.mysql_real_escape_string($time).'\'
				,threads = \''.mysql_real_escape_string($threads->num).'\'
				WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			$type = getThreadType((int)$_POST['threadtype']);
			
			$activityid = activity('newpost','update',$t->firstpostid,(int)$_POST['forumid'],__LINE__,$type);
	
			$url = urlify(forumtitle($_POST['forumid'])).'/'.urlify($_POST['title'],$_POST['threadid']).'.html';
			if ($_POST['ajax']){
                $json = new stdClass();
                $json->id = (int)$t->firstpostid;
                $json->threadid = (int)$_POST['threadid'];
                $json->url = $url;
                $json->forumid = (int)$_POST['forumid'];
				$json->type = $_POST['type'];
				$json->do = $_POST['do'];
				$json->errors = array();
                $json->update = array();
                $json->activityid = (int)$activityid;
                echo json_encode($json);
            } else {
                header('Location: '.$url);
            }
			exit();
		}
		
		if ($_POST['type'] == 'post') {
			mysql_query('
				UPDATE post SET
				pagetext = \''.mysql_real_escape_string($_POST['textarea_pagetext']).'\'
				,html = \''.mysql_real_escape_string($bbcode->parse((string)$_POST['textarea_pagetext'])).'\'
				,ipaddress = \''.mysql_real_escape_string(userip()).'\'
				,visible = \''.mysql_real_escape_string($session->staff && $_POST['visible'] == 2 ? '2' : '1').'\'
				,point_lock = \''.mysql_real_escape_string((int)$_POST['point_lock']).'\'
				,updated = \''.mysql_real_escape_string(time()).'\'
				WHERE postid = \''.mysql_real_escape_string((int)$_POST['postid']).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			mysql_query('
				UPDATE user SET
				lastactivity = \''.mysql_real_escape_string(time()).'\'
				WHERE userid = \''.mysql_real_escape_string($session->userid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			$p = mysql_single('
				SELECT threadid FROM post WHERE postid = \''.mysql_real_escape_string((int)$_POST['postid']).'\'
				',__LINE__.__FILE__);
				
			$t = mysql_single('
				SELECT title,threadid,forumid FROM thread WHERE threadid = \''.mysql_real_escape_string($p->threadid).'\'
				',__LINE__.__FILE__);
				
			$activityid = activity('newpost','update',(int)$_POST['postid'],$t->forumid,__LINE__,'post');
			
			//perhaps add postid to url later in order to highlight new/updated post?
			$url = urlify(forumtitle($t->forumid)).'/'.urlify($t->title,$t->threadid).'.html?p='.$_POST['postid'];
			if ($_POST['ajax']) {
                $json = new stdClass();
                $json->id = (int)$_POST['postid'];
                $json->threadid = (int)$p->threadid;
				$json->type = $_POST['type'];
				$json->do = $_POST['do'];
				$json->errors = array();
                $json->update = array();
                $json->activityid = (int)$activityid;
                $json->forumid = (int)$t->forumid;
                
                echo json_encode($json);
            } else { 
                header('Location: '.$url);
            }
			exit();
			
		}
	}
} elseif ($_POST['ajax']) {
	//echo 'error:'.implode('|',$check);
	$json = new stdClass();
	$json->errors = $check;
    $json->type = $_POST['type'];
    $json->do = $_POST['do'];
	echo json_encode($json);
}

if ($_POST['ajax']) exit();

<?

$pmtextid = '';
$check = array();

if (!function_exists('updateConversationCount')) {
    require PATH.'/includes/conversation-functions.inc.php';
}

if ($_POST['do'] == 'insert' && $session->userid) {
    
    if (!class_exists('StringParser_BBCode')) {
        require PATH.'/classes/stringparser_bbcode.class.php';
        require PATH.'/includes/bbcode-functions.php';	
    }
    
	$bbcode = bbcode();
    
	if ($_POST['type'] == 'conversation') {
		if (strlen(trim($_POST['title'])) > MAXTITLELENGTH) $check[] = 'Sorry, the title must be less than '.MAXTITLELENGTH.' characters.';
		if (strlen(trim($_POST['title'])) < MINTITLELENGTH) $check[] = 'Sorry, the title must be more than '.MINTITLELENGTH.' characters.';
		if (strlen(trim($_POST['textarea_message'])) > MAXPOSTLENGTH) $check[] = 'Sorry, the message must be less than '.MAXPOSTLENGTH.' characters.';
		if (strlen(trim($_POST['textarea_message'])) < MINPOSTLENGTH) $check[] = 'Sorry, the message must be more than '.MINPOSTLENGTH.' characters.';
        if ($_POST['participants[]']) $_POST['participants'] = $_POST['participants[]'];
		//check the message/title length, check they're logged in
        $json = new stdClass();
        $json->errors = $check;
        $json->type = $_POST['type'];
        $json->do = $_POST['do'];
		if (!$check) {
			//create conversation
			mysql_query('
				INSERT INTO pmtext SET
				fromuserid = \''.mysql_real_escape_string($session->userid).'\'
				,fromusername = \''.mysql_real_escape_string($session->username).'\'
				,title = \''.mysql_real_escape_string(trim($_POST['title'])).'\'
				,message = \''.mysql_real_escape_string($_POST['textarea_message']).'\'
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
				,message = \''.mysql_real_escape_string($_POST['textarea_message']).'\'
				,html = \''.mysql_real_escape_string($bbcode->parse($_POST['textarea_message'])).'\'
				,dateline = \''.mysql_real_escape_string($time).'\'
				') or die(__LINE__.__FILE__.mysql_error());
            
            $pmnodeid = mysql_insert_id();
			
			//create participants
			$participants = $_POST['participants'] && is_array($_POST['participants']) ? $_POST['participants'] : array($session->userid);
			if (!in_array($session->userid,$participants)) $participants[] = $session->userid;
			
			foreach ($participants as $participant) {
				//if user exists then insert into pm
				mysql_query('
					INSERT INTO pm SET
					pmtextid = \''.mysql_real_escape_string($pmtextid).'\'
					,userid = \''.mysql_real_escape_string($participant).'\'
					,messageread = \''.mysql_real_escape_string($participant == $session->userid ? '1':'0').'\'
					,unread = \''.mysql_real_escape_string($participant == $session->userid ? '1':'0').'\'
					') or die(__LINE__.__FILE__.mysql_error());
			}
			
			#notify('conversation',$pmtextid);
			
			//update user pm counts per participant
			updateConversationCount($participants);
			
            $json->pm = (int)$pmtextid;
            $json->id = (int)$pmnodeid;
		}
        if ($_POST['ajax']) {
            echo json_encode($json);
            exit;
        }
		//pmtextid is set after insertion of conversation (above)
	}
	
	if ($_POST['addparticipant'] && $_POST['type'] == 'message' && $_POST['participant'] > 0) {
        
		//if the conversation is already created
		if ($_POST['pm'] > 0) {
			//check the user isn't already a participant
			$part = mysql_single('
				SELECT pmid FROM pm 
				WHERE userid = \''.mysql_real_escape_string((int)$_POST['participant']).'\'
				AND pmtextid = \''.mysql_real_escape_string((int)$_POST['pm']).'\'
				',__LINE__.__FILE__);
			if ($part->pmid) $check[] = 'That user is already part of this conversation.';
			
			//check you're allowed to create a participant and that the conversation exists
			$pm = mysql_single('
				SELECT pmtextid FROM pmtext 
				WHERE pmtextid = \''.mysql_real_escape_string((int)$_POST['pm']).'\' 
				'.($session->staff ? '':'AND fromuserid = \''.mysql_real_escape_string($session->userid).'\'').'
				',__LINE__.__FILE__);
				
			if (!$pm->pmtextid) $check[] = 'You don\'t have the necessary permissions to add a participant';
			
			//check the max participants limit
			$part = mysql_single('
				SELECT COUNT(pmid) as `count` FROM pm WHERE pmtextid = \''.mysql_real_escape_string((int)$_POST['pm']).'\'
				',__LINE__.__FILE__);
			if ($part->count >= MAXPARTICIPANTS) $check[] = 'The maximum number of participants has been reached.';
		}
		
        
        $json = new stdClass();
        if (!$check) {
            //and the user exists also
            $u = mysql_single('
                SELECT userid FROM user WHERE userid = \''.mysql_real_escape_string($_POST['participant']).'\'
                ',__LINE__.__FILE__);
            if (!$u->userid) $check[] = 'That user doesn\'t exist.';

            if ($pm->pmtextid) {
                //as you're adding a participant you should also update their pm total for total conversations the users are a part of and the total unread
                $pmnode = mysql_single('
                    SELECT COUNT(pmnodeid) AS `count` FROM pmnode WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
                    ',__LINE__.__FILE__);
                mysql_query('
                    INSERT INTO pm SET
                    pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
                    ,userid = \''.mysql_real_escape_string($u->userid).'\'
                    ,unread = \''.mysql_real_escape_string($pmnode->count ? $pmnode->count : '0').'\'
                    ') or die(__LINE__.__FILE__.mysql_error());

                $pmnode = mysql_single('
                    SELECT pmnodeid FROM pmnode 
                    WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\' 
                    ORDER BY dateline DESC LIMIT 1
                    ',__LINE__.__FILE__);

                    /*UPDATE user SET 
                    pmunread = pmunread + 1,
                    pmtotal = pmtotal + 1
                    WHERE userid = \''.mysql_real_escape_string($u->userid).'\';*/
                //although slightly server intensive on this run it's far easier, more accurate and less server intensive overall
                updateConversationCount($u->userid); 

                if ($_POST['ajax']) {	
                    //echo $_GET['participant'];
                    $json->pm = (int)$pm->pmtextid;
                    $json->id = (int)$pmnode->pmnodeid;
                    $json->addparticipant = true;
                    $json->username = (string)$_POST['username'];
                    $json->participant = (int)$_POST['participant'];
                }
            }
        }
        
        $json->do = $_POST['do'];
        $json->errors = $check;
        $json->type = $_POST['type'];
        echo json_encode($json);
        exit;
		
		unset($_POST['type']);
	}
    
	if ($_POST['type'] == 'message' && $_POST['pm'] > 0) {
		//do the checks first... message too long/short,logged in or are you a part of this conversation?
		if (strlen(trim($_POST['textarea_message'])) > MAXPOSTLENGTH) $check[] = 'Sorry, the message must be less than '.MAXPOSTLENGTH.' characters.';
		if (strlen(trim($_POST['textarea_message'])) < MINPOSTLENGTH) $check[] = 'Sorry, the message must be more than '.MINPOSTLENGTH.' characters.';
		$part = mysql_single('
			SELECT pmid FROM pm WHERE pmtextid = \''.mysql_real_escape_string((int)$_POST['pm']).'\'
			',__LINE__.__FILE__);
		if (!$part->pmid) $check[] = 'Sorry, you are not (or are no longer) part of this conversation.';
		
		$pm = mysql_single('
			SELECT pmtextid FROM pmtext 
			WHERE pmtextid = \''.mysql_real_escape_string((int)$_POST['pm']).'\' 
			',__LINE__.__FILE__);
		if (!$pm->pmtextid) $check[] = 'This conversation does not exist.';
		
		if (!$check) {
			//create the message
			mysql_query('
				INSERT INTO pmnode SET 
				pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
				,userid = \''.mysql_real_escape_string($session->userid).'\'
				,message = \''.mysql_real_escape_string($_POST['textarea_message']).'\'
				,html = \''.mysql_real_escape_string($bbcode->parse($_POST['textarea_message'])).'\'
				,dateline = \''.mysql_real_escape_string($time = time()).'\'
				') or die(__LINE__.__FILE__.mysql_error());
            
            $pmnodeid = mysql_insert_id();
			
			//notify all other users
			$parts = mysql_single('
				SELECT GROUP_CONCAT(userid SEPARATOR \',\') AS userids FROM pm 
				WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\' 
				AND userid != \''.mysql_real_escape_string($session->userid).'\'
				',__LINE__.__FILE__);
            
			if ($parts->userids) {
				//update the participant table (pm) and update the users
				//don't update total pm's! total pm's is conversation participant total
				mysql_query('
					UPDATE pm SET 
					messageread = 0,
					unread = unread + 1
					WHERE userid IN ('.mysql_real_escape_string($parts->userids).')
					AND pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\';
					') or die(__LINE__.__FILE__.mysql_error());
				
				#notify('conversation',$pm->pmtextid);
				/*
					UPDATE user SET pmunread = pmunread + 1
					WHERE userid IN ('.mysql_real_escape_string($parts->userids).')
				*/
                
				updateConversationCount($parts->userids);
			}
			mysql_query('
				UPDATE pmtext SET lastpm = \''.mysql_real_escape_string($time).'\' WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
		}
		
		if ($_POST['ajax']) {
            //echo $_GET['participant'];
            $json = new stdClass();
            $json->pm = (int)$pm->pmtextid;
            $json->id = (int)$pmnodeid;
            $json->errors = $check;
            $json->type = $_POST['type'];
            $json->do = $_POST['do'];
            echo json_encode($json);
            exit;
        }
	}
}

if (($_GET['do'] == 'delete' || $_POST['do'] == 'delete') && $session->userid) {
    
	if ($_POST['type'] == 'message' && $_POST['pmnodeid'] > 0) {
        $check = array();
		//check you have permission to delete this node
		$pmn = mysql_single('
			SELECT userid,pmtextid,pmnodeid FROM pmnode WHERE pmnodeid = \''.mysql_real_escape_string((int)$_POST['pmnodeid']).'\'
			'.($session->staff ? '':'AND userid = \''.mysql_real_escape_string($session->userid).'\'').'
			',__LINE__.__FILE__);
		if (!$pmn->pmnodeid) $check[] = 'You don\'t have the necessary permissions to delete this node.'; 	
		
		if (!$check) {
			mysql_query('
				DELETE FROM pmnode WHERE pmnodeid = \''.mysql_real_escape_string((int)$_POST['pmnodeid']).'\'
				') or die(__LINE__.__FILE__.mysql_error());
		}
		//$pmtextid = $_POST['pm'] ? (int)$_POST['pm'] : '';
        if ($_POST['ajax']) {
            $json = new stdClass();
            $json->errors = $check;
            $json->type = $_POST['type'];
            $json->do = $_POST['do'];
            $json->id = (int)$_POST['pmnodeid'];
            $json->pm = $pmn->pmtextid;
            echo json_encode($json);
            exit;
        }
	}
	
	if ($_POST['type'] == 'conversation' && $_POST['pm'] > 0) {
		//check you are allowed to delete this conversation
		$pm = mysql_single('
			SELECT pmtextid,title FROM pmtext 
			WHERE pmtextid = \''.mysql_real_escape_string($_POST['pm']).'\'
			'.($session->staff ? '':'AND fromuserid = \''.mysql_real_escape_string($session->userid).'\'').'
			',__LINE__.__FILE__);
		if (!$pm->pmtextid) $check[] = 'You are not allowed to delete this conversation.';
		//if the conversation exists and you created it or are staff
		if (!$check) {
			//remove the conversation
			mysql_query('
				DELETE FROM pmtext WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
				') or die(__LINE__.__FILE__.mysql_error());
			
			$parts = mysql_single('
				SELECT GROUP_CONCAT(userid SEPARATOR \',\') AS users FROM pm WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\' 
				',__LINE__.__FILE__);
			/*$result = mysql_query('
				SELECT userid FROM pm WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\' 
				') or die(__LINE__.__FILE__.mysql_error());
			while ($part = mysql_fetch_object($result)) {
				mysql_query('
					UPDATE user SET
					pmtotal = pmtotal - 1
					'.(!$part->messageread ? ',pmunread = pmunread - 1':'').'
					WHERE userid = \''.mysql_real_escape_string($part->userid).'\'
					') or die(__LINE__.__FILE__.mysql_error());
			}*/
			//remove the participants, remove the nodes
			mysql_query('
				DELETE FROM pm WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\';
				') or die(__LINE__.__FILE__.mysql_error());
				
			mysql_query('
				DELETE FROM pmnode WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\';
				') or die(__LINE__.__FILE__.mysql_error());
			
			
			updateConversationCount($parts->userids);
			$message = 'The conversation: '.$pm->title.' was deleted successfully.';
		}
		
        if ($_POST['ajax']) {
            $json = new stdClass();
            $json->errors = $check;
            $json->type = $_POST['type'];
            $json->do = $_POST['do'];
            $json->pm = (int)$pm->pmtextid;
            echo json_encode($json);
            exit;
        }
        
		$pmtextid = '';
	}
	
	if ($_GET['type'] == 'participant' && $_GET['participant'] > 0) {
		if ($_GET['pm'] > 0) {
		//check that you are the conversation starter or staff
			$pm = mysql_single('
				SELECT pmtextid FROM pmtext 
				WHERE pmtextid = \''.mysql_real_escape_string((int)$_GET['pm']).'\' 
				'.($session->staff ? '':'AND fromuserid = \''.mysql_real_escape_string($session->userid).'\'').'
				',__LINE__.__FILE__);
				
			if (!$pm->pmtextid) $check[] = 'You are not allowed to manage participants in this conversation.';
		}
		
		//and that the user was actually a participant
		$part = mysql_single('
			SELECT userid FROM pm WHERE pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\'
			AND userid = \''.mysql_real_escape_string($_GET['participant']).'\'
			',__LINE__.__FILE__);
		
		if (!$part->userid) $check[] = 'The user you\'re trying to remove wasn\'t a participant in this conversation.';
		
		if (!$check && $pm->pmtextid) {
			mysql_query('
				DELETE FROM pm 
				WHERE userid = \''.mysql_real_escape_string($part->userid).'\' 
				AND pmtextid = \''.mysql_real_escape_string($pm->pmtextid).'\';
				') or die(__LINE__.__FILE__.mysql_error());
			/* UPDATE user SET 
				pmtotal = pmtotal - 1
				WHERE userid = \''.mysql_real_escape_string($part->userid).'\'; */
			updateConversationCount($part->userid);
		}
		$pmtextid = $pm->pmtextid ? $pm->pmtextid : '';
	}
}

if (!$session->userid) $check[] = 'You must be logged in to do this!';

if (!$_REQUEST['ajax']) {
	$query = array();
	if ($pmtextid) $query[] = 'pm='.$pmtextid;
	if ($check) $query[] = 'check='.urlencode(serialize($check));
	if ($message) $query[] = 'message='.urlencode($message);
	
}

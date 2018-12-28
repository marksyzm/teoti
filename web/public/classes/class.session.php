<?php
/*
  Session class by Stephen McIntyre
  http://stephenmcintyre.net
*/
class Session
{
  private $id = '';
  private $alive = true;
  private $dbc = NULL;
 
  function __construct()
  {
    session_set_save_handler(
      array(&$this, 'open'),
      array(&$this, 'close'),
      array(&$this, 'read'),
      array(&$this, 'write'),
      array(&$this, 'destroy'),
      array(&$this, 'clean'));
 
    session_start();
  }
 
  function __destruct()
  {
    if($this->alive)
    {
      session_write_close();
      $this->alive = false;
    }
  }
 
  function delete()
  {
    if(ini_get('session.use_cookies'))
    {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
      );
    }
 
    session_destroy();
 
    $this->alive = false;

    return true;
  }
 
  private function open()
  {    
    $this->dbc = new MYSQLi(HOST, DBUSERNAME, DBPASSWORD, DBNAME) OR die('Could not connect to database.');
    return true;
  }
 
  private function close()
  {
    return !!$this->dbc->close();
  }
 
  private function read($sid)
  {
    $this->id = $sid;
 
    $q = "SELECT `data` FROM `sessions` WHERE `id` = '".$this->dbc->real_escape_string($sid)."' LIMIT 1";
    $r = $this->dbc->query($q);
 
    if($r->num_rows == 1)
    {
      $fields = $r->fetch_assoc();
 
      return $fields['data'];
    }
    else {
      return '';
    }
  }
 
  private function write($sid, $data)
  {
  	
    $json = json_encode($_SESSION);
    $q = "
    REPLACE INTO `sessions` (`id`, `data`, `json`, `username`) VALUES (
      '".$this->dbc->real_escape_string($sid)."',
      '".$this->dbc->real_escape_string($data)."',
      '".$this->dbc->real_escape_string($json)."',
      '".$this->dbc->real_escape_string($_SESSION['uid'])."'
    )";

    $this->dbc->query($q);
    return !!$this->dbc->affected_rows;
  }
 
  private function destroy($sid)
  {
    $q = "DELETE FROM `sessions` WHERE `id` = '".$this->dbc->real_escape_string($sid)."'"; 
    $this->dbc->query($q);
 
    $_SESSION = array();
 
    return !!$this->dbc->affected_rows;
  }
 
  private function clean($expire = 0)
  {
  	if (!$expire) $expire = ini_get('session.gc_maxlifetime');
    $q = "DELETE FROM `sessions` WHERE DATE_ADD(`lastaccessed`, INTERVAL ".(int) $expire." SECOND) < NOW()"; 
    $this->dbc->query($q);
 
    return !!$this->dbc->affected_rows;
  }
}
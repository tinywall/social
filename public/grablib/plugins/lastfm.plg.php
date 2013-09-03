<?php
$_pluginInfo=array(
	'name'=>'Last.fm',
	'version'=>'1.0.5',
	'description'=>"Get the contacts from a Last.fm account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.last.fm',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * LastFm Plugin
 * 
 * Import user's contacts from Last.fm AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class lastfm extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	private $sess_id, $username, $siteAddr;
	
	public $debug_array=array(
			  'login_post'=>'logout',
			  'friends_url'=>'username'
	);
	
	/**
	 * Login function
	 * 
	 * Makes all the necessary requests to authenticate
	 * the current user to the server.
	 * 
	 * @param string $user The current user.
	 * @param string $pass The password for the current user.
	 * @return bool TRUE if the current user was authenticated successfully, FALSE otherwise.
	 */
	public function login($user,$pass)
		{
		$this->resetDebugger();
		$this->service='lastfm';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$post_elements=array("username"=>"{$user}",
							"password"=>"{$pass}",
							"backto"=>urldecode("http%3A%2F%2Fwww.last.fm%2Flogin%2FsuccessCallback"));
		$res=$this->post("https://www.last.fm/login?lang=&withsid",$post_elements,true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"https://www.last.fm/login?lang=&withsid",'POST');		
		else
			{
			$this->updateDebugBuffer('login_post',"https://www.last.fm/login?lang=&withsid",'POST',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$this->login_ok = "http://www.last.fm/inbox/compose";
		return true;		
		}
	
	/**
	 * Get the current user's contacts
	 * 
	 * Makes all the necesarry requests to import
	 * the current user's contacts
	 * 
	 * @return mixed The array if contacts if importing was successful, FALSE otherwise.
	 */	
	public function getMyContacts()
		{
		if (!$this->login_ok)
			{
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		else $url=$this->login_ok;
		$res=$this->get($url,true);
		$contacts=array();
		if (preg_match_all("#\"r4\_(.+)\"\:\{\"username\"\:\"(.+)\"#U",$res,$matches))
			{
			if (!empty($matches[1]))
				foreach($matches[1] as $key=>$id)  
					if (!empty($matches[2][$key])) $contacts["r4_{$id}"]=$matches[2][$key];
			}
		return $contacts;
		}
		
	/**
	 * Send message to contacts
	 * 
	 * Sends a message to the contacts using
	 * the service's inernal messaging system
	 * 
	 * @param string $cookie_file The location of the cookies file for the current session
	 * @param string $message The message being sent to your contacts
	 * @param array $contacts An array of the contacts that will receive the message
	 * @return mixed FALSE on failure.
	 */	
	public function sendMessage($session_id,$message,$contacts)
		{
		$res = $this->get("http://www.last.fm/inbox/compose");
		$postelem = $this->getHiddenElements($res);
		$postelem['to']="";
		$postelem['subject']=$message['subject'];
		$postelem['body']=$message['body'];
		$countMessages=0;
		foreach ($contacts as $id => $username)
			{
			$countMessages++;
			$postelem['to_ids%5B%5D'] = $id;
			$res = $this->post('http://www.last.fm/inbox/compose',$postelem, true, true);
			sleep($this->messageDelay);
			if ($countMessages>$this->maxMessages) {$this->debugRequest();$this->resetDebugger();$this->stopPlugin();break;}
			}
		}

	/**
	 * Terminate session
	 * 
	 * Terminates the current user's session,
	 * debugs the request and reset's the internal 
	 * debudder.
	 * 
	 * @return bool TRUE if the session was terminated successfully, FALSE otherwise.
	 */	
	public function logout()
		{
		if (!$this->checkSession()) return false;
		$logout_url = "http://www.last.fm/login/logout";
		$res = $this->get($logout_url);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
}
?>
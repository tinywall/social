<?php
$_pluginInfo=array(
	'name'=>'Tagged',
	'version'=>'1.1.0',
	'description'=>"Get the contacts from a Tagged.com account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.tagged.com/home.html',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Tagged Plugin
 * 
 * Import user's contacts from a Tagged Account
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class tagged extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	protected $timeout=30;
	
	public $debug_array=array(
			  'login_post'=>'http://www.tagged.com/home.html?jli=1',
			  'redirect'=>'http://www.tagged.com/logout.html',
			  'contacts'=>'name',
			  'message'=>'recipientId'
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
		$this->service='tagged';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res = $this->get("http://www.tagged.com");
		$postAction = "https://secure.tagged.com/secure_login.html?r=%2Fhome.html&uri=http%3A%2F%2Fwww.tagged.com";
		$postElem = array();
		$postElem['username'] = $user;
		$postElem['password'] = $pass;
		$res = $this->post($postAction, $postElem, true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',$postAction,'POST',true,$postElem);		
		else
			{
			$this->updateDebugBuffer('login_post',$postAction,'POST',false,$postElem);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$res = $this->get("http://www.tagged.com/home.html?jli=1");
		if ($this->checkResponse("redirect",$res))
			$this->updateDebugBuffer('redirect',"http://www.tagged.com/home.html?jli=1",'GET');		
		else
			{
			$this->updateDebugBuffer('redirect',"http://www.tagged.com/home.html?jli=1",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_get_friends='http://www.tagged.com/messages.html?action=compose';
		$this->login_ok=$url_get_friends;
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
		else $url = $this->login_ok;
		$res=$this->get($url);
		if ($this->checkResponse("contacts",$res))
			$this->updateDebugBuffer('contacts',$url,'GET');		
		else
			{
			$this->updateDebugBuffer('contacts',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$contacts = array();
		if (preg_match_all("#\{\"name\"\:\"(.+)\"\,\"id\"\:(.+)\}#U", $res, $matches))
			{
			if (!empty($matches[1]))
				foreach($matches[1] as $key=>$value)
					if (!empty($matches[2][$key])) $contacts[$matches[2][$key]]=$value;
			}
			
		reset($contacts);$firstKey=key($contacts);
		if (preg_match("#\[\{\"name\"\:\"(.+)\"\,\"id\"\:(.+)\}#U", $res, $matches)) $contacts[$firstKey]=$matches[1];
		else unset($contacts[$firstKey]);
				
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
		$countMessages=0;
		foreach ($contacts as $id=>$username)
			{
			$countMessages++;
			$form_action="http://www.tagged.com/messages.html?action=compose&rid={$id}";			
			$post_elements=array('recipient_id'=>$id,
								 'subject'=>$message['subject'],
								 'mce_editor_0_fontSizeSelect'=>0,
								 'entryText'=>$message['body'],
								 'from'=>'compose',
								 'section'=>'send',
								 'message_type'=>'N',
								 'came_from_url'=>'http://www.tagged.com/messages.html',
								 'save_sent'=>'save',
								 'action'=>'sendMessage',
								 'skip_confirmation'=>1,
								 'ajax_sent'=>1,
								 'recipient_id_hidden'=>0,
								 'on_success_action'=>'call_function|complete_sendMsg()'
								);
			$res=$this->post($form_action,$post_elements,true);
			if (strpos($res,'captcha')) break;
			if ($this->checkResponse("message",$res))
				$this->updateDebugBuffer('message',$form_action,'POST',true,$post_elements);		
			else
				{
				$this->updateDebugBuffer('message',$form_action,'POST',false,$post_elements);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
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
		$logout_url = "http://www.tagged.com/logout.html";
		$res = $this->get($logout_url);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
}
?>
<?php
$_pluginInfo=array(
	'name'=>'NetLog',
	'version'=>'1.0.7',
	'description'=>"Get the contacts from a NetLog account And Shout a message to your friends",
	'base_version'=>'1.8.3',
	'type'=>'social',
	'check_url'=>'http://en.netlog.com/m/login',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Netlog Plugin
 * 
 * Import Friends from Netlog
 * You can Shouts Messages to your friends using Netlog system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class netlog extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	protected $timeout=30;
	protected $userAgent='Mozilla/4.1 (compatible; MSIE 5.0; Symbian OS; Nokia 3650;424) Opera 6.10  [en]';
		
	public $debug_array=array(
			  'initial_get'=>'target',
			  'login_post'=>'messages',
			  'get_friends'=>'option',
			  'send_message'=>'success',
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
		$this->service='netlog';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res=$this->get('http://en.netlog.com/m/login');
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://en.netlog.com/m/login",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://en.netlog.com/m/login",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}  

		$form_action="http://en.netlog.com/m/login";
		$post_elements=array('action'=>'login','target'=>$this->getElementString($res,'name="target" value="','"'),'nickname'=>$user,'password'=>$pass);
		$res=$this->post($form_action,$post_elements,true,true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$this->login_ok="http://en.netlog.com/m/messages/send";
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
		$res=$this->get($url);
		if ($this->checkResponse("get_friends",$res))
			$this->updateDebugBuffer('get_friends',$url,'GET');
		else
			{
			$this->updateDebugBuffer('get_friends',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//option";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$user=$node->getAttribute('value');$name=$node->nodeValue;
			if (strpos($name,'Album >')===false) if (!empty($user)) $contacts[$user]=$name;
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
		$countMessages=0;
		foreach($contacts as $user=>$name)
			{
			$countMessages++;
			$form_action="http://en.netlog.com/m/shouts/add";
			$post_elements=array('nickname'=>$user,'action'=>'__button','shout'=>$message['body'],'__btaAddShout'=>'Shout');
			$res=$this->post($form_action,$post_elements,true,true);
			if (strpos($res,'warning')===false)
				if ($this->checkResponse("send_message",$res))
					$this->updateDebugBuffer('send_message',"{$form_action}",'POST',true,$post_elements);
				else
					{
					$this->updateDebugBuffer('send_message',"{$form_action}",'POST',false,$post_elements);
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
		$logout_url = "http://en.netlog.com/m/login/action=logout";
		$res = $this->get($logout_url);
		$this->debugRequest();
		$this->resetDebugger();
 		$this->stopPlugin();
		}
	}
?>
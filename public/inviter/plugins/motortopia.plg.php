<?php
/*Import Friends from Motortopia
 * You can Write Private Messages using Motortopia system
 */
$_pluginInfo=array(
	'name'=>'Motortopia',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from a Motortopia account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.motortopia.com/main/cars',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Motortopia Plugin
 * 
 * Import Friends from Motortopia
 * You can Write Private Messages using Motortopia system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class motortopia extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'email',
				'login_post'=>'logout',
				'get_friends'=>'Go to this person',
				'send_message'=>'inbox'
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
		$this->service='motortopia';
		$this->service_user=$user;
		$this->service_password=$pass;	
		if (!$this->init()) return false;
		
		$res=$this->get("http://www.motortopia.com/main/cars",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.motortopia.com/en/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.motortopia.com/en/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://www.motortopia.com/user/login/main/y";
		$post_elements=array('email'=>$user,
							 'password'=>$pass,
							 'submit'=>'Login',
							 'token'=>$this->getElementString($res,'name="token" value="','"')
							);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$url_friends='http://www.motortopia.com/friend/browse/u/'.$this->getElementString($res,'/friend/browse/u/','"');
		$this->login_ok=$url_friends;
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
		$names_array=$this->getElementDOM($res,"//dd[@class='allHead']");
		if (!empty($names_array))
			foreach($names_array as $key=>$value)  $contacts[$value]=$value;
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
		foreach($contacts as $name)
			{			
			$countMessages++;
			$form_action="http://www.motortopia.com/mail/send/{$name}";
			$post_elements=array('subject'=>$message['subject'],'body'=>$message['body'],'submit'=>'Send');
			$res=$this->post($form_action,$post_elements,true);
						
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
		$res=$this->get("http://www.motortopia.com/user/logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
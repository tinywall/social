<?php
/*Import Friends from mydogspace
 * You can Write Private Messages using mydogspace system
 */
$_pluginInfo=array(
	'name'=>'Mydogspace',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a mydogspace account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.mydogspace.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * Mydogspace Plugin
 * 
 * Import Friends from Mydogspace.com
 * You can Write Private Messages using Mydogspace system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class mydogspace extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'username',
				'login_post'=>'/account/logout',
				'get_friends'=>'receiver_id',
				'send_message'=>'was sent'
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
		$this->service='mydogspace';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://www.mydogspace.com/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.mydogspace.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.mydogspace.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$form_action="http://www.mydogspace.com/account/login";
		$post_elements=array('username'=>$user,'password'=>$pass,'x'=>rand(1,100),'y'=>rand(1,100));
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
		$url_friends='http://www.mydogspace.com/email/compose';
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
		$res=$this->get($url,true);
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
		$names_array=$this->getElementDOM($res,'//option');
		$ids_array=$this->getElementDOM($res,'//option','value');
		foreach($ids_array as $key=>$value)
			if (!empty($value)) $contacts[$value]=$names_array[$key];
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
		foreach($contacts as $id=>$name)
			{
			$countMessages++;
			$form_action="http://www.mydogspace.com/email/compose";
			$post_elements=array('email[receiver_id]'=>$id,
								 'email[subject]'=>$message['subject'],
								 'email[content]'=>$message['body'],
								 'x'=>rand(1,100),
								 'y'=>rand(1,100)
								);
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
		$res=$this->get("http://www.mydogspace.com/account/logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
<?php
/*Import Friends from Faces
 * You can Write Private Messages using Faces system
 */
$_pluginInfo=array(
	'name'=>'Faces',
	'version'=>'1.0.6',
	'description'=>"Get the contacts from a Faces account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.faces.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * Faces Plugin
 * 
 * Import Friends from Faces
 * You can Write Private Messages using Faces system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class faces extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'loginName',
				'login_post'=>'logout',
				'get_friends'=>'name',
				'send_message'=>'Inbox'
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
		$this->service='faces';
		$this->service_user=$user;
		$this->service_password=$pass;	
		if (!$this->init()) return false;
		
		$res=$this->get("http://www.faces.com/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.faces.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.faces.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://www.faces.com/login";
		$post_elements=array('loginName'=>$user,
							 'password'=>$pass,
							 'action'=>'log in'
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
		$url_friends='http://www.faces.com/friends';
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
		$names_array=$this->getElementDOM($res,"//a[@class='name']");
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
		$form_action="http://www.faces.com/mailbox/compose";
		$post_elements=array('scoop'=>'scoop',
							'subject'=>$message['subject'],
							'message'=>$message['body'],
							'send'=>'Send'
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
		$res=$this->get("http://www.faces.com/logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
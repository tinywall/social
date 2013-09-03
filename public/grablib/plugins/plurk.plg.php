<?php
$_pluginInfo=array(
	'name'=>'Plurk',
	'version'=>'1.0.7',
	'description'=>"Get the contacts from a Plurk account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.plurk.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Plurk Plugin
 * 
 * Imports user's contacts from Plurk and
 * .
 * 
 * @author OpenInviter
 * @version 1.0.3
 */
class plurk extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=false;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'nick_name',
				'login'=>'user_id',
				'get_contacts'=>'nick_name',
				'send_message'=>'"error": null'
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
		$this->service='plurk';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
			
		$res=$this->get("http://www.plurk.com/");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.plurk.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.plurk.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$form_action='http://www.plurk.com/Users/login';
		$post_elements=array('nick_name'=>$user,'password'=>$pass);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse("login",$res))
			$this->updateDebugBuffer('login',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login',$form_action,'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$user_id=$this->getElementString($res,'"user_id": ',',');		
		$this->login_ok=$is->login_ok=$user_id;
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
		else $user_id=$this->login_ok;
		$url_request_friends="http://www.plurk.com/Friends/getFriendsByOffset";
		$post_elements=array('offset'=>0,'user_id'=>$user_id);
		$res=$this->post($url_request_friends,$post_elements,true);
		if ($this->checkResponse("get_contacts",$res))
			$this->updateDebugBuffer('get_contacts',$url_request_friends,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('get_contacts',$url_request_friends,'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$contacts=array();
		while(strpos($res,'"nick_name": "')!==false)
			{
			$name=$this->getElementString($res,'"nick_name": "','"');
			$name_delete='"nick_name": "'.$name;
			$uid=$this->getElementString($res,'"uid": ',',');
			$uid_delete='"uid": '.$uid;
			$res=str_replace($name_delete,'',str_replace($uid_delete,'',$res));
			if(isset($uid)) $contacts[$uid]=(isset($name)?$name:false);
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
		$res=$this->get('http://www.plurk.com',true);
		$user_id=$this->getElementString($res,'"user_id": ',',');
		$form_action="http://www.plurk.com/TimeLine/addPlurk";
		$post_elements=array('posted'=>'%222009-1-12T14%3A18%3A30%22',
							'qualifier'=>'is',
							'content'=>$message['body'],
							'lang'=>'en',
							'no_comments'=>0,
							'uid'=>$user_id
							);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse("send_message",$res))
			$this->updateDebugBuffer('send_message',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('send_message',$form_action,'POST',false,$post_elements);
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
		$res=$this->get("http://www.plurk.com/Users/logout",true); 
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
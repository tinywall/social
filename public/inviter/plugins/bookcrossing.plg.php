<?php
$_pluginInfo=array(
	'name'=>'Bookcrossing',
	'version'=>'1.0.4',
	'description'=>"Get your frineds from a bookcrossing.com account and sends private messages",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.bookcrossing.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * bookcrossing.com Plugin
 * 
 * Imports user's friends from bookcrossing.com's 
 * 
 * @author OpenInviter
 * @version 1.0.3
 */
class bookcrossing extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'submitLoginForm',
				'login_post'=>'Welcome',
				'get_friends'=>'tiny',
				'send_message'=>'Sent'
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
		$this->service='bookcrossing';
		$this->service_user=$user;
		$this->service_password=$pass;
	
		if (!$this->init()) return false;
		$res=$this->get('http://bookcrossing.com/login',true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://bookcrossing.com/login",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://bookcrossing.com/login",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action='http://bookcrossing.com/login';
		$post_elements=array('action'=>'submitLoginForm','currentaction'=>'login','email'=>$user,'password'=>$pass);
		$res=$this->post("http://bookcrossing.com/action.htm",$post_elements,true,true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$this->login_ok="http://bookcrossing.com/friends";
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
		$xpath=new DOMXPath($doc);$query="//td[@class='tiny'][@align='left']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=$node->childNodes->item(1)->nodeValue;
			if (!empty($name)) $contacts[$name]=$name;
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
		foreach($contacts as $name)
			{
			$countMessages++;
			$form_action="http://bookcrossing.com/sendmessage/{$name}";
			$post_elements=array('sysSubmitButton'=>'Send Now','action'=>'submitSendMessage','messageto'=>$name,'subject'=>$message['subject'],'message'=>$message['body']);
			$res=$this->post($form_action,$post_elements,true,true);
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
		$res=$this->get("http://bookcrossing.com/logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}?>

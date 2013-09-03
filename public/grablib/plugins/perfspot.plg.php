<?php
/*Import Friends from Perfspot
 * You can send private message using Perfspot system to your Friends
 */
$_pluginInfo=array(
	'name'=>'Perfspot',
	'version'=>'1.0.7',
	'description'=>"Get the contacts from a Perfspot account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://m.perfspot.com/index.asp',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Perfspot Plugin
 * 
 * Imports user's contacts from Perfspot and send messages
 * using Perfspot's internal system
 * 
 * @author OpenInviter
 * @version 1.0.6
 */
class perfspot extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'txtEmail',
				'post_login'=>'accesskey="7"',
				'url_menu'=>'class="name"',
				'url_friend'=>'accesskey="4"',
				'url_send_message'=>'Title',
				'send_message'=>'color: Red;'
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
		$this->service='perfspot';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
	
		$res=$this->get("http://m.perfspot.com/index.asp");
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://m.perfspot.com/index.asp",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://m.perfspot.com/index.asp",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$form_action="http://m.perfspot.com/index.asp";
		$post_elements=array('txtEmail'=>$user,
							 'txtPassword'=>$pass,
							 'LoginButton'=>'Login'
							);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse('post_login',$res))
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$url_menu_array=$this->getElementDOM($res,"//a[@accesskey='7']",'href');
		$url_menu="http://m.perfspot.com/".$url_menu_array[0];
		$this->login_ok=$url_menu; 
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
		
		$url_contacts_array=$this->getElementDOM($res,"//a[@accesskey='3']",'href');
		$url_contacts="http://m.perfspot.com/".$url_contacts_array[0];
		$res=$this->get($url_contacts);
		if ($this->checkResponse('url_menu',$res))
			$this->updateDebugBuffer('url_menu',$url,'GET');
		else 
			{
			$this->updateDebugBuffer('url_menu',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
				
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//a[@class='name']";$data=$xpath->query($query);
		foreach($data as $node)
			$contacts[$node->getAttribute('href')]=$node->nodeValue; 
		return $contacts;
		}

	/**
	 * Send message to contacts
	 * 
	 * Sends a message to the contacts using
	 * the service's inernal messaging system
	 * 
	 * @param string $session_id The OpenInviter user's session ID
	 * @param string $message The message being sent to your contacts
	 * @param array $contacts An array of the contacts that will receive the message
	 * @return mixed FALSE on failure.
	 */
	public function sendMessage($session_id,$message,$contacts)
		{
		$countMessages=0;
		foreach($contacts as $href=>$name)
			{
			$countMessages++;
			$url_friend=html_entity_decode("http://m.perfspot.com/{$href}");
			$res=$this->get($url_friend);
			if ($this->checkResponse('url_friend',$res))
				$this->updateDebugBuffer('url_friend',$url_friend,'GET');
			else 
				{
				$this->updateDebugBuffer('url_friend',$url_friend,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;	
				}
			
			$url_message_array=$this->getElementDOM($res,"//a[@accesskey='4']",'href');
			$url_message="http://m.perfspot.com/".$url_message_array[0];
			$res=$this->get($url_message);
			if ($this->checkResponse('url_send_message',$res))
				$this->updateDebugBuffer('url_send_message',$url_message,'GET');
			else 
				{
				$this->updateDebugBuffer('url_send_message',$url_message,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;	
				}
			$form_action="http://m.perfspot.com/".$this->getElementString($res,'action="','"');
			$post_elements=array('Title'=>$message['subject'],'txtMessage'=>$message['body'],'btnSend'=>'Send');
			$res=$this->post($form_action,$post_elements,true);
			if ($this->checkResponse('send_message',$res))
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
		$res=$this->get("http://m.perfspot.com/index.asp?mode=logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
			
		}
	}

?>

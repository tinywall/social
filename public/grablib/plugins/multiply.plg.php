<?php
/*Import Friends from Multiply
 * You can Write Private Messages using Multiply system
 */
$_pluginInfo=array(
	'name'=>'Multiply',
	'version'=>'1.0.5',
	'description'=>"Get the contacts from a Multiply account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://multiply.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * Multiply Plugin
 * 
 * Import Friends from Multiply
 * You can Write Private Messages using Multiply system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class multiply extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'splashcontainer',
				'login_post'=>'logout',
				'get_friends'=>'contactbox',
				'url_send_message'=>'form::subject',
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
		$this->service='multiply';
		$this->service_user=$user;
		$this->service_password=$pass;	
		if (!$this->init()) return false;
		
		$res=$this->get("http://multiply.com/",true);		
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.multiply.com/en/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.multiply.com/en/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://multiply.com/user/signin";
		$post_elements=$this->getHiddenElements($res);$post_elements['signin::signin_id']=$user;$post_elements['signin::password']=$pass;$post_elements['signin::remember']='on';$post_elements['omniture_submission']='submitted';
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
		
		$url_contacts="http://{$user}.multiply.com/contacts";
		$this->login_ok=$url_contacts;
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
		$xpath=new DOMXPath($doc);$query="//div[@class='contactbox']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$href=$node->firstChild->getAttribute('href');
			$name=trim($this->getElementString($href,'http://','.multiply.com'));
			if (!empty($href)) $contacts[$href]=utf8_decode($name);
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
		foreach($contacts as $href=>$name)
			{	
			$countMessages++;	
			$send_message_url="http://multiply.com/compose/pm?individual={$name}";
			$res=$this->get($send_message_url,true);
			if ($this->checkResponse("url_send_message",$res))
				$this->updateDebugBuffer('url_send_message',$send_message_url,'GET');
			else
				{
				$this->updateDebugBuffer('url_send_message',$send_message_url,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			
			$form_action="http://multiply.com/compose/pm";
			$post_elements=$this->getHiddenElements($res);$post_elements['form::subject']=$message['subject'];$post_elements['form::body']=$message['body'];$post_elements['form::Send Personal Message']='  Send Personal Message  ';
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
		$res=$this->get("http://multiply.com/user/signout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
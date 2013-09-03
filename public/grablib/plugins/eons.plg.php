<?php
/*Import Friends from eons
 * You can Write Private Messages using Brazencareerist system
 */
$_pluginInfo=array(
	'name'=>'Eons',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Eons account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.eons.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Eons Plugin
 * 
 * Import Friends from Eons
 * You can Write Private Messages using Eons system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class eons extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'account[username]',
				'login_post'=>'com/logout',
				'url_home'=>'/members/friends/',
				'get_friends'=>'cont-image member-card',
				'send_message'=>'sent'
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
		$this->service='eons';
		$this->service_user=$user;
		$this->service_password=$pass;
	
		if (!$this->init()) return false;

		$res=$this->get("http://www.eons.com/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.eons.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.eons.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="https://www.eons.com/login";
		$post_elements=array('resource'=>'http://www.eons.com/','account[username]'=>$user,'account[password]'=>$pass,'permanent'=>1,'commit'=>'Log in');
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
			
		$url_home="http://www.eons.com/my_eons";
		$res=$this->get($url_home,true);
		if ($this->checkResponse("url_home",$res))
			$this->updateDebugBuffer('url_home',$url_home,'GET');
		else
			{
			$this->updateDebugBuffer('url_home',$url_home,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_friends='http://www.eons.com/members/friends/'.$this->getElementString($res,'/members/friends/','"');
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
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//div[@class='cont-image member-card']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=$node->childNodes->item(3)->childNodes->item(1)->nodeValue;
			if (!empty($name)) $contacts[$name]=!empty($name)?$name:false;
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
			$form_action="http://www.eons.com/messages/write";
			$post_elements=array('ref'=>"http://www.eons.com/members/profile/{$name}",
								'msg[recipient_name]'=>$name,
								'msg[subject]'=>$message['subject'],
								'msg[body]'=>$message['body'],
								'commit'=>'Send Message'
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
		$res=$this->get("https://www.eons.com/logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
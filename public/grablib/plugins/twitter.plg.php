<?php
$_pluginInfo=array(
	'name'=>'Twitter',
	'version'=>'1.1.1',
	'description'=>"Get the contacts from a Twitter account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://twitter.com',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * Twitter Plugin
 * 
 * Imports user's contacts from Twitter and
 * posts a new tweet from the user as a invite.
 * 
 * @author OpenInviter
 * @version 1.0.3
 */
class twitter extends OpenInviter_Base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $requirement='user';
	public $internalError=false;
	public $allowed_domains=false;
	protected $timeout=30;
	protected $maxUsers=100;
	
	public $debug_array=array(
				'initial_get'=>'username',
				'login_post'=>'inbox',
				'friends_url'=>'list-tweet',
				'wall_message'=>'latest_text',
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
		$this->service='twitter';
		$this->service_user=$user;
		$this->service_pass=$pass;
		if (!$this->init()) return false;
		$res=$this->get("https://mobile.twitter.com/session/new",true);
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"https://mobile.twitter.com/session/new",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"https://mobile.twitter.com/session/new",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();	
			return false;
			}
		
		$form_action="https://mobile.twitter.com/session";
		$post_elements=array('authenticity_token'=>$this->getElementString($res,'name="authenticity_token" type="hidden" value="','"'),'username'=>$user,'password'=>$pass);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse('login_post',$res))
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();	
			return false;
			}				
		$this->login_ok="http://mobile.twitter.com/{$user}/followers";
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
		if ($this->checkResponse('friends_url',$res))
			$this->updateDebugBuffer('friends_url',"{$url}",'GET');
		else 
			{
			$this->updateDebugBuffer('friends_url',"{$url}",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();	
			return false;
			}	
		$contacts=array();$countUsers=0;		
		do
			{			
			$nextPage=false;
			$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
			$xpath=new DOMXPath($doc);
			$query="//a[@name]";$data=$xpath->query($query);
			foreach ($data as $node)
				{
				$user=(string)$node->getAttribute("name");
				if (!empty($user)) {$contacts[$countUsers]=$user; $countUsers++; }									
				}			
			$query="//div[@class='list-more']/a";$data=$xpath->query($query);
			foreach($data as $node) { $nextPage=$node->getAttribute("href");break; }					
			if ($countUsers>$this->maxUsers) break; 
			if (!empty($nextPage)) $res=$this->get('http://mobile.twitter.com'.$nextPage);			
			}
		while ($nextPage);			
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
		$countMessages=0;$res=$this->get("http://mobile.twitter.com");$auth=$this->getElementString($res,'name="authenticity_token" type="hidden" value="','"');
		
		$form_action="http://mobile.twitter.com";
		$post_elements=array("authenticity_token"=>$auth,'tweet[text]'=>$message['body'],'tweet[in_reply_to_status_id]'=>false,'tweet[lat]'=>false,'tweet[long]'=>false,'tweet[place_id]'=>false,'tweet[display_coordinates]'=>false);		
		$res=$this->post($form_action,$post_elements,true);					
		
		foreach($contacts as $screen_name)
			{
			$countMessages++;$form_action='http://mobile.twitter.com/inbox';						
			$post_elements=array('authenticity_token'=>$auth,'message[text]'=>$message['body'],'message[recipient_screen_name]'=>$screen_name,'return_to'=>false,);
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
	 * 
	 */	
	public function logout()
		{
		if (!$this->checkSession()) return false;
		$this->get("http://twitter.com/logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
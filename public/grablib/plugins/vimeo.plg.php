<?php
/*Import Friends from Vimeo
 * You can Post Messages using Vimeo system
 */
$_pluginInfo=array(
	'name'=>'Vimeo',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from a Vimeo account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://vimeo.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Vimeo Plugin
 * 
 * Import user's contacts from Vimeo and Post comments
 * using Vimeo's internal Posting  system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class vimeo extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'sign_in[email]',
				'login_post'=>'user',
				'url_home'=>'contacts',
				'get_friends'=>'username',
				'url_send_message'=>'MemoContent',
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
		$this->service='vimeo';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://vimeo.com/log_in");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://vimeo.com/log_in",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://vimeo.com/log_in",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://vimeo.com/log_in";
		$post_elements=array('sign_in[email]'=>$user,'sign_in[password]'=>$pass);
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
			
		$url_profile='http://vimeo.com/';
		$this->login_ok=$url_profile;
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
		if ($this->checkResponse("url_home",$res))
			$this->updateDebugBuffer('url_home',$url,'GET');
		else
			{
			$this->updateDebugBuffer('url_home',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$user_id=$this->getElementString($res,'http://vimeo.com/user','/');
		$url_contacts="http://vimeo.com/user{$user_id}/contacts";
		$res=$this->get($url_contacts);
		if ($this->checkResponse("get_friends",$res))
			$this->updateDebugBuffer('get_friends',$url_contacts,'GET');
		else
			{
			$this->updateDebugBuffer('get_friends',$url_contacts,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//a[@class='username']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=$node->nodeValue;
			$id=str_replace('/user','',(string)$node->getAttribute('href'));
			if (!empty($name)) $contacts[$id]=$name;
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
		foreach($contacts as $id=>$name)
			{
			$countMessages++;
			$form_action="http://vimeo.com/ajax/conversation/send_message";
			$post_elements=array('message'=>$message['body'],'jdata'=>'{"user_id":'.$id.',"layout":"private"}');
			$res=$this->post($form_action,$post_elements);
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
		$res=$this->get("http://vimeo.com/log_out");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
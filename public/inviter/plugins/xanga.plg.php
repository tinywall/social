<?php
$_pluginInfo=array(
	'name'=>'Xanga',
	'version'=>'1.0.6',
	'description'=>"Get the contacts from a Xanga account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.xanga.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * Xanga Plugin
 * 
 * Import user's contacts from Xanga and send 
 * messages using the internal messaging system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class xanga extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'txtSigninPassword',
				'post_login'=>'home.aspx',
				'get_friends'=>'thumbnail',
				'url_send_message'=>'messagesubject',
				'send_message'=>'private'
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
		$this->service='xanga';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://www.xanga.com/signin.aspx?ReturnUrl=http%3a%2f%2fwww.xanga.com%2fdefault.aspx");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://hk.xanga.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://hk.xanga.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://www.xanga.com/signin.aspx?ReturnUrl=http%3a%2f%2fwww.xanga.com%2fdefault.aspx";
		$post_elements=$this->getHiddenElements($res);
		$post_elements['txtSigninUsername']=$user;$post_elements['txtSigninPassword']=$pass;$post_elements['cmbNetwork']=1;$post_elements['signInButton']='Sign In';
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse("post_login",$res))
			$this->updateDebugBuffer('post_login',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('post_login',$form_action,'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$url_friends="http://www.xanga.com/private/homemain.aspx";
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
		$contacts=array();
		if ($this->checkResponse("get_friends",$res))
			$this->updateDebugBuffer('get_friends',$url,'GET');
		else
			{
			$this->updateDebugBuffer('get_friends',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//a[@class='thumbnail']";$data=$xpath->query($query);
		foreach($data as $node)
			if (strpos($node->getAttribute('title'),'Visit')!==false)
				{
				$href=$node->getAttribute('href');
				if (!empty($href)) $name=$this->getElementString($href,'//','.');
				if (!empty($name)) $contacts[$href]=!empty($name)?$name:false;
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
			$url_send_message="http://www.xanga.com/message.aspx?user={$name}";
			$res=$this->get($url_send_message,true);
			if ($this->checkResponse("url_send_message",$res))
				$this->updateDebugBuffer('url_send_message',$url_send_message,'GET');
			else
				{
				$this->updateDebugBuffer('url_send_message',$url_send_message,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}		
			$form_action=$url_send_message;
			$post_elements=$this->getHiddenElements($res);$post_elements['messagesubject']=$message['subject'];$post_elements['messagetext']=$message['body'];$post_elements['btnSubmit']='Submit';
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
		$res=$this->get("http://www.xanga.com/logout.aspx",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
<?php
/*Import Friends from Bebo
 * You can send message to your Bebo Inbox
 */
$_pluginInfo=array(
	'name'=>'Bebo',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Bebo account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.bebo.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * Bebo Plugin
 * 
 * Import user's contacts from Bebo and send 
 * messages using the internal messaging system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class bebo extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'EmailUsername',
				'login_post'=>'top.location.replace',
				'url_friends'=>'height=90',
				'url_send_message'=>'SendTo',
				'send_message'=>'message has been sent'
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
		$this->service='bebo';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res=$this->get("http://www.bebo.com/");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.bebo.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.bebo.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="https://secure.bebo.com/SignIn.jsp";
		$post_elements=array('EmailUsername'=>$user,
							'Password'=>$pass,
							'SignIn'=>'Sign In >'
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
		$url_friends='http://www.bebo.com/MyFriends.jsp';
		
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
		if ($this->checkResponse("url_friends",$res))
			$this->updateDebugBuffer('url_friends',$url,'GET');
		else
			{
			$this->updateDebugBuffer('url_friends',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//img[@height='90']";$data=$xpath->query($query);
		foreach ($data as $node) $contacts[$node->parentNode->getAttribute('href')]=$node->parentNode->nodeValue;
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
			$url_send_message='http://www.bebo.com/mail/MailCompose.jsp?ToMemberId='.str_replace("Profile.jsp?MemberId=",'',$href);
			$res=$this->get($url_send_message,true);
			if ($this->checkResponse("url_send_message",$res))
				$this->updateDebugBuffer('url_send_message',"shttp://www.bebo.com/",'GET');
			else
				{
				$this->updateDebugBuffer('url_send_message',"http://www.bebo.com/",'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			$form_action=$url_send_message;
			$post_elements=array('SendTo'=>'M'.str_replace("Profile.jsp?MemberId=",'',$href),
								 'Subject'=>$message['subject'],
								 'Message'=>$message['body'],
								 'Send'=>' Send ',
								 'MailSkinId'=>$this->getElementString($res,"MailSkinId value=",' '),
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
		$res=$this->get("http://www.bebo.com/c/account/sign_out");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
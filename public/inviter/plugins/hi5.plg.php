<?php
$_pluginInfo=array(
	'name'=>'Hi5',
	'version'=>'1.1.7',
	'description'=>"Get the contacts from a Hi5 account",
	'base_version'=>'1.6.7',
	'type'=>'social',
	'check_url'=>'http://www.hi5.com',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Hi5 Plugin
 * 
 * Imports user's contacts from Hi5 and sends messages
 * using Hi5's internal messaging system.
 * 
 * @author OpenInviter
 * @version 1.0.4
 */
class hi5 extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=50;
	
	public $debug_array=array(
				'initial_get'=>'setRequestId',
				'login_post'=>'friends',
				'url_friends'=>'friend-name',
				'url_message'=>'toIds',
				'send_message'=>'reqs'
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
		$this->service='hi5';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://hi5.com/friend/displayHomePage.do");		
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.hi5.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.hi5.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$form_action="http://www.hi5.com/friend/login.do";
		$post_elements=array(
							'email'=>$user,
							'password'=>$pass,
							'remember'=>'on'
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
		$url_friends="http://www.hi5.com/friend/viewFriends.do?abredirect=true";
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
		$contacts=array();$mail_contacts=array();$url_next=false;
		if ($this->checkResponse("url_friends",$res))
			$this->updateDebugBuffer('url_friends',$url,'GET');
		else
			{
			$this->updateDebugBuffer('url_friends',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}	
		$nr_of_friends=(int)$this->getElementString($res,'id="pagination-number">','<');$page=20;
		do
			{
			$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
			$xpath=new DOMXPath($doc);$query="//div[@class='friend-name']";$data=$xpath->query($query);$id=false;
			foreach ($data as $node)
				{
				$name=$node->childNodes->item(1)->nodeValue;$href=$node->childNodes->item(1)->getAttribute('href');$id=$this->getElementString($href,'p','-');
				if (!empty($id)) $contacts[$id]=!empty($name)?$name:false;
				}
			$url_next_array=$this->getElementDOM($res,"//a[@class='link_pagination_arrow']",'href');
			if (!empty($url_next_array[0])) 
				{$url_next=$this->getElementString($url_next_array[0],'/','offset=')."offset={$page}";$page+=20;$res=$this->get("http://hi5.com/{$url_next}",true);}
			else $id=false;		
			}
		while($id);
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
			$url_send_message="http://hi5.com/friend/mail/displayComposeMail.do?toIds={$id}";
			$res=$this->get($url_send_message);
			if ($this->checkResponse("url_message",$res))
				$this->updateDebugBuffer('url_message',$url_send_message,'GET');
			else
				{
				$this->updateDebugBuffer('url_message',$url_send_message,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			$form_action="http://hi5.com/friend/mail/sendMail.do";
			$post_elements=array('toIds'=>$this->getElementString($res,"idToName['","'"),
								 'subject'=>$message['subject'],
								 'method'=>'send',
								 'body'=>$message['body'],
								 'timestamp'=>$this->getElementString($res,'name="timestamp" value="','"'),
								 'mailOp'=>'',
								 'senderId'=>'',
								 'msgId'=>'',
								 'submitSend'=>'Send Message'
								);
			$res=$this->post($form_action,$post_elements,true);
			if ($this->checkResponse("send_message",$res))
				$this->updateDebugBuffer('send_message',$url_send_message,'POST',true,$post_elements);
			else
				{
				$this->updateDebugBuffer('send_message',$url_send_message,'POST',false,$post_elements);
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
		$res=$this->get("http://hi5.com/friend/logoff.do",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
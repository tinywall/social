<?php
/*Import Friends from Hyves
 * You can Post Messages using Hyves system
 */
$_pluginInfo=array(
	'name'=>'Hyves',
	'version'=>'1.1.8',
	'description'=>"Get the contacts from a Hyves account",
	'base_version'=>'1.8.1',
	'type'=>'social',
	'check_url'=>'http://www.hyves.nl/?l1=mo',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * http://hyves.net/ Plugin
 * 
 * Import user's contacts from Hyves and send private messages
 * using Hyves system
 * 
 * @author OpenInviter
 * @version 1.1.2
 */
class hyves extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'domainname',
				'url_login'=>'auth_username',
				'login_post'=>'global_member_username',
				'url_profile'=>'listitem',
				'get_friends'=>'memberlistname',
				'url_friend'=>'accesskey="2"',
				'url_send_message'=>'postman',
				'send_message'=>'color: green;'
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
		$this->service='hyves';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://www.hyves.nl/?l1=mo");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.hyves.nl/?l1=mo",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.hyves.nl/?l1=mo",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}			
		$form_action=html_entity_decode($this->getElementString($res,'form class="form" action="','"'));
		$post_elements=$this->getHiddenElements($res);$post_elements['auth_username']=$user;$post_elements['auth_password']=$pass;$post_elements['login']='Login';
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
			
		$url_logout='http://www.hyves.nl/?module=authentication&action=logoutMobile'.html_entity_decode($this->getElementString($res,"?module=authentication&amp;action=logoutMobile",'"'));
		$url_friends="http://www.hyves.nl/mini/hyver/{$user}/friends";
		$this->login_ok=$url_friends;
		file_put_contents($this->getLogoutPath(),$url_logout);
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
		$contacts=array();$hasContacts=true;$page=0;
		while ($hasContacts)
			{
			$hasContacts=false;
			$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
			$xpath=new DOMXPath($doc);$query="//a[@class='memberlistname'][not(contains(@href,'".$this->service_user."'))]";$data=$xpath->query($query);
			foreach($data as $node) 
				{$href=$node->getAttribute('href');$name=(string)$node->nodeValue;if (!empty($name)) $name=$name[0].$this->getElementString($name,$name[0],'(');if (!empty($href)) {$contacts[$href]=!empty($name)?utf8_decode($name):false;$hasContacts=true;}}
			$page=$page+10;
			$urlNext="http://www.hyves.nl/mini/hyver/{$this->service_user}/friends/?&startpos={$page}";
			$res=$this->get($urlNext,true);
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
			$res=$this->get($href,true);
			if ($this->checkResponse("url_friend",$res))
				$this->updateDebugBuffer('url_friend',$href,'GET');
			else
				{
				$this->updateDebugBuffer('url_friend',$href,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			
			$url_send_message_array=$this->getElementDOM($res,"//a[@accesskey='2']",'href');
			$url_send_message='http://www.hyves.nl'.urldecode($url_send_message_array[0]);
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
			$form_action="http://www.hyves.nl/?l1=mo&l2=mb&l3=hm&l4=sendi";
			$post_elements=array(
								'postman'=>'Message/send',
								'postman_secret'=>$this->getElementString($res,'postman_secret" value="','"'),
								'sitepositionurl'=>$this->getElementString($res,'name="sitepositionurl" value="','"'),
								'sendmessage_to'=>$this->getElementString($res,'sendmessage_to" value="','"'),
								'sendmessage_subject'=>$message['subject'],
								'sendmessage_body'=>$message['body'],
								'sendmessage_type'=>2,
								'bsend'=>'send'
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
		if (file_exists($this->getLogoutPath()))
			{
			$url_logout=file_get_contents($this->getLogoutPath());
			$res=$this->get($url_logout,true);
			}
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
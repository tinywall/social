<?php
/*Import Friends from Mevio
 * You can Send Private Messages using mevio system
 */
$_pluginInfo=array(
	'name'=>'Mevio',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Mevio account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.mevio.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Mvio Plugin
 * 
 * Import user's contacts from Mevio and send Private messages
 * using  Mevio system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class mevio extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	
	public $debug_array=array(
				'initial_get'=>'username',
				'login_post'=>'activePersona=',
				'url_home'=>'selected nobg',
				'get_friends'=>'laminate-std-name',
				'url_friend'=>'personaId',
				'url_send_message'=>'subject',
				'send_message'=>'message sent'
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
		$this->service='mevio';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://www.mevio.com/login/#loginOverlay",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://us.cyworld.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://us.cyworld.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://www.mevio.com/login/#loginOverlay";
		$post_elements=array('username'=>$user,'password'=>$pass,'LoginSubmit'=>'Log In');
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
			
		$url_profile='http://www.mevio.com/mc/digs/?control=Digs&action=editDigs&digMode=entourage&activePersona='.$this->getElementString($res,'http://www.mevio.com/mc/digs/?control=Digs&action=editDigs&digMode=entourage&activePersona=','"');
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
		$xpath=new DOMXPath($doc);$query="//div[@class='laminate-std-name']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=$node->childNodes->item(1)->nodeValue;
			$href=$node->childNodes->item(1)->getAttribute('href');
			if (!empty($href)) $contacts[$href]=$name;
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
			$friend_id=$this->getElementString($res,'"personaId":"','"');
			$url_send_message='http://www.mevio.com/u2u/?class=NewTextMessage&method=auto_compose&to_select='.$friend_id;
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
							
			$form_action="http://www.mevio.com/u2u/?class=NewTextMessage&method=send";
			$post_elements=array( 'to_select[]'=>$friend_id,
								  'subject'=>$message['subject'],
								  'body'=>$message['body'],
								  'response'=>'message_center'
								  );
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
		$res=$this->get("http://www.mevio.com/login/?mode=logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
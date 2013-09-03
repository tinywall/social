<?php
/*Import Friends from Skyrock
 * You can send private message using Skyrock system to your Friends
 */
$_pluginInfo=array(
	'name'=>'Skyrock',
	'version'=>'1.0.8',
	'description'=>"Get the contacts from a Skyrock account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.skyrock.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * Skyrock Plugin
 * 
 * Imports user's contacts from Skyrock and send messages
 * using Skyrock's internal system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class skyrock extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;	
	public $internalError=false;
	protected $timeout=30;
		
	public $debug_array=array(
				'initial_get'=>'need_login_form_login',
				'login_post'=>'logout',
				'url_friends'=>'bouton edit',
				'url_send_message'=>'id_dest',
				'send_message'=>'confirmation'
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
		$this->service='skyrock';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://www.skyrock.com");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.skyrock.com",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.skyrock.com",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://www.skyrock.com/";
		$post_elements=array('need_login_form_login'=>$user,
							'need_login_form_password'=>$pass,
							'x'=>rand(0,20),
							'y'=>rand(0,20),
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
		
		$url_friends="http://www.skyrock.com/m/friends/";
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
		$page=0;$hasFriends=true;
		while($hasFriends)
			{
			$page++;$message_array=array();
			$names_array=$this->getElementDOM($res,"//ul[@class='friends_list']/li",'title');
			$message_array=$this->getElementDOM($res,"//a[@class='bouton edit']",'href');
			if (empty($message_array)) $hasFriends=false;
			if (!empty($names_array)) foreach($names_array as $key=>$value) $contacts[$message_array[$key]]=$value;
			$url_next="http://www.skyrock.com/m/friends/?order=1&page={$page}";
			$res=$this->get($url_next,true);
			}
		
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
			$url_send_message=html_entity_decode("http://www.skyrock.com{$href}");
			$res=$this->get($url_send_message);
			if ($this->checkResponse("url_send_message",$res))
				$this->updateDebugBuffer('url_send_message',$url_send_message,'GET');
			else
				{
				$this->updateDebugBuffer('url_send_message',$url_send_message,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			
			$form_action="http://www.skyrock.com/m/messages/write_message.php";
			$post_elements=array('id_dest'=>$this->getElementString($res,'name="id_dest" value="','"'),
								 'sendMe'=>$this->getElementString($res,'sendMe" value="','"'),
								 'posted'=>TRUE,
								 'message_zone'=>$message['body'], 
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
		$res=$this->get("http://www.skyrock.com/m/account/logout.php");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
			
		}
	}

?>

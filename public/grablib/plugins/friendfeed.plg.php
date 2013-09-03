<?php
/*Import Friends from Friendfeed
 * You can POST message to your Friends
 */
$_pluginInfo=array(
	'name'=>'Friendfeed',
	'version'=>'1.0.5',
	'description'=>"Get the contacts from a Friendfeed account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'https://friendfeed.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * FaceBook Plugin
 * 
 * Imports user's contacts from FaceBook and sends
 * messages using FaceBook's internal system.
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class friendfeed extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
		
	public $debug_array=array(
				'initial_get'=>'email',
				'login_post'=>'Photos',
				'friends'=>'class="picture medium"',
				'url_home'=>'share',
				'send_message'=>'sid='
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
		$this->service='friendfeed';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("https://friendfeed.com/account/login",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"https://friendfeed.com/account/login",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"https://friendfeed.com/account/login",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action="https://friendfeed.com/account/login";
		$post_elements=$this->getHiddenElements($res);
		$post_elements["email"]=$user;$post_elements["password"]=$pass;
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
		
		$url_friends="https://friendfeed.com/friends";
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
		if ($this->checkResponse("friends",$res))
			$this->updateDebugBuffer('friends',$url,'GET');
		else
			{
			$this->updateDebugBuffer('friends',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$contacts=array();
		$contacts=$this->getElementDOM($res,"//img[@class='picture large']",'alt');
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
		foreach($contacts as $name)
			{
			$countMessages++;
			$res=$this->get("http://friendfeed.com/filter/direct?dm={$name}",true);
			if ($this->checkResponse('url_home',$res))
				$this->updateDebugBuffer('url_home',"http://friendfeed.com/",'GET');
			else
				{
				$this->updateDebugBuffer('url_home',"http://friendfeed.com/",'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
				
			$form_action="http://friendfeed.com/a/share";
			$post_elements=array('streams'=>$name,
								 'direct_view'=>1,
								 'title'=>$message['body'],
								 'at'=>$this->getElementString($res,'name="at" value="','"'),
								 'maybetweet'=>0,
								 '_nano'=>1
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
		$res=$this->get("http://friendfeed.com/account/logout",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
<?php
/*Import Friends from Flixster
 * You can Post Messages using Flixster system
 */
$_pluginInfo=array(
	'name'=>'Flixster',
	'version'=>'1.0.7',
	'description'=>"Get the contacts from a Flixster account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.flixster.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Flixster Plugin
 * 
 * Import user's contacts from Flixster and Post comments
 * using Flixster's internal Posting  system
 * 
 * @author OpenInviter
 * @version 1.0.4
 */
class flixster extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'userauthAction',
				'login_post'=>'TalkCommentPopupLink',
				'get_friends'=>'username',
				'url_friends'=>'addProfileComment=&friendsUserId',
				'send_message'=>'comment'
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
		$this->service='flixster';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://www.flixster.com/friends");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.flixster.com/friends",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.flixster.com/friends",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action="http://www.flixster.com/userAuth.do";
		$post_elements=array("userauthAction"=>"doLogin",
							 "redirectTarget"=>"/friends",
							 "userauthEmail"=>$user,
							 "userauthPassword"=>$pass,
							 "userauthRemember"=>"on",
							 "submit"=>"Login to Flixster &gt;"
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
		
		$url_friend=explode("@",$user);
		$url_friends="http://www.flixster.com/user/{$url_friend[0]}/friends";

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
		$xpath=new DOMXPath($doc);$query="//a[@class='username']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=str_replace("...","",$node->nodeValue);
			$id=$node->getAttribute('href');
			if (!empty($name)) $contacts[$id]=!empty($name)?$name:false;
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
			$res=$this->get("http://www.flixster.com{$id}");
			if ($this->checkResponse("url_friends",$res))
				$this->updateDebugBuffer('url_friends',"http://www.flixster.com{$id}",'GET');
			else
				{
				$this->updateDebugBuffer('url_friends',"http://www.flixster.com{$id}",'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			$user_id=$this->getElementString($res,'addProfileComment=&friendsUserId=','"');
			$form_action="http://www.flixster.com/talk.do ";
			$post_elements=array(
								'talkAction'=>'addProfileComment',
								'talkAnchor'=>'comments',
								'friendsUserId'=>$user_id,
								'comment'=>$message['body'],
								'submit'=>'Send'
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
		$res=$this->get("http://www.flixster.com/userAuth.do?userauthAction=doLogout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
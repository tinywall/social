<?php
/*Import Friends from Flingr
 * You can Write Private Messages using Flingr system
 */
$_pluginInfo=array(
	'name'=>'Flingr',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Flingr account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.flingr.com/index.php',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Flingr Plugin
 * 
 * Import Friends from Flingr
 * You can Write Private Messages using Flingr system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class flingr extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'email',
				'login_post'=>'logout',
				'get_friends'=>'friend-actions',
				'send_message'=>'UserID'
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
		$this->service='flingr';
		$this->service_user=$user;
		$this->service_password=$pass;	
		if (!$this->init()) return false;
		
		$res=$this->get("http://www.flingr.com/index.php",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.flingr.com/index.php",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.flingr.com/index.php",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://www.flingr.com/login.php";
		$post_elements=array('email'=>$user,
							 'password'=>$pass,
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
		$url_friends='http://www.flingr.com/friends/action/viewall/UserID/'.$this->getElementString($res,'http://www.flingr.com/friends/action/viewall/UserID/','/').'/';
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
		$xpath=new DOMXPath($doc);$query="//span[@class='friend-actions']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=trim((string)$node->childNodes->item(0)->nodeValue);
			$id=str_replace('/','',str_replace('http://www.flingr.com/friends/action/remove/UserID/','',(string)$node->childNodes->item(1)->childNodes->item(0)->getAttribute('href')));
			if (!empty($id)) $contacts[$id]=(!empty($name)?$name:false);
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
			$form_action="http://www.flingr.com/messages/action/send/UserID/{$id}/";
			$post_elements=array('Title'=>$message['subject'],
								'editor'=>$message['body'],
								'submit'=>'Send Message'
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
		$res=$this->get("http://www.flingr.com/login/action/logout/");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
<?php
/*Import Friends from brazencareerist
 * You can Write Private Messages using Brazencareerist system
 */
$_pluginInfo=array(
	'name'=>'Brazencareerist',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from a Brazencareerist account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.brazencareerist.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Brazencareerist Plugin
 * 
 * Import Friends from Brazencareerist
 * You can Write Private Messages using Brazencareerist system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class brazencareerist extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'form_build_id',
				'login_post'=>'logout',
				'get_friends'=>'field-content',
				'url_send_message'=>'recipients',
				'send_message'=>'from-view-message'
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
		$this->service='brazencareerist';
		$this->service_user=$user;
		$this->service_password=$pass;
	
		if (!$this->init()) return false;

		$res=$this->get("http://www.brazencareerist.com/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.brazencareerist.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.brazencareerist.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://www.brazencareerist.com/";
		$post_elements=array('name'=>$user,
							'pass'=>$pass,
							'form_build_id'=>$this->getElementString($res,'name="form_build_id" id="','"'),
							'form_id'=>'user_login',
							'op'=>'Log in',
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
		$url_fans='http://www.brazencareerist.com/profile/'.$this->getElementString($res,'href="/profile/','"')."/fans";
		$this->login_ok=$url_fans;
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
		$xpath=new DOMXPath($doc);$query="//span[@class='field-content']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=$node->nodeValue;
			if (!empty($name)) $contacts[$name]=$name;
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
		foreach($contacts as $name)
			{
			$countMessages++;
			$url_send_message="http://www.brazencareerist.com/node/add/pm";
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
			
			$form_action="http://www.brazencareerist.com/node/add/pm";
			$post_elements=$this->getHiddenElements($res);
			$post_elements['recipients']=$name;$post_elements['title']=$message['subject'];$post_elements['body']=$message['body'];$post_elements['op']='Send private message';$post_elements['parent']=0;
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
		$res=$this->get("http://www.brazencareerist.com/logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
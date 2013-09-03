<?php
/*Import Friends from Cyworld
 * You can Post Messages using Cyworld system
 */
$_pluginInfo=array(
	'name'=>'Cyworld',
	'version'=>'1.0.7',
	'description'=>"Get the contacts from a Cyworld account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://us.cyworld.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Cyworld Plugin
 * 
 * Import user's contacts from Flixster and Post comments
 * using Flixster's internal Posting  system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class cyworld extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	
	public $debug_array=array(
				'initial_get'=>'Cyworld',
				'login_post'=>'MyHompy_GSP',
				'url_home'=>'selected nobg',
				'get_friends'=>'friend_message_link',
				'url_send_message'=>'MemoContent',
				'send_message'=>'replace'
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
		$this->service='cyworld';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://us.cyworld.com/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://us.cyworld.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://us.cyworld.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://us.cyworld.com/common/include/login_check_proc.php";
		$post_elements=array("txtEmail"=>$user,
							"txtPassword"=>$pass,
							"hidReturnURL"=>'/',
							"c.x"=>rand(1,20),
							"c.y"=>rand(1,20),
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
			
		$res=$this->get('http://us.cyworld.com/');
		if ($this->checkResponse("url_home",$res))
			$this->updateDebugBuffer('url_home',"http://us.cyworld.com/",'GET');
		else
			{
			$this->updateDebugBuffer('url_home',"http://us.cyworld.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$user=$this->getElementString($res,'&sign=http://us.cyworld.com/','&');
		$url_profile="http://us.cyworld.com/{$user}/friend";
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
		$xpath=new DOMXPath($doc);$query="//a[@id='friend_message_link']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$href=$node->getAttribute('href');$name=$this->getElementString($href,'/','/');$href='http://us.cyworld.com'.$href;
			if ((!empty($name)) and (!empty($href))) $contacts[$href]=$name;
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
			$url_send_message="{$href}/message/send_message";
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
			
			$form_action="http://us.cyworld.com/main/memo_send_proc.php";
			$post_elements=$this->getHiddenElements($res);$post_elements['MemoContent']=$message['body'];
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
		$res=$this->get("http://us.cyworld.com/common/include/logout_proc.php");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
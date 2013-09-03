<?php
/*Import Friends from vkontakte.ru
 * You can Post Messages using Vkontakte system
 */
$_pluginInfo=array(
	'name'=>'Vkontakte',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from a Vkontakte.ru account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://vkontakte.ru',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Cyworld Plugin
 * 
 * Import user's contacts from vkontakte.ru and Sends private messages
 * using vkontakte.ru internal Posting  system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class vkontakte extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'email',
				'login_post'=>'myfriends',
				'get_friends'=>'friendOrder',
				'url_send_message'=>'title',
				'send_message'=>'<div id="message">'
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
		$this->service='vkontakte';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://vkontakte.ru/");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://vkontakte.ru/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://vkontakte.ru/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://vkontakte.ru/login.php";
		$post_elements=array("try_to_login"=>1,"email"=>$user,"pass"=>$pass);
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
			
		$url_friends="http://vkontakte.ru/".$this->getElementString($res,"myfriends'><a href='","'");		
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
		while (strpos($res,'list:[[')!==false)
			{
			$countact_bulk=$this->getElementString($res,'list:[[','}');
			$id=substr($countact_bulk,0,strpos($countact_bulk,','));
			$names_array=explode("'",$countact_bulk);
			if (isset($id))
				$contacts[$id]=(isset($names_array[1])?$names_array[1]:false)." ".(isset($names_array[3])?$names_array[3]:false);
			$res=str_replace("list:[[".$countact_bulk,"",$res);
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
			$url_send_message="http://vkontakte.ru/mail.php?act=write&to={$id}";
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
			
			$form_action="http://vkontakte.ru/mail.php";
			$post_elements=$this->getHiddenElements($res);$post_elements['title']=$message['subject'];$post_elements['message']=$message['body'];
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
		$res=$this->get("http://vkontakte.ru/login.php?op=logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
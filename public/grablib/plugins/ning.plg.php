<?php
/*Import Friends from ning
 * You can Update your status using ning system
 */
$_pluginInfo=array(
	'name'=>'Ning',
	'version'=>'1.0.1',
	'description'=>"Get the contacts from a ning account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.ning.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Ning Plugin
 * 
 * Import Friends from Ning
 * You can Write Private Messages using Brazencareerist system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class ning extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'signin_password',
				'login_post'=>'xn_signout',
				'get_friends'=>'tb',
				'send_message'=>'200 '
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
		$this->service='ning';
		$this->service_user=$user;
		$this->service_password=$pass;
	
		if (!$this->init()) return false;

		$res=$this->get("http://www.ning.com/main/signin",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.brazencareerist.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.brazencareerist.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="https://www.ning.com/main/signin?area=System_SignIn";
		$post_elements=array('target'=>'http://www.ning.com/',
							'emailAddress'=>$user,
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
		$url_friends='http://www.ning.com/'.$this->getElementString($res,'id="xn_username">','<')."/friends";
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
		$xpath=new DOMXPath($doc);$query="//div[@class='tb']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=$node->childNodes->item(1)->nodeValue;
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
		$res=$this->get("http://www.ning.com");
		$form_action="http://www.ning.com/main/ajax?area=Status";
		$post_elements=array("status"=>$message['body'],'xp_token'=>$this->getElementString($res,'"xp_token" value="','"'));
		$res=$this->post($form_action,$post_elements,true);
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
		$res=$this->get("http://www.ning.com/main/signout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
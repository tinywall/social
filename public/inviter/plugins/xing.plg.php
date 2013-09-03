<?php
$_pluginInfo=array(
	'name'=>'Xing',
	'version'=>'1.0.7',
	'description'=>"Get the contacts from a Xing account",
	'base_version'=>'1.8.0',
	'type'=>'email',
	'check_url'=>'https://mobile.xing.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Xing Plugin
 * 
 * Import user's contacts from Xing and send 
 * messages using the internal messaging system
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class xing extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'login_user_name',
				'post_login'=>'contact',
				'get_friends'=>'user-name',				
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
		$this->service='xing';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("https://www.xing.com/");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"https://mobile.xing.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"https://mobile.xing.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$form_action="https://www.xing.com/app/user";
		$post_elements=array('op'=>'login',
							 'dest'=>'https://www.xing.com/',
							 'login_user_name'=>$user,
							 'login_password'=>$pass,
							 'sv-remove-name'=>'Log in'					
							);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse("post_login",$res))
			$this->updateDebugBuffer('post_login',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('post_login',$form_action,'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
					
		$url_adressbook='https://www.xing.com/app/contact';		
		$this->login_ok=$url_adressbook;
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
		$contacts=array();
		if ($this->checkResponse("get_friends",$res))
			$this->updateDebugBuffer('get_friends',$url,'GET');
		else
			{
			$this->updateDebugBuffer('get_friends',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);
		$query="//a[@class='user-name']";$data=$xpath->query($query);
		foreach ($data as $node) $users[$node->getAttribute('href')]=$node->nodeValue;
		if (!empty($users))
			foreach($users as $profileLink=>$name)
				{
				$res=$this->get('https://www.xing.com'.$profileLink,true);				
				$mails=$this->getElementDOM($res,"//a[@class='url']");
				if (!empty($mails[0])) $contacts[$mails[0]]=array('email_1'=>$mails[0],'first_name'=>$name);
				}
		foreach ($contacts as $email=>$name) if (!$this->isEmail($email)) unset($contacts[$email]);
		return $this->returnContacts($contacts);				
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
		$res=$this->get("https://www.xing.com/app/user?op=logout",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
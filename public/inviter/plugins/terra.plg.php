<?php
$_pluginInfo=array(
	'name'=>'Terra',
	'version'=>'1.0.8',
	'description'=>"Get the contacts from an Terra account",
	'base_version'=>'1.6.7',
	'type'=>'email',
	'check_url'=>'http://correo.terra.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Terra Plugin
 * 
 * Imports user's contacts from Terra.com
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class terra extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array('initial_get'=>'username',
			  				  'post_login'=>'location.href',
			  				  'url_post_redirect'=>'td',
			  				  'file_contacts'=>'Users["'
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
	public function login($user, $pass)
		{
		$this->resetDebugger();
		$this->service='terra';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://correo.terra.com/");
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://correo.terra.com/",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://correo.terra.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$form_action="http://correo.terra.com/atmail.php";
		$post_elements=array('username'=>$user,'password'=>$pass);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse('post_login',$res))
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$url_redirect=$this->getElementString($res,"href='","'");
		$domain=$this->getElementString($res,'http://correo.terra.com/','/showmail');
		$res=$this->get($url_redirect,true);
		if ($this->checkResponse('url_post_redirect',$res))
			$this->updateDebugBuffer('url_post_redirect',"{$url_redirect}",'GET');
		else 
			{
			$this->updateDebugBuffer('url_post_redirect',"{$url_redirect}",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$url_file_contacts="http://correo.terra.com/{$domain}/abook.php?func=composebook&emailto=&emailcc=&emailbcc=";	
		$this->login_ok=$url_file_contacts;
		file_put_contents($this->getLogoutPath(),$domain);
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
		if ($this->checkResponse('file_contacts',$res))
			$this->updateDebugBuffer('file_contacts',$url,'GET');
		else 
			{
			$this->updateDebugBuffer('file_contacts',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$contacts=array();
		if (preg_match_all("#Users\[\"(.+)\"\] \= \'(.+) \&lt\;#U", $res, $matches))
			if (!empty($matches[1]))
				foreach($matches[1] as $key=>$email)
					$contacts[$email]=array('first_name'=>(!empty($matches[2][$key])?$matches[2][$key]:false),'email_1'=>$email);
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
		if (file_exists($this->getLogoutPath()))
			{ $domain=file_get_contents($this->getLogoutPath());$res=$this->get("http://correo.terra.com{$domain}/index.php?func=logout",true); }
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		}
	}
?>
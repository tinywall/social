<?php
$_pluginInfo=array(
	'name'=>'Rambler',
	'version'=>'1.1.7',
	'description'=>"Get the contacts from a Rambler account",
	'base_version'=>'1.8.3',
	'type'=>'email',
	'check_url'=>'http://www.rambler.ru',
	'requirement'=>'email',
	'allowed_domains'=>array('/(rambler.ru)/i'),
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Rambler Plugin
 * 
 * Import user's contacts from Rambler AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.9
 */
class rambler extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'login',
				'login_post'=>'ramac_add_handler',
				'url_contacts'=>'email'
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
		$this->service='rambler';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
				
		$res=$this->get("http://www.rambler.ru/",true);
		
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.rambler.ru/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.rambler.ru/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$post_elements=$this->getHiddenElements($res);$post_elements['login']=$user;$post_elements['passw']=$pass; 
		unset($post_elements[0]); 
		$res=$this->post("http://id.rambler.ru/script/auth.cgi",$post_elements,true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"http://id.rambler.ru/script/auth.cgi",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',"http://id.rambler.ru/script/auth.cgi",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_contact_array=$this->getElementDOM($res,"//a[@id='addressbook-link']",'href');
		$value=substr($url_contact_array[0],strpos($url_contact_array[0],"r=")+2,strlen($url_contact_array[0])-strpos($url_contact_array[0],"r=")-2);
		$url_contact="http://mail.rambler.ru/mail/contacts.cgi?r={$value}";
		$this->login_ok=$url_contact;
		file_put_contents($this->getLogoutPath(),$value);
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
		if ($this->checkResponse("url_contacts",$res))
			$this->updateDebugBuffer('url_contacts',$url,'GET');
		else
			{
			$this->updateDebugBuffer('url_contacts',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$contacts=array();
		$emailsArray=$this->getElementDOM($res,"//td[@class='mtbox-cell mtbox-email quiet']");
		$namesArray=$this->getElementDOM($res,"//a[@class='mtbox-action mtbox-view']");		
		if (!empty($emailsArray))
			foreach($emailsArray as $key=>$emailValue) $contacts[trim($emailValue)]=array('first_name'=>(isset($namesArray[$key])?trim($namesArray[$key]):false),'email_1'=>trim($emailValue));			
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
			{
			$url_logout="http://id.rambler.ru/script/auth.cgi?back=;mode=logout;r=".file_get_contents($this->getLogoutPath());
			$res=$this->get($url_logout,true);
			}
		$this->debugRequest();			
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
	}	
?>
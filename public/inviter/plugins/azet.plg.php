<?php
$_pluginInfo=array(
	'name'=>'Azet',
	'version'=>'1.0.5',
	'description'=>"Get the contacts from a Azet account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://emailnew.azet.sk/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Azet Plugin
 * 
 * Imports user's contacts from Azet AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.1
 */
class azet extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'heslo',
				'login_post'=>'OtvorPomoc',
				'url_contacts'=>'adr_mail'				
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
		$this->service='azet';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
					
		$res=$this->get("http://emailnew.azet.sk/");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://emailnew.azet.sk/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://emailnew.azet.sk/",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action=$this->getElementString($res,'action="','"');
		$post_elements=array('form[username]'=>$user,
							 'form[password]'=>$pass,
							 'Posliform'=>urldecode('Prihl%C3%A1si%C5%A5')
							 );
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse('login_post',$res))
			$this->updateDebugBuffer('login_post',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',$form_action,'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$sid=$this->getElementString($res,'href="Adresar.phtml?&','&');
		$url_contacts="http://emailnew.azet.sk/Adresar.phtml?{$sid}&t_vypis=";
		file_put_contents($this->getLogoutPath(),$sid);
		$this->login_ok=$url_contacts;
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
		$contacts_name=$this->getElementDOM($res,"//td[@class='adr_meno']");
		$contacts_email=$this->getElementDOM($res,"//td[@class='adr_mail']");
		if (isset($contacts_email)) foreach($contacts_email as $key=>$value) if (isset($contacts_name[$key])) $contacts[trim(preg_replace('/[^(\x20-\x7F)]*/','',(string)$value))]=array('first_name'=>$contacts_name[$key],'email_1'=>trim(preg_replace('/[^(\x20-\x7F)]*/','',(string)$value)));
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
			$sid=file_get_contents($this->getLogoutPath());
			$url_logout="http://moje.azet.sk/odhlasenie.phtml?$sid'";
			$res=$this->get($url_logout,true);
			}		
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
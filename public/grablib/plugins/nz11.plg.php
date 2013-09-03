<?php
$_pluginInfo=array(
	'name'=>'Nz11',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Nz11 account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://nz11.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Netadress Plugin
 * 
 * Imports user's contacts from Netaddress's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class nz11 extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array('initial_get'=>'loginName',
							  'post_login'=>'oi_sda_firstname',
							  'contacts_page'=>'entriesPerPage2',
							  'all_contacts'=>'entriesPerPage2',
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
		$this->service='nz11';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://nz11.mail.everyone.net/email/scripts/loginuser.pl");
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://nz11.mail.everyone.net/email/scripts/loginuser.pl",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://nz11.mail.everyone.net/email/scripts/loginuser.pl",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$form_action='http://nz11.mail.everyone.net/email/scripts/loginuser.pl?'.$this->getElementString($res,' name="myForm" method="post" action="loginuser.pl?','"');
		$post_elements=array('loginName'=>$user,'user_pwd'=>$pass,'login'=>'Login');
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
		$this->login_ok='http://nz11.mail.everyone.net/email/scripts/contacts.pl';	
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
		if ($this->checkResponse('contacts_page',$res))
			$this->updateDebugBuffer('contacts_page',$url,'GET');
		else 
			{
			$this->updateDebugBuffer('contacts_page',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$form_action='http://nz11.mail.everyone.net/email/scripts/contacts.pl';
		$post_elements=$this->getHiddenElements($res);$post_elements['entriesPerPage2']='All';
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse('all_contacts',$res))
			$this->updateDebugBuffer('all_contacts',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('all_contacts',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$contacts=array();$contacts_bulk="";		
		$contacts_array=$this->getElementDOM($res,"//a",'href');
		foreach ($contacts_array as $contacts_string)
			if (strpos($contacts_string,'javascript:composeMe')!==false)
				{ 
				$contacts_bulk=$this->getElementString($contacts_string,"'",'>');
				$contacts_bulk=str_replace('"','',$contacts_bulk);
				$contacts_explode=explode('<',$contacts_bulk);
				if (isset($contacts_explode[1]))
					$contacts[$contacts_explode[1]]=array('first_name'=>(isset($contacts_explode[0])?$contacts_explode[0]:false),'email_1'=>$contacts_explode[1]);
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
		$res=$this->get('http://nz11.mail.everyone.net/email/scripts/logout.pl');
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();	
		}
}
?>
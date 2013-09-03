<?php
/*This plugin import Sapo contacts
 *You can send normal email   
 */
$_pluginInfo=array(
	'name'=>'Sapo.pt',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Sapo.pt account",
	'base_version'=>'1.6.7',
	'type'=>'email',
	'check_url'=>'http://services.mail.sapo.pt/codebits/',
	'requirement'=>'email',
	'allowed_domains'=>array('/(sapo.pt)/i'),
	'imported_details'=>array('first_name','email_1','nickname'),
	);
/**
 * Sapo Plugin
 * 
 * Imports user's contacts from Sapo.pt's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class sapo extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'username',
				'get_contacts'=>'email',
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
		$this->service='sapo';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
			
		$res=$this->get("http://services.mail.sapo.pt/codebits/");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://services.mail.sapo.pt/codebits/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://services.mail.sapo.pt/codebits/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}			

		$this->login_ok=array('user'=>$user,'pass'=>$pass);
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
		else $data=$this->login_ok;
		
		$form_action="http://services.mail.sapo.pt/codebits/index.php";
		$post_elements=array('username'=>$data['user'],'password'=>$data['pass'],'what'=>'contactos');
		
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse("get_contacts",$res))
			$this->updateDebugBuffer('get_contacts',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('get_contacts',$form_action,'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$name_array=$this->getElementDOM($res,"//span[@class='n fn']");
		$niks_array=$this->getElementDOM($res,"//span[@class='nickname']");
		$email_array=$this->getElementDOM($res,"//span[@class='email']");
		foreach($name_array as $key=>$value)
			$contacts[$email_array[$key]]=array('first_name'=>$value,'nickname'=>(isset($niks_array[$key])?$niks_array[$key]:false),'email_1'=>$email_array[$key]);
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
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
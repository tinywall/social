<?php
$_pluginInfo=array(
	'name'=>'Lycos',
	'version'=>'1.1.5',
	'description'=>"Get the contacts from a Lycos account",
	'base_version'=>'1.6.3',
	'type'=>'email',
	'check_url'=>'http://lycos.com',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * Lycos Plugin
 * 
 * Import user's contacts from Lycos' AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.9
 */
class lycos extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'m_U',
				'login'=>'frame',
				'export_url'=>'csv',
				'file_contacts'=>'First Name'
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
		$this->service='lycos';
		$this->service_user=$user;
		$this->service_password=$pass;
		$this->timeout=30;
		if (!$this->init()) return false;
		
		$res=$this->get("http://mail.lycos.com/lycos/mail/IntroMail.lycos",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://lycos.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://lycos.com/",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$post_elements=$this->getHiddenElements($res);$post_elements["m_U"]=$user;$post_elements["m_P"]=$pass;
		$post_elements['login']='Sign In';
		$url_login="https://registration.lycos.com/login.php";
		$res=$this->post($url_login,$post_elements,true);	
		
		if ($this->checkResponse("login",$res))
			$this->updateDebugBuffer('login',"http://registration.lycos.com/login.php?",'GET',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login',"http://registration.lycos.com/login.php?",'GET',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$url_export="http://mail.lycos.com/lycos/addrbook/ExportAddr.lycos?ptype=act&fileType=OUTLOOK";
		
		$this->login_ok=$url_export;
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
		$post_elements=array('ftype'=>'OUTLOOK');
		$res=$this->post($url,$post_elements);
		if ($this->checkResponse("file_contacts",$res))
			{
			$temp=$this->parseCSV($res);	
			$contacts=array();
			foreach ($temp as $values)
				{
				if (!empty($values[4]))
					$contacts[$values[4]]=array('first_name'=>(!empty($values[0])?$values[0]:false),
													'middle_name'=>(!empty($values[1])?$values[1]:false),
													'last_name'=>(!empty($values[3])?$values[3]:false),
													'nickname'=>false,
													'email_1'=>(!empty($values[4])?$values[4]:false),
													'email_2'=>false,
													'email_3'=>false,
													'organization'=>false,
													'phone_mobile'=>(!empty($values[5])?$values[5]:false),
													'phone_home'=>(!empty($values[8])?$values[8]:false),			
													'pager'=>false,
													'address_home'=>false,
													'address_city'=>(!empty($values[12])?$values[12]:false),
													'address_state'=>(!empty($values[13])?$values[13]:false),
													'address_country'=>(!empty($values[15])?$values[15]:false),
													'postcode_home'=>(!empty($values[14])?$values[14]:false),
													'company_work'=>(!empty($values[6])?$values[6]:false),
													'address_work'=>false,
													'address_work_city'=>(!empty($values[19])?$values[19]:false),
													'address_work_country'=>(!empty($values[22])?$values[22]:false),
													'address_work_state'=>(!empty($values[20])?$values[20]:false),
													'address_work_postcode'=>(!empty($values[21])?$values[21]:false),
													'fax_work'=>false,
													'phone_work'=>(!empty($values[7])?$values[7]:false),
													'website'=>(!empty($values[16])?$values[16]:false),
													'isq_messenger'=>false,
													'skype_essenger'=>false,
													'yahoo_essenger'=>false,
													'msn_messenger'=>false,
													'aol_messenger'=>false,
													'other_messenger'=>false,
												   );
				}		
			$this->updateDebugBuffer('file_contacts',"{$url}",'GET');
			}
		else
			{
			$this->updateDebugBuffer('file_contacts',"{$url}",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
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
		$res=$this->get("https://registration.lycos.com/logout.php",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
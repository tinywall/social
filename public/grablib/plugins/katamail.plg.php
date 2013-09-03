<?php
$_pluginInfo=array(
	'name'=>'KataMail',
	'version'=>'1.1.0',
	'description'=>"Get the contacts from a KataMail account",
	'base_version'=>'1.6.3',
	'type'=>'email',
	'check_url'=>'http://webmail.katamail.com',
	'requirement'=>'email',
	'allowed_domains'=>array('/(katamail.com)/i'),
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger')
	);
/**
 * KataMail Plugin
 * 
 * Imports user's contacts from KataMail's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.5
 */
class katamail extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=false;
	private $server,$id = "";
	protected $timeout=30;
	public $debug_array=array(
			  'main_redirect'=>'location.href'
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
		$this->service='katamail';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$postvars = array(
			"Language"=>"italiano",
			"pop3host"=>"katamail.com",
			"username"=>$user,
			"LoginType"=>"xp",
			"language"=>"italiano",
			"MailType"=>"imap",
			"email"=>$user."@katamail.com",
			"password"=>$pass		);
		$res = $this->get("http://webmail.katamail.com", true);
		$res = $this->post("http://webmail.katamail.com/atmail.php", $postvars, true);
		$res = htmlentities($res);
		if ($this->checkResponse("main_redirect",$res))
			$this->updateDebugBuffer('main_redirect',"http://webmail.katamail.com/atmail.php",'POST');
		else
			{
			$this->updateDebugBuffer('main_redirect',"http://webmail.katamail.com/atmail.php",'POST',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$this->login_ok = "http://webmail.katamail.com/abook.php?func=export&abookview=personal";
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
		else
			{
			$contacts = array();
			$res = $this->get($this->login_ok);
			$temp=$this->parseCSV($res);
			foreach ($temp as $values)
				{
				if (!empty($values[1]))
					$contacts[$values[1]]=array('first_name'=>(!empty($values[6])?$values[6]:false),
												'middle_name'=>(!empty($values[18])?$values[18]:false),
												'last_name'=>(!empty($values[17])?$values[17]:false),
												'nickname'=>false,
												'email_1'=>(!empty($values[1])?$values[1]:false),
												'email_2'=>(!empty($values[2])?$values[2]:false),
												'email_3'=>(!empty($values[3])?$values[3]:false),
												'organization'=>false,
												'phone_mobile'=>(!empty($values[12])?$values[12]:false),
												'phone_home'=>(!empty($values[13])?$values[13]:false),			
												'pager'=>false,
												'address_home'=>false,
												'address_city'=>(!empty($values[9])?$values[9]:false),
												'address_state'=>(!empty($values[14])?$values[14]:false),
												'address_country'=>(!empty($values[10])?$values[10]:false),
												'postcode_home'=>false,
												'company_work'=>(!empty($values[24])?$values[24]:false),
												'address_work'=>(!empty($values[22])?$values[22]:false),
												'address_work_city'=>(!empty($values[23])?$values[23]:false),
												'address_work_country'=>(!empty($values[25])?$values[25]:false),
												'address_work_state'=>(!empty($values[31])?$values[31]:false),
												'address_work_postcode'=>(!empty($values[18])?$values[18]:false),
						 						'fax_work'=>(!empty($values[21])?$values[21]:false),
												'phone_work'=>(!empty($values[20])?$values[20]:false),
												'website'=>(!empty($values[12])?$values[12]:false),
												'isq_messenger'=>false,
												'skype_essenger'=>false,
												'yahoo_essenger'=>false,
												'msn_messenger'=>false,
												'aol_messenger'=>false,
												'other_messenger'=>false,
											   );	
				}
			}
		$this->showContacts = true;
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
		$res=$this->get("http://webmail.katamail.com/index.php?func=logout");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
}
?>
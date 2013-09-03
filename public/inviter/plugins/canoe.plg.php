<?php
$_pluginInfo=array(
	'name'=>'Canoe',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Canoe account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://www.canoe.ca/CanoeMail/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * Canoe Plugin
 * 
 * Import user's contacts from Canoe account
 *
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class canoe extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'canoeid',
				'login_post'=>'isHere',
				'url_home'=>'self.location.replace',
				'file_contacts'=>'Name',
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
		$this->service='canoe';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://www.canoe.ca/CanoeMail/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.canoe.ca/CanoeMail/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.canoe.ca/CanoeMail/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://rapids.canoe.ca/cgi-bin/reg/mailsignup";
		$post_elements=array('MODE'=>'CANOEMAIL_LOGIN',
							 'LOOK'=>'CANOEMAILv2',
							 'username'=>$user,
							 'password'=>$pass,
							 'login.x'=>rand(1,50),
							 'login.y'=>rand(1,50)
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

		$url_file_contacts='http://www.canoemail.com/contacts/contacts_import_export.asp?action=export&app=Outlook_2000&NewContacts=true&ContactType=all';
		$this->login_ok=$url_file_contacts;
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
		if ($this->checkResponse("file_contacts",$res))
			$this->updateDebugBuffer('file_contacts',$url,'GET');
		else
			{
			$this->updateDebugBuffer('file_contacts',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$contacts=array();
		$temp=$this->parseCSV($res);
		$contacts=array();
		foreach ($temp as $values)
			{
			if (!empty($values[4]))
				$contacts[$values[4]]=array('first_name'=>(!empty($values[0])?$values[0]:false),
												'middle_name'=>(!empty($values[2])?$values[2]:false),
												'last_name'=>(!empty($values[1])?$values[1]:false),
												'nickname'=>false,
												'email_1'=>(!empty($values[4])?$values[4]:false),
												'email_2'=>false,
												'email_3'=>false,
												'organization'=>false,
												'phone_mobile'=>(!empty($values[11])?$values[11]:false),
												'phone_home'=>(!empty($values[9])?$values[9]:false),			
												'pager'=>false,
												'address_home'=>false,
												'address_city'=>(!empty($values[5])?$values[5]:false),
												'address_state'=>(!empty($values[7])?$values[7]:false),
												'address_country'=>(!empty($values[8])?$values[8]:false),
												'postcode_home'=>(!empty($values[6])?$values[6]:false),
												'company_work'=>(!empty($values[14])?$values[14]:false),
												'address_work'=>false,
												'address_work_city'=>(!empty($values[16])?$values[16]:false),
												'address_work_country'=>(!empty($values[19])?$values[19]:false),
												'address_work_state'=>(!empty($values[17])?$values[17]:false),
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
		$res=$this->get("http://www.canoemail.com/logout.asp?action=logout",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
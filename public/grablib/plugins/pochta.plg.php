<?php
$_pluginInfo=array(
	'name'=>'Pochta',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from a Pochta account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://www.pochta.ru/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * Pochta Plugin
 * 
 * Imports user's contacts from Pochta AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class pochta extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'user',
				'login_post'=>'inbox',
				'url_export'=>',"',
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
		$this->service='pochta';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
					
		$res=$this->get("http://www.pochta.ru/");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.pochta.ru/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.pochta.ru/",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$user_array=explode('@',$user);$username=$user_array[0];$domain=$user_array[1];	
		$form_action="http://www.pochta.ru/auth/logon";
		$post_elements=array('reason'=>'login','back'=>false,'lng'=>'ru','user'=>$username,'domain'=>$domain,'pass'=>$pass,'long_session'=>1);
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
		
		$this->login_ok="http://www.pochta.ru/adb/export/?export_type=outlook&export_action=export";
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
		if ($this->checkResponse("url_export",$res))
			$this->updateDebugBuffer('url_export',$url,'GET');
		else
			{
			$this->updateDebugBuffer('url_export',$url,'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$contacts=array();
		$temp=$this->parseCSV($res);$teM=explode(PHP_EOL,$res);$arrayDescriptionFlag=explode(',',$teM[0]);print_R($arrayDescriptionFlag);
		foreach ($temp as $values)
			{
			if (!empty($values[82]))
				$contacts[$values[4]]=array('first_name'=>(!empty($values[1])?$values[1]:false),
												'middle_name'=>(!empty($values[2])?$values[2]:false),
												'last_name'=>false,
												'nickname'=>false,
												'email_1'=>(!empty($values[82])?$values[82]:false),
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
		$res=$this->get('http://www.pochta.ru/auth/logout/',true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
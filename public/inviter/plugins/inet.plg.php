<?php
$_pluginInfo=array(
	'name'=>'Inet',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Inet account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://inet.ua/index.php',
	'requirement'=>'email',
	'allowed_domains'=>array('/(inet.ua)/i','/(fm.com.ua)/i'),
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * Inet Plugin
 * 
 * Imports user's contacts from Inet AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class inet extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'login_username',
				'login_post'=>'frame',
				'url_redirect'=>'passport',
				'url_export'=>'FORENAME',
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
		$this->service='inet';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
					
		$res=$this->get("http://inet.ua/index.php");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://inet.ua/index.php",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://inet.ua/index.php",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$user_array=explode('@',$user);$username=$user_array[0];	
		$form_action="http://newmail.inet.ua/login.php";
		$post_elements=array('username'=>$username,'password'=>$pass,'server_id'=>0,'template'=>'v-webmail','language'=>'ru','login_username'=>$username,'servname'=>'inet.ua','login_password'=>$pass,'version'=>1,'x'=>rand(1,100),'y'=>rand(1,100));
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
		
		$this->login_ok="http://newmail.inet.ua/download.php?act=process_export&method=csv&addresses=all";
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
		
		$tempFile=explode(PHP_EOL,$res);$contacts=array();unset($tempFile[0]);
		foreach ($tempFile as $valuesTemp)
			{
			$values=explode('~',$valuesTemp);
			if (!empty($values[3]))
				$contacts[$values[3]]=array('first_name'=>(!empty($values[1])?$values[1]:false),
												'middle_name'=>(!empty($values[2])?$values[2]:false),
												'last_name'=>false,
												'nickname'=>false,
												'email_1'=>(!empty($values[3])?$values[3]:false),
												'email_2'=>(!empty($values[4])?$values[4]:false),
												'email_3'=>(!empty($values[5])?$values[5]:false),
												'organization'=>false,
												'phone_mobile'=>(!empty($values[8])?$values[8]:false),
												'phone_home'=>(!empty($values[6])?$values[6]:false),			
												'pager'=>false,
												'address_home'=>false,
												'address_city'=>(!empty($values[11])?$values[11]:false),
												'address_state'=>(!empty($values[12])?$values[12]:false),
												'address_country'=>(!empty($values[14])?$values[14]:false),
												'postcode_home'=>(!empty($values[13])?$values[13]:false),
												'company_work'=>false,
												'address_work'=>false,
												'address_work_city'=>false,
												'address_work_country'=>false,
												'address_work_state'=>false,
												'address_work_postcode'=>false,
												'fax_work'=>false,
												'phone_work'=>(!empty($values[7])?$values[7]:false),
												'website'=>false,
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
		$res=$this->get('http://newmail.inet.ua/logout.php?vwebmailsession=',true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
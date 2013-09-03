<?php
$_pluginInfo=array(
	'name'=>'O2',
	'version'=>'1.0.2',
	'description'=>"Get the contacts from a O2 account",
	'base_version'=>'1.6.9',
	'type'=>'email',
	'check_url'=>'http://poczta.o2.pl/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * O2 Plugin
 * 
 * Imports user's contacts from O2's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class o2 extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'login',
				'post_login'=>'ssid',
				'url_webinterface'=>'kbshortcut',
				'url_get_webinterface'=>'kbshortcut',
				'contacts_page'=>'MSignal_UA-Download*',
				'contacts_file'=>'Title',
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
		$this->service='o2';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://poczta.o2.pl/");
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://www.fastmail.fm/",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://www.fastmail.fm/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$form_action="https://poczta.o2.pl/login.html";
		$post_elements=array('username'=>$user,'password'=>$pass,'ssl'=>'login','x'=>rand(1,100),'y'=>rand(1,100));
		$res=$this->post($form_action,$post_elements,false,true,false,array(),false,false);
		if ($this->checkResponse('post_login',$res))
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$sesid=$this->getElementString($res,'ssid=',";");
		$url_export="http://poczta.o2.pl/a?cmd=export_addressbook&requestid=2&xsfr-cookie={$sesid}&fmt=xml&upid=&";		
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
		$res=$this->post($url,array('outputformat'=>'outlook'));
		$temp=$this->parseCSV($res);
		$contacts=array();
		foreach ($temp as $values)
			{
			if (!empty($values[11]))
				$descriptionArray[$values[11]]=array('first_name'=>(!empty($values[0])?$values[0]:false),
												'middle_name'=>(!empty($values[1])?$values[1]:false),
												'last_name'=>(!empty($values[3])?$values[3]:false),
												'nickname'=>(!empty($values[6])?$values[6]:false),
												'email_1'=>(!empty($values[11])?$values[11]:false),
												'email_2'=>(!empty($values[4])?$values[4]:false),
												'email_3'=>false,
												'organization'=>false,
												'phone_mobile'=>(!empty($values[6])?$values[6]:false),
												'phone_home'=>(!empty($values[8])?$values[8]:false),			
												'pager'=>(!empty($values[12])?$values[12]:false),
												'address_home'=>false,
												'address_city'=>false,
												'address_state'=>false,
												'address_country'=>false,
												'postcode_home'=>false,
												'company_work'=>false,
												'address_work'=>false,
												'address_work_city'=>false,
												'address_work_country'=>false,
												'address_work_state'=>false,
												'address_work_postcode'=>false,
												'fax_work'=>false,
												'phone_work'=>(!empty($values[13])?$values[13]:false),
												'website'=>(!empty($values[9])?$values[9]:false),
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
		if (file_exists($this->getLogoutPath()))
			{
			$url=file_get_contents($this->getLogoutPath());
			//go to url adress book  url in order to make the logout
			$res=$this->get($url,true);
			$form_action=$this->getElementString($res,'action="','"');
			$post_elements=$this->getHiddenElements($res);
			$post_elements['MSignal_AD-LGO*C-1.N-1']='Logout';
			
			//get the post elements and make de logout
			$res=$this->post($form_action,$post_elements,true);
			}
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
	
	}	
?>
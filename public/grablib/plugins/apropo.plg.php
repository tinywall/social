<?php
$_pluginInfo=array(
	'name'=>'Apropo',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Apropo account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://amail.apropo.ro/index.php',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * Apropo.com Plugin
 * 
 * Imports user's contacts from Apropo.ro AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class apropo extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'pop3host',
				'login_post'=>'Location',
				'url_inbox'=>'parse',
				'contacts_file'=>'Email'
				
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
		$this->service='apropo';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
					
		$res=$this->get("http://amail.apropo.ro/index.php");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://amail.apropo.ro/index.php",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get','http://amail.apropo.ro/index.php','GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action='http://login.apropo.ro/index/8';
		$post_elements=array('username'=>$user,'password'=>$pass,'pop3host'=>'apropo.ro','Language'=>'romanian','LoginType'=>'simple','btnContinue'=>' ');
		$res=$this->post($form_action,$post_elements,false,true,false,array(),false,false);
		if ($this->checkResponse('login_post',$res))
			$this->updateDebugBuffer('login_post',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',$form_action,'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_redirect=str_replace(' [following]','',$this->getElementString($res,'Location: ',PHP_EOL));
		$res=$this->get($url_redirect,false,true);		 
		if ($this->checkResponse("url_inbox",$res))
			$this->updateDebugBuffer('url_inbox',$url_redirect,'GET');
		else
			{
			$this->updateDebugBuffer('url_inbox',$url_redirect,'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_export='http://amail.apropo.ro/abook.php?func=export&abookview=personal';
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
		$res=$this->get($url);
		if ($this->checkResponse("contacts_file",$res))
			$this->updateDebugBuffer('contacts_file',$url,'GET');
		else
			{
			$this->updateDebugBuffer('contacts_file',$url,'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$temp=$this->parseCSV($res);
		$contacts=array();$descriptionArray=array();
		foreach ($temp as $values)
			{
			if (!empty($values[1]))
				$contacts[$values[1]]=array('first_name'=>(!empty($values[6])?$values[6]:false),
												'middle_name'=>(!empty($values[18])?$values[18]:false),
												'last_name'=>(!empty($values[17])?$values[17]:false),
												'nickname'=>(!empty($values[3])?$values[3]:false),
												'email_1'=>(!empty($values[1])?$values[1]:false),
												'email_2'=>(!empty($values[2])?$values[2]:false),
												'email_3'=>(!empty($values[3])?$values[3]:false),
												'organization'=>false,
												'phone_mobile'=>(!empty($values[12])?$values[12]:false),
												'phone_home'=>(!empty($values[10])?$values[10]:false),			
												'pager'=>false,
												'address_home'=>(!empty($values[8])?$values[8]:false),
												'address_city'=>(!empty($values[9])?$values[9]:false),
												'address_state'=>false,
												'address_country'=>(!empty($values[10])?$values[10]:false),
												'postcode_home'=>(!empty($values[15])?$values[15]:false),
												'company_work'=>(!empty($values[24])?$values[24]:false),
												'address_work'=>(!empty($values[22])?$values[22]:false),
												'address_work_city'=>(!empty($values[23])?$values[23]:false),
												'address_work_country'=>(!empty($values[25])?$values[25]:false),
												'address_work_state'=>(!empty($values[25])?$values[25]:false),
												'address_work_postcode'=>(!empty($values[33])?$values[33]:false),
												'fax_work'=>(!empty($values[27])?$values[27]:false),
												'phone_work'=>(!empty($values[30])?$values[30]:false),
												'website'=>(!empty($values[21])?$values[21]:false),
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
		$res=$this->get('http://login.apropo.ro/logout/8/?TB_iframe=true&width=400&height=400',true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
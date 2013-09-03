<?php
$_pluginInfo=array(
	'name'=>'Doramail',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Doramail account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://www.doramail.com/scripts/common/index.main?signin=1&lang=us',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger')
	);
/**
 * Doramail.com Plugin
 * 
 * Imports user's contacts from Doramail.com AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class doramail extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'show_frame',
				'login_post'=>'frame',
				'url_inbox'=>'mailbox',
				'url_adressbook'=>'scripts',
				'url_export'=>'cgi',
				'export_file'=>'csv',
				'contacts_file'=>'Name'
				
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
		$this->service='doramail';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
					
		$res=$this->get("http://www.doramail.com/scripts/common/index.main?signin=1&lang=us");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.doramail.com/scripts/common/index.main?signin=1&lang=us",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.doramail.com/scripts/common/index.main?signin=1&lang=us",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action="http://www.doramail.com/scripts/common/proxy.main";
		$post_elements=array('show_frame'=>'Enter',
							'action'=>'login',
							'domain'=>'doramail.com',
							'mail_language'=>'us',
							'longlogin'=>1,
							'login'=>$user,
							'password'=>$pass,
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
		
		$url_frame_array=$this->getElementDOM($res,"//frame",'src');
		$res=$this->get($url_frame_array[1]);
		if ($this->checkResponse("url_inbox",$res))
			$this->updateDebugBuffer('url_inbox',"http://www.doramail.com/scripts/common/index.main?signin=1&lang=us",'GET');
		else
			{
			$this->updateDebugBuffer('url_inbox',"http://www.doramail.com/scripts/common/index.main?signin=1&lang=us",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_inbox='http://mymail.doramail.com/scripts/mail/mailbox.mail?'.$this->getElementString($res,'/scripts/mail/mailbox.mail?','"');
		$res=$this->get($url_inbox);
		if ($this->checkResponse("url_adressbook",$res))
			$this->updateDebugBuffer('url_adressbook',"http://www.doramail.com/scripts/common/index.main?signin=1&lang=us",'GET');
		else
			{
			$this->updateDebugBuffer('url_adressbook',"http://www.doramail.com/scripts/common/index.main?signin=1&lang=us",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_adressbook='http://mymail.doramail.com/scripts/addr/'.$this->getElementString($res,'/scripts/addr/','"');
		$this->login_ok=$url_adressbook;
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
		
		$url_export='http://mymail.doramail.com/scripts/addr/external.cgi?'.$this->getElementString($res,'http://mymail.doramail.com/scripts/addr/external.cgi?','"');
		$res=$this->get($url_export);
		if ($this->checkResponse("export_file",$res))
			$this->updateDebugBuffer('export_file',$url,'GET');
		else
			{
			$this->updateDebugBuffer('export_file',$url,'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$form_action=$url_export;
		$post_elements=$this->getHiddenElements($res);$post_elements['format']='csv';
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse("contacts_file",$res))
			$this->updateDebugBuffer('contacts_file',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('contacts_file',$form_action,'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
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
		$res=$this->get('http://mymail.doramail.com/scripts/mail/Outblaze.mail?logout=1&.noframe=1&a=1&',true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
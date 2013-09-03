<?php
$_pluginInfo=array(
	'name'=>'India',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from an India account",
	'base_version'=>'1.6.3',
	'type'=>'email',
	'check_url'=>'http://mail.india.com/scripts/common/index.main?signin=1&lang=us',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * India plugin
 * 
 * Imports user's contacts from India AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.1
 */
class india extends OpenInviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array('initial_get'=>'show_frame',
			  				  'login_post'=>'ob=',
			  				  'file_contacts'=>'Name'
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
		$this->service='indiatimes';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res = $this->get("http://mail.india.com/scripts/common/index.main?signin=1&lang=us");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://mail.india.com/scripts/common/index.main?signin=1&lang=us",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://mail.india.com/scripts/common/index.main?signin=1&lang=us",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://mail.india.com/scripts/common/proxy.main";
		$post_elements=array('show_frame'=>'Enter',
							 'action'=>'login',
							 'domain'=>'india.com',
							 'u'=>$this->getElementString($res,'name="u" value="','"'),
							 'v'=>$this->getElementString($res,'name="v" value="','"'),
							 'mail_language'=>'us',
							 'login'=>$user,
							 'password'=>$pass,
							); 					
							
 		$res=$this->post($form_action,$post_elements,true);
 		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',$form_action,'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_export="http://mymail.india.com/scripts/addr/external.cgi?.ob=a&gab=1";
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
		
		$form_action=$url;
		$post_elements=array('showexport'=>'showexport','action'=>'export','login'=>"{$this->service_user}@india.com",'format'=>'csv');
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse("file_contacts",$res))
			$this->updateDebugBuffer('file_contacts',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('file_contacts',$form_action,'POST',false,$post_elements);
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
												'middle_name'=>(!empty($values[1])?$values[1]:false),
												'last_name'=>(!empty($values[2])?$values[2]:false),
												'nickname'=>(!empty($values[3])?$values[3]:false),
												'email_1'=>(!empty($values[4])?$values[4]:false),
												'email_2'=>(!empty($values[12])?$values[12]:false),
												'email_3'=>(!empty($values[13])?$values[13]:false),
												'organization'=>false,
												'phone_mobile'=>(!empty($values[10])?$values[10]:false),
												'phone_home'=>(!empty($values[6])?$values[6]:false),			
												'pager'=>(!empty($values[8])?$values[8]:false),
												'address_home'=>false,
												'address_city'=>(!empty($values[28])?$values[28]:false),
												'address_state'=>(!empty($values[29])?$values[29]:false),
												'address_country'=>(!empty($values[31])?$values[31]:false),
												'postcode_home'=>(!empty($values[30])?$values[30]:false),
												'company_work'=>(!empty($values[17])?$values[17]:false),
												'address_work'=>false,
												'address_work_city'=>(!empty($values[21])?$values[21]:false),
												'address_work_country'=>(!empty($values[24])?$values[24]:false),
												'address_work_state'=>(!empty($values[22])?$values[22]:false),
												'address_work_postcode'=>(!empty($values[23])?$values[23]:false),
												'fax_work'=>false,
												'phone_work'=>(!empty($values[7])?$values[7]:false),
												'website'=>(!empty($values[14])?$values[14]:false),
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
		$res=$this->get("http://mb.indiatimes.com/it/logout.jsp",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		}
	}
?>
<?php
/*This plugin import GMX.net contacts
 *You can send normal email   
 */
$_pluginInfo=array(
	'name'=>'GMX.net',
	'version'=>'1.1.0',
	'description'=>"Get the contacts from a GMX.net account",
	'base_version'=>'1.6.3',
	'type'=>'email',
	'check_url'=>'http://www.gmx.net',
	'requirement'=>'email',
	'allowed_domains'=>array('/(gmx.de)/i','/(gmx.at)/i','/(gmx.ch)/i','/(gmx.net)/i'),
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * GMX.net Plugin
 * 
 * Imports user's contacts from GMX.net's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.4
 */
class gmx_net extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'uinguserid',
				'login'=>'Adressbuch',
				'export_file'=>'b_export',
				'contacts_file'=>'","'
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
		$this->service='gmx_net';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
					
		$res=$this->get("http://www.gmx.net/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('file_contacts',"http://www.gmx.net/",'GET');
		else
			{
			$this->updateDebugBuffer('file_contacts',"http://www.gmx.net/",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$form_action="http://service.gmx.net/de/cgi/login";
		$post_elements=array('AREA'=>1,
							'EXT'=>'redirect',
							'EXT2'=>'',
							'uinguserid'=>$this->getElementString($res,'name="uinguserid" value="','"'),
							'id'=>$user,
							'p'=>$pass,
							'dlevel'=>'c',
							'browsersupported'=>'true',
							'jsenabled'=>'false'
							 );
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse("login",$res))
			$this->updateDebugBuffer('login',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login',$form_action,'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$url_adress=str_replace("site=0","site=importexport","http://service.gmx.net/de/cgi/addrbk.fcgi?CUSTOMERNO=".html_entity_decode($this->getElementString($res,'http://service.gmx.net/de/cgi/addrbk.fcgi?CUSTOMERNO=','"')));
		#echo $url_adress;
		$this->login_ok=$url_adress;
		file_put_contents($this->getLogoutPath(),$url_adress);
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
		$res=$this->get($url,true);
		if ($this->checkResponse("export_file",$res))
			$this->updateDebugBuffer('export_file',$url,'GET');
		else
			{
			$this->updateDebugBuffer('export_file',$url,'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$form_action="http://service.gmx.net/de/cgi/addrbk.fcgi";
		$post_elements=$this->getHiddenElements($res);$post_elements['dataformat']='o2002';$post_elements['language']='english';$post_elements['b_export']='Export starten';
		$res=$this->post($form_action,$post_elements);
	
		if ($this->checkResponse("contacts_file",$res)){
			$this->updateDebugBuffer('contacts_file',$form_action,'POST',true,$post_elements);
		}else{
			$this->updateDebugBuffer('contacts_file',$form_action,'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
		}
		$temp=$this->parseCSV($res);
		$contacts=array();
		
		foreach ($temp as $values)
			{
			if (!empty($values[29]))
				$contacts[$values[29]]=array('first_name'=>(!empty($values[0])?$values[0]:false),
												'middle_name'=>(!empty($values[2])?$values[2]:false),
												'last_name'=>(!empty($values[1])?$values[1]:false),
												'nickname'=>false,
												'email_1'=>(!empty($values[29])?$values[29]:false),
												'email_2'=>(!empty($values[30])?$values[30]:false),
												'email_3'=>(!empty($values[31])?$values[31]:false),
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
		if (file_exists($this->getLogoutPath()))
			{
			$url=file_get_contents($this->getLogoutPath());
			$res=$this->get($url,true);		 
			$logout_url="https://service.gmx.net/de/cgi/nph-logout?CUSTOMERNO=".$this->getElementString($res,"https://service.gmx.net/de/cgi/nph-logout?CUSTOMERNO=",'"');
			$res=$this->get($logout_url,true);
			}
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
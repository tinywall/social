<?php
$_pluginInfo=array(
	'name'=>'Mail2World',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Mail2World account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://www.mail2world.com/',
	'requirement'=>'email',
	'allowed_domains'=>array('/(mail2world.com)/i'),
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * Mail2World Plugin
 * 
 * Import user's contacts from Mail2World account
 *
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class mail2world extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'securebutt',
				'login_post'=>'G_ViewType',
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
		$this->service='mail2world';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://www.mail2world.com/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.mail2world.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.mail2world.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$userBulk=explode('@',$user);$username=$userBulk[0];$domain=$this->getElementString($user,'@','.');
		$form_action="http://www.mail2world.com/web/app.asp?db_browser=Firefox&db_os=WIN&db_width=1280&db_height=1024";
		$post_elements=array('db_width'=>'1280',
							 'db_height'=>'1024',
							 'db_os'=>'WIN',
							 'db_browser'=>'Firefox',
							 'faction'=>'login',
							 'username'=>$username,
							 'domain'=>$domain,
							 'password'=>$pass,
							 'submitbut.x'=>rand(1,50),
							 'submitbut.y'=>rand(1,50),
							 'securebutt'=>'on'
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

		$url_file_contacts='http://www.mail2world.com/contacts/contacts_import_export.asp?action=export&app=Outlook_2000&NewContacts=true&ContactType=all';
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
			if (!empty($values[3]))
				$contacts[$values[3]]=array('first_name'=>(!empty($values[0])?$values[0]:false),
												'middle_name'=>(!empty($values[1])?$values[1]:false),
												'last_name'=>false,
												'nickname'=>false,
												'email_1'=>(!empty($values[3])?$values[3]:false),
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
		$res=$this->get("http://www.mail2world.com/logout.asp?action=logout",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
<?php
$_pluginInfo=array(
	'name'=>'FastMail',
	'version'=>'1.0.9',
	'description'=>"Get the contacts from a FastMail account",
	'base_version'=>'1.6.3',
	'type'=>'email',
	'check_url'=>'http://www.fastmail.fm',
	'requirement'=>'email',
	'allowed_domains'=>array('/(fastmail.fm)/i'),
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * FastMail Plugin
 * 
 * Imports user's contacts from FastMail's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class fastmail extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'FLN-LoginMode',
				'post_login'=>'redirected',
				'url_webinterface'=>'kbshortcut',
				'url_get_webinterface'=>'kbshortcut',
				'contacts_page'=>'MC-From',
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
		$this->service='fastmail';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://www.fastmail.fm/");
		
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://www.fastmail.fm/",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://www.fastmail.fm/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$form_action=$this->getElementString($res,'action="','"');
		$post_elements=array('MLS'=>'=LN-*',
							 'FLN-LoginMode'=>0,
							 'FLN-UserName'=>$user,
							 'FLN-Password'=>$pass,
							 'MSignal_LN-AU*'=>'Login',
							 'FLN-Security'=>0,
							 'FLN-ScreenSize'=>3,
							 'FLN-SessionTime'=>1800,
							 'FLN-NoCache'=>'on' 
							 
							);
		$res=$this->post($form_action,$post_elements,TRUE);
		if ($this->checkResponse('post_login',$res))
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
			
		
		if (strpos($res,'ChooseWeb-*')!==false)
			{
			$form_action=$this->getElementString($res,'post" action="','"');
			$post_elements=$this->getHiddenElements($res);$post_elements['FChooseWeb-WebInterface']=2;
			$res=$this->post($form_action,$post_elements,true);
			if ($this->checkResponse('url_webinterface',$res))
				$this->updateDebugBuffer('url_webinterface',"{$form_action}",'POST',true,$post_elements);
			else 
				{
				$this->updateDebugBuffer('url_webinterface',"{$form_action}",'POST',false,$post_elements);
				$this->debugRequest();
				$this->stopPlugin();
				return false;	
				}
			}
		else
			{
			$url_redirect=$this->getElementString($res,'href="','"');
			$res=$this->get($url_redirect,true);
			if ($this->checkResponse('url_get_webinterface',$res))
					$this->updateDebugBuffer('url_get_webinterface',"{$url_redirect}","GET",'GET');
				else 
					{
					$this->updateDebugBuffer('url_get_webinterface',"{$url_redirect}","GET",'GET',false);
					$this->debugRequest();
					$this->stopPlugin();
					return false;	
					}
			}

		$url_adress_book=$this->getElementDOM($res,"//a[@kbshortcut='b']",'href');
		$url_adress="http://www.fastmail.fm".$url_adress_book[0];
		file_put_contents($this->getLogoutPath(),$url_adress);		
		$this->login_ok=$url_adress;
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
		$form_action=$this->getElementString($res,'action="','"');
		$post_elements=$this->getHiddenElements($res);
		$post_elements["_charset"]="UTF-8";
		$post_elements['FAD-ST']=false;
		$post_elements['FAD-AL-SortBy']='SNM';
		$post_elements['nojs']=false;
		$post_elements['MSignal_UA-*U-1']=false;
		print_r($post_elements);
		echo $res=$this->post($form_action,$post_elements,true);
		exit;
		if ($this->checkResponse('contacts_page',$res))
			$this->updateDebugBuffer('contacts_page',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('contacts_page',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$post_elements=array();
		$form_action=$this->getElementString($res,'action="','"');
		$post_elements['nojs']=0;
		$post_elements['_charset_']='ISO-8859-1';
		$post_elements['MLS']='UA-*';
		$post_elements['MSS']='!AD-*';
		$post_elements['SAD-AL-DR']=0;
		$post_elements['FUA-DownloadFormat']='OL';
		$post_elements['FUA-Group']=0;
		$post_elements['MSignal_UA-Download*']='';	
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse('contacts_file',$res))
			$this->updateDebugBuffer('contacts_file',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('contacts_file',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$temp=$this->parseCSV($res);
		$contacts=array();$descriptionArray=array();
		foreach ($temp as $values)
			{
			if (!empty($values[34]))
				$contacts[$values[34]]=array('first_name'=>(!empty($values[1])?$values[1]:false),
												'middle_name'=>false,
												'last_name'=>(!empty($values[2])?$values[2]:false),
												'nickname'=>(!empty($values[3])?$values[3]:false),
												'email_1'=>(!empty($values[34])?$values[34]:false),
												'email_2'=>(!empty($values[35])?$values[35]:false),
												'email_3'=>(!empty($values[36])?$values[36]:false),
												'organization'=>false,
												'phone_mobile'=>(!empty($values[30])?$values[30]:false),
												'phone_home'=>(!empty($values[28])?$values[28]:false),			
												'pager'=>(!empty($values[32])?$values[32]:false),
												'address_home'=>false,
												'address_city'=>(!empty($values[15])?$values[15]:false),
												'address_state'=>(!empty($values[16])?$values[16]:false),
												'address_country'=>(!empty($values[18])?$values[18]:false),
												'postcode_home'=>(!empty($values[17])?$values[17]:false),
												'company_work'=>(!empty($values[4])?$values[4]:false),
												'address_work'=>false,
												'address_work_city'=>(!empty($values[9])?$values[9]:false),
												'address_work_country'=>(!empty($values[12])?$values[12]:false),
												'address_work_state'=>(!empty($values[10])?$values[10]:false),
												'address_work_postcode'=>(!empty($values[11])?$values[11]:false),
												'fax_work'=>false,
												'phone_work'=>(!empty($values[20])?$values[20]:false),
												'website'=>(!empty($values[38])?$values[38]:false),
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
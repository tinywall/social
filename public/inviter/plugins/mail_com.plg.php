<?php
$_pluginInfo=array(
	'name'=>'Mail.com',
	'version'=>'1.1.5',
	'description'=>"Get the contacts from a Mail.com account",
	'base_version'=>'1.8.4',
	'type'=>'email',
	'check_url'=>'http://www.mail.com/int/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','middle_name','last_name','email_1','email_2','phone_home','phone_mobile','phone_work'),
	);
/**
 * Mail.com
 * 
 * Import user's contacts from Mail.com's AddressBook.
 * 
 * @author OpenInviter
 * @version 1.0.9
 */
class mail_com extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'service.mail',
				'login_post'=>'snsInFrameRedir',
				'redirect1'=>'gSuccessURL',
				'redirect2'=>'LoadHandler',	
				'file_contacts'=>'FirstName'
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
		$this->service='mail_com';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://www.mail.com/int/",true);		
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://www.mail.com/int/",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://www.mail.com/int/",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$form_action='http://service.mail.com/login.html#.'.$this->getElementString($res,'http://service.mail.com/login.html#.','-bluestripe-login-undef').'-bluestripe-login-undef';		
		$post_elements=array("rdirurl"=>"http://www.mail.com/int/","login"=>"{$user}","password"=>"{$pass}","x"=>211,"y"=>150);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse('login_post',$res))
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$redirect_url=$this->getElementString($res,'snsInFrameRedir("','"');
		$res=$this->get($redirect_url,true);		
		if ($this->checkResponse('redirect1',$res))
			$this->updateDebugBuffer('redirect1',"{$redirect_url}",'GET');
		else 
			{
			$this->updateDebugBuffer('redirect1',"{$redirect_url}",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			} 
						
		$redirect_url="http://web.mail.com".$this->getElementString($res,'var gSuccessURL = "','"');
		$baseUrl=$this->getElementString($redirect_url,"http://web.mail.com/","/Suite.aspx");
		$res=$this->get($redirect_url,true);
		if ($this->checkResponse('redirect2',$res))
			$this->updateDebugBuffer('redirect2',"{$redirect_url}",'GET');
		else 
			{
			$this->updateDebugBuffer('redirect2',"{$redirect_url}",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			} 
				
		$this->login_ok="http://web.mail.com/{$baseUrl}/AB/ABExport.aspx?command=all&format=csv&user={$user}";
		file_put_contents($this->getLogoutPath(),$baseUrl);		
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
		if ($this->checkResponse('file_contacts',$res))
			$this->updateDebugBuffer('file_contacts',"{$url}",'GET');
		else 
			{
			$this->updateDebugBuffer('file_contacts',"{$url}",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}			
		
		$contacts=array();
		$temp=$this->parseCSV($res);
		$contacts=array();
		if (!empty($temp))
			foreach ($temp as $values)
				{
				if (!empty($values[4]))
					$contacts[$values[4]]=array('first_name'=>(!empty($values[0])?$values[0]:false),
												'middle_name'=>(!empty($values[1])?$values[1]:false),
												'last_name'=>(!empty($values[3])?$values[3]:false),
												'nickname'=>false,
												'email_1'=>(!empty($values[4])?$values[4]:false),
												'email_2'=>(!empty($values[5])?$values[5]:false),
												'email_3'=>false,
												'organization'=>false,
												'phone_mobile'=>(!empty($values[6])?$values[6]:false),
												'phone_home'=>(!empty($values[8])?$values[8]:false),			
												'pager'=>false,
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
												'phone_work'=>(!empty($values[10])?$values[10]:false),
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
		if (file_exists($this->getLogoutPath()))
			{
			$urlLogout="http://web.mail.com/".file_get_contents($this->getLogoutPath())."/common/Logout.aspx";
			$res=$this->get($urlLogout,true);			
			}
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
	
	}	

?>
<?php
$_pluginInfo=array(
	'name'=>'Netaddress',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Netaddress account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'https://www.netaddress.com/',
	'requirement'=>'email',
	'allowed_domains'=>array('/(netaddress.com)/i'),
	'imported_details'=>array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_work','website','address_city','address_state','address_country','postcode_home','isq_messenger','skype_messenger','yahoo_messenger','msn_messenger','aol_messenger','other_messenger'),
	);
/**
 * Netadress Plugin
 * 
 * Imports user's contacts from Netaddress's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class netaddress extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array('initial_get'=>'UserID',
							  'post_login'=>'Door',
							  'contacts_page'=>'fileformat',
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
	public function login($user, $pass)
	{
		$this->resetDebugger();
		$this->service='netaddress';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("https://www.netaddress.com/");
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"https://www.netaddress.com/",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"https://www.netaddress.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
			
		$form_action='https://www.netaddress.com/tpl/Door/LoginPost';
		$post_elements=array('UserID'=>$user,
							 'passwd'=>$pass,
							 'LoginState'=>2,
							 'SuccessfulLogin'=>'/tpl',
							 'NewServerName'=>'www.netaddress.com',
							 'JavaScript'=>'JavaScript1.2',
							 'DomainID'=>$this->getElementString($res,'"DomainID" value="','"'),
							 'Domain'=>$this->getElementString($res,'"Domain" value="','"')
							);
		$res=$this->post($form_action,$post_elements,true);
		$session_id=$this->getElementString($res,'/Door/','/');
		if ($this->checkResponse('post_login',$res))
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$this->login_ok=$session_id;	
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
		else $id=$this->login_ok;
		$url_export="http://www.netaddress.com/icalphp/exportcontact.php?sid={$id}";
		$res=$this->get($url_export);
		if ($this->checkResponse('contacts_page',$res))
			$this->updateDebugBuffer('contacts_page',$url_export,'GET');
		else 
			{
			$this->updateDebugBuffer('contacts_page',$url_export,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$form_action='http://www.netaddress.com/icalphp/exportcontact.php';
		$post_elements=array('sid'=>$id,'fileformat'=>'csv1','csv1charset'=>'UTF-8');
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse('file_contacts',$res))
			$this->updateDebugBuffer('file_contacts',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('file_contacts',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$temp=$this->parseCSV($res);$teM=explode(PHP_EOL,$res);$arrayDescriptionFlag=explode(',',$teM[0]);print_R($arrayDescriptionFlag);
		$contacts=array();
		foreach ($temp as $values)
			{
			$name=$values[1].(empty($values[2])?'':(empty($values[1])?'':'-')."{$values[2]}").(empty($values[3])?'':" \"{$values[3]}\"");
			if (!empty($values[5]))
				$contacts[$values[5]]=(empty($name)?$values[5]:$name);
			
			if (!empty($values[5]))
				$descriptionArray[$values[5]]=array('first_name'=>(!empty($values[1])?$values[0]:false),
												'middle_name'=>(!empty($values[2])?$values[2]:false),
												'last_name'=>(!empty($values[3])?$values[1]:false),
												'nickname'=>false,
												'email_1'=>(!empty($values[5])?$values[4]:false),
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
			
		print_R($descriptionArray);			
		foreach ($contacts as $email=>$name) if (!$this->isEmail($email)) unset($contacts[$email]);
		return $descriptionArray;
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
		$res=$this->get('http://mail.in.com/logout',true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();	
		}
}
?>

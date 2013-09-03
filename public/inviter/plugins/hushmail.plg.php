<?php
$_pluginInfo=array(
	'name'=>'Hushmail',
	'version'=>'1.0.5',
	'description'=>"Get the contacts from an Hushmail account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'https://m.hush.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	); 
/**
 * Hushmail Plugin
 * 
 * Imports user's contacts from Hushmail's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */	
class hushmail extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	protected $timeout=30;
	public $debug_array=array(
				'initial_get'=>'passphrase',
				'login_post'=>'3Dmobile',
				'get_contacts'=>'compose_from',
				'url_contacts'=>'listItem'
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
		$this->service='hushmail';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res=$this->get("https://m.hush.com/",true);
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"https://m.hush.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"https://m.hush.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action='https://m.hush.com'.$this->getElementString($res,'action="','"');
		$post_elements=$this->getHiddenElements($res);$post_elements['user']=$user;$post_elements['passphrase']=$pass;
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
		$url_compose="https://m.hush.com/m/{$user}@hushmail.com/compose?next=%3Fskin%3Dmobile%26save_skin%3D1";
		$this->login_ok=$url_compose;
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
		else $url = $this->login_ok;
		$res=$this->get($url);
		if ($this->checkResponse('get_contacts',$res))
			$this->updateDebugBuffer('get_contacts',$url,'GET');
		else
			{
			$this->updateDebugBuffer('get_contacts',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action="https://m.hush.com".$this->getElementString($res,'action="','"');
		$post_elements=$this->getHiddenElements($res);$post_elements['compose_from']="{$this->service_user}@hushmail.com";$post_elements['compose_encrypt']='on';$post_elements['compose_sign']='on';$post_elements['compose_save']='on';$post_elements['action']='Add contact (to)';
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse("url_contacts",$res))
			$this->updateDebugBuffer('url_contacts',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('url_contacts',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
			
		$contacts=array();
		$contacts_string_array=$this->getElementDOM($res,"//input[@type='checkbox']",'value');
		foreach($contacts_string_array as $temp)
			{
			$temp=str_replace('>','',str_replace('<','',str_replace('"','',urldecode($temp))));
			$contacts_array_temp=explode(' ',$temp);
			if (isset($contacts_array_temp[1])) $contacts[$contacts_array_temp[1]]=array('first_name'=>(isset($contacts_array_temp[0])?$contacts_array_temp[0]:false),'email_1'=>$contacts_array_temp[1]);
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
		$logout_url="https://m.hush.com/authentication/caracuraa@hushmail.com/logout?skin=mobile&next_webapp_name=contacts_webapp&next_webapp_url_name=contacts";
		$res = $this->get($logout_url,true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
}
?>
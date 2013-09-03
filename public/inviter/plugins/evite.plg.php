<?php
$_pluginInfo=array(
	'name'=>'Evite',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from an Evite account",
	'base_version'=>'1.6.7',
	'type'=>'email',
	'check_url'=>'http://www.evite.com/',
	'requirement'=>'user',
	'allowed_domains'=>array('/(evite.com)/i'),
	'imported_details'=>array('first_name','last_name','email_1'),
	); 
/**
 * Evite Plugin
 * 
 * Imports user's contacts from Evite's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.1
 */	
class evite extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	protected $timeout=30;	
	public $debug_array=array(
				'initial_get'=>'submitForm',
				'login_post'=>'Log out',
				'get_contacts'=>'abCheck'
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
		$this->service='evite';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res=$this->get("http://www.evite.com/loginRegForm?redirect=/pages/addrbook/contactList.jsp",true);
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://www.evite.com/loginRegForm?redirect=/pages/addrbook/contactList.jsp",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.evite.com/loginRegForm?redirect=/pages/addrbook/contactList.jsp",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action="http://www.evite.com/loginRegForm";
		$post_elements=array('cmd'=>'login','submitForm'=>'true','redirect'=>'http://www.evite.com/pages/addrbook/contactList.jsp','emailLogin'=>$user,'passLogin'=>$pass,'rememberMe'=>'on');
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
		
		$this->login_ok="http://www.evite.com/pages/addrbook/contactList.jsp";
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
			$this->updateDebugBuffer('get_contacts',"http://www.evite.com/loginRegForm?redirect=/pages/addrbook/contactList.jsp",'GET');
		else
			{
			$this->updateDebugBuffer('get_contacts',"http://www.evite.com/loginRegForm?redirect=/pages/addrbook/contactList.jsp",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//td[@class='abCheck']";$data=$xpath->query($query);$name="";
		foreach ($data as $node)
			{
			if ($node->getAttribute('style')=='padding-left:0px;width:149px;') $name.=" ".trim($node->nodeValue);
			if ($node->getAttribute('style')=='width:219px;') {$email=trim((string)$node->nodeValue);$contacts[$email]=array('first_name'=>$name,'email_1'=>$email);$name="";}
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
		$logout_url="http://www.evite.com/logout?linkTagger=header";
		$res = $this->get($logout_url,true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
}
?>
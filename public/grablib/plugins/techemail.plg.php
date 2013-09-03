<?php
$_pluginInfo=array(
	'name'=>'Techemail',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from an Techemail account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://techemail.mail.everyone.net/email/scripts/loginuser.pl',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	); 
/**
 * Techemail Plugin
 * 
 * Imports user's contacts from Techemail's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */	
class techemail extends openinviter_base
{
	private $login_ok=false;
	protected $timeout=30;
	public $showContacts=true;
	public $debug_array=array(
				'initial_get'=>'loginName',
				'login_post'=>'oi_sda_firstname',
				'get_contacts'=>'composeMe',
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
		$this->service='techemail';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res=$this->get("http://techemail.mail.everyone.net/email/scripts/loginuser.pl",true);
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://super.popstarmail.org/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://super.popstarmail.org/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action="http://techemail.mail.everyone.net/email/scripts/loginuser.pl?EV1=".$this->getElementString($res,"loginuser.pl?EV1=",'"');
		$post_elements=array('loginName'=>$user,'user_pwd'=>$pass,'login'=>'Login');
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
			
		$url_contacts="http://techemail.mail.everyone.net/email/scripts/contacts.pl?EV1=";
		$this->login_ok=$url_contacts;
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
		$res=$this->get($url,true);
		if ($this->checkResponse('get_contacts',$res))
			$this->updateDebugBuffer('get_contacts',$url,'GET');
		else
			{
			$this->updateDebugBuffer('get_contacts',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//a";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$nameBulk=$node->getAttribute('href');
			if (strpos($nameBulk,'javascript:composeMe')!==false)
				{
				$name=$this->getElementString($nameBulk,'"','"');$email=$node->nodeValue;
				if (!empty($email)) $contacts[$email]=array('first_name'=>(!empty($name)?$name:false),'email_1'=>$email);
				}
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
		$logout_url="http://techemail.mail.everyone.net/email/scripts/logout.pl";
		$res = $this->get($logout_url,true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
}
?>
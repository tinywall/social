<?php
$_pluginInfo=array(
	'name'=>'Mail.in',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from a Mail.in account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://mail.in.com/',
	'requirement'=>'email',
	'allowed_domains'=>array('/(in.com)/i'),
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Mail_in Plugin
 * 
 * Imports user's contacts from Mail.in's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class mail_in extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array('initial_get'=>'frmloginverify',
							  'post_login'=>'inboxmailshide',
							  'contacts_page'=>'displaycontacts',
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
		$this->service='mail_in';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://mail.in.com/");
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://mail.in.com/",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://mail.in.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$form_action='http://mail.in.com'.$this->getElementString($res,'name="frmloginverify" method="POST" action="','"');
		$post_elements=array('f_sourceret'=>'http://mail.in.com/mails/mailstartup','f_id'=>$user,'f_pwd'=>$pass);
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse('post_login',$res))
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
			
		$url_contacts='http://mail.in.com/mails/getcontacts.php';
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
		else $url=$this->login_ok;
		$res=$this->get($url);
		if ($this->checkResponse('contacts_page',$res))
			$this->updateDebugBuffer('contacts_page',$url,'GET');
		else 
			{
			$this->updateDebugBuffer('contacts_page',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//td";$data=$xpath->query($query);
		foreach($data as $node) 
			{
			if (strpos($node->getAttribute('onclick'),'displaycontacts')!==false)
				{
				$name=$node->nodeValue;
				$email_array=explode("'",(string)$node->getAttribute('onclick'));
				if (!empty($email_array[1])) $contacts[$email_array[1]]=array('first_name'=>(!empty($name)?$name:false),'email_1'=>$email_array[1]);
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
		$res=$this->get('http://mail.in.com/logout',true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();	
		}
}
?>

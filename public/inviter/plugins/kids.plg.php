<?php
$_pluginInfo=array(
	'name'=>'Kids',
	'version'=>'1.0.2',
	'description'=>"Get the contacts from a Kids account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://www.kids.co.uk/email/index.php',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Kids Plugin
 * 
 * Import user's contacts from Kids account
 *
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class kids extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'login_id',
				'login_post'=>'frame',
				'url_contacts'=>'doaddresses.php?_MATRIXaction=',
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
		$this->service='kids';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;

		$res=$this->get("http://www.kids.co.uk/email/index.php",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.kids.co.uk/email/index.php",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.kids.co.uk/email/index.php",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action="http://www.kids.co.uk/email/home/dologin.php";
		$post_elements=array('did'=>2,
							 'login_id'=>$user,
							 'did'=>2,
							 'login_pwd'=>$pass,
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

		$url_addressbook='http://www.kids.co.uk/email/home/addressbook.php';
		$this->login_ok=$url_addressbook;
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
		if ($this->checkResponse("url_contacts",$res))
			$this->updateDebugBuffer('url_contacts',$url,'GET');
		else
			{
			$this->updateDebugBuffer('url_contacts',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//a";$data=$xpath->query($query);$odd=true;
		foreach($data as $node)
			{
			if (strpos($node->getAttribute('href'),'doaddresses.php?_MATRIXaction=Modify')!==false)
				{
				if ($odd) $names[]=$node->nodeValue;else $emails[]=$node->nodeValue;
				$odd=!$odd;
				}
			}
		if (!empty($names)) foreach($names as $key=>$value) if(!empty($emails[$key])) $contacts[$emails[$key]]=array('first_name'=>$value,'email_1'=>$emails[$key]);
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
		$res=$this->get("http://kids.co.uk/email/dologout.php",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
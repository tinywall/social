<?php
$_pluginInfo=array(
	'name'=>'Virgilio',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from an virgilio.it account",
	'base_version'=>'1.0.0',
	'type'=>'email',
	'check_url'=>'http://mobimail.virgilio.it/cp/ps/Main/login/LoginVirgilio?d=virgilio.it',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Virgilio.it Plugin
 * 
 * Imports user's contacts from Virgilio.it account
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class virgilio extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	private $sessionVer;
	public $debug_array=array('initial_get'=>'login_type',
			  				  'login_post'=>'&t=',
			  				  'url_contact'=>'contatti'
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
		$this->service='virgilio';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res = $this->get("http://mobimail.virgilio.it/cp/ps/Main/login/LoginVirgilio?d=virgilio.it",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://mobimail.virgilio.it/cp/ps/Main/login/LoginVirgilio?d=virgilio.it",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://mobimail.virgilio.it/cp/ps/Main/login/LoginVirgilio?d=virgilio.it",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$form_action="http://mobimail.virgilio.it/cp/ps/Main/login/WrapLogin";
		$post_elements=array('p'=>false,'login_type'=>'virgilio','NGUserID'=>'null','u'=>$user,'password'=>$pass,'d'=>'virgilio.it');
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
		$vergilioT=$this->getElementString($res,'&t=','&');$this->sessionVer=$vergilioT;
		$url_contacts="http://mobimail.virgilio.it/cp/ps/PSPab/Contacts?d=virgilio.it&u={$user}&t={$vergilioT}&reset=true&startAt=1&l=it";
		$this->login_ok=$url_contacts;
		file_put_contents($this->getLogoutPath(),$vergilioT);
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
		if ($this->checkResponse("url_contact",$res))
			$this->updateDebugBuffer('url_contact',$this->login_ok,'GET');
		else
			{
			$this->updateDebugBuffer('url_contact',$this->login_ok,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$contacts=array();
		$nrFriends=(int)$this->getElementString($res,"Hai "," contatti");$exit=0;$page=1;
		while($nrFriends>count($contacts))
			{
			$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
			$xpath=new DOMXPath($doc);$query="//a";$data=$xpath->query($query);
			foreach($data as $node)
				if (strpos($node->getAttribute('href'),'PABreturnURL=Contacts')!==false) 
					{ $name=$node->childNodes->item(0)->nodeValue;$email=$node->childNodes->item(2)->nodeValue;if (!empty($email)) $contacts[$email]=array('first_name'=>(!empty($name)?$name:false),'email_1'=>$email); }
			$page++;$res=$this->get("http://mobimail.virgilio.it/cp/ps/PSPab/Contacts?d=virgilio.it&u={$this->service_user}&t={$this->sessionVer}&reset=true&startAt={$page}&l=it",true);		
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
			{$vergilioT=file_get_contents($this->getLogoutPath());$res=$this->get("http://mobimail.virgilio.it/cp/ps/Main/login/Logout?d=virgilio.it&u={$this->service_user}&t={$vergilioT}&l=it",true);}
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
	}
?>
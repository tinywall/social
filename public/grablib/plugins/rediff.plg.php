<?php
$_pluginInfo=array(
	'name'=>'Rediff',
	'version'=>'1.2.1',
	'description'=>"Get the contacts from a Rediff account",
	'base_version'=>'1.8.1',
	'type'=>'email',
	'check_url'=>'http://mail.rediff.com',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Rediff Plugin
 * 
 * Import user's contacts from Rediff's AddressBook
 * 
 * @author OpenInviter
 * @version 1.1.6
 */
class rediff extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	private $sess_id, $username, $siteAddr;
	public $debug_array=array(
			  'login_post'=>'window.location.replace',
			  'url_contacts'=>'var session_id',
			  'url_contacts_form'=>'els',
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
		$this->service='rediff';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$post_elements=array("login"=>"{$user}",
							"passwd"=>"{$pass}",
							"FormName"=>"existing");
		$res=htmlentities($this->post("http://mail.rediff.com/cgi-bin/login.cgi",$post_elements,true));
		if ($this->checkResponse("login_post",$res))
			{
				$this->updateDebugBuffer('login_post',"http://mail.rediff.com/cgi-bin/login.cgi",'POST',true,$post_elements);
				$link_to_extract = $this->getElementString($res, 'window.location.replace(&quot;', '&quot;);');
				$this->siteAddr = $this->getElementString($link_to_extract,'http://','/');
				$this->username = $user;
				$this->sess_id = $this->getElementString($link_to_extract,'&amp;session_id=','&amp;');					
				$url_redirect = "http://{$this->siteAddr}/bn/toggle.cgi?flipval=1&login={$this->username}&session_id={$this->sess_id}&folder=Inbox&formname=sh_folder&user_size=1";
			}
		else
		{
			$this->updateDebugBuffer('login_post',"http://mail.rediff.com/cgi-bin/login.cgi",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
		}
		$res = ($this->get($url_redirect, true));
		if ($this->checkResponse("url_contacts",$res))
			$this->updateDebugBuffer('url_contacts',"{$url_redirect}",'GET');
		else
			{
			$this->updateDebugBuffer('url_contacts',"{$url_redirect}",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$url_contact="http://{$this->siteAddr}/prism/exportaddrbook?output=web";
		$this->login_ok = $url_contact;
		$logout_url = "http://login.rediff.com/bn/logout.cgi?formname=general&login={$this->username}&session_id={$this->sess_id}&function_name=logout";
		file_put_contents($this->getLogoutPath(),$logout_url);
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
		if ($this->checkResponse("url_contacts_form",$res))
			$this->updateDebugBuffer('url_contacts_form',$url,'GET');
		else
			{
			$this->updateDebugBuffer('url_contacts_form',$url,'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$post_elements=array('output'=>'web','els'=>$this->getElementString($res,'name="els" value="','"'),'exporttype'=>'outlook');		
		$form_action="http://{$this->siteAddr}/prism/exportaddrbook?service=outlook";
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse("file_contacts",$res))
			$this->updateDebugBuffer('file_contacts',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('file_contacts',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$temp=$this->parseCSV($res);
		$contacts=array();
		foreach ($temp as $values)
			{
			$name=$values['0'].(empty($values['1'])?'':(empty($values['0'])?'':'-')."{$values['1']}").(empty($values['3'])?'':" \"{$values['3']}\"").(empty($values['2'])?'':' '.$values['2']);
			if (!empty($values['5']))
				$contacts[$values['5']]=array('first_name'=>$name,'email_1'=>$values['5']);			
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
			 $url_logout=file_get_contents($this->getLogoutPath());		
			if (!empty($url_logout)) $res=$this->get($url_logout);
			}
			$this->debugRequest();
			$this->resetDebugger();
			$this->stopPlugin();	
		}
}
?>
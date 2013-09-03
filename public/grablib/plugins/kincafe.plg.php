<?php
/*
 * Created on Feb 10, 2009 by Vlad
 */
 
$_pluginInfo=array(
	'name'=>'Kincafe',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from a kincafe.com account",
	'base_version'=>'1.6.7',
	'type'=>'social',
	'check_url'=>'http://www.kincafe.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
	
/**
 * kincafe Plugin
 * 
 * Import user's contacts from kincafe
 * 
 * @author OpenInviter
 * @version 1.0.1
 */
class kincafe extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'login_post'=>'logout.fam',
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
		$this->service='kincafe';
		$this->service_user=$user;
		$this->service_password=$pass;
	
		if (!$this->init()) return false;

		$res = $this->get('http://www.kincafe.com/signin.fam',true);
		$post_elements = $this->getHiddenElements($res);
		$post_elements['loginForm:username']=$user;
		$post_elements['loginForm:pwd']=$pass;
		$post_elements['loginForm:bottomSignInBtn']='+Sign+In+';
		$res = $this->post("http://www.kincafe.com/signin.fam",$post_elements,true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"http://www.kincafe.com/signin.fam",'POST');		
		else
			{
			$this->updateDebugBuffer('login_post',"http://www.kincafe.com/signin.fam",'POST',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$this->login_ok = "http://www.kincafe.com/fammemlist.fam";
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
		$res = $this->get($url,true);
		//echo $res;
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//tr[@style='background-color:#fbfbfb']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$td=$node->childNodes;
			$name = $td->item(2)->nodeValue;
			$email = $td->item(6)->nodeValue;
			$contacts[$email]=array('first_name'=>(!empty($name)?$name:false),'email_1'=>$email);
			}			
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
		$res=$this->get("http://www.kincafe.com/logout.fam");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	
?>

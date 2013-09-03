<?php
$_pluginInfo=array(
	'name'=>'Web.de',
	'version'=>'1.0.6',
	'description'=>"Get the contacts from an web.de account",
	'base_version'=>'1.6.7',
	'type'=>'email',
	'check_url'=>'http://m.web.de',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * web.de Plugin
 * 
 * Imports user's contacts from web.de's AddressBook
 * 
 * @author OpenInviter
 * @version 1.6.7
 */
class web_de extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	protected $timeout=30;		
	public $debug_array=array(
			 'initial_check'=>'[5]',
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
		$this->service='web_de';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://m.web.de");
		$postElem = $this->getHiddenElements($res);
		$postAction = $this->getElementString($res,'action="','"');
		$s=str_replace(";",'',$this->getElementString($res,'/;s=','/'));
			
		$postAction='/;e=utf-8;s='.$s.'/mail';
		
		$postElem['user']=$user;
		$postElem['passw']=$pass;
		$postElem['sv-remove-name']='Login';
		$res = $this->post("http://m.web.de".$postAction, $postElem, true);
		
		if ($this->checkResponse("initial_check",$res))
			$this->updateDebugBuffer('initial_check',"http://m.web.de".$postAction,'POST');		
		else
			{
			$this->updateDebugBuffer('initial_check',"http://m.web.de".$postAction,'POST',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$url_email=$this->getElementString($res,'[5] <a href="','"');
		
		$this->login_ok =$url_email; 
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
		else
		$url=$this->login_ok;
		//go to url inbox
		$contacts = array();
		$res=$this->get($url,true);
		$res = $this->getElementString($res, '</div><div class="separator">','<input type="hidden"');
		$contacts_array=$this->getElementDOM($res,'//a');
		if(!is_array($contacts_array))
			return false;
		foreach($contacts_array as $key=>$val)
			{
			if ($key%2==0) $name=$val;
            elseif($key%2!=0) 
            	{
                $val = preg_replace('/(.+\\.[A-Z]+)[^A-Z]*$/i',"\$1",trim($val));
               	$contacts[trim($val)]=array('first_name'=>(!empty($name)?$name:false),'email_1'=>trim($val));
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
		if (file_exists($this->getLogoutPath()))
			{
			 $url_logout=file_get_contents($this->getLogoutPath());		
			if (!empty($url_logout)) $res=$this->get($url_logout,true);
			}
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
				
	}
?>
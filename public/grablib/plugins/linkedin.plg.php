<?php
$_pluginInfo=array(
	'name'=>'LinkedIn',
	'version'=>'1.1.2',
	'description'=>"Get the contacts from a LinkedIn account",
	'base_version'=>'1.8.0',
	'type'=>'email',
	'check_url'=>'http://www.linkedin.com',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * LinkedIn
 * 
 * Imports user's email contacts from LinkedIn 
 * 
 * @author OpenInviter
 * @version 1.1.1
 */
class linkedin extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'session_password',
				'login_post'=>'window.location.replace',
				'js_page'=>'csrfToken',
				'get_friends'=>'emailAddress',
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
		$this->service='linkedin';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("https://www.linkedin.com/secure/login?trk=hb_signin");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"https://www.linkedin.com/secure/login?trk=hb_signin",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"https://www.linkedin.com/secure/login?trk=hb_signin",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$form_action="https://www.linkedin.com/secure/login";
		$post_elements=array('csrfToken'=>'guest_token',
							 'session_key'=>$user,
							 'session_password'=>$pass,
							 'session_login'=>'Sign In',
							 'session_login'=>'',
							 'session_rikey'=>''
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
			
		$res=$this->get("http://www.linkedin.com/home",true);
		if ($this->checkResponse("js_page",$res))
			$this->updateDebugBuffer('js_page',"https://www.linkedin.com/secure/login?trk=hb_signin",'GET');
		else
			{
			$this->updateDebugBuffer('js_page',"https://www.linkedin.com/secure/login?trk=hb_signin",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}		
		$this->ajaxSes=$this->getElementString($res,'name="csrfToken" value="','"');
        $url_friends="http://www.linkedin.com/dwr/exec/ConnectionsBrowserService.getMyConnections.dwr";
		$this->login_ok=$url_friends;
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
		else $form_action=$this->login_ok;
		$post_elements=array('callCount'=>'1',
						'JSESSIONID'=>$this->ajaxSes,
						'c0-scriptName'=>'ConnectionsBrowserService',
						'c0-methodName'=>'getMyConnections',
						'c0-param0'=>'string:0',
						'c0-param1'=>'number:-1',
						'c0-param2'=>'string:DONT_CARE',
						'c0-param3'=>'number:10000',
						'c0-param4'=>'boolean:false',
						'c0-param5'=>'boolean:true',
						'xml'=>'true',
						);
		$headers = array('Content-Type'=>'text/plain');
		$res=$this->post($form_action,$post_elements,false,false,false,$headers);
		if ($this->checkResponse("get_friends",$res))
			$this->updateDebugBuffer('get_friends',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('get_friends',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$cr = "/var s\\d+=\\{\\};(.*?)\\.profileLink=/ims";
		$fr = "/var s\\d+=\"([^\"]*)\";s\\d+.firstName=s\\d+/ims";
		$er = "/var s\\d+=\"([^\"]*)\";s\\d+.emailAddress=s\\d+/ims";
		$lr = "/var s\\d+=\"([^\"]*)\";s\\d+.lastName=s\\d+/ims";
		$ar = "/var s\\d+=\"([A-Z#])\";s\\d+\\[\\d+\\]=s\\d+;/ims";
		$dr = "/;s\\d+\\['([A-Z#])'\\]=s\\d+;/ims";		
		preg_match_all($cr, $res, $found, PREG_SET_ORDER);
		foreach ($found as $val) 
			{ $tempHtml= $val[0];if (preg_match($er,$tempHtml,$foundEmails)) { $email=$foundEmails[1];if ($this->isEmail($email)) { $first_name=preg_match($fr,$tempHtml,$foundEmails) ? $foundEmails[1] : ''; $last_name=preg_match($lr,$tempHtml,$foundEmails) ? $foundEmails[1] : '';$last_name;$contacts[$email]=array('first_name'=>isset($first_name)?$first_name:false,'last_name'=>isset($last_name)?$last_name:false,'email_1'=>$email); } } }			
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
		$res=$this->get("https://www.linkedin.com/secure/login?session_full_logout=&trk=hb_signout",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
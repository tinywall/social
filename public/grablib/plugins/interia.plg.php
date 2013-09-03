<?php
$_pluginInfo=array(
	'name'=>'Interia',
	'version'=>'1.0.7',
	'description'=>"Get the contacts from an Interia.pl account, Plugin developed by Bartosz Zarczynski",
	'base_version'=>'1.8.0',
	'type'=>'email',
	'check_url'=>'http://poczta.interia.pl/',
	'requirement'=>'email',
	'allowed_domains'=>array('/(interia.pl)/i', '/(poczta.fm)/i', '/(interia.eu)/i', '/(1gb.pl)/i', '/(2gb.pl)/i', '/(vip.interia.pl)/i', '/(serwus.pl)/i', '/(akcja.pl)/i', '/(czateria.pl)/i', '/(znajomi.pl)/i'),
	'imported_details'=>array('first_name','last_name','email_1'),
	);
/**
 * Interia.pl Plugin
 * 
 * Imports user's contacts from Interia.pl account
 * 
 * @author Bartosz Zarczynski
 * @version 1.0.0
 */
class interia extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;

	
	public $debug_array=array('initial_get'=>'pocztaLoginForm',
			  				  'login_post'=>'side-folders',
			  				  'url_contact'=>'while(1)'
							 );
	private $sid;

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
		$this->service='interia';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res = $this->get("http://poczta.interia.pl/",true);
		
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://poczta.interia.pl/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://poczta.interia.pl/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$at = strpos($user, '@');
		$domain = substr($user, $at+1, strlen($user)-$at);
		
		$form_action="http://ssl.interia.pl/login.html?classicMail=1";
		$post_elements=array('login'=>$user,
							'pass'=>$pass,
							'domain' => $domain,
							'htmlMail' => 'checked',
							'referer' => 'http://poczta.interia.pl/poczta/'
							 );

		$res=$this->post($form_action,$post_elements, true, true, false);
		
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"http://ssl.interia.pl/login.html?classicMail=1",'GET');
		else
			{
			$this->updateDebugBuffer('login_post',"http://ssl.interia.pl/login.html?classicMail=1",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}

		$pos = strpos($res, "logout,uid,");
		$this->sid = substr($res, $pos+11, 16);
		
		$this->login_ok = "http://poczta.interia.pl/html/getcontacts,all,1,uid,$this->sid?inpl_network_request=true";
		
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
			
			$noheader = substr($res, strpos($res, "while(1);[{") + 11, strlen($res) - 2);
			$lines = explode("},{", $noheader);
			$i = 0;
			foreach ($lines as $line)
			{
				$data[$i] = explode(",", $line);
				$i++;
			}
	
			$contacts = array();
			foreach ($data as $line=>$param)
			{
				foreach ($param as $x)
				{
					$pos_mail = strpos($x, "email");
					$email = substr($x, 9, strlen($x)-10);
					$pos_firstname = strpos($x, "firstName");
					if ($pos_firstname != false) $firstname = substr($x, 13, strlen($x)-14);
					$pos_lastname = strpos($x, "lastName");
					if ($pos_lastname != false) $lastname = substr($x, 12, strlen($x)-13);
					if ($pos_mail != false) $contacts[$email] =array('first_name'=>$firstname,'last_name'=>$lastname,'email_1'=>$email);
				}
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
			$res = $this->get("http://poczta.interia.pl/html/logout,uid,$this->sid", true);
			$this->debugRequest();
			$this->resetDebugger();
			$this->stopPlugin();
			return true;
		}
	}
?>
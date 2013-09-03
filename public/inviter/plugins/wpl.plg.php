<?php
$_pluginInfo=array(
	'name'=>'Wp.pt',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from an Wp.pt account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://wap.poczta.wp.pl/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Wp.pt Plugin
 * 
 * Imports user's contacts from Wp.pt account
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class wpl extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;		
	
	public $debug_array=array('initial_get'=>'zaloguj',
			  				  'login_post'=>'addresses.html',
			  				  'url_adress'=>'addraction',
			  				  'url_contact'=>'info'
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
		$this->service='wp';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res = $this->get("http://wap.poczta.wp.pl/",true);
	
		
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://wap.poczta.wp.pl/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://wap.poczta.wp.pl/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action="http://wap.poczta.wp.pl/index.html";
		$post_elements=array('login'=>$user,
							 'password'=>$pass,
							 'zaloguj'=>'Zaloguj'
							); 
		
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',$form_action,'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$url_adress='http://wap.poczta.wp.pl/addresses.html?'.$this->getElementString($res,'addresses.html?','"');
		$this->login_ok=$url_adress;
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
		if ($this->checkResponse("url_adress",$res))
			$this->updateDebugBuffer('url_adress',$url,'GET');
		else
			{
			$this->updateDebugBuffer('url_adress',$url,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$contacts=array();
		$href_array=$this->getElementDOM($res,"//a[@href]",'href');
		foreach ($href_array as $value)
			if (strpos($value,'addresses.html?action=addraction&aid=')!==false)
				{
			
				$res=$this->get("http://wap.poczta.wp.pl/{$value}");
				if ($this->checkResponse("url_contact",$res))
					$this->updateDebugBuffer('url_contact',"http://wap.poczta.wp.pl/{$value}",'GET');
				else
					{
					$this->updateDebugBuffer('url_contact',"http://wap.poczta.wp.pl/{$value}",'GET',false);
					$this->debugRequest();
					$this->stopPlugin();
					return false;
					}
				$contacts_array=$this->getElementDOM($res,"//p[@class='info']");
				$contacts_exploded=explode(' <',$contacts_array[0]);$email=str_replace('>','',$contacts_exploded[1]);$name=trim(preg_replace('/[^(\x20-\x7F)]*/','',$contacts_exploded[0]));
				$contacts[$email]=array('first_name'=>(!empty($name)?$name:false),'email_1'=>$email);
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
		$res=$this->get('http://wap.poczta.wp.pl/');
		$url_logout='http://wap.poczta.wp.pl/index.html?logout=1&ticaid='.$this->getElementString($res,'index.html?logout=1&ticaid=','"');
		$res=$this->get($url_logout);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		}
	}
?>
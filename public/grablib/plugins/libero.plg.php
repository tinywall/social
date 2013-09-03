<?php
$_pluginInfo=array(
	'name'=>'Libero',
	'version'=>'1.0.4',
	'description'=>"Get the contacts from a Libero account",
	'base_version'=>'1.6.3',
	'type'=>'email',
	'check_url'=>'http://imodemail.libero.it/imodeaccess/',
	'requirement'=>'email',
	'allowed_domains'=>array('/(libero.it)/i','/(inwind.it)/i','/(iol.it)/i','/(blu.it)/i'),
	'imported_details'=>array('first_name','last_name','nickname','email_1'),
	);
/**
 * Libero Plugin
 * 
 * Imports user's contacts from Libero's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.2
 */
class libero extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;	
	public $debug_array=array('initial_get'=>'password',
							  'post_login'=>'Location:',
							  'inbox'=>'accesskey="4"',
							  'contacts_page'=>'accesskey="1"',
							  'contact_info'=>'Addr_ln',
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
		$this->service='libero';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://imodemail.libero.it/imodeaccess/",true);
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://imodemail.libero.it/imodeaccess/",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://imodemail.libero.it/imodeaccess/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$form_action="http://imodemail.libero.it/imodemail/servlets/CliLoginImode?Act=enter";
		$domain_array=explode("@",$user);$domain=$domain_array[1];$libero_user=$domain_array[0];
		$post_elements=array(
							"u"=>$libero_user,
							"d"=>$domain,
							"p"=>$pass,
							''=>'Entra'
						    );
		$res=$this->post($form_action,$post_elements,false,true,false,array(),false,false);
		if ($this->checkResponse('post_login',$res))
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('post_login',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
			
		$base_url="http://".$this->getElementString($this->getElementString($res,"Location: ",PHP_EOL),"http://",'/');
		$url_redirect=str_replace(" [following]","",$this->getElementString($res,"Location: ",PHP_EOL));
		$res=$this->get($url_redirect,true);
		if ($this->checkResponse('inbox',$res))
			$this->updateDebugBuffer('inbox',$url_redirect,'GET');
		else 
			{
			$this->updateDebugBuffer('inbox',$url_redirect,'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$url_contacts_array=$this->getElementDOM($res,"//a[@accesskey='4']",'href');
		$url_contacts=array();$url_contacts=array($base_url,$url_contacts_array[0]);
		$this->login_ok=$url_contacts;
		file_put_contents($this->getLogoutPath(),$url_redirect);	
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
		
		$res=$this->get($url[0].$url[1]);
		if ($this->checkResponse('contacts_page',$res))
			$this->updateDebugBuffer('contacts_page',$url[0].$url[1],'GET');
		else 
			{
			$this->updateDebugBuffer('contacts_page',$url[0].$url[1],'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		
		$contacts=array();$next=true;
		while($next)
			{
			$url_next_array=$this->getElementDOM($res,"//a[@accesskey='1']",'href');
			$url_next=$url[0].$url_next_array[0];
			$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
			$xpath=new DOMXPath($doc);$query="//a";$data=$xpath->query($query);
			foreach($data as $node) 
				{
				if (strpos($node->getAttribute('href'),'Act_Role=0')!==false)
					{
					$url_contact=$url[0].$node->getAttribute('href');
					$res=$this->get($url_contact,true);
					if ($this->checkResponse('contact_info',$res))
						$this->updateDebugBuffer('contact_info',$url_contact,'GET');
					else 
						{
						$this->updateDebugBuffer('contact_info',$url_contact,'GET',false);
						$this->debugRequest();
						$this->stopPlugin();
						return false;	
						}
					
					$cognome=$this->getElementString($res,'name="Addr_ln" value="','"');
					$nome=$this->getElementString($res,'name="Addr_fn" value="','"');
					$alias=$this->getElementString($res,'name="Addr_alias" value="','"');
					$email=$this->getElementString($res,'Addr_mail" value="','"');					
					if (!empty($email)) $contacts[$email]=array('first_name'=>$nome,'last_name'=>$cognome,'nickname'=>$alias,'email_1'=>$email);
					}	
				}
			if (!empty($url_next_array[0])) $res=$this->get($url_next,true);
			else $next=false;
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
			$url=file_get_contents($this->getLogoutPath());
			$res=$this->get($url,true);
			$url_logout=$this->getElementDOM($res,"//a[@accesskey='8']",'href');
			$base_url="http://".$this->getElementString($url,"http://",'/');
			$res=$this->get($base_url.$url_logout[0]);
			$res=$this->get("http://portal.imode.wind.it/gprs/mn/main.htm",true);
			}
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();	
		}
}
?>

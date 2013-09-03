<?php
$_pluginInfo=array(
	'name'=>'Inbox.com',
	'version'=>'1.0.6',
	'description'=>"Get the contacts from an Inbox.com account",
	'base_version'=>'1.8.0',
	'type'=>'email',
	'check_url'=>'https://www.inbox.com/xm/login.aspx',
	'requirement'=>'email',
	'allowed_domains'=>array('/(inbox.com)/i'),
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Inbox.com Plugin
 * 
 * Imports user's contacts from Inbox.com's AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class inbox extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'ACT',
				'post_login'=>'Location:',
				'inbox'=>'accesskey="8"',
				'contacts'=>'checkbox'
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
		$this->service='inbox';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("https://www.inbox.com/xm/login.aspx");
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"https://www.inbox.com/xm/login.aspx",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"https://www.inbox.com/xm/login.aspx",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$form_action="https://www.inbox.com/xm/login.aspx";
		$post_elements=array('ACT'=>'LGN',
							 'login'=>$user,
							 'pwd'=>$pass,
							 'cmdLgn'=>'Sign In'
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
			
		$base_url="http://".$this->getElementString($this->getElementString($res,"Location: ",PHP_EOL),"http://",'?ACT');
		$url_redirect=trim(str_replace(" [following]","",$this->getElementString($res,"Location: ",PHP_EOL)));
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
		
		
		$url_contacts_array=$this->getElementDOM($res,"//a[@accesskey='8']",'href');
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
		//go to url adressbook
		$res=$this->get($url[0].$url[1],true);

		$form_action=$url[0]."default.aspx";$post_elements=$this->getHiddenElements($res);$post_elements['cmdADDR']="To: Addr";
		$res=$this->post($form_action,$post_elements,true);
		if ($this->checkResponse('contacts',$res))
			$this->updateDebugBuffer('contacts',"{$form_action}",'POST',true,$post_elements);
		else 
			{
			$this->updateDebugBuffer('contacts',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;	
			}
		$contacts=array();$page=1;
		$total_nr_friends_array=explode("of ",$this->getElementString($res,'<td align="center">','<'));if (!empty($total_nr_friends_array[1])) $total_nr_friends=trim($total_nr_friends_array[1]);else $total_nr_friends=0;$friend_contor=0;
		while($total_nr_friends>$friend_contor)
			{
			$contacts_temp=$this->getElementDOM($res,"//input[@type='checkbox']",'value');
			foreach($contacts_temp as $value)
				{
				$contacts_array=explode("<",$value);
				if (!empty($contacts_array[1])) $contacts[str_replace(">","",$contacts_array[1])]=array('first_name'=>(!empty($contacts_array[0])?trim($contacts_array[0]):false),'email_1'=>str_replace(">","",$contacts_array[1]));
				else $contacts[$value]=array('first_name'=>false,'email_1'=>$value);
				$friend_contor++;
				}
			$page++;$res=$this->get("{$form_action}?ACT=CPAB&AF=1&CID=-1&PG={$page}");
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
			$url_logout=str_replace('?ACT=INIT','default.aspx?ACT=LGO',$url);
			$res=$this->get($url_logout,true);
			}
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	

?>
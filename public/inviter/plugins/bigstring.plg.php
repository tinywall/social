<?php
$_pluginInfo=array(
	'name'=>'Bigstring',
	'version'=>'1.0.5',
	'description'=>"Get the contacts from an Bigstring account",
	'base_version'=>'1.8.0',
	'type'=>'email',
	'check_url'=>'http://www.bigstring.com/?old=1',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Bigstring Plugin
 * 
 * Imports user's contacts from Bigstring AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class bigstring extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array('initial_get'=>'user',
			  				  'login_post'=>'progress_upload_bar',
			  				  'url_contacts'=>'contacts'
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
		$this->service='bigstring';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res = $this->get("http://www.bigstring.com");
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.bigstring.com",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.bigstring.com",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$form_action='http://www.bigstring.com/mail/index.php';
		$post_elements=array('user'=>$user,'pass'=>$pass); 		
 		$res=$this->post($form_action,$post_elements,true);
 		$res=$this->get("http://www.bigstring.com/mail/mailbox.php",true);
 		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',$form_action,'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$url_contacts="http://www.bigstring.com/mail/ajax/contacts/viewcontact.php";
		$this->login_ok=$url_contacts;
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
		$post_elements=array('user'=>$this->service_user."@bigstring.com",'pass'=>$this->service_password,"lang"=>"en");
		$res=$this->post($url,$post_elements);
		if ($this->checkResponse("url_contacts",$res))
			$this->updateDebugBuffer('url_contacts',$url,'POST');
		else
			{
			$this->updateDebugBuffer('url_contacts',$url,'POST',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$contacts=array();
		if (preg_match_all("#\(\'\'\,\'(.+)\'\,\'(.+)\'#U",$res,$matches))
			{
			foreach($matches[2] as $key=>$name)
				if (!empty($matches[1][$key])) $contacts[$matches[1][$key]]=array('email_1'=>$matches[1][$key],'first_name'=>$name);	
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
		$res=$this->get("http://www.bigstring.com/email/logout.php",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		}
	}
?>
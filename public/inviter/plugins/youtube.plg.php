<?php
$_pluginInfo=array(
	'name'=>'YouTube',
	'version'=>'1.0.2',
	'description'=>"Get the contacts from a YouTube account AddressBook ",
	'base_version'=>'1.8.0',
	'type'=>'email',
	'check_url'=>'http://www.youtube.com',
	'requirement'=>'user',
	'allowed_domains'=>false,
	);
/**
 * Youtube Plugin
 * 
 * Imports user's contacts from YouTube AddressBook and

 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class youtube extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;
	public $debug_array=array(
			  'initial_get'=>'ltmpl',
			  'login_post'=>'location.replace(',
			  'url_redirect'=>'gXSRF_token',
			  'url_addressbook'=>'YT_address_book',
			  'url_contacts'=>'fid'
			  
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
		$this->service='youtube';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res=$this->get("https://www.google.com/accounts/ServiceLogin?uilel=3&service=youtube&passive=true&continue=http%3A%2F%2Fwww.youtube.com%2Fsignup%3Fnomobiletemp%3D1%26hl%3Den_US%26next%3D%252F&hl=en_US&ltmpl=sso");
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"https://www.google.com/accounts/ServiceLogin?uilel=3&service=youtube&passive=true&continue=http%3A%2F%2Fwww.youtube.com%2Fsignup%3Fnomobiletemp%3D1%26hl%3Den_US%26next%3D%252F&hl=en_US&ltmpl=sso",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"https://www.google.com/accounts/ServiceLogin?uilel=3&service=youtube&passive=true&continue=http%3A%2F%2Fwww.youtube.com%2Fsignup%3Fnomobiletemp%3D1%26hl%3Den_US%26next%3D%252F&hl=en_US&ltmpl=sso",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();	
			return false;
			}
		
		$form_action="https://www.google.com/accounts/ServiceLoginAuth?service=youtube";
		$post_elements=array('ltmpl'=>'sso',
							 'continue'=>'http://www.youtube.com/signin?action_handle_signin=true&nomobiletemp=1&hl=en_US&next=%2Findex',
							 'next'=>'/',
							 'service'=>'youtube',
							 'uilel'=>3,
							 'ltmpl'=>'sso',
							 'hl'=>'en_US',
							 'ltmpl'=>'sso',
							 'GALX'=>$this->getElementString($res,'name="GALX" value="','"'),
							 'Email'=>$user,
							 'Passwd'=>$pass,
							 'PersistentCookie'=>'yes',
							 'rmShown'=>1,
							 'signIn'=>'Sign in',
							 'asts'=>false,
							);
		$res=$this->post($form_action,$post_elements,true,true);
		if ($this->checkResponse('login_post',$res))
			$this->updateDebugBuffer('login_post',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',$form_action,'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$url_redirect=html_entity_decode(urldecode(str_replace('\x', '%', $this->getElementString($res,'location.replace("','"'))));
		$res=$this->get($url_redirect,true);
	    if ($this->checkResponse('url_redirect',$res))
			$this->updateDebugBuffer('url_redirect',"{$url_redirect}",'GET');
		else 
			{
			$this->updateDebugBuffer('url_redirect',"{$url_redirect}",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();	
			return false;
			}
			
		$this->login_ok='http://www.youtube.com/address_book';
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
		if ($this->login_ok===false)
			{
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		else $url=$this->login_ok;
		
		$res=$this->get($url);
		if ($this->checkResponse('url_addressbook',$res))
			$this->updateDebugBuffer('url_addressbook',"{$url}",'GET');
		else 
			{
			$this->updateDebugBuffer('url_addressbook',"{$url}",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();	
			return false;
			}
		
		$form_action='http://www.youtube.com/address_book?action_ajax=1';
		$post_elements=array('session_token'=>$this->getElementString($res,"YT_address_book('session_token=","'"),
							'messages'=>'[{"type":"ajax_fetch_contacts","request":{"gid":"_all_contacts_","link_count":1000,"page":1}}]',
							);
		$res=$this->post($form_action,$post_elements);
		if ($this->checkResponse('url_contacts',$res))
			$this->updateDebugBuffer('url_contacts',$form_action,'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('url_contacts',$form_action,'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$contacts=array();
		if (preg_match_all("#fid\"\: \"(.+)\"\}#U", $res, $matchesArray))
			{		
			if (!empty($matchesArray[1]))
				foreach($matchesArray[1] as $dummy=>$id)
					{
					$emailsArray=array();$namesArray=array();
					$res=$this->get("http://www.youtube.com/address_book?action_display_contact_details=1&fid={$id}");
					if (preg_match_all("#mailto\:(.+)\"#U", $res, $emailsArray)) 
						{
						if (preg_match_all("#\"\/user\/(.+)\"#U", $res, $namesArray)) $contacts[$emailsArray[1][0]]=array('first_name'=>$namesArray[1][0],'email_1'=>$emailsArray[1][0]);
						else $contacts[$emailsArray[1][0]]=array('first_name'=>$emailsArray[1][0],'email_1'=>$emailsArray[1][0]);
						}
					}  
			}
		foreach ($contacts as $email=>$dummy) if (!$this->isEmail($email)) unset($contacts[$email]);
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
		$res=$this->get('http://www.youtube.com/index',true);
		$post_elements=array('action_logout'=>1,
							 'session_token'=>$this->getElementString($res,"YT_php_support('","'"),
							 );
		$res=$this->post('http://www.youtube.com/index',$post_elements,true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
}
?>
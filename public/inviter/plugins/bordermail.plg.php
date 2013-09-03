<?php
$_pluginInfo=array(
	'name'=>'Bordermail',
	'version'=>'1.0.3',
	'description'=>"Get the contacts from a Bordermail account",
	'base_version'=>'1.6.5',
	'type'=>'email',
	'check_url'=>'http://www.boardermail.com/',
	'requirement'=>'user',
	'allowed_domains'=>false,
	'imported_details'=>array('first_name','email_1'),
	);
/**
 * Bordermail Plugin
 * 
 * Imports user's contacts from Bordermail AddressBook
 * 
 * @author OpenInviter
 * @version 1.0.0
 */
class bordermail extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'name="mailcom"',
				'login_post'=>'mailcomframe',
				'inbox'=>'outblaze',
				'export_page'=>'addrURL',
				'post_contacts'=>'csv',
				'file_contacts'=>'Title'
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
		$this->service='bordermail';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://www.mail.com/login.aspx?domain=boardermail.com&lang=en",true);
		
		if ($this->checkResponse('initial_get',$res))
			$this->updateDebugBuffer('initial_get',"http://www.mail.com/login.aspx?domain=boardermail.com&lang=en",'GET');
		else 
			{
			$this->updateDebugBuffer('initial_get',"http://www.mail.com/login.aspx?domain=boardermail.com&lang=en",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			} 
		$form_action=$this->getElementString($res,'name="mailcom"  action="','"');
		$post_elements=array("login"=>"{$user}","password"=>"{$pass}","redirlogin"=>1,"siteselected"=>"normal");
		$res=$this->post($form_action,$post_elements,true);
		
		if ($this->checkResponse('login_post',$res))	
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}		
		$url_redirect=$this->getElementDOM($res,"//frame[@name='mailcomframe']",'src');
		$res=$this->get($url_redirect[0],true);
		
		$this->login_ok=$url_redirect[0];
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
		
		if ($this->checkResponse('inbox',$res))
			$this->updateDebugBuffer('login_post',"{$url}",'GET');
		else
			{
			$this->updateDebugBuffer('login_post',"{$url}",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}	
		$url_contacts=$this->getElementDOM($res,"//a[@id='addrURL']",'href'); 
		$res=$this->get($url_contacts[0],true);
		
		if ($this->checkResponse("export_page",$res))
			{
			$url_export="";
			$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
			$xpath=new DOMXPath($doc);$query="//a[@href]";$data=$xpath->query($query);
			foreach($data as $val) 
			if (strstr($val->nodeValue,"Import/Export")) $url_export=$val->getAttribute('href')."&gab=1";
			$this->updateDebugBuffer('post_contacts',"{$url_contacts[0]}",'GET');
			}
		else
			{
			$this->updateDebugBuffer('post_contacts',"{$url_contacts[0]}",'GET',false);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$post_elements=array("showexport"=>"showexport","action"=>"export","format"=>"csv");
		$res=$this->post($url_export,$post_elements);
		
		if ($this->checkResponse('file_contacts',$res))
			{
			$temp=$this->parseCSV($res);		
			$contacts=array();
			foreach ($temp as $values)
				{
				$name=$values['0'].(empty($values['1'])?'':(empty($values['0'])?'':'-')."{$values['1']}").(empty($values['3'])?'':" \"{$values['3']}\"").(empty($values['2'])?'':' '.$values['2']);
				if (!empty($values['4']))
					$contacts[$values['4']]=(empty($name)?$values['4']:$name);
				if (!empty($values['12']))
					$contacts[$values['12']]=(empty($name)?$values['12']:$name);
				if (!empty($values['13']))
					$contacts[$values['13']]=(empty($name)?$values['13']:$name);
				}
				
			//full description
			$teM=explode(PHP_EOL,$res);$arrayDescriptionFlag=explode(',',$teM[0]);
			foreach($temp as $tempA)
				{
				$name=$tempA['0'].(empty($tempA['1'])?'':(empty($tempA['0'])?'':'-')."{$tempA['1']}").(empty($tempA['3'])?'':" \"{$tempA['3']}\"").(empty($tempA['2'])?'':' '.$tempA['2']);
				foreach ($arrayDescriptionFlag as $key=>$value)
					$descriptionArray[(!empty($name)?$name:(!empty($tempA[4])?$tempA[4]:false))][$value]=isset($tempA[$key])?$tempA[$key]:false;
				}		
			//print_R($descriptionArray);	
						
			$this->updateDebugBuffer('login_post',"{$url_export}",'POST',true,$post_elements);
			}
		else
			{
			$this->updateDebugBuffer('login_post',"{$url_export}",'POST',false,$post_elements);	
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		foreach ($contacts as $email=>$name) if (!$this->isEmail($email)) unset($contacts[$email]);
		return $descriptionArray;
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
		$res=$this->get("http://www.mail.com/logout.aspx");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	
	}	
?>
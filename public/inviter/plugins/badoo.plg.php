<?php
/*
 * Created on Feb 10, 2009 by Vlad
 */
 
$_pluginInfo=array(
	'name'=>'Badoo',
	'version'=>'1.0.5',
	'description'=>"Get the contacts from a badoo.com account",
	'base_version'=>'1.6.7',
	'type'=>'social',
	'check_url'=>'http://www.badoo.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
class badoo extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'login_post'=>'location',
				'get_friends'=>'name',
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
		$this->service='badoo';
		$this->service_user=$user;
		$this->service_password=$pass;
	
		if (!$this->init()) return false;

		$res = $this->get('http://badoo.com/?lang_id=3',true);
		$url = $this->getElementString($res,'<a href="http://badoo.com/signin/','" class="sign_in">');
		$res = $this->get("http://badoo.com/signin/".$url,true);
		$post_elements=array();
		$post_elements['email']=$user;
		$post_elements['password']=$pass;
		$post_elements['post']='';
		$res = $this->post("http://badoo.com/signin/",$post_elements,true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"http://badoo.com/signin/",'POST');		
		else
			{
			$this->updateDebugBuffer('login_post',"http://badoo.com/signin/",'POST',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$id=$this->getElementString($res,"user_id=","&");
		if (!is_numeric($id)) return false;
		$this->login_ok=$id;
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
		else $id=$this->login_ok;
		$res=$this->get("http://badoo.com/{$id}/contacts/subscriptions.phtml",true);
		if ($this->checkResponse("get_friends",$res))
			$this->updateDebugBuffer('get_friends',"http://badoo.com/{$id}/contacts/subscriptions.phtml",'GET');
		else
			{
			$this->updateDebugBuffer('get_friends',"http://badoo.com/{$id}/contacts/subscriptions.phtml",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$contacts=array();
		$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$query="//a[@class='name']";$data=$xpath->query($query);
		foreach($data as $node)
			{
			$name=$node->nodeValue;
			$cId=str_replace('uid1','01',$node->getAttribute('id'));
			$href="http://badoo.com/{$id}/contacts/message/{$cId}";
			if (!empty($href)) $contacts[$href]=$name;
			}
		return $contacts;
		}
	/**
	 * Send message to contacts
	 * 
	 * Sends a message to the contacts using
	 * the service's inernal messaging system
	 * 
	 * @param string $cookie_file The location of the cookies file for the current session
	 * @param string $message The message being sent to your contacts
	 * @param array $contacts An array of the contacts that will receive the message
	 * @return mixed FALSE on failure.
	 */
	public function sendMessage($session_id,$message,$contacts)
		{
		$countMessages=0;
		foreach($contacts as $url=>$name)
			{
			$countMessages++;
			$res = $this->get($url."?swf=1");
			$master_id = $this->getElementString($url,'http://badoo.com/','/'); 
			$post_url = "http://badoo.com/{$master_id}/contacts/ws-post.phtml";
			$post_elements = array('s1'=>$this->getElementString($res,'name="s1" value="','"'),'contact_user_id'=>$this->getElementString($res,'name="contact_user_id" value="','"'),'action'=>'add');
			$post_elements['flash'] = '1';
			$post_elements['message'] = $message['subject']."<br>".$message['body'];
			$res = $this->post($post_url,$post_elements,true);
			sleep($this->messageDelay);
			if ($countMessages>$this->maxMessages) {$this->debugRequest();$this->resetDebugger();$this->stopPlugin();break;}
			}
		return true;
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
		$res=$this->get("http://badoo.com/signout/");
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	
?>

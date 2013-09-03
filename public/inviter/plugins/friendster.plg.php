<?php
/*Import Friends from Friendster.com
 * You can send private message using Friendster system to your Friends
 */
$_pluginInfo=array(
	'name'=>'Friendster',
	'version'=>'1.1.0',
	'description'=>"Get the contacts from a Friendster account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.friendster.com',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * Friendster Plugin
 * 
 * Import user's contacts from Friendster and send messages
 * using Friendster's internal messaging system
 * 
 * @author OpenInviter
 * @version 1.0.8
 */
class friendster extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	
	public $debug_array=array(
				'initial_get'=>'_submitted',
				'login_post'=>'pageViewerID',
				'contacts'=>'thumbnaildelete',
				'message_compose'=>'msg_type',
				'message_send'=>'noliststyle noindent'
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
		$this->service='friendster';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
				
		$res=$this->get("http://www.friendster.com/");	
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://www.friendster.com/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://www.friendster.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$form_action="http://www.friendster.com/login.php";
		$post_elements=array('tzoffset'=>1,
							 'next'=>'/',
							 '_submitted'=>1,
							 'email'=>$user,
							 'password'=>$pass						
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
		
		$url_friends="http://www.friendster.com/friends.php";	
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
		else $url=$this->login_ok;
		
		$res=$this->get($url,true);
		$contacts=array();
		$page_next=0;
		$number_of_friends_array=explode("of ",$this->getElementString($res,'class="paginglinksmodule">','<'));
		if (!empty($number_of_friends_array[1])) $total_friends=$number_of_friends_array[1];
		else $total_friends=0;
		do
			{
			$page_next++;
			
			//go to url friends
			if ($this->checkResponse('contacts',$res))
				$this->updateDebugBuffer('contacts',$url,'GET');
			else
				{
				$this->updateDebugBuffer('contacts',$url,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
			$xpath=new DOMXPath($doc);$query="//span[@class='strong']";$data=$xpath->query($query);
			foreach ($data as $node)
				$contacts[str_replace("http://profiles.friendster.com/","",(string)$node->firstChild->getAttribute('href'))]=(string)$node->firstChild->nodeValue;
			$url_next="{$url}?page={$page_next}";
			$res=$this->get($url_next,true);		 
			}
		while ($total_friends>count($contacts)+1);	
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
		foreach ($contacts as $id=>$name)
			{
			$countMessages++;
			$res=$this->get("http://www.friendster.com/sendmessage.php?uid={$id}",true);
			if ($this->checkResponse('message_compose',$res))
				$this->updateDebugBuffer('message_compose',"http://www.friendster.com/sendmessage.php?uid={$id}",'GET');
			else
				{
				$this->updateDebugBuffer('message_compose',"http://www.friendster.com/sendmessage.php?uid={$id}",'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
				
			$form_action="http://www.friendster.com/sendmessage.php";
			$post_elements=$this->getHiddenElements($res);
			$post_elements['message']=$message['body'];
			$post_elements['subject']=$message['subject'];
			$res=$this->post($form_action,$post_elements,true);
			if ($this->checkResponse('message_send',$res))
				$this->updateDebugBuffer('message_send',"{$form_action}",'POST',true,$post_elements);
			else
				{
				$this->updateDebugBuffer('message_send',"{$form_action}",'POST',false,$post_elements);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			sleep($this->messageDelay);
			if ($countMessages>$this->maxMessages) {$this->debugRequest();$this->resetDebugger();$this->stopPlugin();break;}
			}		
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
		$res=$this->get("http://www.friendster.com/logout.php",true);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}
	
?>
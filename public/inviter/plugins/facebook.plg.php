<?php
/*Import Friends from Facebook
 * You can send message to your Friends Inbox
 */
$_pluginInfo=array(
	'name'=>'Facebook',
	'version'=>'1.2.9',
	'description'=>"Get the contacts from a Facebook account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://apps.facebook.com/causes/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	);
/**
 * FaceBook Plugin
 * 
 * Imports user's contacts from FaceBook and sends
 * messages using FaceBook's internal system.
 * 
 * @author OpenInviter
 * @version 1.0.8
 */
class facebook extends openinviter_base
	{
	private $login_ok=false;
	public $showContacts=true;
	public $internalError=false;
	protected $timeout=30;
	protected $userId;
	
	public $debug_array=array(
				'initial_get'=>'pass',
				'login_post'=>'javascript',
				'get_user_id'=>'profile.php?id=',				
				'url_friends'=>'fb_dtsg:"',								
				'message_elements'=>'mailBoxItems',
				'send_message'=>'"error":0',
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
		$this->service='facebook';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		
		$res=$this->get("http://apps.facebook.com/causes/",true);
		if ($this->checkResponse("initial_get",$res))
			$this->updateDebugBuffer('initial_get',"http://apps.facebook.com/causes/",'GET');
		else
			{
			$this->updateDebugBuffer('initial_get',"http://apps.facebook.com/causes/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}		
		$form_action="https://login.facebook.com/login.php?login_attempt=1";
		$post_elements=array('email'=>$user,
							 'pass'=>$pass,
							 'next'=>'http://apps.facebook.com/causes/home?_method=GET',
							 'return_session'=>0,
							 'req_perms'=>0,
							 'session_key_only'=>0,
							 'api_key'=>$this->getElementString($res,'name="api_key" value="','"'),
							 'version'=>'1.0',
							 );
		$res=$this->post($form_action,$post_elements,true,true);	
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',true,$post_elements);
		else
			{
			$this->updateDebugBuffer('login_post',"{$form_action}",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
		$res=$this->get('http://facebook.com/',true);		
		if ($this->checkResponse("get_user_id",$res))
			$this->updateDebugBuffer('get_user_id',"http://facebook.com/",'GET');
		else
			{
			$this->updateDebugBuffer('get_user_id',"http://facebook.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}						
		$this->userId=$this->getElementString($res,"{user:",',');						
		if (empty($this->userId)) $this->login_ok=false;
		else $this->login_ok="http://www.facebook.com/ajax/social_graph/fetch.php?__a=1";
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
		
		$res=$this->get("http://www.facebook.com/profile.php?id={$this->userId}",true);
		if (strpos($res,'window.location.replace("')!==FALSE)
			{
			$url_redirect=stripslashes($this->getElementString($res,'window.location.replace("','"'));
			if (!empty($url_redirect)) $res=$this->get($url_redirect,true);	
			}		
		if ($this->checkResponse("url_friends",$res))
			$this->updateDebugBuffer('url_friends',"http://www.facebook.com/profile.php?id={$this->userId}&ref=profile",'GET');
		else
			{
			$this->updateDebugBuffer('url_friends',"http://www.facebook.com/profile.php?id={$this->userId}&ref=profile",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}	
		$postFormId=$this->getElementString($res,'name="post_form_id" value="','"');
		$fbDtsg=$this->getElementString($res,'fb_dtsg:"','"');
		$page=0;		
		$form_action=$this->login_ok;
		$post_elements=array('edge_type'=>'browse',
							 'page'=>$page,
							 'limit'=>100,
							 'node_id'=>$this->userId,
							 'class'=>'FriendManager',
							 'post_form_id'=>$postFormId,
							 'fb_dtsg'=>$fbDtsg,
							 'post_form_id_source'=>'AsyncReques',
							);							
		$res=$this->post($form_action,$post_elements,true);	
		//!!!		
		$contacts=array();
		while(preg_match_all("#\{\"id\"\:(.+)\,\"title\"\:\"(.+)\"#U",$res,$matches))
			{
			$page++;
			$post_elements=array('edge_type'=>'browse',
							 'page'=>$page,
							 'limit'=>100,
							 'node_id'=>$this->userId,
							 'class'=>'FriendManager',
							 'post_form_id'=>$postFormId,
							 'fb_dtsg'=>$fbDtsg,
							 'post_form_id_source'=>'AsyncReques',
							);
			$res=$this->post($form_action,$post_elements);
			if (!empty($matches[1])) 
				foreach($matches[1] as $key=>$fbId) 
					if (!empty($matches[2][$key])) $contacts[$fbId]=$matches[2][$key];					
			}								
		return $contacts;
		}

	/**
	 * Send message to contacts
	 * 
	 * Sends a message to the contacts using
	 * the service's inernal messaging system
	 * 
	 * @param string $session_id The OpenInviter user's session ID
	 * @param string $message The message being sent to your contacts
	 * @param array $contacts An array of the contacts that will receive the message
	 * @return mixed FALSE on failure.
	 */
	public function sendMessage($session_id,$message,$contacts)
		{
		$countMessages=0;							
		$res=$this->get('http://www.facebook.com/?sk=messages',true);
		if ($this->checkResponse("message_elements",$res))
			$this->updateDebugBuffer('message_elements',"http://www.facebook.com/home.php?#!/?sk=messages",'GET');
		else
			{
			$this->updateDebugBuffer('message_elements',"http://www.facebook.com/home.php?#!/?sk=messages",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$composerId=$this->getElementString($res,'"composer_id\" value=\"','\"');
		$postFormId=$this->getElementString($res,'name="post_form_id" value="','"');
		$userId=$this->getElementString($res,"www.facebook.com\/profile.php?id=",'\"');
		$fbDtsg=$this->getElementString($res,'fb_dtsg:"','"');
		$form_action="http://www.facebook.com/ajax/gigaboxx/endpoint/MessageComposerEndpoint.php?__a=1";
		$post_elements=array();
		foreach($contacts as $fbId=>$name)
			{						
			$countMessages++;
			if ($countMessages>$this->maxMessages) break;			
			$post_elements=array("ids_{$composerId}[0]"=>$fbId,
								  "ids[0]"=>$fbId,
								  'subject'=>$message['subject'],
								  'status'=>$message['body'],
								  'action'=>'send_new',
								  'home_tab_id'=>1,
								  'profile_id'=>$userId,
								  'target_id'=>0,							  
								  'composer_id'=>$composerId,
								  'hey_kid_im_a_composer'=>'true',							  
								  'post_form_id'=>$postFormId,
								  'fb_dtsg'=>$fbDtsg,
								  '_log_action'=>'send_new',							 
								  'ajax_log'=>1,
								  'post_form_id_source'=>'AsyncRequest'								  
								  );				
			$res=$this->post($form_action,$post_elements);
			if ($this->checkResponse("send_message",$res))
				$this->updateDebugBuffer('send_message',"{$form_action}",'POST',true,$post_elements);
			else
				{
				$this->updateDebugBuffer('send_message',"{$form_action}",'POST',false,$post_elements);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			sleep($this->messageDelay);
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
		$res=$this->get("http://www.facebook.com/home.php",true);
		if (!empty($res)) $res=$this->get('http://www.facebook.com/logout.php?h='.html_entity_decode($this->getElementString($res,'http://www.facebook.com/logout.php?h=','"')));		
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;	
		}
	}	

?>
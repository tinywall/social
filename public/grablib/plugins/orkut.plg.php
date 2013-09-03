<?php
$_pluginInfo=array(
	'name'=>'Orkut',
	'version'=>'1.1.6',
	'description'=>"Get the contacts from an Orkut account",
	'base_version'=>'1.8.0',
	'type'=>'social',
	'check_url'=>'http://www.orkut.com/',
	'requirement'=>'email',
	'allowed_domains'=>false,
	); 
class orkut extends openinviter_base
{
	private $login_ok=false;
	public $showContacts=true;	
	protected $timeout=30;
	
	public $debug_array=array(
				'secondary_get'=>'Email:',
				'login_post'=>'url=&#39;',
				'url_redirect'=>'mblock',
				'url_friends'=>'mblock',
				'url_send_message'=>'scrapText',
				);
				
	public function login($user,$pass)
		{
		$this->resetDebugger();
		$this->service='orkut';
		$this->service_user=$user;
		$this->service_password=$pass;
		if (!$this->init()) return false;
		$res=$this->get("http://m.orkut.com/",true);
		if ($this->checkResponse('secondary_get',$res))
			$this->updateDebugBuffer('secondary_get',"http://www.orkut.com/",'GET');
		else
			{
			$this->updateDebugBuffer('secondary_get',"http://www.orkut.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
			
		$postAction = "https://www.google.com/accounts/ServiceLoginAuth?service=orkut";
		$postElem = $this->getHiddenElements($res);
		$postElem["Email"] = $user;
		$postElem["Passwd"]= $pass;
		$res=$this->post($postAction,$postElem,true);
		if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',$postAction,'POST',true,$postElem);		
		else
			{
			$this->updateDebugBuffer('login_post',$postAction,'POST',false,$postElem);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		
			
		$url_redirect=html_entity_decode($this->getElementString($res,'url=&#39;','&#39;')); 
		$res=$this->get($url_redirect,true);
		
		if (strpos($res,'url=&#39;')!==false)
			{
			$url_redirect=html_entity_decode($this->getElementString($res,'url=&#39;','&#39;'));
			$res=$this->get($url_redirect,true);
			}	
		if ($this->checkResponse('url_redirect',$res))
			$this->updateDebugBuffer('url_redirect',"http://www.orkut.com/",'GET');
		else
			{
			$this->updateDebugBuffer('url_redirect',"http://www.orkut.com/",'GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
			}
		$this->login_ok = "http://m.orkut.com/Friends";
		return true;
		}		
		
	public function getMyContacts()
		{
			if (!$this->login_ok)
			{
				$this->debugRequest();
				$this->stopPlugin();
				return false;
			}
			else $url = $this->login_ok;
			$originalLink = array(
			"a"=>"http://m.orkut.com/ShowFriends?small=a&caps=A&pgsize=10000",			
			"b"=>"http://m.orkut.com/ShowFriends?small=b&caps=B&pgsize=10000",
			"c"=>"http://m.orkut.com/ShowFriends?small=c&caps=C&pgsize=10000",
			"d"=>"http://m.orkut.com/ShowFriends?small=d&caps=D&pgsize=10000",
			"e"=>"http://m.orkut.com/ShowFriends?small=e&caps=E&pgsize=10000",
			"f"=>"http://m.orkut.com/ShowFriends?small=f&caps=F&pgsize=10000",
			"g"=>"http://m.orkut.com/ShowFriends?small=g&caps=G&pgsize=10000",
			"h"=>"http://m.orkut.com/ShowFriends?small=h&caps=H&pgsize=10000",
			"i"=>"http://m.orkut.com/ShowFriends?small=i&caps=I&pgsize=10000",
			"j"=>"http://m.orkut.com/ShowFriends?small=j&caps=J&pgsize=10000",
			"k"=>"http://m.orkut.com/ShowFriends?small=k&caps=K&pgsize=10000",
			"l"=>"http://m.orkut.com/ShowFriends?small=l&caps=L&pgsize=10000",
			"m"=>"http://m.orkut.com/ShowFriends?small=m&caps=M&pgsize=10000",
			"n"=>"http://m.orkut.com/ShowFriends?small=n&caps=N&pgsize=10000",
			"o"=>"http://m.orkut.com/ShowFriends?small=o&caps=O&pgsize=10000",
			"p"=>"http://m.orkut.com/ShowFriends?small=p&caps=P&pgsize=10000",
			"q"=>"http://m.orkut.com/ShowFriends?small=q&caps=Q&pgsize=10000",
			"r"=>"http://m.orkut.com/ShowFriends?small=r&caps=R&pgsize=10000",
			"s"=>"http://m.orkut.com/ShowFriends?small=s&caps=S&pgsize=10000",
			"t"=>"http://m.orkut.com/ShowFriends?small=t&caps=T&pgsize=10000",
			"u"=>"http://m.orkut.com/ShowFriends?small=u&caps=U&pgsize=10000",
			"v"=>"http://m.orkut.com/ShowFriends?small=v&caps=V&pgsize=10000",
			"w"=>"http://m.orkut.com/ShowFriends?small=w&caps=W&pgsize=10000",
			"x"=>"http://m.orkut.com/ShowFriends?small=x&caps=X&pgsize=10000",
			"y"=>"http://m.orkut.com/ShowFriends?small=y&caps=Y&pgsize=10000",
			"z"=>"http://m.orkut.com/ShowFriends?small=z&caps=Z&pgsize=10000",
			"*"=>"http://m.orkut.com/ShowFriends?small=*&caps=*&pgsize=10000"
			); 
				
			$contacts=array();			
			foreach($originalLink as $link)
				{
				$res=$this->get($link,true);
				if ($this->checkResponse('url_friends',$res))
					$this->updateDebugBuffer('url_friends',$link,'GET');
				else
					{
					$this->updateDebugBuffer('url_friends',$link,'GET',false);
					$this->debugRequest();
					$this->stopPlugin();
					return false;
					}	
				$doc=new DOMDocument();libxml_use_internal_errors(true);if (!empty($res)) $doc->loadHTML($res);libxml_use_internal_errors(false);
				$xpath=new DOMXPath($doc);$query="//div[@class='mblock']";$data=$xpath->query($query);
				foreach ($data as $node)
					{
					$firstChild=$node->childNodes->item(1);
					if (isset($firstChild)) if ($firstChild->nodeName=='a') if (strpos((string)$firstChild->getAttribute('href'),'/FullProfile?uid=')!==false)
						{
						$id=str_replace('/FullProfile?uid=','',(string)$firstChild->getAttribute('href'));$name=trim(preg_replace('/[^(\x20-\x7F)]*/','',(string)$firstChild->nodeValue));
						if (!empty($id)) $contacts[$id]=$name;
						}
					}
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
		foreach($contacts as $id=>$name)
			{	
			$countMessages++;
			$url_scrap="http://m.orkut.com/Scrapbook?uid={$id}";
			$res=$this->get($url_scrap);
			if ($this->checkResponse("url_send_message",$res))
				$this->updateDebugBuffer('url_send_message',$url_scrap,'GET');
			else
				{
				$this->updateDebugBuffer('url_send_message',$url_scrap,'GET',false);
				$this->debugRequest();
				$this->stopPlugin();
				return false;
				}
			
			$form_action="http://m.orkut.com/Scrapbook";
			$post_elements=$this->getHiddenElements($res);$post_elements['scrapText']=str_replace(array('.','&'),'~',$message['body']);
			$res=$this->post($form_action,$post_elements,true);
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
		$logout_url = "http://www.orkut.com/GLogin.aspx?cmd=logout";
		$res = $this->get($logout_url);
		$this->debugRequest();
		$this->resetDebugger();
		$this->stopPlugin();
		return true;
		}
}
?>
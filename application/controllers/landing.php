<?php
require_once('tw_controller.php');
class Landing extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->load->helper('string');
		
	}
	function _validateUser(){
		if($this->session->userdata('logged_in')){
			if($user_data=$this->twfunctions->getSessionData($this->session->userdata('session_id'))){
				$this->session_user=$user_data[0];
				return TRUE;
			}else{
				redirect('logout');
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	function index()
	{
		if($this->_validateUser()){
			redirect('dashboard');
		}else{
			//$this->load->view('index_header_view');
			$this->load->view('index_view');
			//$this->load->view('index_footer_view');
		}
	}
	function fblogin(){
		$config = array(
                         'appId'  => '',
		  				'secret' => '',
    public function _validateUser($page)
        if ($this->_validateUser('')) {
                        'fileUpload' => true, // Indicates if the CURL based @ syntax for file uploads is enabled.
                        );
        $this->load->library('Facebook', $config);
        $user = $this->facebook->getUser();
       $profile = null;
        if($user)
        {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $profile = $this->facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }
		if(!$profile){
			redirect($this->facebook->getLoginUrl(array('scope'=>'email,publish_stream,user_birthday,user_location')));
			//echo "<a href='".$this->facebook->getLoginUrl(array('scope'=>'email,publish_stream,user_birthday,user_location'))."'>Login</a>";
			//echo "<a href='https://graph.facebook.com/oauth/authorize?client_id=255296981155389&redirect_uri=http://tinywall.com/landing/fblogin/&scope=publish_stream,email'>Facebook Login</a>";
		}else{
			//-------------------------------
			//echo "Id:".$profile['id'].$profile['first_name'].$profile['last_name'].$profile['gender'].$profile['birthday']."Em:".$profile['email']."At:".$this->facebook->getAccessToken()."<br/>";
			//echo "<a href='".$this->facebook->getLogoutUrl()."'>Logout</a>";
			if($user_info=$this->twfunctions->getFacebookAuthenticateData($profile['id'],$profile['email'],$this->facebook->getAccessToken())){
				$row=$user_info[0];
				$access_token=random_string('alnum',32);
				if($this->twfunctions->setAccessToken($row->id_users,$access_token)){
					$user_session=$row->session_id.'_'.$access_token;
					if($this->twfunctions->set_login_log($row->id_users)){
						
					}
					$session_data = array(
	                   'username'  => $row->username,
	                   'session_id' => $user_session,
	                   'logged_in' => TRUE
	               );
					$this->session->set_userdata($session_data);
					$redirect=$this->input->post('redirect', TRUE);
					if($redirect){
						redirect($redirect);	
					}else{
						redirect('dashboard');
					}
				}else{
					$this->session->set_flashdata('alert', 'Error');
					redirect('login');
				}		
			}else{
				//echo $profile[''];
				$session_data = array(
	                   'fbid'  => $profile['id'],
	                   'fbfirst_name' => $profile['first_name'],
					   'fblast_name'=>$profile['last_name'],
					   'fbgender'=>$profile['gender'],
					   'fbemail'=>$profile['email'],
					   'fbbirthday'=>$profile['birthday'],
					   'fbaccess_token'=>$this->facebook->getAccessToken(),
	                   'logged_in' => FALSE
	               );
					$this->session->set_userdata($session_data);
					redirect('landing/fbregister');
			}
			//----------------
		}
		//$this->load->view('welcome', $data);
	}
	function fbregister(){
		if($this->input->post('fbregister')){
			$user['first_name']=$this->input->post('first_name');
			$user['last_name']=$this->input->post('last_name');
			$user['username']=$this->input->post('username');
			$user['email']=$this->session->userdata('fbemail');
			$user['mobile']=$this->input->post('mobile');
			$user['password']=md5($this->input->post('password'));
			$user['birth_date']=$this->input->post('birth_date');
			$user['gender']=$this->input->post('gender');
			$user['email_active']=1;
			$user['mobile_active']=0;
			$user['facebook_id']=$this->session->userdata('fbid');
			$user['facebook_access_token']=$this->session->userdata('fbaccess_token');
			if($user_data=$this->twfunctions->fbregister($user)){
				$user_registered=$user_data[0];
				$this->twfunctions->create_wallphoto_album($user_registered->id_users);
				$this->send_twmail($user_registered->email,'','Welcome to TinyWall',"Welcome to TinyWall");
				
				$access_token=random_string('alnum',32);
				if($this->twfunctions->setAccessToken($user_registered->id_users,$access_token)){
					$user_session=$user_registered->session_id.'_'.$access_token;
					if($this->twfunctions->set_login_log($user_registered->id_users)){
						
					}
					$session_data = array(
	                   'username'  => $user_registered->username,
	                   'session_id' => $user_session,
	                   'logged_in' => TRUE
	               );
					$this->session->set_userdata($session_data);
					$redirect=$this->input->post('redirect', TRUE);
					if($redirect){
						redirect($redirect);	
					}else{
						redirect('dashboard');
					}
				}else{
					$this->session->set_flashdata('alert', 'Error');
					redirect('login');
				}
			}else{
				$this->session->set_flashdata('alert','Email already registered');
				redirect('register');
			}
		}
		$twdata['fbfirst_name']=$this->session->userdata('fbfirst_name');
		$twdata['fblast_name']=$this->session->userdata('fblast_name');
		$twdata['fbgender']=$this->session->userdata('fbgender');
		$twdata['fbemail']=$this->session->userdata('fbemail');
		$twdata['fbbirthday']=$this->session->userdata('fbbirthday');
		$this->load->view('index_header_view');
		$this->load->view('fbregister_view',$twdata);
		$this->load->view('index_footer_view');
			
	}
	function register(){
		if($this->input->post('register',TRUE)){
			$user['first_name']=$this->input->post('first_name');
			$user['last_name']=$this->input->post('last_name');
			$user['username']=$this->input->post('username');
			$user['email']=$this->input->post('email');
			$user['mobile']=$this->input->post('mobile');
			$user['password']=md5($this->input->post('password'));
			$user['birth_date']=$this->input->post('dob_year')."-".$this->input->post('dob_month')."-".$this->input->post('dob_date');
			$user['gender']=$this->input->post('gender');
			$user['email_key']=random_string('alnum',32);
			$user['email_active']=0;
			$user['mobile_active']=0;
			
			if($user_data=$this->twfunctions->register($user)){
				$user_registered=$user_data[0];
				$this->twfunctions->create_wallphoto_album($user_registered->id_users);
				$this->send_twmail($user_registered->email,'','Welcome to TinyWall',"Click the following link to activate your account : <a href='".base_url().'activate/'.$user_registered->username.'/'.$user_registered->email_key."'>".base_url().'activate/'.$user_registered->username.'/'.$user_registered->email_key."</a>");
				$this->session->set_flashdata('alert','Successfully registered');	
				redirect('register');
			}else{
				$this->session->set_flashdata('alert','Already registered');
				redirect('register');
			}
		}
		$this->load->view('index_header_view');
		$this->load->view('register_view');
		$this->load->view('index_footer_view');
	}
	function login(){
		if($this->_validateUser()){
			redirect('dashboard');
		}else{
			$this->load->view('index_header_view');
			$this->load->view('login_view');
			$this->load->view('index_footer_view');
		}
	}
	function authenticate(){
		$username=$this->input->post('username', TRUE);
		$password=$this->input->post('password', TRUE);
		$redirect=$this->input->post('redirect', TRUE);
		if($user_info=$this->twfunctions->getAuthenticateData($username)){
			$row=$user_info[0];
			if(($username==$row->username||$username==$row->email)&&md5($password)==$row->password){
				$access_token=random_string('alnum',32);
				if($this->twfunctions->setAccessToken($row->id_users,$access_token)){
					$user_session=$row->session_id.'_'.$access_token;
					if($this->twfunctions->set_login_log($row->id_users)){
						
					}
					$session_data = array(
	                   'username'  => $row->username,
	                   'session_id' => $user_session,
	                   'logged_in' => TRUE
	               );
					$this->session->set_userdata($session_data);
					if($redirect){
						redirect($redirect);	
					}else{
						redirect('dashboard');
					}
				}else{
					$this->session->set_flashdata('alert', 'Error');
					redirect('login');
				}
			}else{
				$this->session->set_flashdata('alert', 'Invalid password');
				redirect('login');
			}		
		}
		$this->session->set_flashdata('alert', 'Not user or not activated.');
		redirect('login');
	}
	function logout(){
		if($this->_validateUser()){
			$this->twfunctions->unsetAccessToken($this->session_user->id_users,$this->session->userdata('session_id'));
		}
		$session_data = array(
                   'username'  => '',
                   'session_id' => '',
                   'logged_in' => FALSE
               );
		$this->session->unset_userdata($session_data);
		$this->session->set_flashdata('alert', 'logged out');
		redirect('login');
	}
	function activate(){
		$username=$this->uri->segment(2);
		$email_key=$this->uri->segment(3);
		if($username&&$email_key&&($user_data=$this->twfunctions->activate_email($username,$email_key))){
			$user=$user_data[0];
			$this->send_twmail($user->email,'','Welcome to TinyWall '.$user->first_name.' '.$user->last_name,', Your account activated successfully');
			$this->session->set_flashdata('alert', 'successfully activated');
			redirect('login');
		}else{
			$this->session->set_flashdata('alert', 'something went wrong');
			redirect('login');
		}
	}
	function forgot(){
		if($this->input->post('forgot')){
			$reset_key=random_string('alnum',32);
			if($user_data=$this->twfunctions->forgot_password($this->input->post('email'),$reset_key)){
				$user=$user_data[0];
				$this->send_twmail($user->email,'','Password reset confirmation',"Click the link to reset your password : <a href='".base_url().'reset/'.$user->username.'/'.$user->pwd_reset_key."'>".base_url().'reset/'.$user->username.'/'.$user->pwd_reset_key."</a>");
				$this->session->set_flashdata('alert','Reset mail sent to mail.');
				redirect('forgot');
			}else{
				$this->session->set_flashdata('alert','Email not exist');
				redirect('forgot');
			}
		}
		$this->load->view('index_header_view');
		$this->load->view('forgot_view');
		$this->load->view('index_footer_view');
	}
	function reset(){
		$username=$this->uri->segment(2);
		$reset_key=$this->uri->segment(3);
		$password=random_string('alnum',8);
		if($username&&$reset_key&&($user_data=$this->twfunctions->reset_password($username,md5($password),$reset_key))){
			$user=$user_data[0];
			$this->send_twmail($user->email,'','New changed password','New password is '.$password);
			$this->session->set_flashdata('alert', 'New password mailed');
			redirect('login');
		}else{
			$this->session->set_flashdata('alert', 'something went wrong');
			redirect('login');
		}
	}
	function avatar($type,$username){
		header('Content-type: image/jpeg');
		if(file_exists('./images/profile_pictures/'.$type.'/'.$username.'.jpg')){
			readfile('./images/profile_pictures/'.$type.'/'.$username.'.jpg');	
		}else{
			readfile('./images/profile_pictures/'.$type.'/default.jpg');	
		}
	}
	function snap($type,$snap_id){
		header('Content-type: image/jpeg');
		if(file_exists('./images/album_pictures/'.$type.'/'.$snap_id.'.jpg')){
			readfile('./images/album_pictures/'.$type.'/'.$snap_id.'.jpg');
		}
	}
	function publicsearch()
	{
		parse_str($_SERVER['QUERY_STRING'],$_GET);
		$this->twdata['page_title']="Search @ TinyWall";
		if($this->input->get('name')){
			$this->twdata['page_title']="Search result for ".$this->input->get('name')." @ TinyWall";
		}
		$this->load->view('index_header_view',$this->twdata);
		if($this->input->get('search')){
			$this->twdata['search']=TRUE;
			$search_query['name']=$this->input->get('name');
			$search_query['location']=$this->input->get('location');
			$search_query['page']=$this->input->get('page');
			$search_result=$this->twfunctions->get_publicsearch_result($search_query);
			$this->twdata['search_result']=$search_result['search_result'];
			$this->twdata['search_result_count']=$search_result['search_result_count'];
		}
		$this->load->view('public_search_view',$this->twdata);
		$this->load->view('index_footer_view');
		
	}
	function page_visited_log(){
		if($this->_validateUser()){
			$data['user_id']=$this->session_user->id_users;
			if(($this->session_user->username!=$this->input->post('current_user'))&&($current_user_data=$this->twfunctions->getCurrentDataPublic($this->input->post('current_user')))){
				$this->current_user=$current_user_data[0];
				$data['visited_user']=$this->current_user->id_users;
			}else{
				$data['visited_user']=$this->session_user->id_users;
			}
			$data['url']=$this->input->post('url');
			//$data['page']=$this->input->post('page');
			$this->twfunctions->set_page_visited_log($data);
		}
	}
	function chechUserAvailability(){
		if($this->twfunctions->check_username_availibility($this->uri->segment(3))){
			$message='Username available';$availability=TRUE;
		}else{
			$message='Username not available';$availability=FALSE;
		}
		$outarr=array('message'=>$message,'availability'=>$availability);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function developers(){
		$this->load->view('index_header_view');
		$this->load->view('developers_view');
		$this->load->view('index_footer_view');
	}
	function about(){
		$this->load->view('index_header_view');
		$this->load->view('about_view');
		$this->load->view('index_footer_view');
	}
	function tour(){
		$this->load->view('index_header_view');
		$this->load->view('tour_view');
		$this->load->view('index_footer_view');
	}
	function api(){
		$this->load->view('index_header_view');
		$this->load->view('api_view');
		$this->load->view('index_footer_view');
	}
	function terms(){
		$this->load->view('index_header_view');
		$this->load->view('terms_view');
		$this->load->view('index_footer_view');
	}
	function changeLang(){
		$session_data = array('language'=>$this->uri->segment(3));//first
		$this->session->set_userdata($session_data);
	}
	function openid(){
		//$this->load->library('openid');
		$this->load->library('Lightopenid');
		try {
		    # Change 'localhost' to your domain name.
		    $openid = new Lightopenid();
		    if(!$openid->mode) {
		        if(isset($_GET['login'])) {
					if($_GET['login']=='google'){
						$openid->identity = 'https://www.google.com/accounts/o8/id';	
					}elseif($_GET['login']=='yahoo'){
						$openid->identity = 'http://me.yahoo.com';
					}
					$openid->required = array('namePerson/friendly', 'contact/email');
		            header('Location: ' . $openid->authUrl());
		        }
				?>
				<?php
		    } elseif($openid->mode == 'cancel') {
		        echo 'User has canceled authentication!';
		    } else {
		        //echo 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';
				if($openid->validate()){
					//echo $openid->identity.'->';
					$userdata=$openid->getAttributes();
					//echo $userdata['contact/email'];
					if($user_info=$this->twfunctions->getOpenIDAuthenticateData($userdata['contact/email'])){
						$row=$user_info[0];
						$access_token=random_string('alnum',32);
						if($this->twfunctions->setAccessToken($row->id_users,$access_token)){
							$user_session=$row->session_id.'_'.$access_token;
							if($this->twfunctions->set_login_log($row->id_users)){
								
							}
							$session_data = array(
			                   'username'  => $row->username,
			                   'session_id' => $user_session,
			                   'logged_in' => TRUE
			               );
							$this->session->set_userdata($session_data);
							$redirect=$this->input->post('redirect', TRUE);
							if($redirect){
								redirect($redirect);	
							}else{
								redirect('dashboard');
							}
						}else{
							$this->session->set_flashdata('alert', 'Error');
							redirect('login');
						}		
					}else{
						//signup form
						$session_data = array(
						   'openid_identity'=>$openid->identity,
						   'oiemail'=>$userdata['contact/email'],
						   'logged_in' => FALSE
						);
						$this->session->set_userdata($session_data);
						redirect('landing/openidregister?openid='.$_GET['login']);
					}
				}
		    }
		} catch(ErrorException $e) {
		    //echo $e->getMessage();
		}
		
	}
	function openidregister(){
		if($this->input->post('oiregister')){
			$user['first_name']=$this->input->post('first_name');
			$user['last_name']=$this->input->post('last_name');
			$user['username']=$this->input->post('username');
			$user['email']=$this->session->userdata('oiemail');
			$user['mobile']=$this->input->post('mobile');
			$user['password']=md5($this->input->post('password'));
			$user['birth_date']=$this->input->post('birth_date');
			$user['gender']=$this->input->post('gender');
			$user['email_active']=1;
			$user['mobile_active']=0;
			$user['openid_identity']=$this->session->userdata('openid_identity');
			if($user_data=$this->twfunctions->openid_register($user)){
				$user_registered=$user_data[0];
				$this->twfunctions->create_wallphoto_album($user_registered->id_users);
				$this->send_twmail($user_registered->email,'','Welcome to TinyWall',"Welcome to TinyWall");
				
				$access_token=random_string('alnum',32);
				if($this->twfunctions->setAccessToken($user_registered->id_users,$access_token)){
					$user_session=$user_registered->session_id.'_'.$access_token;
					if($this->twfunctions->set_login_log($user_registered->id_users)){
						
					}
					$session_data = array(
	                   'username'  => $user_registered->username,
	                   'session_id' => $user_session,
	                   'logged_in' => TRUE
	               );
					$this->session->set_userdata($session_data);
					$redirect=$this->input->post('redirect', TRUE);
					if($redirect){
						redirect($redirect);	
					}else{
						redirect('dashboard');
					}
				}else{
					$this->session->set_flashdata('alert', 'Error');
					redirect('login');
				}
			}else{
				$this->session->set_flashdata('alert','Email already registered');
				redirect('register');
			}
		}
		$twdata['oiemail']=$this->session->userdata('oiemail');
		$this->load->view('index_header_view');
		$this->load->view('openidregister_view',$twdata);
		$this->load->view('index_footer_view');
	}
	function twitterregister(){
		if($this->input->post('twitterregister',TRUE)){
			$user['first_name']=$this->input->post('first_name');
			$user['last_name']=$this->input->post('last_name');
			$user['username']=$this->input->post('username');
			$user['email']=$this->input->post('email');
			$user['mobile']=$this->input->post('mobile');
			$user['password']=md5($this->input->post('password'));
			$user['birth_date']=$this->input->post('birth_date');
			$user['gender']=$this->input->post('gender');
			$user['email_key']=random_string('alnum',32);
			$user['email_active']=0;
			$user['mobile_active']=0;
			$user['twitter_id']=$this->session->userdata('twitter_id');
			$user['twitter_oauth_token']=$this->session->userdata('twitter_oauth_token');
			$user['twitter_oauth_token_secret']=$this->session->userdata('twitter_oauth_token_secret');
			
			
			if($user_data=$this->twfunctions->twitter_register($user)){
				$user_registered=$user_data[0];
				$this->twfunctions->create_wallphoto_album($user_registered->id_users);
				$this->send_twmail($user_registered->email,'','Welcome to TinyWall',"Click the following link to activate your account : <a href='".base_url().'activate/'.$user_registered->username.'/'.$user_registered->email_key."'>".base_url().'activate/'.$user_registered->username.'/'.$user_registered->email_key."</a>");
				$this->session->set_flashdata('alert','Successfully registered. Activate email');	
				redirect('register');
			}else{
				$this->session->set_flashdata('alert','Already registered');
				redirect('register');
			}
		}
		$this->load->view('index_header_view');
		$this->load->view('twitterregister_view');
		$this->load->view('index_footer_view');
	}
	function twitterlogin(){
		$this->load->library('tweet');
		//$this->tweet->enable_debug(TRUE);
		if ( !$this->tweet->logged_in()){
			$this->tweet->set_callback(site_url('landing/twitterlogin'));
			$this->tweet->login();
		}else{
			$tokens = $this->tweet->get_tokens();
			$user = $this->tweet->call('get', 'account/verify_credentials');
			if($user_info=$this->twfunctions->getTwitterAuthenticateData($user->id,$tokens['oauth_token'],$tokens['oauth_token_secret'])){//check for $user->id
				$row=$user_info[0];
				$access_token=random_string('alnum',32);
				if($this->twfunctions->setAccessToken($row->id_users,$access_token)){
					$user_session=$row->session_id.'_'.$access_token;
					if($this->twfunctions->set_login_log($row->id_users)){
						
					}
					$session_data = array(
	                   'username'  => $row->username,
	                   'session_id' => $user_session,
	                   'logged_in' => TRUE
	               );
					$this->session->set_userdata($session_data);
					$redirect=$this->input->post('redirect', TRUE);
					if($redirect){
						redirect($redirect);	
					}else{
						redirect('dashboard');
					}
				}else{
					$this->session->set_flashdata('alert', 'Error');
					redirect('login');
				}
			}else{
				$session_data = array(
					'twitter_id'=>$user->id,
					'twitter_oauth_token'=>$tokens['oauth_token'],
					'twitter_oauth_token_secret'=>$tokens['oauth_token_secret'],
					'twittername'=>$user->name,
				   	'twitterscreenname'=>$user->screen_name,
				   	'logged_in' => FALSE
				);
				$this->session->set_userdata($session_data);
				redirect('landing/twitterregister');
			}
			//var_dump($user);
			//$friendship 	= $this->tweet->call('get', 'friendships/show', array('source_screen_name' => $user->screen_name, 'target_screen_name' => 'elliothaughin'));
			//var_dump($friendship);
			//if ( $friendship->relationship->target->following === FALSE){
			//	$this->tweet->call('post', 'friendships/create', array('screen_name' => $user->screen_name, 'follow' => TRUE));
			//}
			//$this->tweet->call('post', 'statuses/update', array('status' => 'Testing #CodeIgniter Twitter library by @elliothaughin - http://bit.ly/grHmua'));
			//$options = array(
			//			'count' => 10,
			//			'page' 	=> 2,
			//			'include_entities' => 1
			//);
			//$timeline = $this->tweet->call('get', 'statuses/home_timeline');
			//var_dump($timeline);
		}
	}
}

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
	function test(){
		echo "sid:".$this->session->userdata('logged_in');
	}
	function _validateUser(){
		if($this->session->userdata('logged_in')){
			if($user_data=$this->twfunctions->getSessionData($this->session->userdata('session_id'))){
				$this->session_user=$user_data[0];
				return TRUE;
			}else{
				//redirect('logout');
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	function index()
	{
		if($this->_validateUser()){echo 'logged';
			//redirect('dashboard');
		}
	}
	function register(){
		if($this->input->post('register',TRUE)){
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
			
			if($user_data=$this->twfunctions->register($user)){
				$user_registered=$user_data[0];
				$this->send_twmail($user_registered->email,'','Welcome to TinyWall',"Click the following link to activate your account : <a href='".base_url().'activate/'.$user_registered->username.'/'.$user_registered->email_key."'>".base_url().'activate/'.$user_registered->username.'/'.$user_registered->email_key."</a>");
				//$this->session->set_flashdata('alert','Successfully registered');	
				//redirect('register');
			}else{
				//$this->session->set_flashdata('alert','Email or username already registered');
				//redirect('register');
			}
		}
		/*$this->load->view('index_header_view');
		$this->load->view('register_view');
		$this->load->view('index_footer_view');*/
	}
	function login(){
		if($this->_validateUser()){
			//redirect('dashboard');
		}else{
			/*$this->load->view('index_header_view');
			$this->load->view('login_view');
			$this->load->view('index_footer_view');*/
		}
	}
	function authenticate(){
		$username=$this->input->post('username', TRUE);
		$password=$this->input->post('password', TRUE);
		if($user_info=$this->twfunctions->getAuthenticateData($username)){
			$row=$user_info[0];
			if(($username==$row->username||$username==$row->email)&&md5($password)==$row->password){
				$access_token=random_string('alnum',32);
				if($this->twfunctions->setApiLoginAccessToken($row->id_users,$access_token)){
					$user_session=$row->session_id.'_'.$access_token;
					if($this->twfunctions->set_login_log($row->id_users)){
						
					}
					$session_data = array(
	                   'username'  => $row->username,
	                   'session_id' => $user_session,
	                   'logged_in' => TRUE
	                );
					$this->session->set_userdata($session_data);
					//redirect('dashboard');
					echo "{\"response\":{\"success\":true,\"message\":\"Successfully Logged in\",\"logged_in\":true,\"user_id\":".$row->id_users.",\"username\":\"".$row->username."\",\"access_token\":\"".$access_token."\"}}";
					return;
				}else{
					//$this->session->set_flashdata('alert', 'Error');
					//redirect('login');
					echo "{\"response\":{\"success\":true,\"message\":\"Something went wrong\",\"logged_in\":false}}";
					return;
				}
			}else{
				//$this->session->set_flashdata('alert', 'Invalid password');
				//redirect('login');
				echo "{\"response\":{\"success\":true,\"message\":\"Invalid password\",\"logged_in\":false}}";
				return;
			}		
		}
		//$this->session->set_flashdata('alert', 'Not user or not activated.');
		//redirect('login');
		echo "{\"response\":{\"success\":true,\"message\":\"Not user or not activated\",\"logged_in\":false}}";
		return;
	}
	function logout(){
		$user_id=$this->input->get('user_id', TRUE);
		$access_token=$this->input->get('access_token', TRUE);
		if($this->twfunctions->unsetApiLoginAccessToken($user_id,$access_token)){
			$session_data = array(
	                   'username'  => '',
	                   'session_id' => '',
	                   'logged_in' => FALSE
	               );
			$this->session->unset_userdata($session_data);
			echo "{\"response\":{\"success\":true,\"message\":\"Logged out\",\"logged_out\":true}}";
		}
		return;
		//$this->session->set_flashdata('alert', 'logged out');
		//redirect('login');
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
}
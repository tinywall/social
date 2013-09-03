<?php
require_once('tw_controller.php');
class Home extends TW_Controller{
	function __construct()
	{
		parent::__construct();	
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateHomeUser();
		
	}
	function _validateHomeUser(){
		if($this->session->userdata('logged_in')){
			if($user_data=$this->twfunctions->getSessionData($this->session->userdata('session_id'))){
				$this->session_user=$user_data[0];$this->twdata['session_user']=$this->session_user;
			}else{
				redirect('logout');
				return FALSE;
			}
			$username=$this->uri->segment(1);
			if($username=='home'||$username=='dashboard'){$username=$this->session_user->username;}
			if($username==$this->session_user->username){
				$this->current_user=$this->session_user;$this->twdata['current_user']=$this->current_user;
				$this->user_relation=0;$this->twdata['user_relation']=$this->user_relation;
				return TRUE;
			}else{
				if($user_data=$this->twfunctions->getCurrentData($username,$this->session_user->id_users)){
					$this->user_relation=1;$this->twdata['user_relation']=$this->user_relation;
					$this->current_user=$user_data[0];$this->twdata['current_user']=$this->current_user;
					return TRUE;
				}else{
					$this->load->view('index_header_view');
					$this->load->view('404');
					$this->load->view('index_footer_view');
					return FALSE;
				}
			}
		}else{
			$username=$this->uri->segment(1);
			if($username=='home'||$username=='dashboard'){
				$this->session->set_flashdata('redirect',uri_string());
				redirect('login/redirect');
			}
			if($user_data=$this->twfunctions->getCurrentDataPublic($username)){
				$this->current_user=$user_data[0];$this->twdata['current_user']=$this->current_user;
				$this->load->view('index_header_view',$this->twdata);
				$this->load->view('public_profile_view',$this->twdata);
				$this->load->view('index_footer_view');
				return FALSE;
			}else{
				$this->load->view('index_header_view');
				$this->load->view('404');
				$this->load->view('index_footer_view');
				return FALSE;
			}
		}
		return FALSE;
	}
	function index()
	{
		if($this->userValid){
			$this->getExtraDetails(array('friends','mutualfriends','friendsuggession'));
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->twdata['current_page']='home';
			$this->load->view('header_view',$this->twdata);
			$this->load->view('home_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function dashboard(){
		if($this->userValid){
			$this->getExtraDetails(array('birthdayalert'));
			$this->twdata['current_page']='dashboard';
			$this->twdata['page_title']=$this->session_user->first_name." ".$this->session_user->last_name."'s Dashboard @ TinyWall";			
			$this->load->view('header_view',$this->twdata);
			$this->load->view('dashboard_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
}
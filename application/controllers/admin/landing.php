<?php
class Landing extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->helper('string');
	}	
	function index()
	{
		$this->load->view('admin/header_view');
		$this->load->view('admin/login_view');
		$this->load->view('admin/footer_view');
	}
	function authenticate(){
		if($this->input->post('login')){
			if($this->input->post('username')=='admin'&&$this->input->post('password')=='adminadmin'){
				$session_data = array(
	                   'admin'=> TRUE
	               );
				$this->session->set_userdata($session_data);
				redirect('admin/home');
			}else{
				redirect('admin');
			}
		}
	}
	function login(){
		$this->load->view('admin/header_view');
		$this->load->view('admin/login_view');
		$this->load->view('admin/footer_view');
	}
	function logout(){
		$session_data = array(
                   'admin'  => FALSE
               );
		$this->session->unset_userdata($session_data);
		redirect('admin/login');
	}
}
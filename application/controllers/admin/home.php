<?php
class Home extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->helper('string');
		$this->_checkSession();
		$this->load->model('Twadmin','twadmin');
	}
	function _checkSession(){
		if(!$this->session->userdata('admin')){
			redirect('admin');
		}
	}
	function index()
	{
		$this->load->view('admin/header_view');
		$this->load->view('admin/home_view');
		$this->load->view('admin/footer_view');
	}
	function resendActivationMail(){
		if($this->input->post('resendActivationMail')){
			if($nausers=$this->twadmin->get_nonactive_users()){
				foreach($nausers as $row){
					//echo $row->email;
					$this->twadmin->send_twmail($row->email,'','TinyWall activation mail',"Click the following link to activate your account : <a href='".base_url().'activate/'.$row->username.'/'.$row->email_key."'>".base_url().'activate/'.$row->username.'/'.$row->email_key."</a>");
				}
				$this->session->set_flashdata('alert','Mail Sent to all nonactive users');	
				redirect('admin/home/resendActivationMail');
			}
		}
		$this->load->view('admin/header_view');
		$this->load->view('admin/resend_activation_mail_view');
		$this->load->view('admin/footer_view');
	}
}
?>
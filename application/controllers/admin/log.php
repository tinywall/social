<?php
class Log extends CI_Controller {
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
	function login(){
		$this->load->view('admin/header_view');
		$data['login']=$this->twadmin->get_login_log();
		$this->load->view('admin/login_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function status(){
		$this->load->view('admin/header_view');
		$data['login']=$this->twadmin->get_status_log();
		$this->load->view('admin/status_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function registeration(){
		$this->load->view('admin/header_view');
		$data['register']=$this->twadmin->get_register_log();
		$this->load->view('admin/register_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function gender(){
		$this->load->view('admin/header_view');
		$data['gender']=$this->twadmin->get_gender_log();
		$this->load->view('admin/gender_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function age(){
		$this->load->view('admin/header_view');
		$data['age']=$this->twadmin->get_age_log();
		$this->load->view('admin/age_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function toplogin(){
		$this->load->view('admin/header_view');
		$data['toplogin']=$this->twadmin->get_toplogin_log();
		$this->load->view('admin/toplogin_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function isnt_common_word($word){
		return !in_array($word, $GLOBALS['word_filter']);
	}
	function trend(){
		$this->load->view('admin/header_view');
		$data['status']=$this->twadmin->get_trend_log();
		$this->load->view('admin/trend_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function toppages(){
		$this->load->view('admin/header_view');
		$data['toppages']=$this->twadmin->get_toppages_log();
		$this->load->view('admin/toppages_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function tophituser(){
		$this->load->view('admin/header_view');
		$data['toppages']=$this->twadmin->get_tophituser_log();
		$this->load->view('admin/tophituser_log_view',$data);
		$this->load->view('admin/footer_view');
	}
	function topvieweduser(){
		$this->load->view('admin/header_view');
		$data['toppages']=$this->twadmin->get_topvieweduser_log();
		$this->load->view('admin/topvieweduser_log_view',$data);
		$this->load->view('admin/footer_view');
	}
}
?>
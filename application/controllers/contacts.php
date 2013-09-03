<?php
require_once('tw_controller.php');
class Contacts extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('contacts'));
	}
	function index()
	{
			if($this->userValid){
			//$this->getExtraDetails(array('friends','friendsuggession','toptips',));
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." / B2B Plassen";
			$this->twdata['current_page']='contacts';
			$this->load->view('header_view',$this->twdata);
			$res=$this->twfunctions->get_contacts($this->session_user->id_users);
			$this->twdata['contacts_result']=$res['contacts_result'];			$this->load->view('contacts_view',$this->twdata);
			$this->load->view('footer_view');
			}
		
	}
	
	function add_contact(){
		if($this->userValid){
			$res=$this->twfunctions->add_contact($this->session_user->id_users,$this->input->post('name'),$this->input->post('mobile'),$this->input->post('mail'),$this->input->post('title'),$this->input->post('cname'));
			redirect('contacts');
		}
	}
	function delete_contact(){
		if($this->userValid){
			$res=$this->twfunctions->del_contact($this->uri->segment(3));
			redirect('contacts');
			}
	}
}
?>
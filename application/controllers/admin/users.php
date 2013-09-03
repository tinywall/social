<?php
class Users extends CI_Controller {
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
		$data['page_title']="Search @ TinyWall";
		$this->load->view('admin/header_view',$data);
		if($this->input->get('search')){
			$data['search']=TRUE;
			$search_query['name']=$this->input->get('name');
			$search_query['location']=$this->input->get('location');
			$search_query['page']=$this->input->get('page');
			$search_result=$this->twadmin->get_usersearch_result($search_query);
			$data['search_result']=$search_result['search_result'];
			$data['search_result_count']=$search_result['search_result_count'];
		}
		$this->load->view('admin/users_view',$data);
		$this->load->view('admin/footer_view',$data);
	}
	function view(){
		$data['page_title']="Search @ TinyWall";
		$this->load->view('admin/header_view',$data);
		if($user_data=$this->twadmin->getCurrentDataPublic($this->uri->segment(4))){
			$current_user=$user_data[0];$data['current_user']=$current_user;
			$this->load->view('admin/user_view',$data);	
		}
		$this->load->view('admin/footer_view',$data);
	}
	function ban(){
		if($this->twadmin->ban_user($this->uri->segment(4))){
			redirect('admin/users/view/'.$this->uri->segment(4));
		}
	}
	function unban(){
		if($this->twadmin->unban_user($this->uri->segment(4))){
			redirect('admin/users/view/'.$this->uri->segment(4));
		}
	} 
}
?>
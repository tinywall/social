<?php
require_once('tw_controller.php');
class Search extends TW_Controller{
	function __construct()
	{
		parent::__construct();	
		parse_str($_SERVER['QUERY_STRING'],$_GET);
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('search'));
	}
	function index()
	{
		if($this->userValid){
			$this->twdata['page_title']="Search @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			if($this->input->get('search')){
				$this->twdata['search']=TRUE;
				$search_query['name']=$this->input->get('name');
				$search_query['location']=$this->input->get('location');
				$search_query['page']=$this->input->get('page');
				$search_query['user_id']=$this->session_user->id_users;
				$search_result=$this->twfunctions->get_search_result($search_query);
				$this->twdata['search_result']=$search_result['search_result'];
				$this->twdata['search_result_count']=$search_result['search_result_count'];
			}
			$this->load->view('search_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
}
?>
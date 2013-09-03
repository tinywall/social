<?php
require_once('tw_controller.php');
class Pad extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('pad'));
	}
	function index()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->load->view('pad_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function get(){
		$datares=$this->twfunctions->get_pad_message($this->session_user->id_users,$this->current_user->id_users,$this->uri->segment(4));
		$this->_get_json_pad($datares);
	}
	function post(){
		if($user_data=$this->twfunctions->getCurrentDataPublic($this->input->post('to'))){
			$user=$user_data[0];
			$data['to']=$user->id_users;
			$data['message']=$this->input->post('message');
			$data['privacy']=$this->input->post('privacy');
			$datares=$this->twfunctions->post_pad_message($this->session_user->id_users,$data);
			$this->send_twmail($user->email,'','Pad message from '.$user->first_name.' '.$user->last_name,'You got a message from '.$user->first_name.' '.$user->last_name);
			$this->_get_json_pad($datares);
		}
	}
	function delete(){
		if($this->twfunctions->delete_pad_message($this->session_user->id_users,$this->uri->segment(3))){
			$message='Successfully Deleted';$success=TRUE;
		}else{
			$message='Something Went Wrong';$success=FALSE;
		}
		$outarr=array('message'=>$message,'success'=>$success);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function _get_json_pad($datares){
		$outarr=array();
		foreach($datares as $row){
			$arr=array('username'=>$row->username,'id'=>$row->id_users,'fullname'=>$row->first_name.' '.$row->last_name);
			$arr2=array('from'=>$arr,'id'=>$row->id_message_pad,'message'=>$row->message,'time'=>date('Y-m-d\TH:i:s\+0000',strtotime($row->time)),'is_read'=>$row->read);
			array_push($outarr,$arr2);
		}
		header('Content-type: application/json');
		echo "{\"messages\":".json_encode($outarr)."}";
		
	}
	function compose(){
		$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
		$this->load->view('header_view',$this->twdata);
		$this->load->view('pad_compose_view',$this->twdata);
		$this->load->view('footer_view');
	}
	function get_to_suggessions(){
		$datares=$this->twfunctions->get_pad_to_suggessions($this->input->get('q'));
		$outarr=array();
		foreach($datares as $row){
			$arr=array('username'=>$row->username,'id'=>$row->id_users,'fullname'=>$row->first_name.' '.$row->last_name);
			array_push($outarr,$arr);
		}
		header('Content-type: application/json');
		echo json_encode($outarr);
	}
}
?>
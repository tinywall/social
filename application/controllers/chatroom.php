<?php
require_once('tw_controller.php');
class chatroom extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('chatroom'));
	}
	function index()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->twdata['current_page']='chatroom';
			$this->load->view('header_view',$this->twdata);
			$this->load->view('chatroom_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function get_chat_msg(){
		if($this->userValid){
			$result=$this->twfunctions->get_chatroom_msg($this->session_user->id_users,$this->uri->segment(3));
			$this->chatroom_result=$result['chatroom_msg'];
			if($this->uri->segment(3)==0){
				$this->_json_status(array_reverse($this->chatroom_result));	
			}else{
				$this->_json_status($this->chatroom_result);
			}
		}
	}
	function _json_status($status_result){
		$outarr=array();
		foreach($status_result as $row){
			$arr=array('chat_msg_id'=>$row->chatroom_id,'author'=>$row->username,'msg'=>$row->msg,'time'=>$row->time);
			array_push($outarr,$arr);
		}
		header('Content-type: application/json');
		echo "{\"Message\":".json_encode($outarr)."}";
	}
	function post_chatroom_msg(){
		if($this->userValid){
			$result=$this->twfunctions->post_newchatroom_msg($this->session_user->id_users,$this->input->post('message'));
			$this->new_chat_result=$result['new_chat_msg'];
			$this->_json_status($this->new_chat_result);
		}
	}
	function ping(){
		if($this->userValid){
			if($this->twfunctions->update_chatroom_ping($this->session_user->id_users)){
				$message='Successfully Pinged';$success=TRUE;
			}else{
				$message='Something Went Wrong';$success=FALSE;
			}
			$outarr=array('message'=>$message,'success'=>$success,'friend'=>$this->chatroom_friends());
			header('Content-type: application/json');
			echo "{\"response\":".json_encode($outarr)."}";
		}
	}
	function chatroom_friends(){
		if($this->userValid){
			$onlinefriends=$this->twfunctions->get_chatroom_online_friends($this->session_user->id_users);
			$newarr=array();
			foreach($onlinefriends as $row2){
				$arrx=array('id'=>$row2->id_users,'fullname'=>$row2->first_name.' '.$row2->last_name,'username'=>$row2->username);
				array_push($newarr,$arrx);
			}
			return $newarr;		
		}
	}
	

}
?>
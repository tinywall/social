<?php
require_once('tw_controller.php');
class chat extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('chat'));
	}
	function index(){
	
	}
	function ping(){
		if($this->userValid){
			if($this->twfunctions->update_chat_ping($this->session_user->id_users)){
				$message='Successfully Pinged';$success=TRUE;
			}else{
				$message='Something Went Wrong';$success=FALSE;
			}
			$outarr=array('message'=>$message,'success'=>$success,'friend'=>$this->onlinefriends());
			header('Content-type: application/json');
			echo "{\"response\":".json_encode($outarr)."}";
		}
	}
	function onlinefriends(){
		if($this->userValid){
			$onlinefriends=$this->twfunctions->get_online_friends($this->session_user->id_users);
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
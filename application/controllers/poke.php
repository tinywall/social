<?php
require_once('tw_controller.php');
class Poke extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('poke'));
	}
	function index()
	{
			//$this->getExtraDetails(array('friends','mutualfriends','friendsuggession','toptips'));
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." / B2B Plassen";
			$this->twdata['current_page']='poke';
			$this->load->view('header_view',$this->twdata);
			//$res=$this->twfunctions->get_contacts($this->session_user->id_users);
			//$this->twdata['contacts_result']=$res['contacts_result'];
			$this->load->view('poke_view',$this->twdata);
			$this->load->view('footer_view');
		
	}
	function get_pokes(){
		if($this->userValid){				
			$pokes_result=$this->twfunctions->show_pokes($this->session_user->id_users);
			$this->_json_poke($pokes_result);
		}
	}
	function add_poke(){
		if($this->userValid){			
			$res=$this->twfunctions->add_poke($this->session_user->id_users,$this->current_user->id_users);
		}
	}
	function remove_poke(){
		if($this->userValid){			
		$res=$this->twfunctions->delete_poke($this->uri->segment(3));
		}
	}
	function _json_poke($poke_result){
		$outarr=array();
		foreach($poke_result as $row){
			$arr=array('from'=>$row->username,'id'=>$row->id_pokes);
			array_push($outarr,$arr);
		}
		header('Content-type: application/json');
		echo "{\"poke\":".json_encode($outarr)."}";
	}
}
?>
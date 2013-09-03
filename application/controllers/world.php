<?php
require_once('tw_controller.php');
class World extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('world'));
	}
	function index()
	{
		if($this->userValid){
			$data['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$data['session_user']=$this->session_user;
			$data['current_user']=$this->current_user;
			$this->load->view('header_view',$this->twdata);
			$this->twdata['item_id']=$this->twfunctions->get_world_having($this->current_user->id_users);
			$this->load->view('living_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function edit()
	{
		if($this->userValid){
			$data['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$data['session_user']=$this->session_user;
			$data['current_user']=$this->current_user;
			$this->load->view('header_view',$this->twdata);
			$this->twdata['item_id']=$this->twfunctions->get_world($this->current_user->id_users);
			$this->load->view('editworld_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function living()
	{
		if($this->userValid){
			$data['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$data['session_user']=$this->session_user;
			$data['current_user']=$this->current_user;
			$this->load->view('header_view',$this->twdata);
			$this->twdata['item_id']=$this->twfunctions->get_world_having($this->current_user->id_users);
			$this->load->view('living_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function dream()
	{
		if($this->userValid){
			$data['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$data['session_user']=$this->session_user;
			$data['current_user']=$this->current_user;
			$this->load->view('header_view',$this->twdata);
			$this->twdata['item_id']=$this->twfunctions->get_world_wish($this->current_user->id_users);
			$this->load->view('dream_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function update()
	{
		if($this->userValid){
			$value=$this->twfunctions->get_world($this->session_user->id_users);
			foreach($value as $id)
			{
			
  if((($id->status==2) && ($this->input->post('item_'.$id->id_world_item)==1)) ||( ($id->status==1) && ($this->input->post('item_'.$id->id_world_item)==2)) )
			{
			$this->twfunctions->update_world($this->session_user->id_users,$id->id_world_item,$this->input->post('item_'.$id->id_world_item));	
			}
			
			
			if(((!$id->status) &&($this->input->post('item_'.$id->id_world_item)==1))|| ((!$id->status) && ($this->input->post('item_'.$id->id_world_item)==2)) )
			{			
			$this->twfunctions->insert_world($this->session_user->id_users,$id->id_world_item,$this->input->post('item_'.$id->id_world_item));
			}
			
			
			if((($id->status==2) &&($this->input->post('item_'.$id->id_world_item)==0))||( ($id->status==1) && ($this->input->post('item_'.$id->id_world_item)==0))) 
			{
			$this->twfunctions->delete_world($this->session_user->id_users,$id->id_world_item);
			}
			
			}
		}
		redirect('world/edit');
	}
	}
	
	
	

?>
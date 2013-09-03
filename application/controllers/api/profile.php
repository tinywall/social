<?php
require_once('tw_controller.php');
class Profile extends TW_Controller{
	private $status_result,$comment_result,$comment_count=0;
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('profile'));
	}
	function index(){
		if($this->userValid){
			$prof=array('username'=>$this->current_user->username,'fullname'=>$this->current_user->first_name." ".$this->current_user->last_name,'gender'=>$this->current_user->gender,'age'=>$this->current_user->age,'location'=>$this->current_user->city.", ".$this->current_user->country,'about'=>$this->current_user->about,'email'=>$this->current_user->email,'mobile'=>$this->current_user->mobile);
			
			$fullprof=array('profile'=>$prof,'status'=>$this->_posts(),'friends'=>$this->_friends());
			header('Content-type: application/json');
			//echo "{\"fullprofile\":{\"profile\":".json_encode($prof)."},".$this->_friends().",".$this->_posts()."}";
			echo "{\"fullprofile\":".json_encode($fullprof)."}";
		}
	}
	function _friends()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name."'s Friends @ TinyWall";
			$friend_requests=$this->twfunctions->get_friend_requests($this->session_user->id_users);
			$this->twdata['friend_request']=$friend_requests;
			$friends=$this->twfunctions->get_friends($this->current_user->id_users);
			$outarr=array();
			foreach($friends as $row){
				$arr=array('username'=>$row->username,'fullname'=>$row->first_name." ".$row->last_name,'gender'=>$row->gender,'age'=>$row->age,'location'=>$row->city.", ".$row->country,'about'=>$row->about,'email'=>$row->email,'mobile'=>$row->mobile);
				array_push($outarr,$arr);
			}
			//header('Content-type: application/json');
			//return "{\"friends\":".json_encode($outarr)."}";
			return $outarr;
			
		}
	}
	function _posts(){
		if($this->userValid){
			$result=$this->twfunctions->get_status_posts($this->current_user->id_users,0);
			$this->status_result=$result['status'];
			$this->comment_result=$result['comment'];
			return $this->_json_status($this->status_result);
		}
	}
	function _json_comment($comres){
		$newarr=array();
		foreach($comres as $row2){
			$arrx=array('id'=>$row2->id_users,'fullname'=>$row2->first_name.' '.$row2->last_name,'username'=>$row2->username);
			$arr2x=array('id'=>$row2->id_status_comment,'from'=>$arrx,'message'=>$row2->comment_message,'created_at'=>date('Y-m-d\TH:i:s\+0000',strtotime($row2->time)));
			array_push($newarr,$arr2x);
		}
		return $newarr;
	}
	function _get_comments($status_id){
		$newarr=array();
		$count=$this->comment_count;
		if($this->comment_result){
			while($count<sizeof($this->comment_result)){
				$row=$this->comment_result[$count];
				if($row->status_id==$status_id){
					array_push($newarr,$row);
					$count++;
				}else{
					break;
				}
			}
		}
		$this->comment_count=$count;
		return $newarr;
	}
	function _json_status($status_result){
		$outarr=array();
		foreach($status_result as $row){
			$arr=array('id'=>$row->id_users,'fullname'=>$row->first_name.' '.$row->last_name,'username'=>$row->username);
			$comres=$this->_get_comments($row->id_status_post);
			$arr2=array('id'=>$row->id_status_post,'from'=>$arr,'message'=>$row->status_message,'status_link'=>$row->link,'status_video'=>$row->video,'type'=>$row->type,'like_count'=>$row->like_count,'liked'=>$row->liked,'commentcount'=>sizeof($comres),'comments'=>$this->_json_comment($comres),'created_at'=>date('Y-m-d\TH:i:s\+0000',strtotime($row->time)));
			array_push($outarr,$arr2);
		}
		//header('Content-type: application/json');
		//return "{\"status\":".json_encode($outarr)."}";
		return $outarr;
	}
}
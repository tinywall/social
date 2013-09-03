<?php
require_once('tw_controller.php');
class Connection extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('connection','friends','followers','followings','contacts','groups'));
	}
	function index()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			
			$this->load->view('footer_view');
		}
	}
	function friends()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name."'s Friends @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$friend_requests=$this->twfunctions->get_friend_requests($this->session_user->id_users);
			$this->twdata['friend_request']=$friend_requests;
			$friends=$this->twfunctions->get_friends($this->current_user->id_users);
			$this->twdata['friends']=$friends;
			$this->load->view('friends_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function followers()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->twdata['followers']=$this->twfunctions->getFollowers($this->current_user->id_users);
			$this->load->view('followers_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function followings()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->twdata['followings']=$this->twfunctions->getFollowings($this->current_user->id_users);
			$this->load->view('followings_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function contacts()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			
			$this->load->view('footer_view');
		}
	}
	function groups()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			
			$this->load->view('footer_view');
		}
	}
	function mutualfriends(){
		if($this->userValid&&$this->user_relation){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name."'s Friends @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$mutual_friends=$this->twfunctions->get_mutual_friends($this->session_user->id_users,$this->current_user->id_users);
			$this->twdata['mutual_friends']=$mutual_friends;
			$this->load->view('mutual_friends_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function friendsuggession(){
		if($this->userValid&&!$this->user_relation){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name."'s Friends @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$mutual_friends=$this->twfunctions->get_friendsuggession($this->session_user->id_users);
			$this->twdata['friend_suggession']=$mutual_friends;
			$this->load->view('friend_suggession_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function sendFriendRequest(){
		$message='';
		if($user_data_result=$this->twfunctions->getCurrentData($this->uri->segment(3),$this->session_user->id_users)){
			$user_data=$user_data_result[0];
			if($user_data->id_users==$this->session_user->id_users){
				$message='Its You.';	
			}
			elseif($user_data->friend_relation){
				$message='Already Friend';
			}elseif($user_data->request_relation){
				$message='Request Pending Already';
			}else{
				if($this->twfunctions->sendFriendRequest($this->session_user->id_users,$user_data->id_users)){
					if($user_data->follow_relation==0){
						$this->twfunctions->sendFollowRequest($this->session_user->id_users,$user_data->id_users);	
					}
					$this->send_twmail($user_data->email,'','Friend request from '.$this->session_user->first_name.' '.$this->session_user->last_name,'You got a friend request from '.$this->session_user->first_name.' '.$this->session_user->last_name);
					$message='Request Sent';
				}else{
					$message='Something Went Wrong';
				}
			}
		}else{
			$message='User Not Exist';
		}
		$outarr=array('message'=>$message,'success'=>TRUE);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function sendFollowRequest(){
		$message='';
		if($user_data_result=$this->twfunctions->getCurrentData($this->uri->segment(3),$this->session_user->id_users)){
			$user_data=$user_data_result[0];
			if($user_data->id_users==$this->session_user->id_users){
				$message='Its You.';	
			}
			elseif($user_data->follow_relation){
				$message='Already Following';
			}else{
				if($this->twfunctions->sendFollowRequest($this->session_user->id_users,$user_data->id_users)){
					$this->send_twmail($user_data->email,'','New Follower '.$this->session_user->first_name.' '.$this->session_user->last_name,'You got a new follower '.$this->session_user->first_name.' '.$this->session_user->last_name);
					$message='Follow Successful';
				}else{
					$message='Something Went Wrong';
				}
			}
		}else{
			$message='User Not Exist';
		}
		$outarr=array('message'=>$message,'success'=>TRUE);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function sendUnfollowRequest(){
		$message='';
		if($user_data_result=$this->twfunctions->getCurrentData($this->uri->segment(3),$this->session_user->id_users)){
			$user_data=$user_data_result[0];
			if($user_data->id_users==$this->session_user->id_users){
				$message='Its You.';	
			}
			elseif(!$user_data->follow_relation){
				$message='Already Unfollowing';
			}else{
				if($this->twfunctions->sendUnfollowRequest($this->session_user->id_users,$user_data->id_users)){
					$message='Unfollow Successful';
				}else{
					$message='Something Went Wrong';
				}
			}
		}else{
			$message='User Not Exist';
		}
		$outarr=array('message'=>$message,'success'=>TRUE);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function acceptFriendRequest(){
		$message='';
		if($user_data_result=$this->twfunctions->getCurrentData($this->uri->segment(3),$this->session_user->id_users)){
			$user_data=$user_data_result[0];
			if($user_data->friend_relation){
				$message='Already Friend';
			}elseif($user_data->request_receive_relation){
				if($this->twfunctions->acceptFriendRequest($this->session_user->id_users,$user_data->id_users)){
					if($user_data->follow_relation==0){
						$this->twfunctions->sendFollowRequest($this->session_user->id_users,$user_data->id_users);	
					}
					$this->send_twmail($user_data->email,'','Friend request accepted by '.$this->session_user->first_name.' '.$this->session_user->last_name,'Your friend request has been accepted by '.$this->session_user->first_name.' '.$this->session_user->last_name);
					$message='Request Accepted';
				}else{
					$message='Something Went Wrong';
				}
			}elseif($user_data->request_send_relation){
				$message='Request already sent and its pending';
			}else{
				$message='No friend request exist';
			}
		}else{
			$message='User Not Exist';
		}
		$outarr=array('message'=>$message,'success'=>TRUE);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function denyFriendRequest(){
		$message='';
		if($user_data_result=$this->twfunctions->getCurrentData($this->uri->segment(3),$this->session_user->id_users)){
			$user_data=$user_data_result[0];
			if($user_data->friend_relation){
				$message='Already Friend';
			}elseif($user_data->request_receive_relation){
				if($this->twfunctions->denyFriendRequest($this->session_user->id_users,$user_data->id_users)){
					$message='Request Rejected';
				}else{
					$message='Something Went Wrong';
				}
			}elseif($user_data->request_send_relation){
				$message='Request already sent and its pending';
			}else{
				$message='No friend request exist';
			}
		}else{
			$message='User Not Exist';
		}
		$outarr=array('message'=>$message,'success'=>TRUE);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function import(){
		if($this->userValid){
			if($this->input->post('sendInviteEmail')&&$this->input->post('invite_email')){
				$this->send_twmail($this->input->post('invite_email'),'','Invitation to TinyWall by '.$this->session_user->first_name.' '.$this->session_user->last_name,'Your have been invited to TinyWall by '.$this->session_user->first_name.' '.$this->session_user->last_name." <br/><br/>Message : ".$this->input->post('invite_message'));
				$this->session->set_flashdata('alert', 'Invitation Sent.');
				redirect('connection/import');
			}
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->load->view('import_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function existingcontacts(){
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->twdata['existing_contacts']=$this->twfunctions->get_existing_contacts($this->session_user->id_users);
			$this->load->view('existing_contacts_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function nonexistingcontacts(){
		if($this->userValid){
			if($this->input->post('send_invite')){
				$this->send_twmail('dscs',$this->input->post('contact_email'),"TinyWall invitation from ".$this->session_user->first_name.' '.$this->session_user->last_name,"TinyWall invitation from ".$this->session_user->first_name.' '.$this->session_user->last_name.'<br/>');
				$this->session->set_flashdata('alert','Successfully Invited');	
				redirect('connection/import');
			}
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->twdata['nonexting_contacts']=$this->twfunctions->get_nonexisting_contacts($this->session_user->id_users);
			$this->load->view('nonexting_contacts_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function unfriend(){
		if($this->userValid){
			if($user_data_result=$this->twfunctions->getCurrentData($this->uri->segment(3),$this->session_user->id_users)){
				$user_data=$user_data_result[0];
				if($user_data->friend_relation){
					if($this->twfunctions->deleteFriend($this->session_user->id_users,$user_data->id_users)){
						if($user_data->follow_relation==0){
							$this->twfunctions->sendUnfollowRequest($this->session_user->id_users,$user_data->id_users);	
						}
						$message='Friend deleted';
					}else{
						$message='Something Went Wrong';
					}
				}else{
					$message='Not your friend.';
				}
			}else{
				$message='User Not Exist';
			}
			$outarr=array('message'=>$message,'success'=>TRUE);
			header('Content-type: application/json');
			echo "{\"response\":".json_encode($outarr)."}";
		}
	}
	function cancelRequest(){
		if($this->userValid){
			if($user_data_result=$this->twfunctions->getCurrentData($this->uri->segment(3),$this->session_user->id_users)){
				$user_data=$user_data_result[0];
				if($this->twfunctions->deleteFriendRequest($this->session_user->id_users,$user_data->id_users)){
					if($user_data->follow_relation==0){
						$this->twfunctions->sendUnfollowRequest($this->session_user->id_users,$user_data->id_users);	
					}
					$message='Friend Request Cancelled';
				}else{
					$message='Something Went Wrong';
				}
			}else{
				$message='User Not Exist';
			}
			$outarr=array('message'=>$message,'success'=>TRUE);
			header('Content-type: application/json');
			echo "{\"response\":".json_encode($outarr)."}";
		}
	}
}
?>
<?php
require_once('tw_controller.php');
class Status extends TW_Controller{
	private $status_result,$comment_result,$comment_count=0;
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('status'));
	}
	function index()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			if($this->uri->segment(1)=='status'){
				$this->twdata['status_request_url']=$this->current_user->username.'/status/feeds/';
				$this->twdata['status_type']='feeds';
			}elseif(is_numeric($this->uri->segment(3))){
				$this->twdata['status_request_url']=$this->current_user->username.'/status/post/'.$this->uri->segment(3).'/';
				$this->twdata['status_type']='post';
			}else{
				$this->twdata['status_request_url']=$this->current_user->username.'/status/posts/';
				$this->twdata['status_type']='posts';
			}
			$this->load->view('status_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function feeds(){
		if($this->userValid){
			$result=$this->twfunctions->get_status_feeds($this->session_user->id_users,$this->uri->segment(4));
			$this->status_result=$result['status'];
			$this->comment_result=$result['comment'];
			$this->_json_status($this->status_result);
		}
	}
	function posts(){
		if($this->userValid){
			$result=$this->twfunctions->get_status_posts($this->current_user->id_users,$this->uri->segment(5));
			$this->status_result=$result['status'];
			$this->comment_result=$result['comment'];
			$this->_json_status($this->status_result);
		}
	}
	function post(){
		if($this->userValid){
			$result=$this->twfunctions->get_status_post($this->current_user->id_users,$this->uri->segment(4));
			$this->status_result=$result['status'];
			$this->comment_result=$result['comment'];
			$this->_json_status($this->status_result);
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
		header('Content-type: application/json');
		echo "{\"status\":".json_encode($outarr)."}";
	}
	function postStatus(){
		if($this->userValid){
			$data['type']=$this->input->post('status_post_type');
			$data['message']=$this->input->post('message');
			$data['link']=$this->input->post('status_post_link');
			if(!$data['link']){
				$data['link']='';
			}
			$status_owner=$this->input->post('owner');
			$imagePosted=0;
			if($status_user_data=$this->twfunctions->getCurrentDataPublic($status_owner)){
				$status_user=$status_user_data[0];
				$data['owner']=$status_user->id_users;
				$data['video']='';
				if(!empty($_FILES['newStatusImage']['tmp_name'])){
					$config['upload_path'] = './images/album_pictures/temp/';
					$config['allowed_types'] = 'gif|jpg|png';
					$config['max_size']	= '4096';
					$config['max_width']  = '3200';
					$config['max_height']  = '2400';
					$config['file_name']  = $this->session_user->id_users.'_upload.jpg';
					$config['overwrite']  = TRUE;
			
					$this->load->library('upload',$config);
					
					if($this->upload->do_upload('newStatusImage')){
						$upload_data=$this->upload->data();
						$file_name=substr($upload_data['client_name'],0,strrpos($upload_data['client_name'],'.'));
						
						$wallphoto_albumid=$this->twfunctions->get_wallphoto_albumid($this->session_user->id_users);
						
						//----------------
						
						if($snap_id=$this->twfunctions->insert_snap($this->session_user->id_users,$wallphoto_albumid,$file_name)){
							//edit check size
							$this->load->library('image_lib');
							$config = array();
							$config['image_library'] = 'gd2';
							$config['source_image']	= './images/album_pictures/temp/'.$this->session_user->id_users.'_upload.jpg';
							$config['new_image'] = './images/album_pictures/full/'.$snap_id.'.jpg';
							$config['maintain_ratio'] = TRUE;
							$config['overwrite']  = TRUE;
							$config['width']	 = 600;
							$config['height']	= 450;
							$this->image_lib->initialize($config);
							if(!$this->image_lib->resize()){
								//edit delete snap
								//$this->session->set_flashdata('alert',$this->image_lib->display_errors());
								//redirect($this->session_user->username.'/album/'.$this->input->post('album_id'));
							}
							//edit check size
							$this->load->library('image_lib');
							$config = array();
							$config['image_library'] = 'gd2';
							$config['source_image']	= './images/album_pictures/temp/'.$this->session_user->id_users.'_upload.jpg';
							$config['new_image'] = './images/album_pictures/thumb/'.$snap_id.'.jpg';
							$config['maintain_ratio'] = TRUE;
							$config['overwrite']  = TRUE;
							$config['width']	 = 100;
							$config['height']	= 100;
							$this->image_lib->initialize($config);
							if(!$this->image_lib->resize()){
								//edit delete snap
								//$this->session->set_flashdata('alert',$this->image_lib->display_errors());
								//redirect($this->session_user->username.'/album/'.$this->input->post('album_id'));
							}else{
								$imagePosted=1;
								//$this->twfunctions->post_photo_status($this->session_user->id_users,$wallphoto_albumid,$snap_id);
								//delete file
							}
						}
						
						//----------------
					}else{
						//upload error json
					}
				}
				preg_match('/[\\?\\&]v=([^\\?\\&]+)/',$this->input->post('status_post_video'),$matches);
				if($matches){
					$data['video']=$matches[1];
					if(!$data['video']){
						$data['video']='';
					}
				}
				if($imagePosted==1){
					$data['image']=$snap_id;
					$data['type']=5;
				}
				$result=$this->twfunctions->post_new_status($this->session_user->id_users,$data);
				$this->status_result=$result['status'];
				$this->comment_result=$result['comment'];
				$this->_json_status($this->status_result);
			}
		}
	}
	function postComment(){
		if($this->userValid){
			$comres=$this->twfunctions->post_new_comment($this->session_user->id_users,$this->input->post('statusId'),$this->input->post('message'));
			if($user_data=$this->twfunctions->getStatusCommentUserData($this->input->post('statusId'))){
				$user=$user_data[0];
				$this->send_twmail($user->email,'','Status comment from '.$this->session_user->first_name.' '.$this->session_user->last_name,'You got a comment from '.$this->session_user->first_name.' '.$this->session_user->last_name);
			}
			header('Content-type: application/json');
			echo "{\"comments\":".json_encode($this->_json_comment($comres))."}";
		}
	}
	function deleteStatus(){
		if($this->twfunctions->deleteStatus($this->session_user->id_users,$this->uri->segment(3))){
			$message='Successfully Deleted';$success=TRUE;
		}else{
			$message='Something Went Wrong';$success=FALSE;
		}
		$outarr=array('message'=>$message,'success'=>$success);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function deleteComment(){
		if($this->twfunctions->deleteComment($this->session_user->id_users,$this->uri->segment(3))){
			$message='Successfully Deleted';$success=TRUE;
		}else{
			$message='Something Went Wrong';$success=FALSE;
		}
		$outarr=array('message'=>$message,'success'=>$success);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function likeStatus(){
		if($this->twfunctions->likeStatus($this->session_user->id_users,$this->uri->segment(3))){
			$message='Successfully Liked';$success=TRUE;
		}else{
			$message='Something Went Wrong';$success=FALSE;
		}
		$outarr=array('message'=>$message,'success'=>$success);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function unlikeStatus(){
		if($this->twfunctions->unlikeStatus($this->session_user->id_users,$this->uri->segment(3))){
			$message='Successfully Unliked';$success=TRUE;
		}else{
			$message='Something Went Wrong';$success=FALSE;
		}
		$outarr=array('message'=>$message,'success'=>$success);
		header('Content-type: application/json');
		echo "{\"response\":".json_encode($outarr)."}";
	}
	function getStatusLikes(){
		if($status_likes=$this->twfunctions->getStatusLikes($this->uri->segment(3))){
			$outarr=array();
			foreach($status_likes as $row){
				$arr=array('id'=>$row->id_users,'fullname'=>$row->first_name.' '.$row->last_name,'username'=>$row->username);
				array_push($outarr,$arr);
			}
			header('Content-type: application/json');
			echo "{\"response\":".json_encode($outarr)."}";
		}
	}
}
?>
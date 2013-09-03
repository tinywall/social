<?php
require_once('tw_controller.php');
class Album extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('album','photo'));
	}
	function index()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->session_user->first_name." ".$this->session_user->last_name."'s Album @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->twdata['albums']=$this->twfunctions->get_albums($this->current_user->id_users);
			$this->load->view('album_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function create(){
		if($this->input->post('create_album')){
			$data['album_name']=$this->input->post('album_name');
			if($album_id=$this->twfunctions->create_album($this->session_user->id_users,$data)){
				redirect($this->session_user->username.'/album/'.$album_id);
			}
		}
	}
	function snaps(){
		$this->twdata['page_title']=$this->session_user->first_name." ".$this->current_user->last_name."'s Album @ TinyWall";
		$this->load->view('header_view',$this->twdata);
		$this->twdata['album_id']=$this->uri->segment(3);
		if($album_details_result=$this->twfunctions->get_album_details($this->current_user->id_users,$this->uri->segment(3))){
			$album_details=$album_details_result[0];
			$this->twdata['album_details']=$album_details;
			$this->twdata['snaps']=$this->twfunctions->get_snaps($this->current_user->id_users,$this->uri->segment(3));
			$this->load->view('snaps_view',$this->twdata);	
		}else{
			$this->load->view('404');
		}
		$this->load->view('footer_view');
	}
	function uploadpicture(){
		if($this->userValid){
			if($this->input->post('uploadpicture')&&$this->twfunctions->check_album_existance($this->session_user->id_users,$this->input->post('album_id'))){
				$config['upload_path'] = './images/album_pictures/temp/';
				$config['allowed_types'] = 'gif|jpg|png';
				$config['max_size']	= '4096';
				$config['max_width']  = '3200';
				$config['max_height']  = '2400';
				$config['file_name']  = $this->session_user->id_users.'_upload.jpg';
				$config['overwrite']  = TRUE;
		
				$this->load->library('upload', $config);
				
				if ($this->upload->do_upload('userfile1')){
					$upload_data=$this->upload->data();
					$file_name=substr($upload_data['client_name'],0,strrpos($upload_data['client_name'],'.'));
					//edit check albumid $this->input->post('album_id')
					if($snap_id=$this->twfunctions->insert_snap($this->session_user->id_users,$this->input->post('album_id'),$file_name)){
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
							$this->session->set_flashdata('alert',$this->image_lib->display_errors());
							redirect($this->session_user->username.'/album/'.$this->input->post('album_id'));
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
							$this->session->set_flashdata('alert',$this->image_lib->display_errors());
							redirect($this->session_user->username.'/album/'.$this->input->post('album_id'));
						}else{
							$this->twfunctions->post_photo_status($this->session_user->id_users,$this->input->post('album_id'),$snap_id);
							//delete file
						}
					}
				}else{
					$this->session->set_flashdata('alert',$this->upload->display_errors());
					redirect($this->session_user->username.'/album/'.$this->input->post('album_id'));
				}
				$this->session->set_flashdata('alert','Successfully uploaded.');
				redirect($this->session_user->username.'/album/'.$this->input->post('album_id'));
			}else{
				$this->session->set_flashdata('alert','Something went wrong.');
				redirect($this->session_user->username.'/album/'.$this->input->post('album_id'));
			}
		}
	}
	function photo(){
		$this->twdata['page_title']=$this->session_user->first_name." ".$this->session_user->last_name."'s Album @ TinyWall";
		$this->twdata['current_page']='photo';
		$this->load->view('header_view',$this->twdata);
		if($this->twdata['photo']=$this->twfunctions->get_photo($this->current_user->id_users,$this->uri->segment(3))){
			$this->load->view('photo_view',$this->twdata);	
		}else{
			$this->load->view('404',$this->twdata);
		}
		$this->load->view('footer_view');
	}
	function deletesnap(){
		if($this->twfunctions->delete_snap($this->session_user->id_users,$this->uri->segment(5))){
			//delete file
			$this->session->set_flashdata('alert','Successfully deleted.');
			redirect($this->session_user->username.'/album/'.$this->uri->segment(4));
		}
	}
}
?>
<?php
require_once('tw_controller.php');
class Profile extends TW_Controller {
	function __construct()
	{
		parent::__construct();	
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('profile'));
	}
	function index()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			if($this->input->get('rel')!='tab'){
				$this->load->view('header_view',$this->twdata);
			}			
			$this->load->view('profile_view',$this->twdata);
			if($this->input->get('rel')!='tab'){
				$this->load->view('footer_view');
			}
		}
	}
	function edit(){
		if($this->userValid&&$this->user_relation==0){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->load->view('edit_profile_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function update(){
		if($this->userValid&&$this->input->post('updateprofile')){
			$user['first_name']=$this->input->post('first_name');
			$user['last_name']=$this->input->post('last_name');
			$user['about']=$this->input->post('about');
			$user['city']=$this->input->post('city');
			$user['gender']=$this->input->post('gender');
			$user['birth_date']=$this->input->post('birth_date');
			$user['country']=$this->input->post('country');
			if($this->twfunctions->update_profile($this->session_user->id_users,$user)){
				$this->session->set_flashdata('alert','Successfully Updated');	
				redirect('profile/edit');
			}
		}
	}
	function uploadpicture(){
		if($this->userValid){
			if($this->input->post('uploadpicture')){
				$config['upload_path'] = './images/profile_pictures/temp/';
				$config['allowed_types'] = 'gif|jpg|png';
				$config['max_size']	= '4096';
				$config['max_width']  = '3200';
				$config['max_height']  = '2400';
				$config['file_name']  = $this->session_user->username.'_upload.jpg';
				$config['overwrite']  = TRUE;
		
				$this->load->library('upload', $config);
				
				
				
				if ($this->upload->do_upload()){
					//edit check size
					$this->load->library('image_lib');
					$config = array();
					$config['image_library'] = 'gd2';
					$config['source_image']	= './images/profile_pictures/temp/'.$this->session_user->username.'_upload.jpg';
					$config['new_image'] = './images/profile_pictures/temp/'.$this->session_user->username.'.jpg';
					$config['maintain_ratio'] = TRUE;
					$config['overwrite']  = TRUE;
					$config['width']	 = 700;
					$config['height']	= 400;
					$this->image_lib->initialize($config);
					if(!$this->image_lib->resize()){
						$this->session->set_flashdata('alert',$this->image_lib->display_errors());
						redirect('profile/uploadpicture');
					}
					redirect('profile/croppicture');
				}else{
					$this->session->set_flashdata('alert',$this->upload->display_errors());
					redirect('profile/uploadpicture');
				}
			}
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->load->view('profile_picture_upload_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function croppicture(){
		$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
		$this->twdata['current_page']='croppicture';
		$this->load->view('header_view',$this->twdata);
		$this->load->view('profile_picture_crop_view',$this->twdata);
		$this->load->view('footer_view');
	}
	function savepicture(){
		$x=$this->input->post('x');
		$y=$this->input->post('y');
		$w=$this->input->post('w');
		$h=$this->input->post('h');
		
		$this->load->library('image_lib');
		
		$config = array();
		$config['image_library'] = 'gd2';
        $config['source_image'] = './images/profile_pictures/temp/'.$this->session_user->username.'.jpg';
        $config['maintain_ratio'] = FALSE;
        $config['new_image'] = './images/profile_pictures/full/'.$this->session_user->username.'.jpg';
        $config['width'] = $w;
        $config['height'] = $h;
        $config['x_axis'] = $x;
        $config['y_axis'] = $y;
		$config['overwrite']  = TRUE;
        //$config['create_thumb'] = TRUE;
		$this->image_lib->initialize($config);
        if(!$this->image_lib->crop()){
			$this->session->set_flashdata('alert',$this->image_lib->display_errors());
			redirect('profile/uploadpicture');
		}else{
			
	        //$this->image_lib->clear();
			
			//$this->load->library('image_lib');
			$config = array();
			$config['image_library'] = 'gd2';
			$config['source_image'] ='./images/profile_pictures/full/'.$this->session_user->username.'.jpg';
			$config['new_image'] ='./images/profile_pictures/full/'.$this->session_user->username.'.jpg';
			$config['maintain_ratio'] = TRUE;
			$config['overwrite']  = TRUE;
			$config['width']	 = 200;
			$config['height']	= 200;
			$this->image_lib->initialize($config);
			if(!$this->image_lib->resize()){
				$this->session->set_flashdata('alert',$this->image_lib->display_errors());
				redirect('profile/uploadpicture');
			}else{
				$config = array();
				$config['image_library'] = 'gd2';
				$config['source_image'] ='./images/profile_pictures/full/'.$this->session_user->username.'.jpg';
				$config['new_image'] ='./images/profile_pictures/thumb/'.$this->session_user->username.'.jpg';
				$config['maintain_ratio'] = TRUE;
				$config['overwrite']  = TRUE;
				$config['width']	 = 75;
				$config['height']	= 75;
				$this->image_lib->initialize($config);
				if(!$this->image_lib->resize()){
					$this->session->set_flashdata('alert',$this->image_lib->display_errors());
					redirect('profile/uploadpicture');
				}else{
					$this->session->set_flashdata('alert','Successfully uploaded.');
					$data['type']=2;
					$data['message']='';
					$data['link']='';
					$data['video']='';
					$this->twfunctions->post_new_status($this->session_user->id_users,$data);
					redirect('profile/uploadpicture');
				}
			}
		}
	}
}
?>
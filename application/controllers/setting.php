<?php
require_once('tw_controller.php');
class Setting extends TW_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('Functions','twfunctions');
		$this->userValid=$this->_validateUser(array('setting'));
	}
	function index()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$this->load->view('header_view',$this->twdata);
			$this->load->view('setting_view',$this->twdata);
			$this->load->view('account_setting_view',$this->twdata);
			$this->load->view('footer_view');
		}
	}
	function password()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			if($this->userValid){
			$user['old_password']=$this->input->post('oldpass');
			$user['new_password']=$this->input->post('newpass');
			if(isset($_POST['Update']))
			{
			if($this->twfunctions->edit_password($this->session_user->id_users,$user)){
				$this->session->set_flashdata('alert','Successfully Updated');	
				redirect('setting/password');
			}	
			else
			{
				$this->session->set_flashdata('alert','Password Mismatch!!!');
			}
			}		
			$this->load->view('header_view',$this->twdata);	
			$this->load->view('setting_view',$this->twdata);		
			$this->load->view('password_setting_view',$this->twdata);
			$this->load->view('footer_view');
			}
		}
	}
	function account()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$user['mobile']=$this->input->post('mobile');
			$user['email']=$this->input->post('email');
			if(isset($_POST['Update']))
			{
			if($this->twfunctions->edit_account($this->session_user->id_users,$user)){
				$this->session->set_flashdata('alert','Successfully Updated');	
				redirect('setting/account');
			}				
			}
			$this->load->view('header_view',$this->twdata);
			$this->load->view('setting_view',$this->twdata);
			$this->load->view('account_setting_view',$this->twdata);
			$this->load->view('footer_view');
			}
}

	function privacy()
	{
		if($this->userValid){
			$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
			$user['privacy']=$this->input->post('visiblity'); // 0 - public 1 - friends
			if(isset($_POST['Update']))
			{
				if($this->session_user->privacy!=$user['privacy'])
			{
				$this->twfunctions->edit_privacy($this->session_user->id_users,$user);
			}
			redirect('setting/privacy');
			}
			/**/
			$this->load->view('header_view',$this->twdata);
			$this->load->view('setting_view',$this->twdata);	
			$this->load->view('privacy_view',$this->twdata);
			$this->load->view('footer_view');
			}
	}
	function themes(){
		if($this->input->post('savetheme')){
			$data['bodybg']=$this->input->post('bodybg');
			$data['contbg']=$this->input->post('contbg');
			$data['font']=$this->input->post('font');
			$data['link']=$this->input->post('link');
			$data['highlight']=$this->input->post('highlight');
			if($this->twfunctions->save_theme($this->session_user->id_users,$data)){
				
			}
		}
		if($this->input->post('savebackgroundimg')){
			$data['backgroundAttachment']=$this->input->post('backgroundAttachment');
			$data['backgroundRepeat']=$this->input->post('backgroundRepeat');
			$data['backgroundPosition']=$this->input->post('backgroundPosition');
			if($this->twfunctions->save_theme_background($this->session_user->id_users,$data)){
				
			}
			copy('./images/theme_pictures/temp/'.$this->session_user->username.'_upload.jpg','./images/theme_pictures/'.$this->session_user->username.'_bg.jpg');
			redirect('setting/themes/');
		}
		if($this->input->post('updatebackgroundimg')){
			$data['backgroundAttachment']=$this->input->post('backgroundAttachment');
			$data['backgroundRepeat']=$this->input->post('backgroundRepeat');
			$data['backgroundPosition']=$this->input->post('backgroundPosition');
			if($this->twfunctions->save_theme_background($this->session_user->id_users,$data)){
				redirect('setting/themes/');
			}
		}
		if($this->input->post('removebackgroundimg')){
			if($this->twfunctions->remove_theme_background($this->session_user->id_users)){
				unlink('./images/theme_pictures/'.$this->session_user->username.'_bg.jpg');
				redirect('setting/themes/');
			}
		}
		if($this->input->post('uploadBackground')){
			$config['upload_path'] = './images/theme_pictures/temp/';
				$config['allowed_types'] = 'gif|jpg|png';
				$config['max_size']	= '4096';
				$config['max_width']  = '3200';
				$config['max_height']  = '2400';
				$config['file_name']  = $this->session_user->username.'_upload.jpg';
				$config['overwrite']  = TRUE;
				$this->load->library('upload', $config);			
				if ($this->upload->do_upload()){
					redirect('setting/themes/?themebg=upload');
				}
		}
		$this->twdata['page_title']=$this->current_user->first_name." ".$this->current_user->last_name." @ TinyWall";
		$this->load->view('header_view',$this->twdata);
		$this->load->view('setting_view',$this->twdata);
		$this->load->view('themes_view',$this->twdata);	
		$this->load->view('footer_view');
	}
	
}
?>
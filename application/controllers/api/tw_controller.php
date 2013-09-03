<?php
class TW_Controller extends CI_Controller {
	protected $session_user,$current_user,$user_relation,$userValid,$twdata;
	function __construct()
	{
		parent::__construct();
		$this->twdata['current_page']='';
	}
	function _validateUser($page){
		if(TRUE){
			if($user_data=$this->twfunctions->getApiSessionData($this->input->get('user_id'),$this->input->get('access_token'))){
				$this->session_user=$user_data[0];$this->twdata['session_user']=$this->session_user;
			}else{
				//redirect('logout');
				return FALSE;
			}
			$username=$this->uri->segment(2);
			if(in_array($username,$page)){$username=$this->session_user->username;}
			if($username==$this->session_user->username){
				$this->user_relation=0;$this->twdata['user_relation']=$this->user_relation;
				$this->current_user=$this->session_user;$this->twdata['current_user']=$this->current_user;
				return TRUE;
			}else{
				$this->user_relation=1;$this->twdata['user_relation']=1;
				if($user_data=$this->twfunctions->getCurrentData($username,$this->session_user->id_users)){
					$this->current_user=$user_data[0];$this->twdata['current_user']=$this->current_user;
					return TRUE;
				}else{
					/*$this->load->view('index_header_view');
					$this->load->view('404');
					$this->load->view('index_footer_view');*/
					return FALSE;
				}
			}
		}else{
			//$this->session->set_flashdata('redirect',uri_string());
			//redirect('login/redirect');
			return FALSE;
		}
		return FALSE;
	}
	function send_twmail($to,$bcc,$subject,$message){
		$this->load->library('email');
		$this->email->from('arundavid.info@gmail.com','Admin TinyWall');
		if($to){$this->email->to($to);}
		//$this->email->cc('arundavid.info@gmail.com'); 
		if($bcc){$this->email->bcc($bcc);}
		$this->email->subject($subject);
		$header='
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head></head><body>';
		$footer='</body></html>';
		$this->email->message($header.$message.$footer);	
		$this->email->send();
		echo 'mail'.$this->email->print_debugger();
		return TRUE;
	}
}
?>
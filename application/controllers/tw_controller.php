<?php
class TW_Controller extends CI_Controller {
	protected $session_user,$current_user,$user_relation,$userValid,$twdata;
	function __construct()
	{
		parent::__construct();
		$this->twdata['current_page']='';
		$this->load->helper('language');
		$this->load->library('session');
		if(!$this->session->userdata('language')){
			$session_data = array('language'=>'english');//first
			$this->session->set_userdata($session_data);
		}
		$this->lang->load('site',$this->session->userdata('language'));
	}
	function _validateUser($page){
		if($this->session->userdata('logged_in')){
			if($user_data=$this->twfunctions->getSessionData($this->session->userdata('session_id'))){
				$this->session_user=$user_data[0];$this->twdata['session_user']=$this->session_user;
			}else{
				redirect('logout');
				return FALSE;
			}
			$username=$this->uri->segment(1);
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
					$this->load->view('index_header_view');
					$this->load->view('404');
					$this->load->view('index_footer_view');
					return FALSE;
				}
			}
		}else{
			$this->session->set_flashdata('redirect',uri_string());
			redirect('login/redirect');
			return FALSE;
		}
		return FALSE;
	}
	function getExtraDetails($details){
		if(in_array('friends',$details)){
			$friends=$this->twfunctions->get_friends_details($this->current_user->id_users);
			$this->twdata['friends_details']=$friends;
		}
		if(in_array('mutualfriends',$details)){
			if($this->user_relation){
				$friends=$this->twfunctions->get_mutualfriends_details($this->session_user->id_users,$this->current_user->id_users);
				$this->twdata['mutualfriends_details']=$friends;	
			}
		}
		if(in_array('friendsuggession',$details)){
			if(!$this->user_relation){
				$friends=$this->twfunctions->get_friendsuggession_details($this->session_user->id_users);
			}
			$this->twdata['friendsuggession_details']=$friends;
		}
		if(in_array('birthdayalert',$details)){
			if(!$this->user_relation){
				$bdalerts=$this->twfunctions->get_birthday_alerts($this->session_user->id_users);
			}
			$this->twdata['birthdayalert_details']=$bdalerts;
		}
	}
	function generateSlug($phrase, $maxLength)
	{
	    $result = strtolower($phrase);
	    $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
	    $result = trim(preg_replace("/[\s-]+/", " ", $result));
	    $result = trim(substr($result, 0, $maxLength));
	    $result = preg_replace("/\s/", "-", $result);
	    return $result;
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
		//$this->email->send();
		//echo 'mail'.$this->email->print_debugger();
		return TRUE;
	}
}
?>
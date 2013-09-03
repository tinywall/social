<?php
class Twadmin extends CI_Model {
    function __construct()
    {
        parent::__construct();
		$this->load->library('session');
		$this->load->database();
    }
	function get_login_log(){
		$query = $this->db->query('SELECT substring(time,1,10) AS date, UNIX_TIMESTAMP(time) as timestamp, COUNT(id_user_login) as count FROM user_login where DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= time GROUP BY date');
		$result=$query->result();
		return $result;
	}
	function get_status_log(){
		$query = $this->db->query('SELECT substring(time,1,10) AS date, UNIX_TIMESTAMP(time) as timestamp, COUNT(id_status_post) as count FROM status_post where DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= time GROUP BY date');
		$result=$query->result();
		return $result;
	}
	function get_register_log(){
		$query = $this->db->query('SELECT substring(register_time,1,10) AS date, UNIX_TIMESTAMP(register_time) as timestamp, COUNT(id_users) as count FROM users where DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= register_time GROUP BY date');
		$result=$query->result();
		return $result;
	}
	function get_gender_log(){
		$query = $this->db->query('select gender,count(id_users) as total from users group by gender order by gender desc');
		$result=$query->result();
		return $result;
	}
	function get_age_log(){
		$query = $this->db->query("select 
		(select count(id_users) from users where gender=1 and birth_date  BETWEEN DATE_SUB(CURDATE(),INTERVAL 17 YEAR) AND DATE_SUB(CURDATE(),INTERVAL 13 YEAR)) as male_teen,
		(select count(id_users) from users where gender=0 and birth_date  BETWEEN DATE_SUB(CURDATE(),INTERVAL 17 YEAR) AND DATE_SUB(CURDATE(),INTERVAL 13 YEAR)) as female_teen,
		(select count(id_users) from users where gender=1 and birth_date  BETWEEN DATE_SUB(CURDATE(),INTERVAL 24 YEAR) AND DATE_SUB(CURDATE(),INTERVAL 17 YEAR)) as male_youth,
		(select count(id_users) from users where gender=0 and birth_date  BETWEEN DATE_SUB(CURDATE(),INTERVAL 24 YEAR) AND DATE_SUB(CURDATE(),INTERVAL 17 YEAR)) as female_youth,
		(select count(id_users) from users where gender=1 and birth_date  BETWEEN DATE_SUB(CURDATE(),INTERVAL 35 YEAR) AND DATE_SUB(CURDATE(),INTERVAL 24 YEAR)) as male_professional,
		(select count(id_users) from users where gender=0 and birth_date  BETWEEN DATE_SUB(CURDATE(),INTERVAL 35 YEAR) AND DATE_SUB(CURDATE(),INTERVAL 24 YEAR)) as female_professional,
		(select count(id_users) from users where gender=1 and birth_date  >= DATE_SUB(CURDATE(),INTERVAL 35 YEAR)) as male_other,
		(select count(id_users) from users where gender=0 and birth_date  >= DATE_SUB(CURDATE(),INTERVAL 35 YEAR)) as female_other
		 from dual");
		$result=$query->result();
		return $result;
	}
	function get_toplogin_log(){
		$query = $this->db->query('select *,(select count(id_user_login) from user_login where users.id_users=user_login.user_id) as total_login from users order by total_login desc limit 0,10');
		$result=$query->result();
		return $result;
	}
	function get_toppages_log(){
		$query='select *,count(id_page_visits) as total_view from page_visits group by url order by total_view desc limit 0,10';
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_tophituser_log(){
		$query='select *,count(id_page_visits) as total_view from page_visits,users where users.id_users=page_visits.user_id group by user_id order by total_view desc limit 0,10';
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_topvieweduser_log(){
		$query='select *,count(id_page_visits) as total_view from page_visits,users where users.id_users=page_visits.visited_user and user_id not in (visited_user) group by visited_user order by total_view desc limit 0,10';
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_trend_log(){
		$query = $this->db->query('select status_message from status_post');
		$result=$query->result();
		return $result;
	}
	function get_usersearch_result($search_query){
		if(!$search_query['page']){$search_query['page']=1;}
		$search_query['limit']=20;
		$search_query['start']=$search_query['page']*$search_query['limit']-$search_query['limit'];
		$query="select * from users where email_active='1' ";
		$query2="select count(id_users) as total from users where email_active='1' ";
		$query_constraints='';
        if($search_query['name']){
            $query_constraints.=" and ( first_name REGEXP '".$search_query['name']."' or last_name REGEXP '".$search_query['name']."' ) ";
        }
        if($search_query['location']){
            $query_constraints.=" and city  REGEXP '".$search_query['location']."' ";
		}
		$query2.=$query_constraints;
		$query.=$query_constraints." order by first_name limit ".$search_query['start'].",".$search_query['limit'];
		$db_query = $this->db->query($query);
		$result['search_result']=$db_query->result();
		$db_query2 = $this->db->query($query2);
		$total_search_result=$db_query2->result();$row=$total_search_result[0];
		$result['search_result_count']=$row->total;
		return $result;	
	}
	function getCurrentDataPublic($username){
		$query ='select *,FLOOR(DATEDIFF(NOW(),users.birth_date)/365.25) as age from users where username="'.$username.'"';
		$db_query=$this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
		return FALSE;
	}
	function ban_user($username){
		$query = $this->db->update('users',array('admin_ban'=>1),array('username'=>$username,'admin_ban'=>0));
		if($this->db->affected_rows()){
			return TRUE;
		}else{
			return FALSE;
		}	
	}
	function unban_user($username){
		$query = $this->db->update('users',array('admin_ban'=>0),array('username'=>$username,'admin_ban'=>1));
		if($this->db->affected_rows()){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function get_nonactive_users(){
		$query ="SELECT * FROM `users` WHERE password<>'385b927dfddc7020c81eb5d7c5b2f55b' and email_active='0'";
		$db_query=$this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;
		}else{
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
		//$this->email->send();
		//echo 'mail'.$this->email->print_debugger();
		return TRUE;
	}
}
?>
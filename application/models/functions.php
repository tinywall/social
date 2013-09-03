<?php
class Functions extends CI_Model {
    function __construct(){
        parent::__construct();
		$this->load->library('session');
		$this->load->database();
    }
	function check_username_availibility($username){
		$query = $this->db->query('select count(*) as total from users where username="'.$username.'"');
		$user_info=$query->result();
		$row=$user_info[0];
		if($row->total==0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function register($user){
		$query = $this->db->query('select count(*) as total from users where username="'.$user['username'].'" or email="'.$user['email'].'"');
		$user_info=$query->result();
		$row=$user_info[0];
		if($row->total==0){
			$data = array('first_name' => $user['first_name'],'last_name' => $user['last_name'],
						  'email' => $user['email'],'email_key' => $user['email_key'],'email_active' => $user['email_active'],
						  'mobile_active' => $user['mobile_active'], 'username' => $user['username'], 'password' => $user['password'],
						   'mobile' => $user['mobile'], 'gender' => $user['gender'], 'birth_date' => $user['birth_date']);
			$sql = $this->db->insert_string('users', $data);
			$this->db->query($sql);
			//update md5 of $this->db->insert_id()
			$query = $this->db->query('select * from users where email="'.$user['email'].'" and username="'.$user['username'].'"');
			$newuser=$query->result();
			$newuser_data=$newuser[0];
			$query = $this->db->update('users',array('session_id'=>md5($newuser_data->id_users)),array('id_users'=>$newuser_data->id_users));
			$query = $this->db->query('select * from users where id_users="'.$newuser_data->id_users.'"');
			return $query->result();
		}else{
			return FALSE;
		}
	}
	function twitter_register($user){
		$query = $this->db->query('select count(*) as total from users where username="'.$user['username'].'" or email="'.$user['email'].'" or twitter_id="'.$user['twitter_id'].'"');
		$user_info=$query->result();
		$row=$user_info[0];
		if($row->total==0){
			$data = array('first_name' => $user['first_name'],'last_name' => $user['last_name'],
						  'email' => $user['email'],'email_key' => $user['email_key'],'email_active' => $user['email_active'],
						  'mobile_active' => $user['mobile_active'], 'username' => $user['username'], 'password' => $user['password'],
						   'mobile' => $user['mobile'], 'gender' => $user['gender'], 'birth_date' => $user['birth_date'],
						   'twitter_id'=> $user['twitter_id'],'twitter_oauth_token'=> $user['twitter_oauth_token'],'twitter_oauth_token_secret'=> $user['twitter_oauth_token_secret']
						   );
			$sql = $this->db->insert_string('users', $data);
			$this->db->query($sql);
			//update md5 of $this->db->insert_id()
			$query = $this->db->query('select * from users where email="'.$user['email'].'" and username="'.$user['username'].'"');
			$newuser=$query->result();
			$newuser_data=$newuser[0];
			$query = $this->db->update('users',array('session_id'=>md5($newuser_data->id_users)),array('id_users'=>$newuser_data->id_users));
			$query = $this->db->query('select * from users where id_users="'.$newuser_data->id_users.'"');
			return $query->result();
		}else{
			return FALSE;
		}
	}
	function fbregister($user){
		$query = $this->db->query('select count(*) as total from users where username="'.$user['username'].'" or email="'.$user['email'].'"');
		$user_info=$query->result();
		$row=$user_info[0];
		if($row->total==0){
			$data = array('first_name' => $user['first_name'],'last_name' => $user['last_name'],
						  'email' => $user['email'],'email_key' => $user['email_key'],'email_active' => $user['email_active'],
						  'mobile_active' => $user['mobile_active'], 'username' => $user['username'], 'password' => $user['password'],
						   'mobile' => $user['mobile'], 'gender' => $user['gender'], 'birth_date' => $user['birth_date'],'facebook_id'=>$user['facebook_id'],'facebook_access_token'=>$user['facebook_access_token']);
			$sql = $this->db->insert_string('users', $data);
			$this->db->query($sql);
			//update md5 of $this->db->insert_id()
			$query = $this->db->query('select * from users where email="'.$user['email'].'" and username="'.$user['username'].'"');
			$newuser=$query->result();
			$newuser_data=$newuser[0];
			$query = $this->db->update('users',array('session_id'=>md5($newuser_data->id_users)),array('id_users'=>$newuser_data->id_users));
			$query = $this->db->query('select * from users where id_users="'.$newuser_data->id_users.'"');
			return $query->result();
		}else{
			return FALSE;
		}
	}
	function openid_register($user){
		$query = $this->db->query('select count(*) as total from users where username="'.$user['username'].'" or email="'.$user['email'].'"');
		$user_info=$query->result();
		$row=$user_info[0];
		if($row->total==0){
			$data = array('first_name' => $user['first_name'],'last_name' => $user['last_name'],
						  'email' => $user['email'],'email_key' => $user['email_key'],'email_active' => $user['email_active'],
						  'mobile_active' => $user['mobile_active'], 'username' => $user['username'], 'password' => $user['password'],
						   'mobile' => $user['mobile'], 'gender' => $user['gender'], 'birth_date' => $user['birth_date'],
						   'openid_identity' => $user['openid_identity']);
			$sql = $this->db->insert_string('users', $data);
			$this->db->query($sql);
			//update md5 of $this->db->insert_id()
			$query = $this->db->query('select * from users where email="'.$user['email'].'" and username="'.$user['username'].'"');
			$newuser=$query->result();
			$newuser_data=$newuser[0];
			$query = $this->db->update('users',array('session_id'=>md5($newuser_data->id_users)),array('id_users'=>$newuser_data->id_users));
			$query = $this->db->query('select * from users where id_users="'.$newuser_data->id_users.'"');
			return $query->result();
		}else{
			return FALSE;
		}
	}
	function activate_email($username,$email_key){
		$query = $this->db->update('users',array('email_active'=>1,'email_key'=>NULL),array('username'=>$username,'email_key'=>$email_key,'email_active'=>0));
		if($this->db->affected_rows()){
			return $this->getCurrentDataPublic($username);
		}else{
			return FALSE;
		}		
	}
	function forgot_password($email,$reset_key){
		$query = $this->db->update('users',array('pwd_reset_key'=>$reset_key),array('email'=>$email));
		if($this->db->affected_rows()){
			$query = $this->db->query('select * from users where email="'.$email.'"');
			return $query->result();
		}else{
			return FALSE;
		}		
	}
	function reset_password($username,$password,$reset_key){
		$query = $this->db->update('users',array('password'=>$password,'pwd_reset_key'=>NULL),array('username'=>$username,'pwd_reset_key'=>$reset_key));
		if($this->db->affected_rows()){
			return $this->getCurrentDataPublic($username);
		}else{
			return FALSE;
		}
	}
	function getSessionData($sessionId){
		$keywords = preg_split("/[\s_]+/", $sessionId);
		$session_id=$keywords[0];
		$access_token=$keywords[1];
		/*$query = $this->db->query('select *,FLOOR(DATEDIFF(NOW(),users.birth_date)/365.25) as age from users where session_id="'.$keywords[0].'" and access_token="'.$keywords[1].'"');
		$result=$query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
		return FALSE;*/
		$sql='select *,FLOOR(DATEDIFF(NOW(),users.birth_date)/365.25) as age from users where session_id="'.$session_id.'" and id_users in (select user_id from web_login where users.id_users=user_id and access_token="'.$access_token.'" and validity=1)';
		$query = $this->db->query($sql);
		$result=$query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
		return FALSE;
	}
	function getApiSessionData($user_id,$access_token){
		$sql='select *,FLOOR(DATEDIFF(NOW(),users.birth_date)/365.25) as age from users where id_users in (select user_id from api_login where user_id="'.$user_id.'" and access_token="'.$access_token.'" and state=1)';
		$query = $this->db->query($sql);
		$result=$query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
		return FALSE;
	}
	function getCurrentData($username,$sessionUserId){
		$query ='select *,(select count(*) from friend_connection where owner=users.id_users and friend='.$sessionUserId.') as friend_relation,
		(select count(*) from follow_connection where owner=users.id_users and follower='.$sessionUserId.') as follow_relation,
		(select count(*) from friend_request where (sender=users.id_users and receiver='.$sessionUserId.') or (receiver=users.id_users and sender='.$sessionUserId.')) as request_relation,
		(select count(*) from friend_request where sender=users.id_users and receiver='.$sessionUserId.') as request_receive_relation,
		(select count(*) from friend_request where receiver=users.id_users and sender='.$sessionUserId.') as request_send_relation,
		FLOOR(DATEDIFF(NOW(),users.birth_date)/365.25) as age
		 from users where username="'.$username.'"';
		$db_query=$this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
		return FALSE;
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
	function getAuthenticateData($username){
		$query='select * from users where ( username="'.$username.'" or email="'.$username.'" ) and email_active=1';
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
	}
	function getFacebookAuthenticateData($fbid,$fbmail,$fbaccesstoken){
		$query='select * from users where ( facebook_id="'.$fbid.'" and email="'.$fbmail.'" ) and email_active=1';
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
	}
	function getOpenIDAuthenticateData($email){
		$query='select * from users where email="'.$email.'" and email_active=1';
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
	}
	function getTwitterAuthenticateData($twitterid,$oauth_token,$oauth_token_secret){
		$query='select * from users where twitter_id="'.$twitterid.'"';
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;
		}else{
			return FALSE;	
		}
	}
	function setAccessToken($id_users,$access_token){
		//$query = $this->db->update('users',array('access_token'=>$access_token),array('id_users'=>$id_users));
		//return $query;
		$data = array('access_token'=>$access_token,'user_id'=>$id_users,'login_time'=>gmdate("Y-m-d H:i:s", time()));
		$sql = $this->db->insert_string('web_login',$data);
		if($this->db->query($sql)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function unsetAccessToken($id_users,$sessionId){
		$keywords = preg_split("/[\s_]+/", $sessionId);
		$session_id=$keywords[0];
		$access_token=$keywords[1];
		$query = $this->db->update('web_login',array('validity'=>0),array('user_id'=>$id_users,'access_token'=>$access_token));
		return $query;
	}
	function unsetApiLoginAccessToken($id_users,$access_token){
		$query = $this->db->update('api_login',array('state'=>0),array('user_id'=>$id_users,'access_token'=>$access_token));
		return $query;
	}
	function setApiLoginAccessToken($id_users,$access_token){
		$data = array('access_token'=>$access_token,'user_id'=>$id_users,'time'=>gmdate("Y-m-d H:i:s", time()));
		$sql = $this->db->insert_string('api_login',$data);
		if($this->db->query($sql)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function set_login_log($user_id){
		$data = array('user_id' => $user_id);
		$sql = $this->db->insert_string('user_login', $data);
		if($this->db->query($sql)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function update_profile($user_id,$user){
		$query = $this->db->update('users',array('first_name'=>$user['first_name'],'last_name'=>$user['last_name'],'country'=>$user['country'],'birth_date'=>$user['birth_date'],'about'=>$user['about'],'city'=>$user['city'],'gender'=>$user['gender']),array('id_users'=>$user_id));
		if($query){
			$data['type']=1;
			$data['message']='';
			$data['link']='';
			$data['video']='';
			$this->post_new_status($user_id,$data);
		}
		return $query;
	}
	function edit_password($user_id,$user){
	$query = "SELECT * FROM users WHERE id_users='".$user_id."' and password='".md5($user['old_password'])."'";
	$db_query=$this->db->query($query);
		$item=$db_query->result();
	//$query2=$query." and password  REGEXP '".."' ";
	if($item)
	{
		$query1 = $this->db->update('users',array('password'=>md5($user['new_password'])),array('id_users'=>$user_id));
	}		
	}
	function edit_privacy($user_id,$user){
	$query="SELECT * FROM `users` WHERE id_users='".$user_id."'";
		$db_query=$this->db->query($query);
		$item=$db_query->result();
		if($item)
	{
		$query1 = $this->db->update('users',array('privacy'=>$user['privacy']),array('id_users'=>$user_id));
		return $query1;
	}	
	}
	function edit_account($user_id,$user){
	$query = "SELECT * FROM users WHERE id_users='".$user_id."'";
	$db_query=$this->db->query($query);
		$item=$db_query->result();
	if($item)
	{
		$query1 = $this->db->update('users',array('mobile'=>($user['mobile']),'email'=>($user['email'])),array('id_users'=>$user_id));
		return $query1;
	}		
	}
	function get_world($user_id){
		
		$query="SELECT * FROM `world_item` left outer join world on (world_item.id_world_item=world.item_id and owner=".$user_id.") order by status desc";
		$db_query=$this->db->query($query);
		$item=$db_query->result();
		return $item;
	}
	function get_world_having($user_id){
		
		$query="SELECT * FROM `world_item`where id_world_item in (select item_id from world where status='2' and owner=".$user_id." ) ";
		$db_query=$this->db->query($query);
		$item=$db_query->result();
		return $item;
	}
		function get_world_wish($user_id){
		
		$query="SELECT * FROM `world_item`where id_world_item in (select item_id from world where status='1' and owner=".$user_id." ) ";
		$db_query=$this->db->query($query);
		$item=$db_query->result();
		return $item;
	}
	function delete_world($user_id,$item_id)
	{
		$this->db->delete('world', array('item_id' => $item_id,'owner'=>$user_id)); 
		
	}
	function insert_world($user_id,$item_id,$status){
		$data = array('owner' => $user_id,'item_id'=>$item_id,'status'=>$status);
		$sql = $this->db->insert_string('world', $data);
		if($this->db->query($sql)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function update_world($user_id,$item_id,$status){
		$query = $this->db->update('world',array('status'=>$status),array('owner'=>$user_id,'item_id'=>$item_id));
		return $query;
	}
	function get_publicsearch_result($search_query){
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
	function get_search_result($search_query){
		if(!$search_query['page']){$search_query['page']=1;}
		$search_query['limit']=20;
		$search_query['start']=$search_query['page']*$search_query['limit']-$search_query['limit'];
		$query="select *,
		(select count(*) from friend_connection where owner=users.id_users and friend=".$search_query['user_id'].") as friend_relation,
		(select count(*) from follow_connection where owner=users.id_users and follower=".$search_query['user_id'].") as follow_relation
		 from users where email_active='1' ";
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
	function sendFriendRequest($sender,$receiver){
		$data = array('sender' => $sender,'receiver' => $receiver);
		$sql = $this->db->insert_string('friend_request', $data);
		if($this->db->query($sql)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function getFollowers($user_id){
		$query="select * from follow_connection,users where follow_connection.follower=users.id_users and owner=".$user_id;
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function getFollowings($user_id){
		$query="select * from follow_connection,users where follow_connection.owner=users.id_users and follower=".$user_id;
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function sendFollowRequest($sender,$receiver){
		$data = array('follower' => $sender,'owner' => $receiver);
		$sql = $this->db->insert_string('follow_connection', $data);
		if($this->db->query($sql)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function sendUnfollowRequest($sender,$receiver){
		if($this->db->delete('follow_connection',array('owner' => $receiver,'follower'=>$sender))){
			return TRUE;
		}
		return FALSE;
	}
	function deleteFriend($user_id,$friend){
		if($this->db->delete('friend_connection',array('owner' => $user_id,'friend'=>$friend))&&$this->db->delete('friend_connection',array('friend' => $user_id,'owner'=>$friend))){
			return TRUE;
		}
		return FALSE;
	}
	function deleteFriendRequest($user_id,$friend){
		$query="delete from friend_request where sender=".$user_id." and receiver=".$friend;
		$db_query = $this->db->query($query);
		if($this->db->affected_rows()){
			return TRUE;
		}
		return FALSE;
	}
	function get_friend_requests($user_id){
		$query="select * from friend_request,users where friend_request.sender=users.id_users and receiver=".$user_id;
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function acceptFriendRequest($receiver,$sender){
		if($this->db->delete('friend_request', array('receiver' => $receiver,'sender'=>$sender))){
			$data1 = array('owner' => $sender,'friend' => $receiver);
			$data2 = array('friend' => $sender,'owner' => $receiver);
			$sql = $this->db->insert_string('friend_connection', $data1);
			$db_query1=$this->db->query($sql);
			$sql = $this->db->insert_string('friend_connection', $data2);
			$db_query2=$this->db->query($sql);
			if($db_query1&&$db_query2){
				return TRUE;
			}
		}
		return FALSE;
	}
	function denyFriendRequest($receiver,$sender){
		if($this->db->delete('friend_request', array('receiver' => $receiver,'sender'=>$sender))){
			return TRUE;
		}
		return FALSE;
	}
	function get_friends($user_id){
		$query="select *,FLOOR(DATEDIFF(NOW(),users.birth_date)/365.25) as age from friend_connection,users where friend_connection.friend=users.id_users and owner=".$user_id;
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_status_feeds($user_id,$last_status_id){
		if($last_status_id==0){
			$query="select *,(select count(id_status_likes) from status_likes where status_likes.status_id=status_post.id_status_post and owner=1) as liked from status_post,users where (status_post.owner in (select owner from follow_connection where follower=".$user_id.") or owner=".$user_id.") and status_post.sender=users.id_users and status_post.owner=status_post.sender order by status_post.id_status_post desc limit 0,10";
		}else{
			$query="select *,(select count(id_status_likes) from status_likes where status_likes.status_id=status_post.id_status_post and owner=1) as liked from status_post,users where (status_post.owner in (select owner from follow_connection where follower=".$user_id.") or owner=".$user_id.") and status_post.sender=users.id_users and status_post.owner=status_post.sender and status_post.id_status_post<".$last_status_id." order by status_post.id_status_post desc limit 0,10";
			//$query="select *,(select count(id_status_likes) from status_likes where status_likes.status_id=status_post.id_status_post and owner=1) as liked from status_post,users where (status_post.owner in (select follower from follow_connection where owner=".$user_id.") or owner=".$user_id.") and status_post.sender=users.id_users and status_post.owner=status_post.sender and status_post.id_status_post<".$last_status_id." order by status_post.id_status_post desc limit 0,10";
		}
		$db_query = $this->db->query($query);
		$result['status']=$db_query->result();
		$result['comment']=$this->get_comments_from_status($result['status']);
		return $result;
	}
	function get_comments_from_status($status_result){
		$status_id='';
		foreach($status_result as $row){
			$status_id=$status_id.','.$row->id_status_post;
		}
		$status_id=substr($status_id,1);
		if($status_id){
			$db_query2 = $this->db->query("select * from status_comment,users where status_comment.owner=users.id_users and status_id in (".$status_id.") order by status_comment.status_id desc,status_comment.id_status_comment asc");
		return $db_query2->result();
		}else{
			return FALSE;
		}
	}
	function get_status_posts($user_id,$last_status_id){
		if($last_status_id==0){
			$query="select *,(select count(id_status_likes) from status_likes where status_likes.status_id=status_post.id_status_post and owner=1) as liked from status_post,users where status_post.sender=users.id_users and owner=".$user_id." order by status_post.id_status_post desc limit 0,10";
		}else{
			$query="select *,(select count(id_status_likes) from status_likes where status_likes.status_id=status_post.id_status_post and owner=1) as liked from status_post,users where status_post.sender=users.id_users and status_post.id_status_post<".$last_status_id." and owner=".$user_id." order by status_post.id_status_post desc limit 0,10";
		}
        $db_query = $this->db->query($query);
		$result['status']=$db_query->result();
		$result['comment']=$this->get_comments_from_status($result['status']);
		return $result;
	}
	/*function get_status_comments($status_id){
		$query="select * from status_comment,users where status_comment.owner=users.id_users and status_id=".$status_id;
		//." order by status_comment.id_status_comment desc"
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}*/
	function get_status_post($user_id,$status_id){
		//$query="select *,(select count(id_status_likes) from status_likes where status_likes.status_id=status_post.id_status_post and owner=1) as liked from status_post,users where status_post.sender=users.id_users and owner=".$user_id." and id_status_post=".$status_id;
        $query="select *,(select count(id_status_likes) from status_likes where status_likes.status_id=status_post.id_status_post and owner=1) as liked from status_post,users where status_post.sender=users.id_users and id_status_post=".$status_id;
        $db_query = $this->db->query($query);
		$result['status']=$db_query->result();
		$result['comment']=$this->get_comments_from_status($result['status']);
		return $result;
	}
	function post_photo_status($owner,$album_id,$snap_id){
		$data['type']=3;
		$data['message']='';
		$data['link']='';
		$data['video']='';
		$data['image']=$snap_id;
		$this->post_new_status($owner,$data);
	}
	function post_new_status($owner,$data){//0-text 1-profileedit 2-profilephoto 3-albumphoto 4-album 5-wallphoto
		if(isset($data['owner'])){
			$status_owner=$data['owner'];
		}else{
			$status_owner=$owner;
		}
		if(isset($data['image'])){
			$status_image=$data['image'];
		}else{
			$status_image=0;
		}
		$status_data = array('sender' => $owner,'owner' => $status_owner,'status_message' => $data['message'],'image'=>$status_image,'link' => $data['link'],'video' => $data['video'],'type' => $data['type'],'time'=>gmdate("Y-m-d H:i:s", time()));
		$sql = $this->db->insert_string('status_post', $status_data);
		if($this->db->query($sql)){
			$query="select *,0 as liked from status_post,users where status_post.sender=users.id_users and status_post.sender=".$owner." order by status_post.id_status_post desc limit 0,1";
			$db_query = $this->db->query($query);
			$result['status']=$db_query->result();
			$result['comment']=FALSE;
			return $result;
		}else{
			return FALSE;
		}
	}
	function post_new_comment($owner,$status_id,$message){
		$data = array('status_id' => $status_id,'owner' => $owner,'comment_message' => $message,'time'=>gmdate("Y-m-d H:i:s", time()));
		$sql = $this->db->insert_string('status_comment', $data);
		if($this->db->query($sql)){
			$query="select * from status_comment,users where status_comment.owner=users.id_users and status_comment.status_id=".$status_id." order by id_status_comment desc limit 0,1";
			$db_query = $this->db->query($query);
			$result=$db_query->result();
			return $result;
		}else{
			return FALSE;
		}
	}
	function deleteStatus($user,$status_id){
		$this->db->delete('status_comment', array('status_id' => $status_id));
		$query="delete from status_post where id_status_post=".$status_id." and ( sender=".$user." or owner=".$user." )";
		$db_query = $this->db->query($query);
		if($this->db->affected_rows()){
			return TRUE;
		}
		return FALSE;
	}
	function deleteComment($user,$comment_id){
		$this->db->delete('status_comment', array('id_status_comment' => $comment_id,'owner' => $user));
		$affected_rows1=$this->db->affected_rows();
		if(!$affected_rows1){
			$query="delete from status_comment where id_status_comment=".$comment_id." and status_id in ( select id_status_post from status_post where owner=".$user." )";
			$db_query = $this->db->query($query);
			$affected_rows2=$this->db->affected_rows();
			if($affected_rows2){
				return TRUE;
			}
		}else{
			return TRUE;
		}
		return FALSE;
	}
	function likeStatus($user,$status_id){
		$query="select * from status_likes where owner=".$user." and status_id=".$status_id;
		$db_query = $this->db->query($query);
		if(!$db_query->num_rows()){
			$data = array('owner' => $user,'status_id' =>$status_id);
			$sql = $this->db->insert_string('status_likes', $data);
			if($this->db->query($sql)){
				$query="update status_post set like_count=(like_count+1) where id_status_post=".$status_id;
				$db_query = $this->db->query($query);
				if($this->db->affected_rows()){
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	function unlikeStatus($user,$status_id){
		$this->db->delete('status_likes', array('status_id' => $status_id,'owner' => $user));
		if($this->db->affected_rows()){
			$query="update status_post set like_count=(like_count-1) where id_status_post=".$status_id;
			$db_query = $this->db->query($query);
			if($this->db->affected_rows()){
				return TRUE;
			}
		}
		return FALSE;
	}
	function getStatusLikes($status_id){
		$query="select * from status_likes,users where status_likes.owner=users.id_users and status_likes.status_id=".$status_id." order by id_status_likes";
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function getStatusCommentUserData($status_id){
		$query="select * from status_post,users where status_post.owner=users.id_users and status_post.id_status_post=".$status_id;
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_wallphoto_albumid($user_id){
		$query="select * from album where owner=".$user_id." and album_type=1";
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		$album_data=$result[0];
		return $album_data->id_album;
	}
	function create_album($user_id,$data){
		$db_data = array('owner' => $user_id,'name' =>$data['album_name']);
		$sql = $this->db->insert_string('album', $db_data);
		if($this->db->query($sql)){
			$album_id=$this->db->insert_id();
			//post status
			$data['type']=4;
			$data['message']=$data['album_name'];
			$data['link']='';
			$data['video']='';
			$data['image']=$album_id;
			$album_status_result=$this->post_new_status($user_id,$data);
			$album_status_data=$album_status_result['status'];
			$album_status=$album_status_data[0];
			$query1=$this->db->update('album',array('album_status_id'=>$album_status->id_status_post),array('owner'=>$user_id,'id_album'=>$album_id));
			if($this->db->affected_rows()){
				return $album_id;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	function create_wallphoto_album($user_id){
		$db_data = array('owner' => $user_id,'name' =>'Wall Photos','album_type'=>'1');
		$sql = $this->db->insert_string('album', $db_data);
		if($this->db->query($sql)){
			$album_id=$this->db->insert_id();
			return $album_id;
		}else{
			return FALSE;
		}
	}
	function get_albums($user_id){
		$query="select *,(select max(id_photo) from photo where photo.album_id=album.id_album) as photo_cover from album where owner=".$user_id;
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_album_details($user_id,$album_id){
		$query="select *,(select max(id_photo) from photo where photo.album_id=album.id_album) as photo_cover from album where owner=".$user_id." and id_album=".$album_id;
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;	
		}else{
			return FALSE;
		}
		return FALSE;
	}
	function insert_snap($user_id,$album_id,$image_name){
		$db_data = array('owner' => $user_id,'album_id' =>$album_id,'description'=>$image_name);
		$sql = $this->db->insert_string('photo', $db_data);
		if($this->db->query($sql)){
			return $this->db->insert_id();
		}else{
			return FALSE;
		}
	}
	function get_snaps($user_id,$album_id){
		$query="select * from photo where owner=".$user_id." and album_id=".$album_id;
		$db_query = $this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result;	
		}else{
			return FALSE;
		}
		return FALSE;
	}
	function get_photo($user_id,$photo_id){
		$query="select *,photo.id_photo as pid,photo.album_id as aid,(select id_photo from photo where owner=".$user_id." and id_photo < pid and  photo.album_id=aid order by id_photo desc limit 0,1) as previous,(select id_photo from photo where owner=".$user_id." and id_photo > pid and  photo.album_id=aid order by id_photo asc limit 0,1) as next,(select id_status_post from status_post where (type=3 or type=5) and image=".$photo_id.") as photo_status_id from photo where owner=".$user_id." and id_photo=".$photo_id;$db_query = $this->db->query($query);
		$result=$db_query->result();
		if(sizeof($result)){
			return $result[0];	
		}else{
			return FALSE;
		}
		return FALSE;
	}
	function set_page_visited_log($data){
		$db_data = array('user_id' => $data['user_id'],'visited_user' =>$data['visited_user'],'url'=>$data['url']);
		$sql = $this->db->insert_string('page_visits', $db_data);
		if($this->db->query($sql)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function get_chatroom_msg($user_id,$last_chatmsg_id){
		if($last_chatmsg_id==0){
			$query="select * from chatroom,users where chatroom.sender_id=users.id_users order by chatroom_id desc limit 0,10";
		}else{
			$query="select * from chatroom,users where chatroom.sender_id=users.id_users  and chatroom_id > ".$last_chatmsg_id;
		}
		$db_query = $this->db->query($query);
		$result['chatroom_msg']=$db_query->result();
		return $result;
		
	}
	function post_newchatroom_msg($owner,$message){
		$data = array('sender_id' => $owner,'msg' => $message);
		$sql = $this->db->insert_string('chatroom', $data);
		if($this->db->query($sql)){
			$query="select * from chatroom,users where chatroom.sender_id=users.id_users  and chatroom.chatroom_id=".$this->db->insert_id();
			$db_query = $this->db->query($query);
			$result['new_chat_msg']=$db_query->result();
			return $result;
		}else{
			return FALSE;
		}
	}
	function update_chat_ping($user){
		$query1 = $this->db->update('users',array('ping'=>gmdate("Y-m-d H:i:s",time())),array('id_users'=>$user));
		if($this->db->affected_rows()){
			return TRUE;
		}
		return FALSE;
	}
	function get_online_friends($owner){
		$query="select * from users where id_users in (select friend from friend_connection where owner=".$owner.") and ping >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL 20 SECOND)";
		$db_query = $this->db->query($query);
		return $db_query->result();
	}
	function update_chatroom_ping($user){
		$query1 = $this->db->update('users',array('chatroom_ping'=>gmdate("Y-m-d H:i:s", time())),array('id_users'=>$user));
		if($this->db->affected_rows()){
			return TRUE;
		}
		return FALSE;
	}
	function get_chatroom_online_friends($owner){
		$query="select * from users where chatroom_ping >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL 20 SECOND)";
		$db_query = $this->db->query($query);
		return $db_query->result();
	}
	function get_pad_message($user_id,$receiver,$lastmsgid){
		if($lastmsgid==0){
			if($user_id==$receiver){
				$query = "select * from message_pad,users where  message_pad.sender=users.id_users and message_pad.receiver=".$receiver." order by id_message_pad desc limit 0,10";
			}else{
				$query = "select * from message_pad,users where  message_pad.sender=users.id_users and message_pad.receiver=".$receiver." and ( message_pad.privacy=0 or message_pad.sender= ".$user_id." ) order by id_message_pad desc limit 0,10";
			}
		}else{
			if($user_id==$receiver){
				$query = "select * from message_pad,users where message_pad.sender=users.id_users and message_pad.receiver=".$receiver." and  message_pad.id_message_pad < ".$lastmsgid."  order by id_message_pad desc limit 0,10";
			}else{
				$query = "select * from message_pad,users where message_pad.sender=users.id_users and message_pad.receiver=".$receiver." and  message_pad.id_message_pad < ".$lastmsgid." and ( message_pad.privacy=0 or message_pad.sender= ".$user_id." )  order by id_message_pad desc limit 0,10";
			}
		}
		$db_query = $this->db->query($query);
		return $db_query->result();		
	}
	function post_pad_message($sender,$data){
		$data = array('sender' =>$sender,'receiver' => $data['to'],'message'=>$data['message'],'privacy'=>$data['privacy'],'time'=>gmdate("Y-m-d H:i:s", time()));
		$sql = $this->db->insert_string('message_pad', $data);
		if($this->db->query($sql)){
			$query = "select * from message_pad, users where message_pad.sender=users.id_users and id_message_pad=".$this->db->insert_id();
			$db_query = $this->db->query($query);
			return $db_query->result();		
		}else{
			return FALSE;
		}		
	}
	function delete_pad_message($sessionuser,$msgid){	
		$str = 'DELETE FROM message_pad WHERE id_message_pad ='.$msgid.' and ( receiver='.$sessionuser.' or sender='.$sessionuser.' )';
		$query = $this->db->query($str);
		if($this->db->affected_rows()){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	function get_friends_details($user_id){
		$query="select * from friend_connection,users where friend_connection.friend=users.id_users and owner=".$user_id." order by rand() limit 0,10";
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_mutual_friends($user_id,$current_user){
		$query="select * from friend_connection,users where friend_connection.friend=users.id_users and owner=".$user_id." and friend_connection.friend in ( select friend from friend_connection where owner=".$current_user." ) order by rand() limit 0,10";
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_friendsuggession($user_id){
		$query="select * from friend_connection,users where friend_connection.friend=users.id_users and friend_connection.owner in (  select friend from friend_connection where owner=".$user_id."  ) and friend_connection.friend not in ( ".$user_id." ) and friend_connection.friend not in ( select friend from friend_connection where owner=".$user_id." ) and friend_connection.friend not in ( select receiver from friend_request where sender=".$user_id." ) and friend_connection.friend not in ( select sender from friend_request where receiver=".$user_id." ) group by id_users order by users.first_name";
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_friendsuggession_details($user_id){
		$query="select * from friend_connection,users where friend_connection.friend=users.id_users and friend_connection.owner in (  select friend from friend_connection where owner=".$user_id."  ) and friend_connection.friend not in ( ".$user_id." ) and friend_connection.friend not in ( select friend from friend_connection where owner=".$user_id." ) and friend_connection.friend not in ( select receiver from friend_request where sender=".$user_id." ) and friend_connection.friend not in ( select sender from friend_request where receiver=".$user_id." ) group by id_users order by rand() limit 0,2";
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_mutualfriends_details($user_id,$current_user){
		$query="select * from friend_connection,users where friend_connection.friend=users.id_users and owner=".$user_id." and friend_connection.friend in ( select friend from friend_connection where owner=".$current_user." ) order by rand() limit 0,10";
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_pad_to_suggessions($string){
		$query="select * from users where username like '".$string."%' or first_name like '".$string."%' or last_name like '".$string."%' order by first_name limit 0,10";
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_existing_contacts($user_id){
		$query="select * from users where email in ( select contact_email from contacts_grabbed where owner=".$user_id." ) and id_users not in ( ".$user_id." ) and id_users not in ( select friend from friend_connection where owner=".$user_id." )";
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function get_nonexisting_contacts($user_id){
		$query="select * from contacts_grabbed where owner=".$user_id." and contact_email not in ( select email from users ) ";
        $db_query = $this->db->query($query);
		$result=$db_query->result();
		return $result;
	}
	function check_album_existance($user_id,$album_id){
		$query = $this->db->query('select count(*) as total from album where id_album='.$album_id.' and owner='.$user_id);
		$user_info=$query->result();
		$row=$user_info[0];
		return $row->total;
	}
	function delete_snap($user_id,$photo_id){
		if($this->db->delete('photo',array('owner' => $user_id,'id_photo'=>$photo_id))){
			//delete status post
			return TRUE;
		}
		return FALSE;
	}
	function show_pokes($user_id){
		$query="SELECT * FROM poke INNER JOIN users ON poke.sender_id=users.id_users where poke.receiver_id=".$user_id." order by poke.id_pokes desc limit 0,10 ";
		$db_query = $this->db->query($query);
		return $db_query->result();
	}
	function add_poke($sender_id,$poke_receiver_id){
		$data = array('sender_id'=>$sender_id,'receiver_id'=>$poke_receiver_id);
		$sql = $this->db->insert_string('poke', $data);
		if($this->db->query($sql)){
			$query = "select * from poke where sender_id=".$sender_id." and id_pokes=".$this->db->insert_id();
			$db_query = $this->db->query($query);
			return $db_query->result();		
		}else{
			return FALSE;
		}
	}
	function delete_poke($id){
		if($this->db->delete('poke', array('id_pokes' =>$id))){
			return TRUE;
		}else {
			return FALSE;
		}
	}
	function get_contacts($user_id){
		$query=" select id_contacts,name,mobile,title,comp_name,email,1 as type from contacts where contact_owner_id=".$user_id." union select id_users,concat_ws(' ',first_name,last_name) as name,mobile,\"Position\" as position1,\"Cname\" as comp_name,email,0 as type from friend_connection,users where friend_connection.friend=users.id_users and owner=".$user_id." order by name";
		$db_query = $this->db->query($query);
		$result['contacts_result']=$db_query->result();
		return $result;
	}
	function add_contact($user_id,$name,$mob,$mail_id,$title,$cname){
		$data = array('contact_owner_id' =>$user_id,'name' =>$name,'mobile' =>$mob,'email'=>$mail_id,'title'=>$title,'comp_name'=>$cname);
		$sql = $this->db->insert_string('contacts', $data);
		if($this->db->query($sql)){
			$query = "select * from contacts where contact_owner_id=".$user_id." and id_contacts=".$this->db->insert_id();
			$db_query = $this->db->query($query);
			return $db_query->result();		
		}else{
			return FALSE;
		}	
	}
	function del_contact($contact_id){
		if($this->db->delete('contacts', array('id_contacts'=>$contact_id))){
			return TRUE;
		}else {
			return FALSE;
		}
	}
	function save_theme($user_id,$data){
		$query = $this->db->update('users',array('theme_type'=>1,'theme_bodybg'=>$data['bodybg'],'theme_contbg'=>$data['contbg'],'theme_font'=>$data['font'],'theme_link'=>$data['link'],'theme_highlight'=>$data['highlight']),array('id_users'=>$user_id));
		if($this->db->affected_rows()){
			return TRUE;
		}else{
			return FALSE;
		}		
	}
	function save_theme_background($user_id,$data){
		$query = $this->db->update('users',array('theme_imgtype'=>1,'theme_imgattachment'=>$data['backgroundAttachment'],'theme_imgrepeat'=>$data['backgroundRepeat'],'theme_imgposition'=>$data['backgroundPosition']),array('id_users'=>$user_id));
		if($this->db->affected_rows()){
			return TRUE;
		}else{
			return FALSE;
		}		
	}
	function remove_theme_background($user_id){
		$query = $this->db->update('users',array('theme_imgtype'=>0),array('id_users'=>$user_id));
		if($this->db->affected_rows()){
			return TRUE;
		}else{
			return FALSE;
		}		
	}
	function get_birthday_alerts($user_id){
		$query="SELECT * FROM friend_connection INNER JOIN users ON friend_connection.friend=users.id_users where friend_connection.owner=".$user_id." and users.birth_date LIKE '%-".date("m-d",mktime(0,0,0,date("m"),date("d"),date("y")))."'";
		$db_query = $this->db->query($query);
		return $db_query->result();
	}
}
?>
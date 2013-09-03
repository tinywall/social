<?php
session_start();
$_SESSION['chat_username']=$session_user->username;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php if(isset($page_title)){echo $page_title;}else{echo "TinyWall";}?></title>
<script language="javascript">
var session_user='<?php echo $session_user->username;?>';
var current_user='<?php echo $current_user->username;?>';
var base_url='<?php echo base_url();?>';
</script>
<script language="javascript" src="<?php echo base_url();?>js/jquery-1.4.4.min.js"></script>

 <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/themes/default.css" />
<link type="text/css" rel="stylesheet" media="all" href="<?php echo base_url();?>css/chat.css" />
<link type="text/css" rel="stylesheet" media="all" href="<?php echo base_url();?>css/screen.css" />
<!--[if lte IE 7]>
<link type="text/css" rel="stylesheet" media="all" href="<?php echo base_url();?>css/screen_ie.css" />
<![endif]-->
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/tipsy.css"/>
<script type="text/javascript" src="<?php echo base_url();?>js/jquery.tipsy.js"></script>
<script src="<?php echo base_url();?>js/jquery.elastic.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/jquery.toggleval.js"></script>
<script language="javascript">
 $(function() {
 	$('.tw-tooltip').tipsy({gravity:'s'});
  });
</script>
<script language="javascript">
function chatPing(){
	var output='';
	$.getJSON('<?php echo base_url();?>chat/ping/'+(new Date().getTime()),function(msg){
	     if(msg.response.success){
		 	if(msg.response.friend.length){
				for(var i=0;i<msg.response.friend.length;i++){
					output+="<a href='javascript:void(0)' onclick=javascript:chatWith('"+msg.response.friend[i].username+"')>"+msg.response.friend[i].fullname+'</a><br/>';
				}
			}else{
				output+='No Friends Online';
			}
		 }
		 $('.online-friends').html(output);
	});	
	setTimeout("chatPing()",10000);
}
$(document).ready(function(){
	chatPing();
	$.ajax({
	   type: "POST",dataType:'json',url:"<?php echo base_url();?>page_visited_log",
	   data: "session_user="+session_user+"&current_user="+current_user+"&url=<?php echo $this->uri->uri_string();?>",
	   success: function(msg){
	    	
	   }
	});
});
</script>
<script>
	function sendFriendRequest(username){
		var output;
		$.getJSON('<?php echo base_url();?>connection/sendFriendRequest/'+username, function(msg) {
			if(msg.response.success){
				$('.friendLink_'+username).html("<a onclick=\"cancelFriendRequest('"+username+"');\">Cancel Request</a>");	
				//$('.friendLink_'+username).hide(100);	
			}
			showAlertBox(msg.response.message);
		});
	}
	function removeFriend(username){
		var output;
		$.getJSON('<?php echo base_url();?>connection/unfriend/'+username, function(msg) {
			if(msg.response.success){
				$('.friendLink_'+username).html("<a onclick=\"sendFriendRequest('"+username+"');\">Add Friend</a>");	
			}
			showAlertBox(msg.response.message);
		});
	}
	function cancelFriendRequest(username){
		var output;
		$.getJSON('<?php echo base_url();?>connection/cancelRequest/'+username, function(msg) {
			if(msg.response.success){
				$('.friendLink_'+username).html("<a onclick=\"showAddfriendConfirmAlertBox('"+username+"');\">Add Friend</a>");	
			}
			showAlertBox(msg.response.message);
		});
	}
	function sendFollowRequest(username){
		var output;
		$.getJSON('<?php echo base_url();?>connection/sendFollowRequest/'+username, function(msg) {
			if(msg.response.success){
				$('.followLink_'+username).html("<a onclick=\"sendUnfollowRequest('"+username+"');\">Unfollow</a>");	
			}else{
				showAlertBox(msg.response.message);	
			}
		});
	}
	function sendUnfollowRequest(username){
		var output;
		$.getJSON('<?php echo base_url();?>connection/sendUnfollowRequest/'+username, function(msg) {
			if(msg.response.success){
				$('.followLink_'+username).html("<a onclick=\"sendFollowRequest('"+username+"');\">Follow</a>");	
			}else{
				showAlertBox(msg.response.message);	
			}
		});
	}
	function acceptFriendRequest(username){
		var output;
		$.getJSON('<?php echo base_url();?>connection/acceptFriendRequest/'+username, function(msg) {
			if(msg.response.success){
				$('.friendLink_'+username).hide(100);	
			}
			showAlertBox(msg.response.message);
		});
	}
	function denyFriendRequest(username){
		var output;
		$.getJSON('<?php echo base_url();?>connection/denyFriendRequest/'+username, function(msg) {
			if(msg.response.success){
				$('.friendLink_'+username).hide(100);	
			}
			showAlertBox(msg.response.message);
		});
	}
</script>
<script>
$(function(){
	$('#tw-top-navigation-outer ul li ul').hide();
	$('#tw-top-navigation-outer ul li').hover(
		function(){
			$('#tw-top-navigation-outer ul li').not($('ul', this)).stop();
			$('ul', this).show();
			$('ul', this).parent('li').children("a").children("div").css({"background":"#eeeeee","color":"#333333"});
		},
		function(){
			$('ul', this).hide();
			$('ul', this).parent('li').children("a").children("div").css({"background":"#3a7a9a","color":"#ffffff"});
		}
	);
});
</script>
<?php
if($current_user->theme_type==1){
?>	
<style>
#tw-content{
	background-color:#<?php echo $session_user->theme_bodybg;?>;
}
.tw-sidebar-outer-box-content,#tw-right-sidebar{
	background-color:#<?php echo $session_user->theme_contbg;?>;
}
#tw-content-outer{
	color:#<?php echo $session_user->theme_font;?>;
}
a{
	color:#<?php echo $session_user->theme_link;?>;
}
.tw-sidebar-outer-box-title,#tw-header{
	background-color:#<?php echo $session_user->theme_highlight;?>;
}
</style>
<?php
}
?>
<?php
if($current_user->theme_imgtype==1){
?>
<style>
#tw-content{
	background-image:url(<?php echo base_url()."images/theme_pictures/".$session_user->username.'_bg.jpg';?>);
	background-attachment:<?php echo $session_user->theme_imgattachment;?>;
	background-position:<?php echo $session_user->theme_imgposition;?>;
	background-repeat:<?php echo $session_user->theme_imgrepeat;?>;
	
}
</style>
<?php
}
?>
</head>
<body>
<div id="main_container">
<div id="tw-container">
<div id="tw-header">
	<div id="tw-header-outer">
		<div id="tw-logo-outer">
		<a href="<?php echo base_url();?>"><img id="tw-logo" src='<?php echo base_url();?>images/themes/default/logo.png'/></a>
		</div>
		<div id="tw-top-navigation-outer">
		<script>
		$(function(){
			$("a[rel='tab']").click(function(e){
				//e.preventDefault();
				pageurl = $(this).attr('href');
				$.ajax({url:pageurl+'?rel=tab',success: function(data){
					$('#tw-page-main-content').html(data);
				}});
				if(pageurl!=window.location){
					window.history.pushState({path:pageurl},'',pageurl);	
				}
				return false;  
			});
		});
		function ajaxLoadTab(pageurl){
			$.ajax({url:pageurl+'?rel=tab',success: function(data){
				$('#tw-page-main-content').html(data);
			}});
			if(pageurl!=window.location){
				window.history.pushState({path:pageurl},'',pageurl);
			}
			//for ie??..
		}
		/*function ajaxMainContent(pageurl){
			$.ajax({url:pageurl,success: function(data) {$('#tw-page-main-content').html(data);}});
			window.history.pushState({path:pageurl},pageurl,pageurl);
			//window.location = "b.html";
		}*/
		$(window).bind('popstate', function() {
		  window.location=location.pathname;
		  /*
		  $.ajax({url:location.pathname+'?rel=tab',success: function(data){
				$('#content').html(data);
			}});
		*/
		});




		/*$('#tw-top-menu-tabs a').click(function() {
		  history.pushState({ path: this.path }, '', this.href);
		  $.get(this.href, function(data) {
		    $('#tw-page-main-content').slideTo(data);
		  })
		  return false
		});*/


		</script>
		<!--<script type="text/javascript" src="<?php echo base_url();?>js/jquery.address-1.4.min.js"></script>-->
		
			<ul id="tw-top-menu-tabs">
				<li><a href="<?php echo base_url();?>dashboard"><div class="tw-top-menu-item">My Dashboard</div></a>
					<ul>
						<!--<li><a onclick="ajaxMainContent('<?php echo base_url();?>status');"><div class="tw-top-menu-subitem">Feeds</div></a></li>-->
						<li><a href="<?php echo base_url();?>status"><div class="tw-top-menu-subitem">Feeds</div></a></li>
						<li><a href="<?php echo base_url();?>pad"><div class="tw-top-menu-subitem">Message Pad</div></a></li>
						<li><a rel="tab" href="<?php echo base_url();?>profile"><div class="tw-top-menu-subitem">Profile</div></a></li>
						<!--<li><a href="<?php echo base_url();?>profile"><div class="tw-top-menu-subitem">Profile</div></a></li>-->
						<li><a rel="tab" href="<?php echo base_url();?>friends"><div class="tw-top-menu-subitem">Friends</div></a></li>
						<li><a href="<?php echo base_url();?>album"><div class="tw-top-menu-subitem">Snaps</div></a></li>
						<li><a href="<?php echo base_url();?>world"><div class="tw-top-menu-subitem">World</div></a></li>
						<li><a href="<?php echo base_url();?>chatroom"><div class="tw-top-menu-subitem">Chatroom</div></a></li>
						
						
					</ul>
				</li>
				<li><a href="<?php echo base_url().$current_user->username;?>"><div class="tw-top-menu-item"><?php echo $current_user->first_name.' '.$current_user->last_name;?></div></a>
					<ul>
						<li><a href="<?php echo base_url().$current_user->username;?>/home"><div class="tw-top-menu-subitem">Home</div></a></li>
						<li><a href="<?php echo base_url().$current_user->username;?>/status"><div class="tw-top-menu-subitem">Status</div></a></li>
						<li><a href="<?php echo base_url().$current_user->username;?>/profile"><div class="tw-top-menu-subitem">Profile</div></a></li>
						<li><a href="<?php echo base_url().$current_user->username;?>/pad"><div class="tw-top-menu-subitem">Message Pad</div></a></li>
						<li><a href="<?php echo base_url().$current_user->username;?>/friends"><div class="tw-top-menu-subitem">Friends</div></a></li>
						<li><a href="<?php echo base_url().$current_user->username;?>/followers"><div class="tw-top-menu-subitem">Followers</div></a></li>
						<li><a href="<?php echo base_url().$current_user->username;?>/followings"><div class="tw-top-menu-subitem">Following</div></a></li>
						<li><a href="<?php echo base_url().$current_user->username;?>/album"><div class="tw-top-menu-subitem">Snaps</div></a></li>
						<li><a href="<?php echo base_url().$current_user->username;?>/world"><div class="tw-top-menu-subitem">World</div></a></li>
						<?php 
						if($user_relation){
						?>
						<li><a href="javascript:void(0)" onclick="javascript:chatWith('<?php echo $current_user->username;?>')">Chat</a></li>
						<?php
						}
						?>
					</ul>
				</li>	
				<li><a href="#"><div class="tw-top-menu-item">My account</div></a>
					<ul>
						<li><a href="<?php echo base_url();?>profile/edit"><div class="tw-top-menu-subitem">Edit Profile</div></a></li>
						<li><a href="<?php echo base_url();?>poke"><div class="tw-top-menu-subitem">Pokes</div></a></li>
						<li><a href="<?php echo base_url();?>contacts"><div class="tw-top-menu-subitem">Contacts</div></a></li>
						<li><a href="<?php echo base_url();?>profile/uploadpicture"><div class="tw-top-menu-subitem">Profile Photo</div></a></li>
						<li><a href="<?php echo base_url();?>setting"><div class="tw-top-menu-subitem">Settings</div></a></li>
						<li><a href="<?php echo base_url();?>connection/import"><div class="tw-top-menu-subitem">Invite Friends</div></a></li>
						<li><a href="<?php echo base_url();?>logout"><div class="tw-top-menu-subitem">Logout</div></a></li>
					</ul>
				</li>
			</ul>
			<div id="tw-top-search-outer">
			<form  action='<?php echo base_url();?>search/' method='get' class="tw-top-search-form">    
				<input type="text"  name="name" value='search' id="topSearchTextBox" class="tw-top-search-form-text" /> 
			    <input type="submit" name="search" value="Search" class="tw-top-search-form-button"/> 
			</form>
			<script>
			$(document).ready(function(){			
				$("#topSearchTextBox").toggleVal();
			});	
			</script>
			</div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="tw-content">
<div id="tw-content-outer">
<div id="tw-alert-box-outer">
	<div id="alert-title">Alert !.!.</div>
	<a id="alert-close-button" class="AlertCloseIcon" onclick="closeAlertBox()"></a>
	<div class="clear"></div>
	<div id="tw-alert-box"></div>
</div>
<div id="tw-left-sidebar">
	<div class="tw-sidebar-outer-box">
	<div class="tw-sidebar-outer-box-title"><?php echo $current_user->first_name.' '.$current_user->last_name;?></div>
	<div class="tw-sidebar-outer-box-content">
	<div><a><img src='<?php echo base_url().'avatar/full/'.$current_user->username.'/'.$current_user->first_name.' '.$current_user->last_name;?>.jpg' /></a></div><br/>
	<div class="tw-sidebar-profile-left">Age</div><div class="tw-sidebar-profile-right">: <?php echo $current_user->age;?></div>
	<div class="clear"></div>
	<div class="tw-sidebar-profile-left">Sex</div><div class="tw-sidebar-profile-right">: <?php if($current_user->gender){echo 'Male';}else{echo 'Female';}?></div>
	<div class="clear"></div>
	<div class="tw-sidebar-profile-left">Location</div><div class="tw-sidebar-profile-right">: <?php echo $current_user->city.', '.$current_user->country;?></div>
	<div class="clear"></div>
	</div>
	</div>
	<?php 
	if($user_relation){
	?>
	<div class="tw-sidebar-outer-box">
	<div class="tw-sidebar-outer-box-content">
		<?php
		if($current_user->friend_relation){
			echo "<div class='tw-left-sidebar-friendLink friendLink_".$current_user->username."'><a onclick=\"showUnfriendConfirmAlertBox('".$current_user->username."');\">Unfriend</a></div>";
		}elseif($current_user->request_send_relation){
			echo "<div class='tw-left-sidebar-friendLink friendLink_".$current_user->username."'><a onclick=\"cancelFriendRequest('".$current_user->username."');\">Cancel Request</a></div>";
		}elseif($current_user->request_receive_relation){
			echo "<div class='tw-left-sidebar-friendLink friendLink_".$current_user->username."'><a onclick=\"acceptFriendRequest('".$current_user->username."')\">Accept</a> <a onclick=\"denyFriendRequest('".$current_user->username."')\">Deny</a></div>";
		}else{
			echo "<div class='tw-left-sidebar-friendLink friendLink_".$current_user->username."'><a onclick=sendFriendRequest('".$current_user->username."')>Add Friend</a></div>";
		}
		if($current_user->follow_relation){
			echo "<div class='tw-left-sidebar-followLink followLink_".$current_user->username."'><a onclick=\"sendUnfollowRequest('".$current_user->username."');\">Unfollow</a></div>";
		}else{
			echo "<div class='tw-left-sidebar-followLink followLink_".$current_user->username."'><a onclick=\"sendFollowRequest('".$current_user->username."');\">Follow</a></div>";
		}
		?>
		<div class="clear"></div>
		<div id="tw-left-sidebar-menu">
		<ul>
			<li><a href="<?php echo base_url().$current_user->username;?>/home"><div class="tw-left-sidebar-menu-item">Home</div></a></li>
			<li><a href="<?php echo base_url().$current_user->username;?>/status"><div class="tw-left-sidebar-menu-item">Status</div></a></li>
			<li><a href="<?php echo base_url().$current_user->username;?>/profile"><div class="tw-left-sidebar-menu-item">Profile</div></a></li>
			<li><a href="<?php echo base_url().$current_user->username;?>/pad"><div class="tw-left-sidebar-menu-item">Message Pad</div></a></li>
			<li><a href="<?php echo base_url().$current_user->username;?>/friends"><div class="tw-left-sidebar-menu-item">Friends</div></a></li>
			<li><a href="<?php echo base_url().$current_user->username;?>/followers"><div class="tw-left-sidebar-menu-item">Followers</div></a></li>
			<li><a href="<?php echo base_url().$current_user->username;?>/followings"><div class="tw-left-sidebar-menu-item">Following</div></a></li>
			<li><a href="<?php echo base_url().$current_user->username;?>/album"><div class="tw-left-sidebar-menu-item">Snaps</div></a></li>
			<li><a href="<?php echo base_url().$current_user->username;?>/world"><div class="tw-left-sidebar-menu-item">World</div></a></li>
		</ul>
		</div>
	</div>
	</div>
	<?php
	}
	?>
	
	<div class="tw-sidebar-outer-box">
	<div class="tw-sidebar-outer-box-title">Online Friends</div>
	<div class="tw-sidebar-outer-box-content online-friends">
		loading...
	</div>
	</div>
	
	
</div><!-- tw-left-sidebar -->
<div id="tw-right-sidebar">
<?php 
if($user_relation){
?>
<div id="tw-right-sidebar-menu">
	<ul>
		<li><a href="<?php echo base_url().$current_user->username;?>/home"><div class="tw-right-sidebar-menu-item">Home</div></a></li>
		<li><a href="<?php echo base_url().$current_user->username;?>/status"><div class="tw-right-sidebar-menu-item">Status</div></a></li>
		<li><a href="<?php echo base_url().$current_user->username;?>/profile"><div class="tw-right-sidebar-menu-item">Profile</div></a></li>
		<li><a href="<?php echo base_url().$current_user->username;?>/pad"><div class="tw-right-sidebar-menu-item">Message Pad</div></a></li>
		<li><a href="<?php echo base_url().$current_user->username;?>/friends"><div class="tw-right-sidebar-menu-item">Friends</div></a></li>
		<li><a href="<?php echo base_url().$current_user->username;?>/followers"><div class="tw-right-sidebar-menu-item">Followers</div></a></li>
		<li><a href="<?php echo base_url().$current_user->username;?>/followings"><div class="tw-right-sidebar-menu-item">Following</div></a></li>
		<li><a href="<?php echo base_url().$current_user->username;?>/album"><div class="tw-right-sidebar-menu-item">Snaps</div></a></li>
		<li><a href="<?php echo base_url().$current_user->username;?>/world"><div class="tw-right-sidebar-menu-item">World</div></a></li>
	</ul>
</div>
<?php 
}
?>
<?php if(isset($current_page)&&($current_page=='photo'||$current_page=='croppicture'||$current_page=='chatroom')){ ?>
<div id="tw-right-sidebar-left" style='width:700px;'>
<?php }else{ ?>
<div id="tw-right-sidebar-right">
	<?php
	if(($current_page=='dashboard')&&sizeof($birthdayalert_details)){
	?>
	<div class="tw-rightsidebar-outer-box">
	<div class="tw-rightsidebar-outer-box-title">Birthday Today</div>
	<div class="tw-rightsidebar-outer-box-content">
		<?php
		foreach($birthdayalert_details as $row){
		?>
			<div class="tw-rightsidebar-mini-photo tw-tooltip" title="<?php echo $row->first_name.' '.$row->last_name;?>"><a href="<?php echo base_url().$row->username;?>"><img src='<?php echo base_url().'avatar/thumb/'.$row->username.'/'.$row->first_name.' '.$row->last_name;?>.jpg' /></a></div>
		<?php
		}
		?>
		<div class="clear"></div>
	</div>
	</div>
	<?php
	}
	?>
	
	<?php
	if(($current_page=='home')&&sizeof($friends_details)){
	?>
	<div class="tw-rightsidebar-outer-box">
	<div class="tw-rightsidebar-outer-box-title">Friends</div>
	<div class="tw-rightsidebar-outer-box-content">
		<?php
		foreach($friends_details as $row){
		?>
			<div class="tw-rightsidebar-mini-photo tw-tooltip" title="<?php echo $row->first_name.' '.$row->last_name;?>"><a href="<?php echo base_url().$row->username;?>"><img src='<?php echo base_url().'avatar/thumb/'.$row->username.'/'.$row->first_name.' '.$row->last_name;?>.jpg' /></a></div>
		<?php
		}
		?>
		<div class="clear"></div>
		<a href="<?php echo base_url().$current_user->username?>/friends" class='tw-rightsidebar-view-all'>View All</a>
		<div class="clear"></div>
	</div>
	</div>
	<?php
	}
	?>
	
	<?php
	if(!$user_relation&&($current_page=='home')&&sizeof($friendsuggession_details)){
	?>
	<div class="tw-rightsidebar-outer-box">
	<div class="tw-rightsidebar-outer-box-title">You may know???...</div>
	<div class="tw-rightsidebar-outer-box-content">
		<?php
		foreach($friendsuggession_details as $row){
		?>
			<div class="tw-friendsuggest-outer">
				<div class="tw-friendsuggest-left"><a href="<?php echo base_url().$row->username;?>"><img src='<?php echo base_url().'avatar/thumb/'.$row->username;?>'/></a></div>
				<div class="tw-friendsuggest-right">
					<div class="tw-friendsuggest-name"><a href="<?php echo base_url().$row->username;?>"><?php echo $row->first_name.' '.$row->last_name;?></a></div>
					<div class="tw-friendsuggest-info">
						<?php echo $row->city.', '.$row->country;?>
					</div>
					<div class="tw-friendsuggest-action"><a onclick="showAddfriendConfirmAlertBox('<?php echo $row->username;?>');">Add Friend</a></div>
				</div>
				<div class="clear"></div>
			</div>
		<?php
		}
		?>
		<div class="clear"></div>
		<a href="<?php echo base_url().$current_user->username?>/friendsuggession" class='tw-rightsidebar-view-all'>View All</a>
		<div class="clear"></div>
	</div>
	</div>
	<?php
	}
	?>
	
	<?php
	if($user_relation&&($current_page=='home')&&sizeof($mutualfriends_details)){
	?>
	<div class="tw-rightsidebar-outer-box">
	<div class="tw-rightsidebar-outer-box-title">Mutual Friends</div>
	<div class="tw-rightsidebar-outer-box-content">
		<?php
		foreach($mutualfriends_details as $row){
		?>
			<div class="tw-rightsidebar-mini-photo tw-tooltip" title="<?php echo $row->first_name.' '.$row->last_name;?>"><a href="<?php echo base_url().$row->username;?>"><img src='<?php echo base_url().'avatar/thumb/'.$row->username.'/'.$row->first_name.' '.$row->last_name;?>.jpg' /></a></div>
		<?php
		}
		?>
		<div class="clear"></div>
		<a href="<?php echo base_url().$current_user->username?>/mutualfriends" class='tw-rightsidebar-view-all'>View All</a>
		<div class="clear"></div>
	</div>
	</div>
	<?php
	}
	?>
	<?php
	if($current_page=='home'){
	?>
	<!-- poke-->
		<div class="mr-5">
		<div class="span-5b leftBavkInner borderf mt-5">
		    <h3 class="sub-heading mr-5">Pokes </h3>
				<span class="fl"></span>
	<script language="javascript">
	function poke(user){
		$.getJSON(base_url+user+'/poke/add_poke',function(msg){
				$('#add_poke').html('Poked');
		});	
	}
	function pokeback(user,id){
		$.getJSON(base_url+user+'/poke/add_poke',function(msg){
				$('#poke_'+id).html('Poked Back').delay(1000).fadeOut('slow');
				delete_poke(id);
		});	
	}
	
	</script>

	<style>
		.rightSinglePoke{
			height:20px;
		}
	</style>
	<?php
	if(!$user_relation){
	?>
	<div class="fri_rcon ml-5" id="pokes">Loading...</div>	
	<script language="javascript">
	$(document).ready(function(){
		$.getJSON('<?php echo base_url();?>poke/get_pokes/'+(new Date().getTime()),function(msg){
			var output='';
			 	if(msg.poke.length!=0){
				output+='<div  style="height:20px;overflow-y:hidden">';
					for(var i=0;i<msg.poke.length;i++){
						output+='<div class="rightSinglePoke" id="poke_'+msg.poke[i].id+'"><b>'+msg.poke[i].from+'</b><span onclick=\"pokeback(\''+msg.poke[i].from+'\','+msg.poke[i].id+");\" >Poke Back</span>"+'<span onclick="delete_poke('+msg.poke[i].id+')"> <u>close</u></span></div>';
					}
					output+='</div>';
				}else{
					output='No Pokes';
				}
				$('#pokes').html(output);
		});
	});
	
	function delete_poke(id){
		$.ajax({
	   		type: "POST",dataType:'json',url:"<?php echo base_url();?>poke/remove_poke/"+id,
	   	    success: function(msg){
			$('#poke_'+id).empty();
			}
	 });
	}
	</script>
	<?php
	}
		if($user_relation){
	?>
	<div class="fri_rcon ml-5" id="add_poke" onclick="poke('<?php echo $current_user->username ?>')">Poke</div>	
	
	<?php
	}
	?>
		</div>
		</div>
	<!--- poke end-->	
	<?php
	}
	?>
</div>
<div id="tw-right-sidebar-left">
<?php } ?>
<?php
if($user_relation&&(!$current_user->friend_relation)&&$current_user->privacy){
	//echo "Only visible to friends...";
}//else{
?>
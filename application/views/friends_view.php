<h1>Friends of <?php echo $current_user->first_name." ".$current_user->last_name;?></h1>
<?php
if(!$user_relation){
	if(sizeof($friend_request)){
		foreach($friend_request as $row){
			?>
			<div class="tw-miniprof-outer">
				<div class="tw-miniprof-left"><a href="<?php echo base_url().$row->username;?>"><img src='<?php echo base_url().'avatar/thumb/'.$row->username;?>'/></a></div>
				<div class="tw-miniprof-right">
					<div class="tw-miniprof-name"><a href="<?php echo base_url().$row->username;?>"><?php echo $row->first_name.' '.$row->last_name;?></a></div>
					<div class="tw-miniprof-info">
						<?php echo date('Y')-date('Y',strtotime($row->birth_date));?> / 
						<?php if($row->gender){echo 'M';}else{echo 'F';}?> / 
						<?php echo $row->city.', '.$row->country;?>
					</div>
					<div class="tw-miniprof-action"><a onclick="acceptFriendRequest('<?php echo $row->username;?>')">Accept</a><a onclick="denyFriendRequest('<?php echo $row->username;?>')">Deny</a></div>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		}
		echo "<div class='clear'></div>";
	}	
}
?>

<?php
	echo sizeof($friends)." Friends:</br/>";
	if(sizeof($friends)){
		foreach($friends as $row){
			?>
			<div class="tw-miniprof-outer">
				<div class="tw-miniprof-left"><a href="<?php echo base_url().$row->username;?>"><img src='<?php echo base_url().'avatar/thumb/'.$row->username;?>'/></a></div>
				<div class="tw-miniprof-right">
					<div class="tw-miniprof-name"><a href="<?php echo base_url().$row->username;?>"><?php echo $row->first_name.' '.$row->last_name;?></a></div>
					<div class="tw-miniprof-info">
						<?php echo date('Y')-date('Y',strtotime($row->birth_date));?> / 
						<?php if($row->gender){echo 'M';}else{echo 'F';}?> / 
						<?php echo $row->city.', '.$row->country;?>
					</div>
					<div class="tw-miniprof-action">
						<?php if($user_relation){?>
							<a onclick="showAddfriendConfirmAlertBox('<?php echo $row->username;?>');">Add Friend</a>
						<?php }else{?>
							<a onclick="showUnfriendConfirmAlertBox('<?php echo $row->username;?>');">Unfriend</a>
						<?php }?>
						<a>Send Message</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		}
		echo "<div class='clear'></div>";
	}
?>
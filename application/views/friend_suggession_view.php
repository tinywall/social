<?php
	if(sizeof($friend_suggession)){
		foreach($friend_suggession as $row){
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
					<div class="tw-miniprof-action"><a onclick="showAddfriendConfirmAlertBox('<?php echo $row->username;?>');">Add Friend</a><a>Send Message</a></div>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		}
		echo "<div class='clear'></div>";
	}
?>
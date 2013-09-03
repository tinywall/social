<style>
.tw-profile-title{
	background:#eeeeee;clear:both;padding:3px;font-weight:bold;
}
.tw-profile-left{
	float:left;
	width:100px;margin-top:5px;margin-left:3px;
}
.tw-profile-right{
	float:right;
	width:400px;margin-top:5px;
}
</style>
<script>
//alert('aaa');
</script>
<div class="tw-profile-title">Profile of <?php echo $current_user->first_name." ".$current_user->last_name;?></div>
<div class="tw-profile-left">First Name</div><div  class="tw-profile-right"><?php echo $current_user->first_name;?></div><div class="clear"></div>
<div class="tw-profile-left">Last Name</div><div  class="tw-profile-right"><?php echo $current_user->last_name;?></div><div class="clear"></div>
<div class="tw-profile-left">Gender</div><div  class="tw-profile-right"><?php if($current_user->gender==1){echo "Male";}else{echo "Female";};?></div><div class="clear"></div>
<div class="tw-profile-left">Date Of Birth</div><div  class="tw-profile-right"><?php echo $current_user->birth_date;?></div><div class="clear"></div>
<div class="tw-profile-left">About Me</div><div  class="tw-profile-right"><?php echo $current_user->about;?></div><div class="clear"></div>
<div class="tw-profile-left">Country</div><div  class="tw-profile-right"><?php echo $current_user->country;?></div><div class="clear"></div>
<div class="tw-profile-left">City</div><div  class="tw-profile-right"><?php echo $current_user->city;?></div><div class="clear"></div>

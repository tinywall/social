<?php
	echo $this->session->flashdata('alert');
?>
<h1>Edit Profile of <?php echo $session_user->first_name." ".$session_user->last_name;?></h1>
<form action='<?php echo base_url();?>profile/update' method="post">
First Name :<input type="text" name="first_name" value="<?php echo $session_user->first_name;?>"/><br/>
Last Name :<input type="text" name="last_name" value="<?php echo $session_user->last_name;?>"/><br/>
Gender :<input name="gender" value="1" type="radio" <?php if($session_user->gender==1){echo "checked";}?>>Male
<input name="gender" value="0" type="radio" <?php if($session_user->gender==0){echo "checked";}?>>Female<br/>
DOB :<input type="text" name="birth_date" value="<?php echo $session_user->birth_date;?>"<br/><br/>
About Me :<textarea name=about><?php echo $session_user->about;?></textarea><br/>
Location :<select name="country">
<option value="india">India</option>
<option value="australia">Australia</option>
<option value="usa">USA</option>			
		</select><br/>
City :<input type="text" name="city" value="<?php echo $session_user->city;?>"<br/>
							<br/>
<input type="submit" name="updateprofile" value="update"/>
</form>
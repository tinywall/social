<?php
	echo $this->session->flashdata('alert');
?>
<script>
function chechUserAvailability(username){
	$.getJSON('<?php echo base_url();?>/landing/chechUserAvailability/'+username,function(msg){
		if(username==$('#username').val()){
			if(msg.response.availability){
			 	$('#username-availability').html('available');
			 }else{
			 	$('#username-availability').html('not available');
			 }	
		}
});
}
</script>
<h2>Sign up with TinyWall</h2>
<form action='<?php echo base_url();?>landing/fbregister' method="post">
Firstname:<br/>
<input type='text' name='first_name' value='<?php echo $fbfirst_name;?>'/><br/>
Last name:<br/>
<input type='text' name='last_name' value='<?php echo $fblast_name;?>'/><br/>
Email:<br/>
<input type='text' name='email' disabled="disabled" value='<?php echo $fbemail;?>'/><br/>
Username:<br/>
<input type='text' name='username' id="username" onkeyup="chechUserAvailability($(this).val());"/><br/>
<span id="username-availability"></span>
Gender:<br/>
<input type='radio' name='gender' <?php if($fbgender=='male'){ echo "checked";} ?>  />Male 
<input type='radio' name='gender' <?php if($fbgender=='female'){ echo "checked";} ?> />Female<br/>
DOB:<br/>
<input type='text' name='birth_date' value='<?php echo $fbbirthday;?>'/><br/>
Mobile:<br/>
<input type='text' name='mobile'/><br/>
Password:<br/>
<input type='password' name='password'/><br/>
<input type="submit" name="fbregister"/>
</form>
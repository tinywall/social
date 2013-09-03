<?php
	echo $this->session->flashdata('alert');
?>
<script>
var registerAvailability=0;
function chechUserAvailability(username){
	$.getJSON('<?php echo base_url();?>/landing/chechUserAvailability/'+username,function(msg){
		if(username==$('#username').val()){
			if(msg.response.availability){
			 	$('#username-availability').html('available');
				registerAvailability=1;
			 }else{
			 	$('#username-availability').html('not available');
			 }	
		}
});
}
</script>
<h2>Sign up with TinyWall</h2>
<form action='<?php echo base_url();?>landing/oiregister' method="post">
Firstname:<br/>
<input type='text' name='first_name' value='<?php echo $this->session->userdata['twittername'];?>'/><br/>
Last name:<br/>
<input type='text' name='last_name' value=''/><br/>
Email:<br/>
<input type='text' name='email'/><br/>
Username:<br/>
<input type='text' name='username' id="username" onkeyup="chechUserAvailability($(this).val());" value="<?php echo $this->session->userdata['twitterscreenname'];?>"/><br/>
<span id="username-availability"></span>
Gender:<br/>
<input type='radio' name='gender'   />Male 
<input type='radio' name='gender'  />Female<br/>
DOB:<br/>
<input type='text' name='birth_date' value=''/><br/>
Mobile:<br/>
<input type='text' name='mobile'/><br/>
Password:<br/>
<input type='password' name='password'/><br/>
<input type="submit" name="twitterregister"/>
<script>
chechUserAvailability($('#username').val());
</script>
</form>
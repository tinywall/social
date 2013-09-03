<div id="tw-public-content-title">Sign up with TinyWall</div>
<div id="tw-public-content">
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

<script type="text/javascript">
    $(function() { 
        $("#register-form").validity(function() {
            $("#first_name").require();
			$("#email").require().match("email");
			$("#username").require().minLength(3).maxLength(15);
			$("#password").require().minLength(6).maxLength(15);
			$("input[type='password']").equal("Passwords do not match.");
			$("#mobile").match("integer").maxLength(10);
        });
    });
</script>

<form action='<?php echo base_url();?>register' method="post" id="register-form">
<table>
<tr>
<td class="landing-form-label">Firstname:</td>
<td><input type='text' name='first_name' id='first_name' class="landing-form-text"/></td>
</tr>
<tr>
<td class="landing-form-label">Last name:</td>
<td><input type='text' name='last_name' id='last_name' class="landing-form-text"/></td>
</tr>
<tr>
<td class="landing-form-label">Email:</td>
<td><input type='text' name='email' id='email' class="landing-form-text"/></td>
</tr>
<tr>
<td class="landing-form-label">Username:</td>
<td><input type='text' name='username' id="username" onkeyup="if($(this).val().length>=3&&$(this).val().length<=15){chechUserAvailability($(this).val());}else{$('#username-availability').empty();}" class="landing-form-text"/><br/>
<span id="username-availability"></span></td>
</tr>
<tr>
<td class="landing-form-label">Gender:</td>
<td style="font-size:16px;"><input type='radio' name='gender' value="1" checked="checked"/> Male <input type='radio' name='gender' value="0"/> Female </td>
</tr>
<tr>
<td class="landing-form-label">Date of Birth:</td>
<td>
	<select name="dob_date" id="dob_date" class="landing-form-text" style="width:auto;">
	<script>
	for(var i=1;i<=31;i++){
		document.write("<option value='"+i+"'>"+i+"</option>");
	}
	</script>
	</select>
	<select name="dob_month" id="dob_month" class="landing-form-text" style="width:auto;">
	<script>
	for(var i=0;i<12;i++){
		document.write("<option value='"+(i+1)+"'>"+month[i]+"</option>");
	}
	</script>
	</select>
	<select name="dob_year" id="dob_year" class="landing-form-text" style="width:auto;">
	<script>
	for(var i=1950;i<2011;i++){
		document.write("<option value='"+i+"'>"+i+"</option>");
	}
	</script>
	</select>
</td>
</tr>
<tr>
<td class="landing-form-label">Mobile:</td>
<td><input type='text' name='mobile' id='mobile' class="landing-form-text"/></td>
</tr>
<tr>
<td class="landing-form-label">Password:</td>
<td><input type='password' name='password' id='password' class="landing-form-text"/></td>
</tr>
<tr>
<td class="landing-form-label">Confirm Password:</td>
<td><input type='password' name='confirm-password' id='confirm-password' class="landing-form-text"/></td>
</tr>
<tr>
<td>
</td>
<td align="left">
<input type="submit" name="register" class="landing-form-button" value='Register'/>
</td>
</tr>
</table>
</form>
</div>
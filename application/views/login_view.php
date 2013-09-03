<div id="tw-public-content-title">Login to TinyWall</div>
<div id="tw-public-content">
<?php if($this->session->flashdata('alert')){echo "<div class='tw-alert-message'>".$this->session->flashdata('alert')."</div>";}?>
<script type="text/javascript">
    $(function() { 
        $("#login-form").validity(function() {
            $("#username").require();
			$("#password").require();
        });
    });
</script>
<form action='<?php echo base_url();?>authenticate' method="post"  class="landing-form" id="login-form">
<table>
<tr>
<td class="landing-form-label">Username</td>
<td><input type='text' name='username' id='username' class="landing-form-text"/> </td>
</tr>
<tr>
<td class="landing-form-label">Password</td> 
<td><input type='password' name='password' id='password' class="landing-form-text"/></td>
</tr>
<tr>
<td>
</td>
<td align="left">
<input type="hidden" name="redirect" value="<?php echo $this->session->flashdata('redirect');?>"/>
<input type="submit" name="login" value="Login" class="landing-form-button"/>
</td>
</tr>
</table>
</form>
</div>

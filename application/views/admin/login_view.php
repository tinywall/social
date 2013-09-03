Admin Login 
<form action="<?php echo base_url();?>admin/authenticate" method='post'>
<input type="text" name="username"/>
<input type="password" name="password"/>
<input type="submit" name="login" value="Login"/>
</form>
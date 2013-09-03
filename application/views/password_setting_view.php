

<h1>Profile Settings of <?php echo $current_user->first_name." ".$current_user->last_name;?></h1>
<form action="<?php echo base_url();?>setting/password" method=post>
Old Password :<input type=text name=oldpass><br><br>
New Password :<input type=password name=newpass><br><br>

<input type=submit name=Update>
</form>


<h1>Profile Settings of <?php echo $current_user->first_name." ".$current_user->last_name;?></h1>

<form action="<?php echo base_url();?>setting/account" method=post>

Mobile No. :<input type=text name=mobile><br><br>
E-Mail ID :<input type=text name=email><br><br>
<center>
<input type=submit name=Update></center>
</form>


<h1>Profile Settings of <?php echo $current_user->first_name." ".$current_user->last_name;?></h1>
<form action="<?php echo base_url();?>setting/privacy" method=post>
Public :<input type="radio" name=visiblity <?php if($session_user->privacy==0){echo "checked";}?> value="0"><br><br>
Friends: <input type="radio" name=visiblity <?php if($session_user->privacy==1){echo "checked";}?> value="1"><br><br>
<center><input type=submit name=Update>
</form>
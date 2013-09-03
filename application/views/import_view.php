<?php 
$_SESSION['base_url']=base_url();
$_SESSION['import_username']=$session_user->username;
echo $this->session->flashdata('alert');
?>
<form action='' method='post'>
Email : <input type='text' name='invite_email' /><br/>
Message : <textarea name='invite_message' ></textarea><br/>
<input type='submit' name='sendInviteEmail' value='Invite' />
</form>
<br/>
<iframe src ="<?php echo base_url().'public/import.php';?>" width="100%" height="100%" border="0" style="border:none;">
  <p>Your browser does not support iframes.</p>
</iframe>
<h3>Invite friends</h3>
<form method="post" action="">
<br/><input type="submit" name="send_invite" value="Send Invite" /><br/>
<?php
foreach($nonexting_contacts as $row){
	echo "<input type='checkbox' name='contact_email[]' value='".$row->contact_email."' /><img src='http://www.gravatar.com/avatar/".md5($row->contact_email)."' width='25' height='25' />".$row->contact_name.' : '.$row->contact_email."<br/><hr/><br/>";
}
?>
<input type="submit" name="send_invite" value="Send Invite" />
</form>
<a href='<?php echo base_url();?>connection/import'>Next</a>
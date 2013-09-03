<?php
	echo $this->session->flashdata('alert');
?>
<form action="" method="POST">
Resend activation email for nonactive users? <input type="submit" name="resendActivationMail" value="Send" />
</form>
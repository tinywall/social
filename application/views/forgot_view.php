<?php
	echo $this->session->flashdata('alert');
?>
<form action="" method="post">
Email : <input type='text' name="email" />
<input type="submit" name="forgot" value="Reset" />
</form>
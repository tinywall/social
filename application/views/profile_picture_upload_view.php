<?php
	echo $this->session->flashdata('alert');
?>
<a href="http://gravatar.com/">Upload using Gavatar</a>
<form method="post" action="<?php echo base_url();?>profile/uploadpicture" enctype="multipart/form-data" />
<input type="file" name="userfile" size="20" />
<input type="submit" name="uploadpicture" value="upload"/>
</form>
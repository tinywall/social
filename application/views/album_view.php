<?php
	echo $this->session->flashdata('alert');
?>
<?php
if(!$user_relation){
?>
<form method="post" action="<?php echo base_url().$session_user->username;?>/album/create">
<input type="text" name="album_name"/>
<input type="submit" name="create_album" value="create"/>
</form>
<?php
}
?>
<br/><br/>Albums : <br/><br/>
<?php
foreach($albums as $row){
	echo "<a href='".base_url().$current_user->username."/album/".$row->id_album."'>".$row->name.'<br/>';
	if($row->photo_cover){
		echo "<img src='".base_url()."snap/thumb/".$row->photo_cover."/".$current_user->first_name.' '.$current_user->last_name.".jpg' /></a><br/>";	
	}else{
		echo "<img src='".base_url()."images/themes/default/header_bg.png' /></a><br/>";	
	}
}
?>
<br/>

<style>
.tw-album-photos{
	float:left;width:120px;height:120px;
}
</style>
<?php echo $this->session->flashdata('alert');?>
<a href="<?php echo base_url().$current_user->username;?>/album">Albums</a> >> Photos<br/>
<?php
if(!$user_relation){
?>
<form method="post" action="<?php echo base_url().$session_user->username;?>/album/uploadpicture" enctype="multipart/form-data" >
<input type="file" name="userfile1" size="20" />
<input type="hidden" name="album_id" value="<?php echo $album_id;?>" />
<input type="submit" name="uploadpicture" value="upload"/>
</form>
<?php
}
?>
<?php
if($snaps){
	foreach($snaps as $row){
		echo "<div class='tw-album-photos'><a href='".base_url().$current_user->username."/photo/".$row->id_photo."'><img src='".base_url()."snap/thumb/".$row->id_photo."/".$current_user->first_name.' '.$current_user->last_name.".jpg' /></a>";
		if(!$user_relation){
			echo "<a href='".base_url().$session_user->username."/album/deletesnap/".$album_id.'/'.$row->id_photo."'>delete</a></div>";	
		}
	}
	echo "<div class='clear'></div>";
}
?>
<br/>
<div id="status-page-content">
<div id='oldstatus'></div>
</div>
<script>
var status_post_type=0;
var lastStatusId=0;var currentStatusUsername="";
$.getJSON(base_url+current_user+'/status/post/<?php echo $album_details->album_status_id;?>', function(msg) {
	$('#oldstatus').html(displayComments(msg));$(".tw-status-outer").fadeIn('slow');$(".tw-comment-outer").fadeIn('slow');
	if(msg.status.length){
		lastStatusId=msg.status[msg.status.length-1].id;makeStatusLoadChanges();
	}
	if(msg.status.length==10){
		$('#status-more-button').show();
	}else{
		$('#status-more-button').hide();
	}
	showHiddenComments(msg.status[0].id);
});
</script>
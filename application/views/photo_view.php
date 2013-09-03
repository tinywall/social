<?php echo $this->session->flashdata('alert');?>
<a href="<?php echo base_url().$current_user->username;?>/album">Albums</a> >> <a href="<?php echo base_url().$current_user->username.'/album/'.$photo->album_id;?>">Photos</a> >> Photo<br/>
<?php
echo "<img src='".base_url()."snap/full/".$photo->id_photo."/".$current_user->first_name.' '.$current_user->last_name.".jpg' /><br/>";
echo "<a href='".base_url().$current_user->username.'/album/'.$photo->album_id."'>Back to album</a> | ";
if($photo->previous){echo "<a href='".base_url().$current_user->username."/photo/".$photo->previous."'>previous</a> | ";}
if($photo->next){echo "<a href='".base_url().$current_user->username."/photo/".$photo->next."'>next</a>";}
?>
<div id="status-page-content">
<div id='oldstatus'></div>
</div>
<script>
var status_post_type=0;
var lastStatusId=0;var currentStatusUsername="";
$.getJSON(base_url+current_user+'/status/post/<?php echo $photo->photo_status_id;?>/', function(msg) {
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
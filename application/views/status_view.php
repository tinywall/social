<style>

</style>
<script>
$(document).ready(function(){			
	$('#newStatusBox').elastic();
	//$('.tw-comment-box').elastic();
	
	$("#newStatusBox").toggleVal({
		focusClass: "hasFocus",
		changedClass: "isChanged"
	});	
});
</script>
<script type="text/javascript" src="<?php echo base_url();?>aulib/ajaxfileupload.js"></script>
<?php if(!($status_type=='post')){?>
<textarea type='text' id="newStatusBox" class="tw-form-text">Your Status here...</textarea>
<div id="newStatusBoxExtraInputs">
<div id="newStatusBoxImage" style="display:none;">Photo: <input id="newStatusImage" type="file" name="newStatusImage" /></div>
<div id="newStatusBoxLink" style="display:none;">Link: <input type="text" id="newStatusLink" class="tw-form-text"/></div>
<div id="newStatusBoxVideo" style="display:none;">Youtube URL: <input type="text" id="newStatusVideo" class="tw-form-text"/></div>
</div>
<div id="newStatusBoxPostButton">
<img onclick="$('#newStatusBoxLink').toggle();$('#newStatusBoxImage').hide();$('#newStatusBoxVideo').hide();" src='<?php echo base_url();?>images/themes/default/status_link.png' class="tw-status-attachment-image" alt="Link" title="Link"/>
<img onclick="$('#newStatusBoxImage').toggle();$('#newStatusBoxLink').hide();$('#newStatusBoxVideo').hide();" src='<?php echo base_url();?>images/themes/default/status_image.png' class="tw-status-attachment-image" alt="Photo" title="Photo"/>
<img onclick="$('#newStatusBoxVideo').toggle();$('#newStatusBoxImage').hide();$('#newStatusBoxLink').hide();" src='<?php echo base_url();?>images/themes/default/status_video.png' class="tw-status-attachment-image" alt="Video" title="Video"/>
<input type='button' value="post" id="status-post-button" onclick='postStatus()' class="tw-form-button"/>
</div>
<div class="clear"></div>
<hr/>
<?php }?>


<div id="status-page-content">
<div id='newstatus'></div>
<div id='oldstatus'></div>
<div id='morestatus'></div>
<center><input type="button" value="more" onclick="getMoreStatus()" id="status-more-button" class="tw-form-button" style="display:none;width:100%;"/></center>
</div>

<script language="javascript" src="<?php echo base_url();?>js/statusfunctions.js"></script>
<script language="javascript">
var status_post_type=0;
var lastStatusId=0;var currentStatusUsername="";
all_status_deletable=<?php if($status_type=='posts'&&(!$user_relation)){echo "true";}else{echo "false";}?>;
$.getJSON('<?php echo base_url().$status_request_url;?>'+lastStatusId, function(msg) {
	$('#oldstatus').html(displayStatus(msg));$(".tw-status-outer").fadeIn('slow');$(".tw-comment-outer").fadeIn('slow');
	if(msg.status.length){
		lastStatusId=msg.status[msg.status.length-1].id;makeStatusLoadChanges();
	}
	if(msg.status.length==10){
		$('#status-more-button').show();
	}else{
		$('#status-more-button').hide();
	}
	<?php if($status_type=='post'){?>showHiddenComments(msg.status[0].id);<?php }?>
});
function getMoreStatus(){
	$.getJSON('<?php echo base_url().$status_request_url;?>'+lastStatusId, function(msg) {
		$('#oldstatus').html($('#oldstatus').html()+displayStatus(msg));$(".tw-status-outer").fadeIn('slow');$(".tw-comment-outer").fadeIn('slow');
		if(msg.status.length){
			lastStatusId=msg.status[msg.status.length-1].id;
			makeStatusLoadChanges();
		}
		if(msg.status.length==10){
			$('#status-more-button').show();
		}else{
			$('#status-more-button').hide();
		}
	});
}
</script>

<style>
#pad-page-content{width:510px;font-family:arial;}
#pad_post_message{
	width:500px;height:30px;max-height:150px;
}
.tw-pad-outer{padding-top:5px;padding-bottom:5px;padding-left:5px;padding-right:5px;
	border-bottom:1px solid #ccc;
}
.tw-pad-reply{
width:400px;padding:3px;background:#eee;border: #ccc 1px solid;margin-top:5px;
	-moz-border-radius: 2px;-webkit-border-radius: 2px;border-radius: 2px;border-top-left-radius: 2px 2px;border-top-right-radius: 2px 2px;border-bottom-right-radius: 2px 2px;border-bottom-left-radius: 2px 2px;
}
.tw-pad-outer:hover{background:#eee;}
.tw-pad-outer:hover .tw-pad-reply{background:#fff;}
.tw-pad-left{float:left;width:60px;}
.tw-pad-right{float:right;width:435px;}
.tw-pad-post-pic{width:50px;height:50px;}
.tw-pad-post-author{font-weight:bold;font-size:13px;}
.tw-pad-post-message{font-size:12px;margin-top:5px;word-wrap: break-word;}
.tw-pad-post-info{font-size:11px;margin-top:3px;float:right;}
.tw-pad-post-info-time{float:left;margin-left:10px;}
.tw-pad-post-info-reply{float:left;margin-left:10px;}
.tw-pad-post-info-delete{float:left;display:none;margin-left:10px;}
.tw-pad-post-info-time a{text-decoration:underline;}
.tw-pad-outer:hover .tw-pad-post-info-delete{display:block;}
.tw-pad-reply-box-outer{padding:10px;}
.pad-reply-textbox{
	width:400px;
}
</style>
<script>
$(document).ready(function(){			
	$('#pad_post_message').elastic();
	//$('.pad-reply-textbox').elastic();
});	
</script>
<div id="pad-page-content">
<?php if($user_relation){ ?>
<form id="message_form" method="post">
<textarea id="pad_post_message" name="new_pad_message"></textarea>
<div style="float:right;">
<input type="radio" name="pad_post_privacy" value="1"/>:Private
<input type="radio" name="pad_post_privacy" value="0" checked="checked"/>:Public
<input type="button" value="send" id="send_button" onclick="postMessage()"/>
</div>
<div class="clear"><br/></div>
</form>
<?php }else{ ?>
<a href="<?php echo base_url();?>pad/compose"><button>Compose Message</button></a><br/><br/>
<?php } ?>
Messages<br/><br/>
<div id='count'></div>
<div id='newMessages'></div>
<div id='oldMessages'></div>
<div id='moreMessages'></div>
<input type="button" id="more-message-button" value="more.." onclick="moreMessage()" />
</div>
<script language="javascript">
var lastmsgid=0,replyBoxId=0;
$.getJSON('<?php echo base_url().$current_user->username;?>/pad/get/0',function(msg){
	$('#oldMessages').html(display_pad_messages(msg));
	if(msg.messages.length){lastmsgid=msg.messages[msg.messages.length-1].id;}
	if(msg.messages.length==10){
		$('#more-message-button').show();
	}else{
		$('#more-message-button').hide();
	}
});
function display_pad_messages(msg){
	var output='';
	for(var i=0;i<msg.messages.length;i++){
		output+=
			"<div class='tw-pad-outer' id='pad_message_"+msg.messages[i].id+"'>"+
			"<div class='tw-pad-left'><a href='<?php echo base_url();?>"+msg.messages[i].from.username+"'>"+
			"<img src='<?php echo base_url().'avatar/thumb/';?>"+msg.messages[i].from.username+"' class='tw-pad-post-pic''/>"+
			"</a></div>"+
			"<div class='tw-pad-right'><a href='<?php echo base_url();?>"+msg.messages[i].from.username+"' class='tw-pad-post-author'>"+
					msg.messages[i].from.fullname+' </a>';
				output+="<div class='tw-pad-post-info'>";
				if(msg.messages[i].from.username==session_user||<?php if(!$user_relation){ echo 'true';}else echo 'false'; ?>){
				output+="<div class='tw-pad-post-info-delete'><a  onclick=\"showConfirmAlertBox('Are you sure???...','Delete','deletePadMessage("+msg.messages[i].id+");closeAlertBox();');\" >delete</a></div>";
			}
			<?php if(!$user_relation){?>
			output+='<div class="tw-pad-post-info-reply"><a onclick="replyMessage('+msg.messages[i].id+',\''+msg.messages[i].from.username+'\');"; >reply</a></div>';
			<?php }?>
			output+="<div class='tw-pad-post-info-time'><a>"+
					toTWDate(msg.messages[i].time)+
					"</a></div>";
			output+="</div><div class='clear'></div>";
			output+="<div class='tw-pad-post-message'>"+
				msg.messages[i].message+'</div>';
				
			<?php if(!$user_relation){?>
			output+= '<div class="tw-pad-reply-box-outer" id="reply_box_'+msg.messages[i].id+'" ></div>';
			<?php }?>
			output+="</div><div class='clear'></div></div>";
	}
 return output;
}
function replyMessage(message_id,author){
	if(replyBoxId){
		$('#reply_box_'+replyBoxId).html("");
	}replyBoxId=message_id;
	$('#reply_box_'+message_id).html(
	"<input type='hidden' id='reply_to_"+message_id+"' value='"+author+"' />"+
	"<textarea class='pad-reply-textbox' id='reply_text_"+message_id+"'></textarea><div style='float:right;'>"+
	"<input type='radio' name='reply_privacy_"+message_id+"' value='1' /> Private "+
	"<input type='radio' name='reply_privacy_"+message_id+"' value='0' checked='checked'/> Public"+
	"<button id='post_reply_"+message_id+"' onclick=postReply("+message_id+");>submit</button></div><div class='clear'></div>"
	);
}
function postMessage(){
	var privacy=$('input:radio[name=pad_post_privacy]:checked').val();
	var to='<?php echo $current_user->username;?>';
	var message=$('#pad_post_message').val();
	$.ajax({  
		type: "POST",  
		url: "<?php echo base_url();?>"+to+"/pad/post/",
		data: "to="+to+"&message="+message+"&privacy="+privacy,
		dataType: 'json',
		success: function(msg) {
			if(msg.messages.length){
				$('#newMessages').html(display_pad_messages(msg)+$('#newMessages').html());
			}
		}
 	});
}
function postReply(message_id){
	var privacy=$('input:radio[name=reply_privacy_'+message_id+']:checked').val();
	var to=$('#reply_to_'+message_id).val();
	var message=$('#reply_text_'+message_id).val();
	$.ajax({  
		type: "POST",  
		url: "<?php echo base_url();?>"+to+"/pad/post/",
		data: "to="+to+"&message="+message+"&privacy="+privacy,
		dataType: 'json',
		success: function(msg) {
			if(msg.messages.length){
				$('#reply_box_'+message_id).html("Reply Sent");
			}
		}
 	});
}
function moreMessage(){
	$.getJSON('<?php echo base_url().$current_user->username;?>/pad/get/'+lastmsgid,function(msg){
		$('#moreMessages').html($('#moreMessages').html()+display_pad_messages(msg));
		if(msg.messages.length){lastmsgid=msg.messages[msg.messages.length-1].id;}
		if(msg.messages.length==10){
			$('#more-message-button').show();
		}else{
			$('#more-message-button').hide();
		}
	});
}
function deletePadMessage(message_id){
	$.getJSON('<?php echo base_url();?>pad/delete/'+message_id, function(msg) {
	     if(msg.response.success){
		 	$('#pad_message_'+message_id).hide('slow');$('#pad_message_'+message_id).empty();
		 }else{
		 	showAlertBox(msg.response.message);
		 }
	 });
}
</script>
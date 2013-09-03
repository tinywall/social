<div style="float:left;">
	<h4>Chatroom:</h4>
	<div id="chat_msg" style="width:400px;border:1px solid #eee;height:400px;overflow-y:auto;padding:10px;background:#eee;"></div>
	<div>
	<input type="text" id="chat_box" style="width:350px;" />
	<button id="post" onclick="post_chat()">Post</button>
	</div>
</div>
<div   style="float:left;width:200px;margin-left:20px;"><h4>Who is Online</h4>
	<div id="chatroom-online-friends"></div>
</div>
<div class="clear"></div>
<script type="text/javascript">
	var lastMessageId=0;
	$(document).ready( function(){
	$('#chat_box').focus();
	$.getJSON('<?php echo base_url();?>chatroom/get_chat_msg/'+lastMessageId, function(msg) {
	$('#chat_msg').html(displayMessage(msg));
	if(msg.Message.length){lastMessageId=msg.Message[msg.Message.length-1].chat_msg_id;}
		setTimeout("update_msg()",100);	
	});
		
	
});
function chatroomPing(){
	$.getJSON('<?php echo base_url();?>chatroom/ping',function(msg){
	     if(msg.response.success){
		 var output='';
		 	if(msg.response.friend.length){
				for(var i=0;i<msg.response.friend.length;i++){
					output+="<a href='"+base_url+msg.response.friend[i].username+"')>"+msg.response.friend[i].fullname+'</a><br/>';
				}
			}else{
				output+='No Users in Chatroom';
			}
		 }
		 $('#chatroom-online-friends').html(output);
		 setTimeout("chatroomPing()",10000);
	});	
}
chatroomPing();
function update_msg(){
	$.getJSON('<?php echo base_url();?>chatroom/get_chat_msg/'+lastMessageId, function(msg) {
		$('#chat_msg').html($('#chat_msg').html()+displayMessage(msg));
		if(msg.Message.length){lastMessageId=msg.Message[msg.Message.length-1].chat_msg_id;$("#chat_msg").attr({ scrollTop:$("#chat_msg").attr("scrollHeight")});}
		setTimeout("update_msg()",100);
	});
}


function displayMessage(msg){
	var output='';
		output = "<div>"
		for(var i=0;i<msg.Message.length&&lastMessageId<msg.Message[i].chat_msg_id;i++){
			//if((lastMessageId==0)||()){
			output += "<a href='"+base_url+msg.Message[i].author+"'><b>"+msg.Message[i].author+":</b></a> "+msg.Message[i].msg+"<br />";
			//}
		}
		output +="</div>";
	return output;
}


function post_chat(){
	//$('#chat_box').css('display', 'none');
	$.ajax({
	   type: "POST",dataType:'json',url:"<?php echo base_url();?>chatroom/post_chatroom_msg/",
	   data: "message="+$('#chat_box').val(),
	   success: function(msg){
	        //$('#new_chat_msg').html(displayMessage(msg)+$('#new_chat_msg').html());
			//$('#chat_form').css('display', 'block');
			$('#chat_box').val('').focus();
			update_msg();
			}
	 });
}
		


</script>
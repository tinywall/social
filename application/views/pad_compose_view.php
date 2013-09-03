<script type='text/javascript' src='<?php echo base_url();?>js/jquery.bgiframe.min.js'></script>
<script type='text/javascript' src='<?php echo base_url();?>js/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='<?php echo base_url();?>js/thickbox-compressed.js'></script>
<script type='text/javascript' src='<?php echo base_url();?>js/jquery.autocomplete.min.js'></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/jquery.autocomplete.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/thickbox.css" />
<style>
#pad_post_to{
	width:500px;
}
#pad_post_message{
	width:500px;height:100px;max-height:250px;
}
</style>
<script>
$(document).ready(function(){			
	$('#pad_post_message').elastic();
});	
</script>
<div id="pad-page-content">
 <form id="message_form" method="post">
To (username) :<br/> <input type="text" id="pad_post_to" onfocus="" /><br/>
Message : <textarea id="pad_post_message" name="new_pad_message"></textarea><br/>
<input type="radio" name="pad_post_privacy" value="1"/>:Private
<input type="radio" name="pad_post_privacy" value="0" checked="checked"/>:Public
<input type="button" value="send" id="send_button" onclick="postMessage()"/>
<div class="clear"><br/></div>
</form>
<script type="text/javascript">
$(function() {
	function format(mail) {
		return mail.fullname + " < " + mail.username + " > ";
	}
	$("#pad_post_to").autocomplete('<?php echo base_url();?>pad/get_to_suggessions/', {
		multiple: false,
		dataType: "json",
		parse: function(data) {
			return $.map(data, function(row) {
				return {
					data: row,
					value: row.fullname,
					result: row.username
				}
			});
		},
		formatItem: function(item) {
			return format(item);
		}
	}).result(function(e, item) {
		
	});
});
</script>
<div id="pad_post_result"></div>
</div>
<script language="javascript">
var pad_post_to='';
function postMessage(){
	var privacy=$('input:radio[name=pad_post_privacy]:checked').val();
	var to=$('#pad_post_to').val();
	var message=$('#pad_post_message').val();
	$.ajax({  
		type: "POST",  
		url: "<?php echo base_url();?>pad/post/",
		data: "to="+to+"&message="+message+"&privacy="+privacy,
		dataType: 'json',
		success: function(msg) {
			$('#pad_post_result').html('Receiver Not Exist').show();
			if(msg.messages.length){
				$('#pad_post_result').html('Message Sent').fadeIn('slow').delay(1000).fadeOut('slow');
			}
		}
 	});
}
</script>
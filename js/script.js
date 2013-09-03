function showAlertBox(msg){
	$('#alert-title').hide();
	$('#tw-alert-box').html(msg+"<div class='tw-alert-box-button'><input class='tw-alert-box-bottom-button' type='button' value='Close' onclick='closeAlertBox()'/></div>");
	$('#tw-alert-box-outer').fadeIn('slow');
}
function showFullAlertBox(title,msg){
	$('#alert-title').html(title).show();
	$('#tw-alert-box').html(msg+"<div class='clear'></div><div class='tw-alert-box-button'><input class='tw-alert-box-bottom-button' type='button' value='Close' onclick='closeAlertBox()'/></div>");
	$('#tw-alert-box-outer').fadeIn('slow');
}
function showConfirmAlertBox(msg,action,callback){
	$('#alert-title').hide();
	$('#tw-alert-box').html(msg+"<div class='clear'></div><div class='tw-alert-box-button'><input class='tw-alert-box-bottom-button' type='button' value='"+action+"' onclick=\""+callback+"\" ><input class='tw-alert-box-bottom-button' type='button' value='Close' onclick='closeAlertBox()'/></div>");
	$('#tw-alert-box-outer').fadeIn('slow');
}
function showFullConfirmAlertBox(title,msg,action,callback){
	$('#alert-title').html(title).show();
	$('#tw-alert-box').html(msg+"<div class='clear'></div><div class='tw-alert-box-button'><input class='tw-alert-box-bottom-button' type='button' value='"+action+"' onclick=\""+callback+"\" ><input class='tw-alert-box-bottom-button' type='button' value='Close' onclick='closeAlertBox()'/></div>");
	$('#tw-alert-box-outer').fadeIn('slow');
}
function closeAlertBox(){
	$('#tw-alert-box-outer').fadeOut('slow');
}
function showAddfriendConfirmAlertBox(username){
	showFullConfirmAlertBox("Add Friend","You are about to send friend request to "+username,"Send Request","sendFriendRequest('"+username+"');");
}
function showUnfriendConfirmAlertBox(username){
	showFullConfirmAlertBox("Remove Friend","You are about to unfriend "+username,"Remove","removeFriend('"+username+"');");
}
function showCancelRequestConfirmAlertBox(username){
	showFullConfirmAlertBox("Cancel Friend Request","You are about to cancel friend request to "+username,"Cancel","cancelFriendRequest('"+username+"');");
}
function getValidDate(fbDate){
	var arrDateTime = fbDate.split("T"); 
    var strTimeCode = arrDateTime[1].substring(0,arrDateTime[1].indexOf("+"));
    var valid_date = new Date();
	var arrDateCode = arrDateTime[0].split("-");
	valid_date.setYear(arrDateCode[0]);
	valid_date.setMonth(arrDateCode[1]-1);
	valid_date.setDate(arrDateCode[2]);
    var arrTimeCode = strTimeCode.split(":"); 
    //var offset=parseInt(new Date(parseInt(fbDate)).getTimezoneOffset());
    valid_date.setHours(arrTimeCode[0]);
	valid_date.setMinutes(arrTimeCode[1]);
	valid_date.setSeconds(arrTimeCode[2]);
	return valid_date;
}
var month=new Array(12);
month[0]="January";
month[1]="February";
month[2]="March";
month[3]="April";
month[4]="May";
month[5]="June";
month[6]="July";
month[7]="August";
month[8]="September";
month[9]="October";
month[10]="November";
month[11]="December";
function toTWDate(gmdate){
	var date=getValidDate(gmdate);
	var offset=parseInt(date.getTimezoneOffset());
	var sec_diff = (((new Date()).getTime()+(offset*60*1000)-date.getTime())/1000);
	var day_diff = Math.floor(sec_diff / 86400);
	if(sec_diff<60){
		return "Just Now";
	}else if(sec_diff<120){
		return "1 minute ago";
	}else if(sec_diff<3600){
		return ""+Math.floor( sec_diff / 60 ) + " minutes ago";
	}else if(sec_diff<7200){
		return "1 hour ago";
	}else if(sec_diff<86400){
		return Math.floor( sec_diff / 3600 ) + " hours ago";
	}else if(day_diff==1){
		return "yesterday";
	}else if(day_diff<7){
		return day_diff + " days ago";
	}else if(day_diff<31){
		return Math.ceil( day_diff / 7 ) + " weeks ago";
	}else{
		return month[date.getMonth()]+" "+date.getDate()+", "+date.getFullYear();
	}
	return date;
}
function text_to_link(text) 
{
	var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
	return text.replace(exp,"<a href='$1' target='_blank'>$1</a>"); 
}
function get_link_data(id,url){
	$.get(base_url+"public/geturldata.php?url="+url,function(response){
	var title=(/<title>(.*?)<\/title>/m).exec(response)[1];
	var logo=(/src='(.*?).png'/m).exec(response)[1];
	//if(logo){
		$("#"+id).html("<a href='"+url+"'><b>"+title+"</b><br/>"+url+"</a>");
	//}else{
	//	$("#"+id).html("<a href='"+url+"'><img src='"+logo+".png' class='img'/><b>"+title+"</b><br/>"+url+"</a>");	
	//}
	});
}
//------------------------ status -----------------------------------
var all_status_deletable=false;
function makeStatusLoadChanges(){
	$('.tw-status-comment-temp-textbox').click(function(){
		$(this).css({'height':'30px','width':'355px'});
		$(this).next('.tw-status-comment-button').show();
		$(this).empty().focus();
	});
	/*$('.tw-status-comment-temp-textbox').blur(function(){
		$(this).css({'height':'10px','width':'385px'});
		$(this).next('.tw-status-comment-button').hide();
		$(this).empty();
	});*/
	
	//$('.tw-status-comment-temp-textbox').elastic();
	//$('.tw-comment-box').elastic();
	
	$(".tw-status-comment-temp-textbox").toggleVal({
		focusClass: "hasFocus",
		changedClass: "isChanged"
	});
}
function likeStatus(status_id){
	$.getJSON(base_url+'status/likeStatus/'+status_id, function(msg) {
	     if(msg.response.success){
		 	$('#like-links_'+status_id).html("<a onclick='unlikeStatus("+status_id+")'>Unike</a>");
			$('#like-count_'+status_id).html(parseInt($('#like-count_'+status_id).html())+1);
		 }else{
		 	showAlertBox(msg.response.message);
		 }
	 });
}
function unlikeStatus(status_id){
	$.getJSON(base_url+'status/unlikeStatus/'+status_id, function(msg) {
	     if(msg.response.success){
		 	$('#like-links_'+status_id).html("<a onclick='likeStatus("+status_id+")'>Like</a>");
			$('#like-count_'+status_id).html(parseInt($('#like-count_'+status_id).html()-1));
		 }else{
		 	showAlertBox(msg.response.message);
		 }
	 });
}
function getStatusLikes(status_id){
	$.getJSON(base_url+'status/getStatusLikes/'+status_id, function(msg) {
		var output='';
		if(msg.response.length){
		for(var i=0;i<msg.response.length;i++){
			output+="<div><a href='"+base_url+
				msg.response[i].username+"'>"+
				msg.response[i].fullname+
				"</a></div>";
		}
		showFullAlertBox("Status Likes",output);
		}
	 });
}
function postStatus(){
	/*$.ajax({
	   type: "POST",dataType:'json',url:base_url+"status/postStatus",
	   data: "message="+$('#newStatusBox').val()+"&status_post_type="+status_post_type+"&status_post_link="+$('#newStatusLink').val()+"&status_post_video="+$('#newStatusVideo').val()+"&owner="+current_user,
	   success: function(msg){
	     $('#newstatus').html(displayStatus(msg)+$('#newstatus').html());$(".tw-status-outer").show('slow');$(".tw-comment-outer").fadeIn('slow');
		 makeStatusLoadChanges();
		 
	   }
	 });*/
	 $.ajaxFileUpload
		(
			{
				url:base_url+"status/postStatus",
				secureuri:false,
				fileElementId:'newStatusImage',
				dataType: 'json',
				data: {message:$('#newStatusBox').val(),status_post_type:status_post_type,status_post_link:$('#newStatusLink').val(),status_post_video:$('#newStatusVideo').val(),owner:current_user},
				success:function(msg,status)
				{
					//alert(msg);
					$('#newstatus').html(displayStatus(msg)+$('#newstatus').html());$(".tw-status-outer").show('slow');$(".tw-comment-outer").fadeIn('slow');
					makeStatusLoadChanges();
				},
				error:function(data,status,e)
				{
					alert(e);
				}
			}
		)
}
function deleteStatus(status_id){
	$.getJSON(base_url+'status/deleteStatus/'+status_id, function(msg) {
	     if(msg.response.success){
		 	$('#status_'+status_id).hide('slow');$('#status_'+status_id).empty();
		 }else{
		 	showAlertBox(msg.response.message);
		 }
	 });
}
function postComment(status_id){
	$.ajax({
	   type: "POST",dataType:'json',url:base_url+"status/postComment",
	   data: "statusId="+status_id+"&message="+$('#newCommentBox_'+status_id).val(),
	   success: function(msg){
		 $('#newcomment_'+status_id).html($('#newcomment_'+status_id).html()+getComments(status_id,msg.comments));$(".tw-comment-outer").show('slow');
		 
	   }
	 });
}
function deleteComment(comment_id){
	$.getJSON(base_url+"status/deleteComment/"+comment_id,function(msg) {
	     if(msg.response.success){
		 	$('#comment_'+comment_id).hide('slow');$('#comment_'+comment_id).empty();
		 }else{
		 	showAlertBox(msg.response.message);
		 }
	 });
}
function displayStatus(msg){
	var output='';
	for(var i=0;i<msg.status.length;i++){currentStatusUsername=msg.status[i].from.username;
		if(msg.status[i].type==0||msg.status[i].type==2||(msg.status[i].type==1&&msg.status[i].commentcount>0)||msg.status[i].type==3||msg.status[i].type==4||msg.status[i].type==5){
				output+="<div class='tw-status-outer' id='status_"+msg.status[i].id+"' style='display:none;'>"+
					"<div class='tw-status-left'><a href='"+base_url+msg.status[i].from.username+"'>"+
					"<img src='"+base_url+"avatar/thumb/"+msg.status[i].from.username+"' class='tw-status-post-pic''/>"+
					"</a></div>"+
					"<div class='tw-status-right'><a href='"+base_url+msg.status[i].from.username+"' class='tw-status-post-author'>"+
					msg.status[i].from.fullname+' </a>';
				output+="<span class='tw-status-post-message'>";
					if(msg.status[i].type==0){
						output+=text_to_link(msg.status[i].message);
					}else if(msg.status[i].type==1){
						output+="has updated <a href='"+base_url+msg.status[i].from.username+"/profile'>his profile</a>.";
					}else if(msg.status[i].type==2){
						output+="has updated his profile photo<br/>";
						output+="<a href='"+base_url+msg.status[i].from.username+"'><img src='"+base_url+"avatar/full/"+msg.status[i].from.username+"' width='100' height='100' style='border:2px solid #eee;'/></a>";
					}else if(msg.status[i].type==3){
						output+="has uploaded photos in his album<br/>";
						output+="<a href='"+base_url+msg.status[i].from.username+"/photo/"+msg.status[i].status_image+"'><img src='"+base_url+"snap/thumb/"+msg.status[i].status_image+"' style='border:2px solid #eee;'/></a>";
					}else if(msg.status[i].type==4){
						output+="has created a <a href='"+base_url+msg.status[i].from.username+"/album/"+msg.status[i].status_image+"'>photo album.</a>";
					}else if(msg.status[i].type==5){
						output+=text_to_link(msg.status[i].message);
						output+="<br/><a href='"+base_url+msg.status[i].from.username+"/photo/"+msg.status[i].status_image+"'><img src='"+base_url+"snap/thumb/"+msg.status[i].status_image+"' style='border:2px solid #eee;'/></a>";
					}
				output+="</span>";
				output+="<div>";
				if(msg.status[i].status_link){
					output+="<div id='status_post_link_"+msg.status[i].id+"'>";
					output+="<a href='"+msg.status[i].status_link+"'>"+msg.status[i].status_link+"</a>";
					output+="</div>";get_link_data("status_post_link_"+msg.status[i].id,msg.status[i].status_link);
				}
				if(msg.status[i].status_video){
					output+="<div id='status_post_video_"+msg.status[i].id+"'>Video:<a href='http://www.youtube.com/watch?v="+msg.status[i].status_video+"' target='_blank'>http://www.youtube.com/watch?v="+msg.status[i].status_video+"</a><br/>";
					output+="<object style='height: 390px; width: 640px'>"+
					"<param name='movie' value='http://www.youtube.com/v/"+msg.status[i].status_video+"?version=3'>"+
					"<param name='allowFullScreen' value='true'>"+
					"<param name='allowScriptAccess' value='always'>"+
					"<embed src='http://www.youtube.com/v/"+msg.status[i].status_video+"?version=3' type='application/x-shockwave-flash' allowfullscreen='true' allowScriptAccess='always' width='320' height='195'>"+
					"</object>";
					output+="</div>";
				}
				output+="</div>";
				output+="<div class='tw-status-post-info'>";
					output+="<div class='tw-status-post-info-time'><a href='"+base_url+msg.status[i].from.username+"/status/"+msg.status[i].id+"' title='"+getValidDate(msg.status[i].created_at).toString()+"'><img src='"+base_url+"images/themes/default/status_time.png' /> "+
					toTWDate(msg.status[i].created_at)+
					"</a></div>";
					output+="<div class='tw-status-post-info-likes'>";
					output+=" <span id='like-links_"+msg.status[i].id+"'>";
						if(msg.status[i].liked=="0"){
							output+="<a onclick='likeStatus("+msg.status[i].id+")'>Like</a>";
						}else{
							output+="<a onclick='unlikeStatus("+msg.status[i].id+")'>Unike</a>";
						}
					output+="</span>";
					//output+="</div>";
					//output+="<div class='tw-status-post-info-likes'>";
					output+=" <a id='like-count-outer_"+msg.status[i].id+"' onclick='getStatusLikes("+msg.status[i].id+")'><img src='"+base_url+"images/themes/default/status_like.png' /> ";
					output+="<span id='like-count_"+msg.status[i].id+"'>"+msg.status[i].like_count+"</span>";
					output+="</a></div>";
					output+="<div class='tw-status-post-info-comment'>";
					output+="<a onclick=\"$('#statusCommentOuterBox_"+msg.status[i].id+"').show();$('#newCommentBox_"+msg.status[i].id+"').click();\">Comment</a>";
					if(msg.status[i].commentcount>3){
						output+=" <a onclick='showHiddenComments("+msg.status[i].id+")'><img src='"+base_url+"images/themes/default/status_comment.png' /> "+msg.status[i].commentcount+"</a>";
					}else{
						output+=" <a><img src='"+base_url+"images/themes/default/status_comment.png' /> "+msg.status[i].commentcount+"</a>";
					}
					output+="</div>";
					if(msg.status[i].from.username==session_user||all_status_deletable){
						output+="<div class='tw-status-post-info-delete'><a onclick=\"showConfirmAlertBox('Are you sure???...','Delete','deleteStatus("+msg.status[i].id+");closeAlertBox();');\"><img src='"+base_url+"images/themes/default/status_delete.png' /> delete</a></div>";
					}
					output+="<div class='clear'></div>";
				output+="</div>";
				if(msg.status[i].comments.length){					
					output+="<div class='tw-status-comments' id='statusCommentOuterBox_"+msg.status[i].id+"'>";
				}else{
					output+="<div class='tw-status-comments' id='statusCommentOuterBox_"+msg.status[i].id+"' style='display:none;'>";
				}
				output+=getComments(msg.status[i].id,msg.status[i].comments);
					output+="<div id='newcomment_"+msg.status[i].id+"'></div>";
					output+="<textarea class='tw-status-comment-temp-textbox' type='text' id='newCommentBox_"+msg.status[i].id+"'>comment...</textarea>"+
					"<input class='tw-status-comment-button' type='button' value='post' onclick='postComment("+msg.status[i].id+");'/>";
				output+="</div>";
				output+="</div><div class='clear'></div></div>";
		}else if((msg.status[i].type==1&&msg.status[i].commentcount==0)){
				output+="<div class='tw-status-outer ' id='status_"+msg.status[i].id+"' style='display:none;'><div class='tw-status-mini-post-outer'>";
				output+="<a class='tw-status-post-author' href='"+base_url+msg.status[i].from.username+"'>"+msg.status[i].from.fullname+"</a> <span class='tw-status-post-message'>";
				if(msg.status[i].type==1){
					output+="has updated <a href='"+base_url+msg.status[i].from.username+"/profile'>his Profile</a>";
				}
				output+="</span><div class='tw-status-post-info'>";
					output+="<div class='tw-status-post-info-time'><a href='"+base_url+msg.status[i].from.username+"/status/"+msg.status[i].id+"' title='"+getValidDate(msg.status[i].created_at).toString()+"'><img src='"+base_url+"images/themes/default/status_time.png' /> "+
					toTWDate(msg.status[i].created_at)+
					"</a></div>";
					output+="<div class='tw-status-post-info-likes'>";
					output+=" <span id='like-links_"+msg.status[i].id+"'>";
						if(msg.status[i].liked=="0"){
							output+="<a onclick='likeStatus("+msg.status[i].id+")'>Like</a>";
						}else{
							output+="<a onclick='unlikeStatus("+msg.status[i].id+")'>Unike</a>";
						}
					output+="</span>";
					//output+="</div>";
					//output+="<div class='tw-status-post-info-likes'>";
					output+=" <a id='like-count-outer_"+msg.status[i].id+"' onclick='getStatusLikes("+msg.status[i].id+")'><img src='"+base_url+"images/themes/default/status_like.png' /> ";
					output+="<span id='like-count_"+msg.status[i].id+"'>"+msg.status[i].like_count+"</span>";
					output+="</a></div>";
					output+="<div class='tw-status-post-info-comment'>";
					output+="<a onclick=\"$('#statusCommentOuterBox_"+msg.status[i].id+"').show();$('#newCommentBox_"+msg.status[i].id+"').click();\">Comment</a>";
					if(msg.status[i].commentcount>3){
						output+=" <a onclick='showHiddenComments("+msg.status[i].id+")'><img src='"+base_url+"images/themes/default/status_comment.png' /> "+msg.status[i].commentcount+"</a>";
					}else{
						output+=" <a><img src='"+base_url+"images/themes/default/status_comment.png' /> "+msg.status[i].commentcount+"</a>";
					}
					output+="</div>";
					if(msg.status[i].from.username==session_user){
						output+="<div class='tw-status-post-info-delete'><a onclick=\"showConfirmAlertBox('Are you sure???...','Delete','deleteStatus("+msg.status[i].id+");closeAlertBox();');\"><img src='"+base_url+"images/themes/default/status_delete.png' /> delete</a></div>";
					}
					output+="<div class='clear'></div>";
				output+="</div>";
				if(msg.status[i].id,msg.status[i].comments.length){					output+="<div class='tw-status-comments' id='statusCommentOuterBox_"+msg.status[i].id+"'>";
				}else{
					output+="<div class='tw-status-comments' id='statusCommentOuterBox_"+msg.status[i].id+"' style='display:none;'>";
				}
				output+=getComments(msg.status[i].id,msg.status[i].comments);
					output+="<div id='newcomment_"+msg.status[i].id+"'></div>";
					output+="<textarea class='tw-status-comment-temp-textbox' type='text' id='newCommentBox_"+msg.status[i].id+"'>comment...</textarea>"+
					"<input class='tw-status-comment-button' type='button' value='post' onclick='postComment("+msg.status[i].id+");'/>";
				output+="</div>";
				output+="</div></div>";
		}
	}
	return output;
}
function displayComments(msg){
	var output='';
	for(var i=0;i<msg.status.length;i++){
		currentStatusUsername=msg.status[i].from.username;
		
				output+="<div class='tw-status-outer' id='status_"+msg.status[i].id+"' style='display:none;'>";
				output+="<div class='tw-status-post-info'>";
					output+="<div class='tw-status-post-info-time'><a href='"+base_url+msg.status[i].from.username+"/status/"+msg.status[i].id+"' title='"+getValidDate(msg.status[i].created_at).toString()+"'><img src='"+base_url+"images/themes/default/status_time.png' /> "+
					toTWDate(msg.status[i].created_at)+
					"</a></div>";
					output+="<div class='tw-status-post-info-likes'>";
					output+=" <span id='like-links_"+msg.status[i].id+"'>";
						if(msg.status[i].liked=="0"){
							output+="<a onclick='likeStatus("+msg.status[i].id+")'>Like</a>";
						}else{
							output+="<a id='like-links_"+msg.status[i].id+"' onclick='unlikeStatus("+msg.status[i].id+")'>Unike</a>";
						}
					output+="</span>";
					//output+="</div>";
					//output+="<div class='tw-status-post-info-likes'>";
					output+=" <a id='like-count-outer_"+msg.status[i].id+"' onclick='getStatusLikes("+msg.status[i].id+")'><img src='"+base_url+"images/themes/default/status_like.png' /> ";
					output+="<span id='like-count_"+msg.status[i].id+"'>"+msg.status[i].like_count+"</span>";
					output+="</a></div>";
					output+="<div class='tw-status-post-info-comment'>";
					output+="<a onclick=\"$('#statusCommentOuterBox_"+msg.status[i].id+"').show();$('#newCommentBox_"+msg.status[i].id+"').click();\">Comment</a>";
					if(msg.status[i].commentcount>3){
						output+=" <a onclick='showHiddenComments("+msg.status[i].id+")'><img src='"+base_url+"images/themes/default/status_comment.png' /> "+msg.status[i].commentcount+"</a>";
					}else{
						output+=" <a><img src='"+base_url+"images/themes/default/status_comment.png' /> "+msg.status[i].commentcount+"</a>";
					}
					output+="</div>";
					if(msg.status[i].from.username==session_user||all_status_deletable){
						output+="<div class='tw-status-post-info-delete'><a onclick=\"showConfirmAlertBox('Are you sure???...','Delete','deleteStatus("+msg.status[i].id+");closeAlertBox();');\"><img src='"+base_url+"images/themes/default/status_delete.png' /> delete</a></div>";
					}
					output+="<div class='clear'></div>";
				output+="</div>";
				if(msg.status[i].comments.length){					
					output+="<div class='tw-status-comments' id='statusCommentOuterBox_"+msg.status[i].id+"'>";
				}else{
					output+="<div class='tw-status-comments' id='statusCommentOuterBox_"+msg.status[i].id+"' style='display:none;'>";
				}
				output+=getComments(msg.status[i].id,msg.status[i].comments);
					output+="<div id='newcomment_"+msg.status[i].id+"'></div>";
					output+="<textarea class='tw-status-comment-temp-textbox' type='text' id='newCommentBox_"+msg.status[i].id+"'>comment...</textarea>"+
					"<input class='tw-status-comment-button' type='button' value='post' onclick='postComment("+msg.status[i].id+");'/>";
				output+="</div>";
				output+="<div class='clear'></div></div>";
		
	}
	return output;
}
function showHiddenComments(status_id){
	$("#hiddenComments_"+status_id).show('slow');
	$("#viewCommentLink_"+status_id).hide();
}
function getComments(status_id,comments){
	var output='';
	if(comments.length>3){
		output+=
		"<a id='viewCommentLink_"+status_id+"' onclick='showHiddenComments("+status_id+")'><center>View all "+comments.length+" Comments</center></a>"+
		"<div id='hiddenComments_"+status_id+"' style='display:none;'>";
		for(var j=0;j<comments.length-3;j++){
			output+=
			"<div class='tw-comment-outer' id='comment_"+comments[j].id+"' style='display:none;'>"+
			"<a class='tw-status-comment-author' href='"+base_url+comments[j].from.username+"'>"+
			comments[j].from.fullname+
			" : </a><span class='tw-status-comment-message'>"+text_to_link(comments[j].message)+"</span><br/>";
			output+="<div class='tw-status-comment-info'>"+
			"<div class='tw-status-comment-info-time'><a title='"+getValidDate(comments[j].created_at).toString()+"'>"+toTWDate(comments[j].created_at)+"</a></div>";
			if((comments[j].from.username==session_user)||(currentStatusUsername==session_user)){
				output+=" <div class='tw-status-comment-info-delete'><a onclick=\"showConfirmAlertBox('Are you sure???...','Delete','deleteComment("+comments[j].id+");closeAlertBox();');\">delete</a></div>";
			}
			output+="<div class='clear'></div></div>";
			output+='</div>';
		}
		output+=
		"</div>";
	}
	var i=comments.length-3;
	if(i<0){i=0;}
	for(var j=i;j<comments.length;j++){
		output+=
			"<div class='tw-comment-outer' id='comment_"+comments[j].id+"' style='display:none;'>"+
			"<a class='tw-status-comment-author' href='"+base_url+comments[j].from.username+"'>"+
			comments[j].from.fullname+
			" : </a><span class='tw-status-comment-message'>"+text_to_link(comments[j].message)+"</span><br/>";
			output+="<div class='tw-status-comment-info'>"+
			"<div class='tw-status-comment-info-time'><a title='"+getValidDate(comments[j].created_at).toString()+"'>"+toTWDate(comments[j].created_at)+"</a></div>";
			if((comments[j].from.username==session_user)||(currentStatusUsername==session_user)){
				output+=" <div class='tw-status-comment-info-delete'><a onclick=\"showConfirmAlertBox('Are you sure???...','Delete','deleteComment("+comments[j].id+");closeAlertBox();');\">delete</a></div>";
			}
			output+="<div class='clear'></div></div>";
			output+='</div>';
	}
	return output;
}
//------------------------ /status -----------------------------------

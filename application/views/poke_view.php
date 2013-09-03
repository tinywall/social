<!-- poke-->
		<div class="mr-5">
		<div class="span-5b leftBavkInner borderf mt-5">
		    <h3 class="sub-heading mr-5">Pokes </h3>
				<span class="fl"></span>
	<script language="javascript">
	function poke(user){
		$.getJSON(base_url+user+'/poke/add_poke',function(msg){
				$('#add_poke').html('Poked');
		});	
	}
	function pokeback(user,id){
		$.getJSON(base_url+user+'/poke/add_poke',function(msg){
				$('#poke_'+id).html('Poked Back').delay(1000).fadeOut('slow');
				delete_poke(id);
		});	
	}
	
	</script>

	<style>
		.rightSinglePoke{
			height:20px;
		}
	</style>
	<?php
	if(!$user_relation){
	?>
	<div class="fri_rcon ml-5" id="pokes">Loading...</div>	
	<script language="javascript">
	$(document).ready(function(){
		$.getJSON('<?php echo base_url();?>poke/get_pokes/'+(new Date().getTime()),function(msg){
			var output='';
			 	if(msg.poke.length!=0){
				output+='<div  style="height:20px;overflow-y:hidden">';
					for(var i=0;i<msg.poke.length;i++){
						output+='<div class="rightSinglePoke" id="poke_'+msg.poke[i].id+'"><b>'+msg.poke[i].from+'</b><span onclick=\"pokeback(\''+msg.poke[i].from+'\','+msg.poke[i].id+");\" >Poke Back</span>"+'<span onclick="delete_poke('+msg.poke[i].id+')"> <u>close</u></span></div>';
					}
					output+='</div>';
				}else{
					output='No Pokes';
				}
				$('#pokes').html(output);
		});
	});
	
	function delete_poke(id){
		$.ajax({
	   		type: "POST",dataType:'json',url:"<?php echo base_url();?>poke/remove_poke/"+id,
	   	    success: function(msg){
			$('#poke_'+id).empty();
			}
	 });
	}
	</script>
	<?php
	}
		if($user_relation){
	?>
	<div class="fri_rcon ml-5" id="add_poke" onclick="poke('<?php echo $current_user->username ?>')">Poke</div>	
	
	<?php
	}
	?>
		</div>
		</div>
	<!--- poke end-->
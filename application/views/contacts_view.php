<h3 class=" border_bottom mb-20 clear pl-10">My Contacts</h3>

<div class="span-15">

	<input type="button" value="Add contacts" style='float:right' onclick="add_contact()" />

<div class="span-14c pt-10 ml-10">

<form id="add_new_comp" style="display:none" action='<?php echo base_url();?>contacts/add_contact' method="post" onsubmit="return Valid_add_contact()">

<table width="90%" cellpadding="0" cellspacing="10" align="left">

<tr>
	<td>Name : <br /><input type="text" name="name" id="name" /></td>
	<td>Title : <input type="text" name="title" id="title" /></td>
	<td> Company Name : <input type="text" name="cname" id="cname" /></td>
</tr>
<tr>
	<td></td>
	<td>Mobile Number :<br /> <input type="text" name="mobile" id="mobile" /></td>
	<td>E-Mail:<br /><input type="text" name="mail" id="mail" /></td>
</tr>

<tr>

	<td colspan='3'><br/></td>

</tr>

<tr>

	<td colspan='3'><span class="err" id="contact_err" ></span><input type="submit" value="Save" style='float:right' /></td>

</tr>

</table>

</form>

</div>

<script language="javascript">
function Valid_add_contact(){
	if($('#name').val().length<2||$('#title').val().length<2||$('#cname').val().length<2||$('#mobile').val().length<2||$('#mail').val().length<2||isNaN($('#mobile').val())){
		$('#contact_err').html('Please Enter Valid Entries');
		return false;
	}
	else{
		$('#contact_err').empty();
		return true;
	}
}
function add_contact(){

	//$('#add_new_comp').css({"display":"block"});
	$('#add_new_comp').show(1200);
}
function search_contact(){
	$("#contact_list .eachcontact").each(function(){
		//alert($(this).next('.contact_name').html());
		//alert($(this).children('div').children('.contact_name').html());
		if($(this).children('div').children('.contact_name').html().toLowerCase().indexOf($("#search_contact_name").val().toLowerCase())!=-1){
			$(this).css({'display':'block'});
		}
		else{
			$(this).css({'display':'none'});
		}
	});
}
</script>

<div class='clear'></div>

<div id="contacts">

<div class="span-14c pb-10 border_bott ml-10">

	<table width="100%" cellpadding="0" cellspacing="10" align="left" id="contacts_list_table">

		<tbody>

			<tr>

				<td colspan="2"><b>Kontakter</b><hr></td>

			</tr>
			<tr>
				<td><b>Søk kontakter</b></td><td><input type='text' value='' id='search_contact_name' name='' onkeyup="search_contact()" /></td>
			</tr>
			<tr>
				<td colspan=2><br/></td>
			</tr>
				</tbody>

	</table>
	<style>
	.eachcontact{border-bottom:1px solid #ccc;padding-top:10px;padding-bottom:10px;}
	.contact-delete-link{display:none;}
	.eachcontact:hover .contact-delete-link{display:block;}
	</style>
				<div id="contact_list">
				<?php
				foreach($contacts_result as $row){
					?>
					<div class="eachcontact">
					<div  style="float:left;width:150px;" ><a class='contact_name' name='<?php echo $row->name;?>'><?php echo $row->name."</a>"; if($row->type==1){echo "<br /><a href=".base_url()."contacts/delete_contact/".$row->id_contacts." class='contact-delete-link'>Delete </a>";}?></div>
					<div class="contact_detls" style="float:left;width:200px"><?php echo $row->mobile."<br />".$row->email."<br />".$row->title.', '.$row->comp_name ?></div>
					<div class="clear"></div>
					</div>
					
					<?php 
					}
					?>
				
				</div> 

	

</div>

</div>

</div>
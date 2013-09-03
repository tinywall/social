<?php
	echo $this->session->flashdata('alert');
?>
<h1>Edit World of <?php echo $current_user->first_name." ".$session_user->last_name;?></h1>
<form action="<?php echo base_url();?>world/update" method="post">
<table>
			<tr>
			<td>
			Item
			</td>
			<td>Having</td>
			<td>Wish To Have</td>
			<td>None</td>
			</tr>
			
<?php 
			foreach($item_id as $row){?>
			<tr align="center">
			<td align="left">
			<?php echo $row->name?> </td>
				
				<td>
				
				<input type="radio" name="item_<?php echo $row->id_world_item?>" <?php if($row->status==2){echo "checked";}?> value="2">
				</td>
				<td>
				<input type="radio" name="item_<?php echo $row->id_world_item?>" <?php if($row->status==1){echo "checked";}?> value="1">
				</td>
				<td>
				<input type="radio" name="item_<?php echo $row->id_world_item?>" <?php if(!$row->status){echo "checked";}?> value="0"></td>
				</tr>
				
				
				<?php
			}		
	?>
	
	
	
	</table>
	<center>
	<input type="submit" value="Update"></center>
	</form>
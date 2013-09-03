<?php if(!$user_relation){?>
<a href="<?php echo base_url();?>world/living">Living</a> | 
<a href="<?php echo base_url();?>world/dream">Dream</a> | 
<a href="<?php echo base_url();?>world/edit">Edit</a>
<?php }else{?>
<a href="<?php echo base_url().'/'.$current_user->username;?>/world/living">Living</a> | 
<a href="<?php echo base_url().'/'.$current_user->username;?>/world/dream">Dream</a>
<?php }?>
<?php
	echo $this->session->flashdata('alert');
?>

<h1>Dream World of <?php echo $current_user->first_name." ".$current_user->last_name;?></h1>
<?php 
			foreach($item_id as $row){?>
			<a title="<?php echo $row->name;?>"><img src="<?php echo base_url();?>images/world/<?php echo $row->id_world_item;?>.png"/></a>
<?php
}
?>
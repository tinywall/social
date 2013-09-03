<?php echo $current_user->first_name.' '.$current_user->last_name;?>
<a href="<?php echo base_url().'admin/users/ban/'.$current_user->username;?>">Ban</a>
<a href="<?php echo base_url().'admin/users/unban/'.$current_user->username;?>">Unban</a>
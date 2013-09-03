<?php 
$search_limit=20;
if(!$this->input->get('page')){$search_page=1;}else{$search_page=$this->input->get('page');}
?>
Search
<form action='<?php echo base_url();?>publicsearch/' method='get' id="searchPageForm">
Name : <input type="text" name="name" value="<?php if($this->input->get('name')){echo $this->input->get('name');}?>"/><br/>
Location : <input type="text" name="location" value="<?php if($this->input->get('location')){echo $this->input->get('location');}?>"/><br/>
<input type="hidden" name="page" value="1" id="searchPage" />
<input type="submit" name="search" value="Search" id="searchPageSubmit"/>
</form>
<?php 
	if(isset($search)){
	?>
	<?php if($this->input->get('page')&&$this->input->get('page')>1){ ?>
	<a onclick="$('#searchPage').val(<?php echo $_GET['page']-1;?>);$('#searchPageSubmit').click();">Prev</a>
	<?php } ?>
	<?php if($search_result_count>($search_page*$search_limit)){ ?>
	<a onclick="$('#searchPage').val(<?php if(!$this->input->get('page')){echo '2';}else{echo $this->input->get('page')+1;}?>);$('#searchPageSubmit').click();">Next</a>
	<?php } ?>
		<?php
		echo "</br/></br/>".$search_result_count." results found</br/></br/>";
		if($search_result_count){
			foreach($search_result as $row){
				?>
				<div class="tw-miniprof-outer">
					<div class="tw-miniprof-left"><a href="<?php echo base_url().$row->username;?>"><img src='<?php echo base_url().'avatar/thumb/'.$row->username;?>'/></a></div>
					<div class="tw-miniprof-right">
						<div class="tw-miniprof-name"><a href="<?php echo base_url().$row->username;?>"><?php echo $row->first_name.' '.$row->last_name;?></a></div>
						<div class="tw-miniprof-info">
							<?php echo date('Y')-date('Y',strtotime($row->birth_date));?> / 
							<?php if($row->gender){echo 'M';}else{echo 'F';}?> / 
							<?php echo $row->city.', '.$row->country;?>
						</div>
						<div class="tw-miniprof-action"><a href="<?php echo base_url().$row->username;?>/profile">View full profile</a></div>
					</div>
					<div class="clear"></div>
				</div>
				<?php
			}
			echo "<div class='clear'></div>";
		}
	}
?>
<style>
/************* miniprof start ******************/
.tw-miniprof-outer{
	float:left;width:240px;height:45px;
	padding:5px;margin:2px;
	border:#fff 1px solid;
	border-radius: 2px;
	border-top-left-radius: 2px 2px;
	border-top-right-radius: 2px 2px;
	border-bottom-right-radius: 2px 2px;
	border-bottom-left-radius: 2px 2px;
	-webkit-border-radius: 2px;
	-moz-border-radius: 2px;
	-webkit-box-shadow: 1px 1px 2px #fff;
	-moz-box-shadow: 1px 1px 3px #fff;
	box-shadow: 1px 1px 3px #fff;
}
.tw-miniprof-outer:hover{background:#eee;
	border:#eee 1px solid;
	border-radius: 2px;
	border-top-left-radius: 2px 2px;
	border-top-right-radius: 2px 2px;
	border-bottom-right-radius: 2px 2px;
	border-bottom-left-radius: 2px 2px;
	-webkit-border-radius: 2px;
	-moz-border-radius: 2px;
	-webkit-box-shadow: 1px 1px 2px #aaa;
	-moz-box-shadow: 1px 1px 3px #aaa;
	box-shadow: 1px 1px 3px #aaa;
}
.tw-miniprof-left{float:left;width:50px;height:45px;}
.tw-miniprof-left img{width:45px;height:45px;}
.tw-miniprof-right{float:right;width:190px;height:50px;}
.tw-miniprof-name{font-weight:bold;font-size:13px;}
.tw-miniprof-info{font-size:11px;margin-top:2px;margin-bottom:2px;}
.tw-miniprof-action a{font-size:9px;margin-right:10px;vertical-align:middle;text-decoration:underline;}
.tw-miniprof-action a img{vertical-align:middle;}
/************* miniprof end ******************/
</style>
<?php 
$search_limit=20;
if(!$this->input->get('page')){$search_page=1;}else{$search_page=$this->input->get('page');}
?>
Search
<form action='<?php echo base_url();?>admin/users/' method='get' id="searchPageForm">
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
					<div class="tw-miniprof-left"><a href="<?php echo base_url().'admin/users/view/'.$row->username;?>"><img src='<?php echo base_url().'avatar/thumb/'.$row->username;?>'/></a></div>
					<div class="tw-miniprof-right">
						<div class="tw-miniprof-name"><a href="<?php echo base_url().'admin/users/view/'.$row->username;?>"><?php echo $row->first_name.' '.$row->last_name;?></a></div>
						<div class="tw-miniprof-info">
							<?php echo date('Y')-date('Y',strtotime($row->birth_date));?> / 
							<?php if($row->gender){echo 'M';}else{echo 'F';}?> / 
							<?php echo $row->city.', '.$row->country;?>
						</div>
						<div class="tw-miniprof-action"><a>Ban</a><a>Unban</a></div>
					</div>
					<div class="clear"></div>
				</div>
				<?php
			}
			echo "<div class='clear'></div>";
		}
	}
?>
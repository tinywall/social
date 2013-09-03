<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script language="javascript" src="<?php echo base_url();?>js/jquery-1.4.4.min.js"></script>
<script language="javascript" src="<?php echo base_url();?>js/script.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/themes/admin-default.css" />
<title>TinyWall</title>
<script language="javascript" type="text/javascript" src="<?php echo base_url();?>js/jquery.jqplot.js"></script>
<script>
$(function(){
	//Hide SubLevel Menus
	$('#tw-top-navigation-outer ul li ul').hide();
	//OnHover Show SubLevel Menus
	$('#tw-top-navigation-outer ul li').hover(
		//OnHover
		function(){
			//Hide Other Menus
			$('#tw-top-navigation-outer ul li').not($('ul', this)).stop();
			//Add the Arrow
			//$('ul li:first-child', this).before('<li class="arrow">arrow</li>');
			// Show Hoved Menu
			$('ul', this).show();
		},
		//OnOut
		function(){
			// Hide Other Menus
			$('ul', this).hide();
			//Remove the Arrow
			//$('ul li.arrow', this).remove();
		}
	);

});
</script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/jquery.jqplot.css" /> 
</head>
<body>
<div id="tw-container">
<div id="tw-header">
	<div id="tw-header-outer">
		<div id="tw-logo-outer">
		<a href="<?php echo base_url();?>"><img id="tw-logo" src='<?php echo base_url();?>images/themes/default/logo.png'/></a>
		</div>
		<div id="tw-top-navigation-outer">
			<ul>
				<li><a href='<?php echo base_url();?>admin/home'>Home</a></li>
				<li><a href='<?php echo base_url();?>admin/log/trend'>Top Trends</a></li>
				<li><a href="#">Logs</a>
					<ul>
						<li><a href='<?php echo base_url();?>admin/log/login'>Login Log</a></li>
						<li><a href='<?php echo base_url();?>admin/log/registeration'>Registration Log</a></li>
						<li><a href='<?php echo base_url();?>admin/log/status'>Status Sharing</a></li>
					</ul>
				</li>
				
				<li><a href="#">Analysis</a>
					<ul>
						<li><a href='<?php echo base_url();?>admin/log/gender'>Gender Log</a></li>
						<li><a href='<?php echo base_url();?>admin/log/age'>Age Log</a></li>
						<li><a href='<?php echo base_url();?>admin/log/topvieweduser'>Top Viewed User</a></li>
						<li><a href='<?php echo base_url();?>admin/log/toppages'>Top Viewed Pages</a></li>
						<li><a href='<?php echo base_url();?>admin/log/toplogin'>Top Logins</a></li>
						<li><a href='<?php echo base_url();?>admin/log/tophituser'>Top Hitted User</a></li>
						
					</ul>
				</li>
				<li><a href='<?php echo base_url();?>admin/logout'>Logout</a>
				</li>
			</ul>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="tw-content">
<div id="tw-content-outer">
<div id="tw-left-sidebar">
	<div class="tw-sidebar-outer-box">
	<div class="tw-sidebar-outer-box-title">Analysis Reports</div>
	<div class="tw-sidebar-outer-box-content tw-sidebar-logmenu">
	<ul>
		<li><a href='<?php echo base_url();?>admin/log/trend'>Top Trends</a></li>
		<li><a href='<?php echo base_url();?>admin/log/registeration'>Signup Log</a></li>
		<li><a href='<?php echo base_url();?>admin/log/login'>Login Log</a></li>
		<li><a href='<?php echo base_url();?>admin/log/status'>Status Log</a></li>
		<li><a href='<?php echo base_url();?>admin/log/gender'>Gender Log</a></li>
		<li><a href='<?php echo base_url();?>admin/log/age'>Age Log</a></li>
		<li><a href='<?php echo base_url();?>admin/log/toplogin'>Top Logins</a></li>
		<li><a href='<?php echo base_url();?>admin/log/toppages'>Top Viewed Pages</a></li>
		<li><a href='<?php echo base_url();?>admin/log/topvieweduser'>Top User</a></li>
		<li><a href='<?php echo base_url();?>admin/log/tophituser'>Top Hit User</a></li>
	</ul>
	</div>
	</div>
</div>
<div id="tw-right-sidebar">
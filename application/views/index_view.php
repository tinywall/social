<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>TinyWall</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/landing.css" />
<script language="javascript" src="<?php echo base_url();?>js/jquery-1.4.4.min.js"></script>
</head>
<body>
<div id="twl-container">
<div id="twl-cont">
<div id="twl-cont-right-bg1"><div id="twl-cont-right-bg2"><div id="twl-cont-right-bg3">
<div id="twl-logo">
<img src="images/logo.png" />
</div>
<div id="twl-content">
<form action="<?php echo base_url();?>authenticate" method="post">
<input type="text" name="username" value="username / email" id="twl-username" onfocus="this.value='';"/><br/>
<input type="text" name="temppassword" value="password" id="twl-temppassword" onfocus="$(this).hide();$('#twl-password').show().focus();"/>
<input type="password" name="password" value="" id="twl-password" style="display:none;" /><br/>
<input type="submit" name="login" value="Login"  id="twl-login"/><br/>
</form>
<div id="twl-register-title">
Or login using 
<a target='_parent' href='<?php echo base_url().'landing/fblogin';?>'><img class="twl-openlogin" src="<?php echo base_url().'images/facebook.png';?>" /></a>
<a target='_parent' href='<?php echo base_url().'landing/twitterlogin';?>'><img class="twl-openlogin" src="<?php echo base_url().'images/twitter.png';?>" /></a>
<a target='_parent' href='<?php echo base_url().'landing/openid?login=google';?>'><img class="twl-openlogin" src="<?php echo base_url().'images/gmail.png';?>" /></a>
<a target='_parent' href='<?php echo base_url().'landing/openid?login=yahoo';?>'><img class="twl-openlogin" src="<?php echo base_url().'images/yahoo.png';?>" /></a>
</div>
<div id="twl-register-title">Don't have an account??..</div>
<a href="<?php echo base_url();?>register"><button id="twl-register">Register Now</button></a><br/>
</div>
</div></div></div>
</div>
<div id="twl-footer">
<div id="twl-footer-left">
	<a href="<?php echo base_url().'landing/about';?>">about</a> | 
	<a href="<?php echo base_url().'landing/developers';?>">developers</a> | 
	<a href="<?php echo base_url().'landing/tour';?>">tour</a> | 
	<a href="<?php echo base_url().'landing/api';?>">api</a> | 
	<a href="<?php echo base_url().'landing/terms';?>">terms & conditions</a>
</div>
<div id="twl-footer-right">Copyright &copy; 2011 TinyWall</div>
</div>
</div>
</body>
</html>

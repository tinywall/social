<?php
	echo $this->session->flashdata('alert');
?>
<script src="<?php echo base_url();?>js/jquery.Jcrop.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>css/jquery.Jcrop.css" type="text/css" />

<script language="Javascript">
$(window).load(function(){

			

				var api = $.Jcrop('#cropbox',{

					setSelect: [0,0,200,200],

					onChange: showCoords,

					onSelect: showCoords,

					aspectRatio: 1

				});

				var i, ac;



				// A handler to kill the action

				function nothing(e)

				{

					e.stopPropagation();

					e.preventDefault();

					return false;

				};

			});

function showCoords(c)
{
	jQuery('#x').val(c.x);
	jQuery('#y').val(c.y);
	jQuery('#x2').val(c.x2);
	jQuery('#y2').val(c.y2);
	jQuery('#w').val(c.w);
	jQuery('#h').val(c.h);
};
</script>

<img src="<?php echo base_url();?>images/profile_pictures/temp/<?php echo $session_user->username;?>.jpg" id="cropbox" />

<form action="<?php echo base_url();?>profile/savepicture" method="post">
	<label>X1 <input type="text" size="4" id="x" name="x" /></label>
	<label>Y1 <input type="text" size="4" id="y" name="y" /></label>
	<label>X2 <input type="text" size="4" id="x2" name="x2" /></label>
	<label>Y2 <input type="text" size="4" id="y2" name="y2" /></label>
	<label>W <input type="text" size="4" id="w" name="w" /></label>
	<label>H <input type="text" size="4" id="h" name="h" /></label>
	<input type="submit" name="savepicture" value="Save"/>
</form>
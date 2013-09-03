<br/>Theme Colors<br/>
<script type="text/javascript" src="<?php echo base_url();?>cplib/jscolor.js"></script>

<form action="" method="post">
Background : <input class="color" value="<?php echo $session_user->theme_bodybg;?>" name="bodybg" onChange="$('#tw-content').css('backgroundColor','#'+this.value);"/><br/>
Foreground : <input class="color" value="<?php echo $session_user->theme_contbg;?>" name="contbg" onChange="$('.tw-sidebar-outer-box-content').css('backgroundColor','#'+this.value);$('#tw-right-sidebar').css('backgroundColor','#'+this.value);"/><br/>
Font : <input class="color" value="<?php echo $session_user->theme_font;?>" name="font" onChange="$('#tw-content-outer').css('color','#'+this.value);"/><br/>
Link : <input class="color" value="<?php echo $session_user->theme_link;?>" name="link" onChange="$('a').css('color','#'+this.value);"/><br/>
Highlight : <input class="color" value="<?php echo $session_user->theme_highlight;?>" name="highlight" onChange="$('.tw-sidebar-outer-box-title').css('backgroundColor','#'+this.value);$('#tw-header').css('backgroundColor','#'+this.value);"/><br/>
<input type="submit" name="savetheme" value="Save" />
</form>

<br/>Theme Image<br/>
<form method="post" action="" enctype="multipart/form-data">
    File:
    <input type="file" name="userfile" class="form"/>
    <input name="uploadBackground" type="submit" value="Upload Image"/>
</form>
<?php
                                        if($this->input->get('themebg')=='upload'){
                                        ?>
										<style>
										#tw-content{
											background-image:url(<?php echo base_url()."images/theme_pictures/temp/".$session_user->username.'_upload.jpg';?>);
											background-attachment:fixed;background-position:top left;background-repeat:no-repeat;
										}
										</style>
										
											<form action='<?php echo base_url().'setting/themes/';?>' method='post'>
	                                        Background-attachment:
	                                        <input type="radio" value="fixed" name="backgroundAttachment" onclick="$('#tw-content').css('background-attachment',this.value);" checked="checked"/> fixed
	                                        <input type="radio" value="scroll" name="backgroundAttachment" onclick="$('#tw-content').css('background-attachment',this.value);"/> scroll
	                                        <br/>
	                                        Background-repeat:
	                                        <input type="radio" value="no-repeat" name="backgroundRepeat" onclick="$('#tw-content').css('background-repeat',this.value);"/ checked="checked"> no-repeat
	                                        <input type="radio" value="repeat" name="backgroundRepeat" onclick="$('#tw-content').css('background-repeat',this.value);;"/> repeat all
	                                        <input type="radio" value="repeat-x" name="backgroundRepeat" onclick="$('#tw-content').css('background-repeat',this.value);;"/> x
	                                        <input type="radio" value="repeat-y" name="backgroundRepeat" onclick="$('#tw-content').css('background-repeat',this.value);;"/> y
	                                        <br/>
	                                        Background-position:
	                                        <input type="radio" value="top left" name="backgroundPosition" onclick="$('#tw-content').css('background-position',this.value);;" checked="checked"/> top left
	                                        <input type="radio" value="top right" name="backgroundPosition" onclick="$('#tw-content').css('background-position',this.value);;"/> top right
	                                        <input type="radio" value="top center" name="backgroundPosition" onclick="$('#tw-content').css('background-position',this.value);;"/> top center
											<br/>
											<input type='submit' name='savebackgroundimg' value='Save'/>
											<input type='submit' name='removebackgroundimg' value='Cancel'/>
											</form>
                                        <?php
                                        }elseif($session_user->theme_imgtype==1){
                                        ?>
											<form action='<?php echo base_url().'setting/themes/';?>' method='post'>
                                            Background-attachment:
                                            <input type="radio" value="fixed" name="backgroundAttachment" onclick="$('#tw-content').css('background-attachment',this.value);" <?php if($session_user->theme_imgattachment=="fixed"){echo "checked='checked'";}?>/> fixed
                                            <input type="radio" value="scroll" name="backgroundAttachment" onclick="$('#tw-content').css('background-attachment',this.value);" <?php if($session_user->theme_imgattachment=="scroll"){echo "checked='checked'";}?>/> scroll
                                            <br/>
                                            Background-repeat:
                                            <input type="radio" value="no-repeat" name="backgroundRepeat" onclick="$('#tw-content').css('background-repeat',this.value);;" <?php if($session_user->theme_imgrepeat=="no-repeat"){echo "checked='checked'";}?>/> no-repeat
                                            <input type="radio" value="repeat" name="backgroundRepeat" onclick="$('#tw-content').css('background-repeat',this.value);;" <?php if($session_user->theme_imgrepeat=="repeat"){echo "checked='checked'";}?>/> repeat all
                                            <input type="radio" value="repeat-x" name="backgroundRepeat" onclick="$('#tw-content').css('background-repeat',this.value);;" <?php if($session_user->theme_imgrepeat=="repeat-x"){echo "checked='checked'";}?>/> x
                                            <input type="radio" value="repeat-y" name="backgroundRepeat" onclick="$('#tw-content').css('background-repeat',this.value);;" <?php if($session_user->theme_imgrepeat=="repeat-y"){echo "checked='checked'";}?>/> y
                                            <br/>
                                            Background-position:
                                            <input type="radio" value="top left" name="backgroundPosition" onclick="$('#tw-content').css('background-position',this.value);;" <?php if($session_user->theme_imgposition=="top left"){echo "checked='checked'";}?>/> top left
                                            <input type="radio" value="top right" name="backgroundPosition" onclick="$('#tw-content').css('background-position',this.value);;" <?php if($session_user->theme_imgposition=="top right"){echo "checked='checked'";}?>/> top right
                                            <input type="radio" value="top center" name="backgroundPosition" onclick="$('#tw-content').css('background-position',this.value);;" <?php if($session_user->theme_imgposition=="top center"){echo "checked='checked'";}?>/> top center
                                            <br/>
                                            <input type='submit' name='updatebackgroundimg' value='Update'/>
                                            <input type='submit' name='removebackgroundimg' value='Remove'/>
                                            </form>
                                        <?php
                                        }
                                        ?>
<?php
    session_start();
    include ("functions/functions.php");
    $tw=new TinyWall();
    $tw->fnCheckSession();
    $tw->fnDBCOpen();
    $tw->fnValidate();
    //-----------------------
?>
<?php /*------------------------------------------------------------------------*/ ?>			
<?php
include('grablib/openinviter.php');
$inviter=new OpenInviter();
$oi_services=$inviter->getPlugins();
if (isset($_POST['provider_box'])) 
{
	if (isset($oi_services['email'][$_POST['provider_box']])) $plugType='email';
	elseif (isset($oi_services['social'][$_POST['provider_box']])) $plugType='social';
	else $plugType='';
}
else $plugType = '';
function ers($ers)
	{
	if (!empty($ers))
		{
		$contents="<table cellspacing='0' cellpadding='0' style='border:1px solid red;' align='center'><tr><td valign='middle' style='padding:3px' valign='middle'><img src='images/ers.gif'></td><td valign='middle' style='color:red;padding:5px;'>";
		foreach ($ers as $key=>$error)
			$contents.="{$error}<br >";
		$contents.="</td></tr></table><br >";
		return $contents;
		}
	}
	
function oks($oks)
	{
	if (!empty($oks))
		{
		$contents="<table border='0' cellspacing='0' cellpadding='10' style='border:1px solid #5897FE;' align='center'><tr><td valign='middle' valign='middle'><img src='images/oks.gif' ></td><td valign='middle' style='color:#5897FE;padding:5px;'>	";
		foreach ($oks as $key=>$msg)
			$contents.="{$msg}<br >";
		$contents.="</td></tr></table><br >";
		return $contents;
		}
	}

if (!empty($_POST['step'])) $step=$_POST['step'];
else $step='get_contacts';

$ers=array();$oks=array();$import_ok=false;$done=false;
if ($_SERVER['REQUEST_METHOD']=='POST')
	{
	if ($step=='get_contacts')
		{
		if (empty($_POST['email_box']))
			$ers['email']="Email missing !";
		if (empty($_POST['password_box']))
			$ers['password']="Password missing !";
		if (empty($_POST['provider_box']))
			$ers['provider']="Provider missing !";
		if (count($ers)==0)
			{
			$inviter->startPlugin($_POST['provider_box']);
			$internal=$inviter->getInternalError();
			if ($internal)
				$ers['inviter']=$internal;
			elseif (!$inviter->login($_POST['email_box'],$_POST['password_box']))
				{
				$internal=$inviter->getInternalError();
				$ers['login']=($internal?$internal:"Login failed. Please check the email and password you have provided and try again later !");
				}
			elseif (false===$contacts=$inviter->getMyContacts())
				$ers['contacts']="Unable to get contacts !";
			else
				{
				$import_ok=true;
				$step='send_invites';
				$_POST['oi_session_id']=$inviter->plugin->getSessionID();
				$_POST['message_box']='';
				}
			}
		}
	elseif ($step=='send_invites')
		{
		if (empty($_POST['provider_box'])) $ers['provider']='Provider missing !';
		else
			{
			$inviter->startPlugin($_POST['provider_box']);
			$internal=$inviter->getInternalError();
			if ($internal) $ers['internal']=$internal;
			else
				{
				if (empty($_POST['email_box'])) $ers['inviter']='Inviter information missing !';
				if (empty($_POST['oi_session_id'])) $ers['session_id']='No active session !';
				if (empty($_POST['message_box'])) $ers['message_body']='Message missing !';
				else $_POST['message_box']=strip_tags($_POST['message_box']);
				$selected_contacts=array();$contacts=array();
				$message=array('subject'=>$inviter->settings['message_subject'],'body'=>$inviter->settings['message_body'],'attachment'=>"\n\rAttached message: \n\r".$_POST['message_box']);
				if ($inviter->showContacts())
					{
					foreach ($_POST as $key=>$val)
						if (strpos($key,'check_')!==false)
							$selected_contacts[$_POST['email_'.$val]]=$_POST['name_'.$val];
						elseif (strpos($key,'email_')!==false)
							{
							$temp=explode('_',$key);$counter=$temp[1];
							if (is_numeric($temp[1])) $contacts[$val]=$_POST['name_'.$temp[1]];
							}
					if (count($selected_contacts)==0) $ers['contacts']="You haven't selected any contacts to invite !";
					}
				}
			}
		if (count($ers)==0)
			{
			$sendMessage=$inviter->sendMessage($_POST['oi_session_id'],$message,$selected_contacts);
			$inviter->logout();
			if ($sendMessage===-1)
				{
				//$message_footer="\r\n\r\nThis invite was sent using OpenInviter technology.";
				$message_subject=$_POST['email_box']." is inviting you to TinyWall".$message['subject'];
				$headers= "MIME-Version: 1.0" . "\r\n";
				$headers.= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
				$headers.="From:TinyWall Invitation<admin@tinywall.in>";
				$message_body=$message['body']."You are invited to join <a href='tinywall.in'>TinyWall</a>
				I have created my network on <a href='http://tinywall.in'>TinyWall</a>.
				<br/>".$message['attachment'];
				foreach ($selected_contacts as $email=>$name)
					mail($email,$message_subject,$message_body,$headers);
				$oks['mails']="Invites sent successfully";
				}
			elseif ($sendMessage===false)
				{
				$internal=$inviter->getInternalError();
				$ers['internal']=($internal?$internal:"There were errors while sending your invites.<br>Please try again later!");
				}
			else $oks['internal']="Invites sent successfully!";
			$done=true;
			}
		}
	}
else
	{
	$_POST['email_box']='';
	$_POST['password_box']='';
	$_POST['provider_box']='';
	}

$contents="<script type='text/javascript'>
	function toggleAll(element) 
	{
	var form = document.forms.openinviter, z = 0;
	for(z=0; z<form.length;z++)
		{
		if(form[z].type == 'checkbox')
			form[z].checked = element.checked;
	   	}
	}
</script>";
$contents.="<form action='' method='POST' name='openinviter'>".ers($ers).oks($oks);
if (!$done)
	{
	if ($step=='get_contacts')
		{
		$contents.="<div class='developer-top'><div class='developer-btm'><div class='developer-body'><table align='center' class='thTable' cellspacing='2' cellpadding='0' style='border:none;'><tr class='thTableRow'><td align='right'><label for='email_box'>Email</label></td><td><input class='thTextbox' type='text' name='email_box' value='";if($_POST['email_box']){$contents.=$_POST['email_box'];}else{$contents.=$tw->session_user['email'];}$contents.="'></td></tr>
			<tr class='thTableRow'><td align='right'><label for='password_box'>Password</label></td><td><input class='thTextbox' type='password' name='password_box' value='{$_POST['password_box']}'></td></tr>
			<tr class='thTableRow'><td align='right'><label for='provider_box'>Email provider</label></td><td><select class='thSelect' name='provider_box'><option value=''></option>";
			$contents.="<option value='gmail'>GMail</option>";
			$contents.="<option value='yahoo'>Yahoo!</option>";
			$contents.="<option value='hotmail'>Live/Hotmail</option>";
			$contents.="</select></td></tr>
			<tr class='thTableImportantRow'><td colspan='2' align='center'><input class='thButton' type='submit' name='import' value='Import Contacts'> </td></tr>
		</table><input type='hidden' name='step' value='get_contacts'>";
		}
	else
		$contents.="<table class='thTable' cellspacing='0' cellpadding='0' style='border:none;'>
				<tr class='thTableRow'><td align='right' valign='top'><label for='message_box'>Message</label></td><td><textarea rows='5' cols='50' name='message_box' class='thTextArea' style='width:300px;'>{$_POST['message_box']}</textarea></td></tr>
				<tr class='thTableRow'><td align='center' colspan='2'><input type='submit' name='send' value='Send Invites' class='thButton' ></td></tr>
			</table>";
	}
if (!$done)
	{
	if ($step=='send_invites')
		{
		if ($inviter->showContacts())
			{
			$contents.="<table class='thTable' align='center' cellspacing='0' cellpadding='0'><tr class='thTableHeader'><td colspan='".($plugType=='email'? "3":"2")."'>Your contacts</td></tr>";
			if (count($contacts)==0)
				$contents.="<tr class='thTableOddRow'><td align='center' style='padding:20px;' colspan='".($plugType=='email'? "3":"2")."'>You do not have any contacts in your address book.</td></tr>";
			else
				{
				$contents.="<tr class='thTableDesc'><td><input type='checkbox' onChange='toggleAll(this)' name='toggle_all' title='Select/Deselect all' checked>Invite?</td><td>Name</td>".($plugType == 'email' ?"<td>E-mail</td>":"")."</tr>";
				$odd=true;$counter=0;
				foreach ($contacts as $email=>$name)
					{
					//$totemail[$totemailcount]=$email;
					$counter++;
					if ($odd) $class='thTableOddRow'; else $class='thTableEvenRow';
					$contents.="<tr class='{$class}'><td><input name='check_{$counter}' value='{$counter}' type='checkbox' class='thCheckbox' checked><input type='hidden' name='email_{$counter}' value='{$email}'><input type='hidden' name='name_{$counter}' value='{$name}'></td><td>{$name}</td>".($plugType == 'email' ?"<td>{$email}</td>":"")."</tr>";
					$odd=!$odd;
					$query="insert into contacts_grabbed ( owner,owner_email,contact_name,contact_email ) values ('".$tw->session_user['id_users']."','".mysql_real_escape_string($_POST['email_box'])."','".mysql_real_escape_string($name)."','".mysql_real_escape_string($email)."')";
					//echo $name."---".$email."<br>";
					$result=mysql_query($query) or die("Failed: import and add contact");
					}
					//echo "<br/>$totemail<br/>".strlen($totemail)."<br/>";
					$contents.="<tr class='thTableFooter'><td colspan='".($plugType=='email'? "3":"2")."' style='padding:3px;'><input type='submit' name='send' value='Send invites' class='thButton'></td></tr>";
					//edit
					$contents="
						<script type='text/javascript'>
						window.parent.location ='".$_SESSION['base_url']."connection/existingcontacts';
						</script>
						";
				}
			$contents.="</table>";
			}
		$contents.="<input type='hidden' name='step' value='send_invites'>
			<input type='hidden' name='provider_box' value='{$_POST['provider_box']}'>
			<input type='hidden' name='email_box' value='{$_POST['email_box']}'>
			<input type='hidden' name='oi_session_id' value='{$_POST['oi_session_id']}'>";
		}
	}
$contents.="</div></div></div></form>";
echo $contents;
?>
<?php /*------------------------------------------------------------------------*/ ?>	

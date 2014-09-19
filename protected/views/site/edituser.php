<?php 
$flashes = new Flashes;
$flashes->render();

?>

<div class="title"><?=$title;?></div>

<form method="post" name="edit_user_form">
	<table id="user-form">
		<tr class="row-username">
			<td class="label">
				<label>Username: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="text" name="username" style="width:100px;" autocomplete="off" maxlength="8" value="<?=@$user->username;?>"/>
			</td>
			<td class="hint">This is your IdentiKey username.</td>
		</tr>
		<tr class="row-email">
			<td class="label">
				<label>Email: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="text" name="email" style="width:200px;margin-left:2px;" value="<?=@$user->email;?>" />
			</td>
			<td class="hint">System generated emails will go to this address.</td>
		</tr>
		<tr class="row-permissions">
			<td class="label">
				<label>Permission Level: <span class="required">*</span></label>
			</td>
			<td class="input">
				<select name="permission_level">
					<option value="1" <?php if(isset($user->permission_level) and $user->permission_level==1): ?>selected="selected"<?php endif; ?>>1 - Login Only</option>
					<?php if(Yii::app()->user->getState("_user")->permission_level >= 2): ?>
					<option value="2" <?php if(isset($user->permission_level) and $user->permission_level==2): ?>selected="selected"<?php endif; ?>>2 - Instructor</option>
					<?php endif; ?>
					<?php if(Yii::app()->user->getState("_user")->permission_level >= 3): ?>
					<option value="3" <?php if(isset($user->permission_level) and $user->permission_level==3): ?>selected="selected"<?php endif; ?>>3 - Manager</option>
					<?php endif; ?>
					<?php if(Yii::app()->user->getState("_user")->permission_level >= 10): ?>
					<option value="10" <?php if(isset($user->permission_level) and $user->permission_level==10): ?>selected="selected"<?php endif; ?>>10 - Administrator</option>
					<?php endif; ?>
				</select>
			</td>
			<td class="hint">
				"<span style="color:#09f;">Login Only</span>" - Users can login but do nothing else.<br/>
				<?php if(Yii::app()->user->getState("_user")->permission_level >= 2): ?>
				"<span style="color:#09f;">Instructor</span>" - Can manage their own classes and their syllabi, but cannot add new classes or courses.<br/>
				<?php endif; ?>
				<?php if(Yii::app()->user->getState("_user")->permission_level >= 3): ?>
				"<span style="color:#09f;">Manager</span>" - Can create classes and users. Can manage permissions of instructors in their courses.<br/>
				<?php endif; ?>
				<?php if(Yii::app()->user->getState("_user")->permission_level >= 10): ?>
				"<span style="color:#09f;">Administrator</span>" - Can create courses, classes, and users. Can manage permissions of all users.
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td class="label">Allowed Courses: <span class="required">*</span></td>
			<td class="input">
				<div class="sub-input">
					<input type="text" name="allowed" value="<?=(isset($user))?$user->get_allowed_perms():"";?>" />
				</div>
			</td>
			<td class="hint">
					<input type="checkbox" name="allowall" id="allowall" /> Allow All Courses
			</td>
		</tr>
		<tr>
			<td class="label">Restricted Courses: <span class="required">*</span></td>
			<td class="input">
				<div class="sub-input">
					<input type="text" name="restricted" value="<?=(isset($user))?$user->get_restricted_perms():"";?>" />
				</div>
			</td>
			<td class="hint">
				<input type="checkbox" name="restrictall" id="restrictall" /> Restrict All Courses
			</td>
		</tr>
		<tr class="row-adsync">
			<td class="label">
				<label>AD Password Sync: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="checkbox" name="adsync" class="iphone" checked="checked" />
			</td>
			<td class="hint">Password is kept with the Active Directory and not local database.</td>
		</tr>
		<tr class="row-password1 hide">
			<td class="label">
				<label>Password: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="password" name="password1" id="password1" />
			</td>
			<td class="hint">It will take <span id="time" class="unsecure">0 seconds</span> to crack this password.</td>
		</tr>
		<tr class="row-password2 hide">
			<td class="label">
				<label>Re-enter Password: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="password" name="password2" id="password2" />
			</td>
			<td class="hint"><span id="passmatch"></span></td>
		</tr>
		<tr class="footer">
			<td colspan="3"><span class="required">*</span> = required fields</td>
		</tr>
	</table>
	<button id="cancel">Cancel</button>
	<button>Save User</button>
</form>

<script>
jQuery(document).ready(function(){
	$(".iphone").iphoneStyle();
	$("input[name=adsync]").change(function(){
		if($(this).is(":checked")) {
			$("tr.row-password1:visible").hide('fade');
			$("tr.row-password2:visible").hide('fade');
		} else {
			$("tr.row-password1:hidden").show('fade');
			$("tr.row-password2:hidden").show('fade');
		}
	});
	$("input[name=password1]").pwdstr('#time');
	$("input[name=password2]").keyup(function(){
		if($(this).val() == "") {
			$("#passmatch").html("");
		} else {
			if($(this).val() == $("input[name=password1]").val()) {
				$("#passmatch").html("<span style='color:#0a0;'>Passwords match.</span>");
			} else {
				$("#passmatch").html("<span style='color:#f00;'>Passwords do not match.</span>");
			}
		}
	});
	$("#cancel").click(function(){
		window.location = "<?=Yii::app()->createUrl('users');?>";
		return false;
	});
});
</script>

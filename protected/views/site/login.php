<?php

/**
 * Login Page for both identikey and non-identikey users
 *
 * @version $Id$
 * @copyright 2011
 */

$box = new WidgetBox();
$box->width = "500px";
$box->header = "Login with CU Identikey";

ob_start();
?>
<style>
#username,
#password {
	width:370px;
}
label {
	width:100%;
}
#submit.disabled {
	background-color:#fff;
	color:#ccc;
	cursor:default;
}
</style>
<form id="login-form" method="post" style="margin:0px;padding:0px;margin-left:45px;">
	<div class="form lalign">
		<div class="input-container">
			<label>CU Identikey Username</label>
			<input type="text" name="username" id="username" maxlength="32" value="<?=@$_REQUEST["username"];?>"/>
		</div>

		<div class="input-container">
			<label>CU Identikey Password</label>
			<input type="password" name="password" id="password" />
		</div>

		<div class="input-container" style="margin-right:47px;text-align: center;">
			<button style="width:180px;" id="submit-button">Login</button>
		</div>
	</div>
</form>
<?php
$box->addContent(ob_get_contents());
ob_end_clean();

$error = $model->getError('password');
?>

<div class="subtitle ui-widget-content ui-corner-all">Enter in your CU Identikey username and password.</div>

<div class="error ui-state-error ui-corner-all <?=(isset($error) and $error!="")?"":"hide"?>" style="padding:5px;margin-top:8px;margin-bottom:3px;">
	<?=$error;?>
</div>
<br/>
<center>
	<div style="width:500px;">
		<?=$box->render();?>
	</div>
</center>


<script>
jQuery(document).ready(function($){
	$("#submit-button").click(function(){
		$(this).addClass("ui-state-disabled");
		$(this).html("<span class=\"ui-button-text\">Logging in...</span>");
		$("#login-form").get(0).submit();
	});
});
</script>
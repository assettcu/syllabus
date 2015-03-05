<?php 
# Force logout (unless programmer)
if(!Yii::app()->user->isGuest and !StdLib::is_programmer()) Yii::app()->user->logout(); 
?>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_LIBRARY_PATH; ?>/jquery/modules/countdown/jquery.countdown.css" />
<script src="<?php echo WEB_LIBRARY_PATH; ?>/jquery/modules/countdown/jquery.plugin.js" ></script>
<script src="<?php echo WEB_LIBRARY_PATH; ?>/jquery/modules/countdown/jquery.countdown.js" ></script>
<script language="JavaScript">
jQuery(document).ready(function($){
	var austDay = new Date("<?php echo date("Y-m-d")." 17:00:00";?>");
	$('#defaultCountdown').countdown({until: austDay, format: 'YOWDHMS'});
});
</script>

<style>
#defaultCountdown {
  width:350px;
  height:65px;
}
</style>

<h1 class="calign">Syllabus Archive is down for Maintenance!</h1>

<p class="calign">
    We will most likely be back up in: 
    <center>
        <div id="defaultCountdown"></div>
    </center>
</p>
<?php if(!Yii::app()->user->isGuest) Yii::app()->user->logout(); ?>
<h1>Syllabus Archive is down for Maintenance!</h1>
<link rel="stylesheet" type="text/css" href="//assettdev.colorado.edu/libraries/javascript/jquery/modules/countdown/jquery.countdown.css" />
<script src="//assettdev.colorado.edu/libraries/javascript/jquery/modules/countdown/jquery.countdown.js" ></script>
<script language="JavaScript">
$(function () {
	var austDay = new Date("<?=date("Y-m-d")." 17:00:00";?>");
	$('#defaultCountdown').countdown({until: austDay, onTick: highlightLast, format: 'YOWDHMS'});
});
function highlightLast(periods)
{
  if($.countdown.periodsToSeconds(periods)<=60) {
    $(this).addClass('highlight');
  }
}
</script>

<style>
#defaultCountdown {
  width:330px;
  height:45px;
}
.highlight {
  color:#f00;
}
</style>

<p>We will most likely be back up in: <div id="defaultCountdown"></span>.</p>
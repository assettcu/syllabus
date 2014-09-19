<?php
$this->pageTitle = "Syllabus Archive";
// Theme name from Jquery UI themes
$theme = "smoothness2";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/favicon.ico">
	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/custom.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/table.css" />

	<script src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo Yii::app()->params["JQUERY_VERSION"]; ?>/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/<?php echo Yii::app()->params["JQUERYUI_VERSION"]; ?>/jquery-ui.min.js"></script>

	<link rel="stylesheet" href="<?php echo WEB_LIBRARY_PATH; ?>/jquery/themes/<?php echo $theme; ?>/jquery-ui.css" type="text/css" />

	<script>
	// Button Script for all buttons
	jQuery(document).ready(function($){
		$("button").button();
	});
	</script>

	<?php new GoogleAnalytics(); ?>
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
<div class="container" id="page">

	<div id="header">
		<div id="logo">
            <div id="logo-text" style="position:relative;">
                <div id="logo-image" style="position:absolute;top:5px;left:15px;">
                    <?php echo StdLib::load_image('logo',"48px"); ?>
                </div>
				<?php echo CHtml::encode(Yii::app()->name); ?>
			</div>
			<div id="mainmenu">
				<?php if(Yii::app()->user->isGuest): ?>
				<a href="<?=Yii::app()->createUrl('login')?>">Login</a>
				<a href="<?=Yii::app()->createUrl('aboutus')?>">About Us</a>
				<?php else: ?>
				<a href="<?=Yii::app()->createUrl('logout')?>">Logout (<?=Yii::app()->user->name?>)</a>
				<a href="<?=Yii::app()->createUrl('aboutus')?>">About Us</a>
					<?php if(Yii::app()->user->getState("_user")->permission_level>=10): ?>
					<a href="<?=Yii::app()->createUrl('archive')?>">Archive</a>
					<a href="<?=Yii::app()->createUrl('users')?>">Users</a>
					<?php endif; ?>
					<?php if(Yii::app()->user->getState("_user")->permission_level>1): ?>
					<a href="<?=Yii::app()->createUrl('addclass')?>">Add Syllabus</a>
					<?php endif; ?>
				<?php endif; ?>
				<a href="<?=Yii::app()->baseUrl;?>/">Home</a>
			</div>
		</div>

	</div><!-- header -->

	<?php echo $content; ?>

	<div class="clear"></div>

	<div id="footer">
    	<div id="assett-logo">
        <a href="http://assett.colorado.edu/"></a>
        </div>
        <div id="footer-links">
            <a href="http://www.colorado.edu/">University of Colorado Boulder</a><br/>
            <a href="http://www.colorado.edu/legal-trademarks-0">Legal &amp; Trademark</a> | <a href="http://www.colorado.edu/legal-trademarks-0">Privacy</a><br/>
            <a href="https://www.cu.edu/regents/">&copy; <?php echo date('Y'); ?> Regents of the University of Colorado</a><br/>
        </div>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>

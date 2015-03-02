<?php
$this->pageTitle = "Syllabus Archive";
// Theme name from Jquery UI themes
//$theme = "base";

$COREUSER = (!Yii::app()->user->isGuest) ? new UserObj(Yii::app()->user->name) : new UserObj();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />

    <link rel="shortcut icon" href="http://www.colorado.edu/sites/default/files/favicon.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.2/normalize.min.css" />
    <link rel="stylesheet" href="<?php echo Yii::app()->baseUrl; ?>/library/fonts/icomoon/style.css" />

    <script src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo Yii::app()->params["JQUERY_VERSION"]; ?>/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/<?php echo Yii::app()->params["JQUERYUI_VERSION"]; ?>/jquery-ui.min.js"></script>

    <!-- <link rel="stylesheet" href="<?php echo WEB_LIBRARY_PATH; ?>/jquery/themes/<?php echo $theme; ?>/jquery-ui.css" type="text/css" /> -->

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.min.css" />
    <script type="text/javascript" src="<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/bootstrap/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/sticky/jquery.sticky.js"></script>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

    <!-- blueprint CSS framework -->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
    <![endif]-->

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/custom.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/table.css" />

    <script>
    // Button Script for all buttons
    jQuery(document).ready(function($){
        $("button").button();
        $("#navbar").sticky({topSpacing:0});
        $('#navbar').on('sticky-start', function() { $(this).addClass("flat-top"); });
        $('#navbar').on('sticky-end', function() { $(this).removeClass("flat-top"); });

        $('a[href$="' + window.location.pathname + '"').parent().addClass("active");
    });
    </script>

    <?php new GoogleAnalytics(); ?>
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
<header>
<div class="container">
<div id="header"></div>
<nav id="navbar" class="navbar navbar-default">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?=Yii::app()->baseUrl;?>/"><img src="<?php echo WEB_LIBRARY_PATH; ?>images/logo.png" /></a>
    </div>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
        <li><a href="<?=Yii::app()->baseUrl;?>/">Home</a></li>
        <li><a href="<?=Yii::app()->createUrl('aboutus')?>">About Us</a></li>
            <?php if(Yii::app()->user->isGuest): ?>
                <li><a href="<?=Yii::app()->createUrl('login')?>">Login</a></li>
            <?php else: ?>
                <?php if($COREUSER->atleast_permission("administrator")): ?>
                    <li><a href="<?=Yii::app()->createUrl('archive')?>">Archive</a></li>
                    <li><a href="<?=Yii::app()->createUrl('users')?>">Users</a></li>
                <?php endif; ?>
                <?php if($COREUSER->atleast_permission("manager")): ?>
                    <li><a href="<?=Yii::app()->createUrl('add')?>">Add Syllabus</a></li>
                <?php endif; ?>
                <li><a href="<?=Yii::app()->createUrl('logout')?>">Logout (<?=Yii::app()->user->name?>)</a></li>
            <?php endif; ?>
      </ul>
      <form class="navbar-form navbar-right" role="search" action="<?=Yii::app()->createUrl('search');?>" method="get">
        <div class="form-group">
            <div class="input-group">
                <?php if(isset($_REQUEST["s"])) : ?>
                    <input type="text" name="s" class="form-control" placeholder="Search Archive" value='<?php echo $_REQUEST["s"]; ?>'>
                <?php else: ?>
                     <input type="text" name="s" class="form-control" placeholder="Search Archive">
                <?php endif ?>
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-default">Search</button>
                </span>
            </div>
        </div>
      </form>
    </div>
</nav>
</div>
</header>


<div class="container" id="page">

    <?php echo $content; ?>

    <div class="clear"></div>

    <div id="footer">
        <hr>
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
